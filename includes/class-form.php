<?php
/**
 * Get Quick Quote - Form Sınıfı
 * Plugin versiyonu
 */

if (!defined('ABSPATH')) {
    exit;
}

class PTF_Multi_Step_Form {

    private static $instance = null;

    private $recaptcha_enabled = false;
    private $recaptcha_site_key = '';
    private $recaptcha_secret_key = '';
    private $recaptcha_min_score = 0.5;

    private $blocked_email_domains = array(
        'gmail.com', 'gmail.com.tr', 'outlook.com', 'outlook.com.tr',
        'hotmail.com', 'hotmail.com.tr', 'yahoo.com', 'yahoo.com.tr',
        'live.com', 'live.com.tr', 'yandex.com', 'yandex.com.tr',
        'turk.net', 'icloud.com', 'aol.com', 'mail.com', 'protonmail.com',
        'zoho.com', 'gmx.com', 'gmx.de', 'inbox.com', 'mail.ru',
        'windowslive.com', 'msn.com', 'me.com', 'mac.com'
    );

    private $test_types = null;
    private $categories = null;

    /**
     * Get test types (dynamic - from question management)
     */
    private function get_test_types() {
        if ($this->test_types === null) {
            // First try to get from PTF_Form_Questions class
            if (class_exists('PTF_Form_Questions')) {
                $this->test_types = PTF_Form_Questions::get_test_types();
            }
            // Otherwise try the old Settings API
            elseif (class_exists('PTF_Form_Settings')) {
                $this->test_types = PTF_Form_Settings::get_test_types();
            }

            // If none available, use defaults
            if (empty($this->test_types)) {
                $this->test_types = array(
                    'internal-network' => 'Internal Network Penetration Test',
                    'external-network' => 'External Network / Internet Penetration Test',
                    'web-application' => 'Web Application Penetration Test',
                    'api-security' => 'API Security Test',
                    'mobile-application' => 'Mobile Application Penetration Test',
                    'wireless-network' => 'Wireless Network (Wi-Fi) Penetration Test',
                    'social-engineering' => 'Social Engineering Test',
                    'ddos-simulation' => 'DDoS Resilience Test'
                );
            }
        }
        return $this->test_types;
    }

    /**
     * Get categories (dynamic - from question management)
     */
    private function get_categories() {
        if ($this->categories === null) {
            if (class_exists('PTF_Form_Questions')) {
                $this->categories = PTF_Form_Questions::get_active_categories();
            } else {
                $this->categories = array();
            }
        }
        return $this->categories;
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->init_recaptcha();
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_ptf_submit_form', array($this, 'handle_form_submission'));
        add_action('wp_ajax_nopriv_ptf_submit_form', array($this, 'handle_form_submission'));
        add_action('wp_ajax_ptf_test_webhooks', array(__CLASS__, 'handle_webhook_test'));
        add_action('wp_footer', array($this, 'render_popup_form'));
        add_shortcode('ptf_multistep_form', array($this, 'render_inline_form'));
        add_shortcode('ptf_popup_trigger', array($this, 'render_popup_trigger'));
    }

    private function init_recaptcha() {
        // Get from admin settings
        $site_key = '';
        $secret_key = '';

        if (class_exists('PTF_Form_Settings')) {
            $site_key = PTF_Form_Settings::get_setting('recaptcha_site_key');
            $secret_key = PTF_Form_Settings::get_setting('recaptcha_secret_key');
        }

        // If not in admin settings, check wp-config.php constants
        if (empty($site_key) && defined('PTF_RECAPTCHA_SITE_KEY')) {
            $site_key = PTF_RECAPTCHA_SITE_KEY;
        }
        if (empty($secret_key) && defined('PTF_RECAPTCHA_SECRET_KEY')) {
            $secret_key = PTF_RECAPTCHA_SECRET_KEY;
        }

        $this->recaptcha_site_key = $site_key;
        $this->recaptcha_secret_key = $secret_key;
        $this->recaptcha_enabled = !empty($this->recaptcha_site_key) && !empty($this->recaptcha_secret_key);
    }

    /**
     * Get setting value
     */
    private function get_setting($key, $default = '') {
        if (class_exists('PTF_Form_Settings')) {
            $value = PTF_Form_Settings::get_setting($key);
            // Accept '0' as valid value (important for checkbox)
            if ($value !== '' && $value !== null) {
                return $value;
            }
        }
        return $default;
    }

    public function enqueue_scripts() {
        // CSS
        wp_enqueue_style(
            'ptf-multistep-form',
            PTF_PLUGIN_URL . 'assets/css/form-styles.css',
            array(),
            PTF_VERSION
        );

        // Dynamic colors inline CSS
        $primary = $this->get_setting('primary_color', '#2F7CFF');
        $secondary = $this->get_setting('secondary_color', '#B7FF10');
        $button_text_color = $this->get_setting('button_text_color', '#ffffff');
        $custom_css = $this->generate_color_css($primary, $secondary, $button_text_color);
        wp_add_inline_style('ptf-multistep-form', $custom_css);

        // reCAPTCHA
        if ($this->recaptcha_enabled) {
            wp_enqueue_script(
                'google-recaptcha',
                'https://www.google.com/recaptcha/api.js?render=' . esc_attr($this->recaptcha_site_key),
                array(),
                null,
                true
            );
        }

        // JS
        wp_enqueue_script(
            'ptf-multistep-form',
            PTF_PLUGIN_URL . 'assets/js/form-scripts.js',
            array('jquery'),
            PTF_VERSION,
            true
        );

        // Check if categories have questions
        $has_category_questions = false;
        $categories = $this->get_categories();
        foreach ($categories as $category) {
            if (!empty($category['questions'])) {
                $has_category_questions = true;
                break;
            }
        }

        // Get validation messages from field_labels
        $field_labels = $this->get_setting('field_labels', array());
        $validation_defaults = array(
            'required' => __('This field is required.', 'pentest-quote-form'),
            'email' => __('Please enter a valid email address.', 'pentest-quote-form'),
            'corporate_email' => __('Please enter your corporate email address. Personal email addresses are not accepted.', 'pentest-quote-form'),
            'phone' => __('Please enter a valid phone number.', 'pentest-quote-form'),
            'checkbox_required' => __('You must accept this to continue.', 'pentest-quote-form'),
            'test_type_required' => __('Please select at least one test type.', 'pentest-quote-form'),
            'error' => __('An error occurred. Please try again.', 'pentest-quote-form'),
            'recaptcha_error' => __('reCAPTCHA verification failed. Please try again.', 'pentest-quote-form'),
        );
        $validation = isset($field_labels['validation']) ? wp_parse_args($field_labels['validation'], $validation_defaults) : $validation_defaults;
        $messages_defaults = array(
            'loading' => __('Sending...', 'pentest-quote-form'),
        );
        $messages = isset($field_labels['messages']) ? wp_parse_args($field_labels['messages'], $messages_defaults) : $messages_defaults;

        wp_localize_script('ptf-multistep-form', 'ptfForm', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ptf_form_nonce'),
            'blockedDomains' => $this->blocked_email_domains,
            'hasCategoryQuestions' => $has_category_questions,
            'recaptcha' => array(
                'enabled' => $this->recaptcha_enabled,
                'siteKey' => $this->recaptcha_site_key,
            ),
            'messages' => array(
                'required' => $validation['required'],
                'email' => $validation['email'],
                'corporate_email' => $validation['corporate_email'],
                'phone' => $validation['phone'],
                'checkbox_required' => $validation['checkbox_required'],
                'test_type_required' => $validation['test_type_required'],
                'success' => __('Your form has been submitted successfully!', 'pentest-quote-form'),
                'error' => $validation['error'],
                'sending' => $messages['loading'],
                'recaptcha_error' => $validation['recaptcha_error'],
            )
        ));
    }

    /**
     * Generate dynamic color CSS
     */
    private function generate_color_css($primary, $secondary, $button_text_color = '#ffffff') {
        $primary_dark = $this->adjust_brightness($primary, -30);

        return "
        :root {
            --ptf-primary: {$primary};
            --ptf-primary-dark: {$primary_dark};
            --ptf-secondary: {$secondary};
            --ptf-button-text: {$button_text_color};
        }
        .ptf-progress-step.active .step-number,
        .ptf-progress-step.completed .step-number {
            background: linear-gradient(135deg, {$primary} 0%, {$primary_dark} 100%);
        }
        .ptf-progress-line::after {
            background: linear-gradient(135deg, {$primary} 0%, {$primary_dark} 100%);
        }
        .ptf-checkbox-item:hover {
            border-color: {$primary};
            background: " . $this->hex_to_rgba($primary, 0.05) . ";
        }
        .ptf-checkbox-item input[type='checkbox']:checked + .ptf-checkbox-mark {
            background: linear-gradient(135deg, {$primary} 0%, {$primary_dark} 100%);
            border-color: {$primary};
        }
        .ptf-checkbox-item input[type='checkbox']:checked ~ .ptf-checkbox-label {
            color: {$primary_dark};
        }
        .ptf-form-group input:focus,
        .ptf-form-group select:focus,
        .ptf-form-group textarea:focus {
            border-color: {$primary};
            box-shadow: 0 0 0 3px " . $this->hex_to_rgba($primary, 0.1) . ";
        }
        .ptf-consent-label:hover {
            border-color: {$primary};
        }
        .ptf-consent-label input[type='checkbox']:checked + .ptf-checkbox-mark {
            background: linear-gradient(135deg, {$primary} 0%, {$primary_dark} 100%);
            border-color: {$primary};
        }
        .ptf-consent-text a {
            color: {$primary};
        }
        .ptf-consent-text a:hover {
            color: {$primary_dark};
        }
        .ptf-btn-next,
        .ptf-btn-submit {
            background: linear-gradient(135deg, {$primary} 0%, {$primary_dark} 100%);
            color: {$button_text_color};
        }
        .ptf-btn-next:hover,
        .ptf-btn-submit:hover:not(:disabled) {
            box-shadow: 0 4px 15px " . $this->hex_to_rgba($primary, 0.4) . ";
            color: {$button_text_color};
        }
        .ptf-popup-trigger {
            background: linear-gradient(135deg, {$primary} 0%, {$primary_dark} 100%);
            box-shadow: 0 4px 15px " . $this->hex_to_rgba($primary, 0.3) . ";
            color: {$button_text_color};
        }
        .ptf-popup-trigger:hover {
            box-shadow: 0 8px 25px " . $this->hex_to_rgba($primary, 0.4) . ";
            color: {$button_text_color};
        }
        .ptf-spinner {
            border-top-color: {$primary};
        }
        .ptf-success-icon {
            color: {$secondary};
        }
        ";
    }

    private function adjust_brightness($hex, $steps) {
        $hex = ltrim($hex, '#');
        $r = max(0, min(255, hexdec(substr($hex, 0, 2)) + $steps));
        $g = max(0, min(255, hexdec(substr($hex, 2, 2)) + $steps));
        $b = max(0, min(255, hexdec(substr($hex, 4, 2)) + $steps));
        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }

    private function hex_to_rgba($hex, $alpha) {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "rgba({$r}, {$g}, {$b}, {$alpha})";
    }

    public function handle_form_submission() {
        // Verify nonce first
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ptf_form_nonce')) {
            wp_send_json_error(array('message' => __('Security verification failed.', 'pentest-quote-form')));
            return;
        }

        // Rate limiting - prevent spam
        $user_ip = $this->get_user_ip();
        $rate_limit_key = 'ptf_rate_' . md5($user_ip);
        $submission_count = get_transient($rate_limit_key);

        if ($submission_count !== false && $submission_count >= 5) {
            wp_send_json_error(array('message' => __('Too many submissions. Please try again later.', 'pentest-quote-form')));
            return;
        }

        // Increment submission count (expires in 1 hour)
        set_transient($rate_limit_key, ($submission_count ? $submission_count + 1 : 1), HOUR_IN_SECONDS);

        if ($this->recaptcha_enabled) {
            $recaptcha_token = isset($_POST['recaptcha_token']) ? sanitize_text_field(wp_unslash($_POST['recaptcha_token'])) : '';
            $recaptcha_result = $this->verify_recaptcha($recaptcha_token);
            if (!$recaptcha_result['success']) {
                wp_send_json_error(array('message' => $recaptcha_result['message'], 'recaptcha_failed' => true));
                return;
            }
        }

        $form_data = $this->sanitize_form_data($_POST);
        $errors = $this->validate_form_data($form_data);

        if (!empty($errors)) {
            wp_send_json_error(array('message' => __('Please fix the errors.', 'pentest-quote-form'), 'errors' => $errors));
            return;
        }

        // Get data saving settings
        $save_to_database = $this->get_setting('save_to_database', '1') === '1';
        $send_email_notification = $this->get_setting('send_email_notification', '1') === '1';

        // At least one option must be active
        if (!$save_to_database && !$send_email_notification) {
            // Default to sending email
            $send_email_notification = true;
        }

        $success = false;

        // Save to database (if enabled)
        if ($save_to_database) {
            $saved = $this->save_form_submission($form_data);
            if ($saved) {
                $success = true;
            }
        }

        // Email bildirimi gönder (eğer aktifse)
        if ($send_email_notification) {
            $this->send_notification_email($form_data);
            $success = true;
        }

        // Send to webhooks (if enabled)
        $enable_webhooks = $this->get_setting('enable_webhooks', '0') === '1';
        if ($enable_webhooks) {
            $this->send_to_webhooks($form_data);
        }

        if ($success) {
            $success_msg = $this->get_setting('success_message', __('Your request has been received! We will contact you shortly.', 'pentest-quote-form'));
            wp_send_json_success(array('message' => $success_msg));
        } else {
            wp_send_json_error(array('message' => __('An error occurred while processing the form.', 'pentest-quote-form')));
        }
    }

    private function sanitize_form_data($data) {
        $sanitized = array();

        // Sanitize test_types array
        $sanitized['test_types'] = array();
        if (isset($data['test_types']) && is_array($data['test_types'])) {
            foreach ($data['test_types'] as $type) {
                $sanitized['test_types'][] = sanitize_key(wp_unslash($type));
            }
        }

        // Get dynamic question fields and sanitize
        $categories = $this->get_categories();

        // Process category questions
        foreach ($categories as $category) {
            if (!empty($category['questions'])) {
                foreach ($category['questions'] as $question) {
                    $key = sanitize_key($question['id']);
                    $type = $question['type'];

                    if (isset($data[$key])) {
                        $raw_value = wp_unslash($data[$key]);

                        if ($type === 'number') {
                            $sanitized[$key] = absint($raw_value);
                        } elseif ($type === 'checkbox' && is_array($raw_value)) {
                            $sanitized[$key] = array_map('sanitize_text_field', $raw_value);
                        } elseif ($type === 'textarea') {
                            $sanitized[$key] = sanitize_textarea_field($raw_value);
                        } elseif ($type === 'email') {
                            $sanitized[$key] = sanitize_email($raw_value);
                        } else {
                            $sanitized[$key] = sanitize_text_field($raw_value);
                        }
                    } else {
                        $sanitized[$key] = '';
                    }
                }
            }
        }

        // Contact information - sanitize with wp_unslash
        $sanitized['first_name'] = isset($data['first_name']) ? sanitize_text_field(wp_unslash($data['first_name'])) : '';
        $sanitized['last_name'] = '';
        $sanitized['email'] = isset($data['email']) ? sanitize_email(wp_unslash($data['email'])) : '';
        $sanitized['company'] = isset($data['company']) ? sanitize_text_field(wp_unslash($data['company'])) : '';
        $sanitized['phone'] = isset($data['phone']) ? sanitize_text_field(wp_unslash($data['phone'])) : '';
        $sanitized['kvkk_consent'] = isset($data['kvkk_consent']) ? 1 : 0;
        $sanitized['submitted_at'] = current_time('mysql');
        $sanitized['user_ip'] = $this->get_user_ip();
        $sanitized['page_url'] = isset($data['page_url']) ? esc_url_raw($data['page_url']) : '';
        return $sanitized;
    }

    private function is_corporate_email($email) {
        $domain = strtolower(substr(strrchr($email, '@'), 1));
        return !in_array($domain, $this->blocked_email_domains);
    }

    private function verify_recaptcha($token) {
        if (empty($token)) {
            return array('success' => false, 'message' => __('reCAPTCHA token not found.', 'pentest-quote-form'));
        }

        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
            'timeout' => 30,
            'body' => array(
                'secret' => $this->recaptcha_secret_key,
                'response' => $token,
                'remoteip' => $this->get_user_ip()
            )
        ));

        if (is_wp_error($response)) {
            return array('success' => true, 'message' => '');
        }

        $result = json_decode(wp_remote_retrieve_body($response), true);
        if (!$result) {
            return array('success' => true, 'message' => '');
        }

        if (isset($result['success']) && $result['success'] === true) {
            $score = isset($result['score']) ? floatval($result['score']) : 1;
            if ($score >= $this->recaptcha_min_score) {
                return array('success' => true, 'message' => '', 'score' => $score);
            }
            return array('success' => false, 'message' => __('Security verification failed.', 'pentest-quote-form'));
        }

        return array('success' => false, 'message' => __('reCAPTCHA doğrulaması başarısız.', 'pentest-quote-form'));
    }

    private function validate_form_data($data) {
        $errors = array();

        if (empty($data['test_types'])) {
            $errors['test_types'] = __('Please select at least one test type.', 'pentest-quote-form');
        }

        if (empty($data['first_name'])) {
            $errors['first_name'] = __('Contact person name is required.', 'pentest-quote-form');
        } elseif (strlen($data['first_name']) > 100) {
            $errors['first_name'] = __('Name is too long.', 'pentest-quote-form');
        }

        if (empty($data['email'])) {
            $errors['email'] = __('Email is required.', 'pentest-quote-form');
        } elseif (!is_email($data['email'])) {
            $errors['email'] = __('Please enter a valid email address.', 'pentest-quote-form');
        } elseif (!$this->is_corporate_email($data['email'])) {
            $errors['email'] = __('Please enter your corporate email address.', 'pentest-quote-form');
        }

        if (empty($data['company'])) {
            $errors['company'] = __('Company name is required.', 'pentest-quote-form');
        } elseif (strlen($data['company']) > 200) {
            $errors['company'] = __('Company name is too long.', 'pentest-quote-form');
        }

        if (empty($data['phone'])) {
            $errors['phone'] = __('Phone number is required.', 'pentest-quote-form');
        } elseif (!$this->is_valid_phone($data['phone'])) {
            $errors['phone'] = __('Please enter a valid phone number.', 'pentest-quote-form');
        }

        if (empty($data['kvkk_consent'])) {
            $errors['kvkk_consent'] = __('Privacy consent is required.', 'pentest-quote-form');
        }

        return $errors;
    }

    /**
     * Validate phone number format safely (prevent ReDoS)
     */
    private function is_valid_phone($phone) {
        // Remove all allowed characters and check if result is 10-15 digits
        $digits_only = preg_replace('/[\s\+\-\(\)]/', '', $phone);

        // Must be only digits after cleanup
        if (!ctype_digit($digits_only)) {
            return false;
        }

        // Must be between 10 and 15 digits
        $length = strlen($digits_only);
        return $length >= 10 && $length <= 15;
    }

    private function save_form_submission($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ptf_submissions';
        $this->maybe_create_table();

        $test_types = $this->get_test_types();
        $test_types_readable = array();
        foreach ($data['test_types'] as $type) {
            $test_types_readable[] = isset($test_types[$type]) ? $test_types[$type] : $type;
        }

        // Combine test details as JSON
        $test_details = $this->build_test_details_text($data);

        return $wpdb->insert($table_name, array(
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'company' => $data['company'],
            'test_types' => implode(', ', $test_types_readable),
            'target_scope' => $test_details,
            'kvkk_consent' => $data['kvkk_consent'],
            'page_url' => $data['page_url'],
            'user_ip' => $data['user_ip'],
            'submitted_at' => $data['submitted_at'],
            'status' => 'new'
        )) !== false;
    }

    /**
     * Convert test details to readable text format (dynamic)
     */
    private function build_test_details_text($data) {
        $details = array();
        $categories = $this->get_categories();

        // Add details for each selected category
        foreach ($categories as $category) {
            if (!in_array($category['id'], $data['test_types'])) {
                continue;
            }

            $details[] = "=== " . $category['name'] . " ===";

            if (!empty($category['questions'])) {
                foreach ($category['questions'] as $question) {
                    $key = $question['id'];
                    $value = isset($data[$key]) ? $data[$key] : '';

                    if (!empty($value)) {
                        // Combine for checkbox array
                        if (is_array($value)) {
                            $value = implode(', ', $value);
                        }
                        // Find option label
                        if (!empty($question['options'])) {
                            foreach ($question['options'] as $opt) {
                                if ($opt['id'] === $value) {
                                    $value = $opt['label'];
                                    break;
                                }
                            }
                        }


                        $details[] = $question['question'] . ": " . $value;
                    }
                }
            }
            $details[] = "";
        }

        return implode("\n", $details);
    }

    private function maybe_create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ptf_submissions';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            $sql = "CREATE TABLE $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                first_name varchar(255) NOT NULL,
                last_name varchar(255) NOT NULL,
                email varchar(255) NOT NULL,
                phone varchar(50) DEFAULT '',
                company varchar(255) NOT NULL,
                test_types text NOT NULL,
                target_scope text NOT NULL,
                kvkk_consent tinyint(1) DEFAULT 0,
                page_url varchar(500) DEFAULT '',
                user_ip varchar(45) DEFAULT '',
                submitted_at datetime DEFAULT CURRENT_TIMESTAMP,
                status varchar(20) DEFAULT 'new',
                PRIMARY KEY (id)
            ) {$wpdb->get_charset_collate()};";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    private function send_notification_email($data) {
        // Bildirim e-posta adresi
        $notification_email = $this->get_setting('notification_email', '');
        if (empty($notification_email)) {
            if (defined('PTF_NOTIFICATION_EMAIL') && !empty(PTF_NOTIFICATION_EMAIL)) {
                $notification_email = PTF_NOTIFICATION_EMAIL;
            } else {
                $notification_email = get_option('admin_email');
            }
        }

        $primary_color = $this->get_setting('primary_color', '#2F7CFF');
        $site_name = get_bloginfo('name');

        $test_types = $this->get_test_types();
        $test_types_readable = array();
        foreach ($data['test_types'] as $type) {
            $test_types_readable[] = isset($test_types[$type]) ? $test_types[$type] : $type;
        }

        // Get test details text
        $test_details = $this->build_test_details_text($data);

        $subject = "[$site_name] " . __('New Quote Request', 'pentest-quote-form') . " - " . $data['company'];
        $message = $this->get_email_template($data, $test_types_readable, $site_name, $primary_color, $test_details);

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'Reply-To: ' . $data['email']
        );

        wp_mail($notification_email, $subject, $message, $headers);

        // Auto reply
        $send_auto_reply = $this->get_setting('send_auto_reply', '1');
        if ($send_auto_reply === '1') {
            $this->send_auto_reply($data);
        }
    }

    private function get_email_template($data, $test_types, $site_name, $primary_color = '#2F7CFF', $test_details = '') {
        $tests_html = '';
        foreach ($test_types as $test) {
            $tests_html .= '<li style="padding: 5px 0;">' . esc_html($test) . '</li>';
        }

        $dark_color = $this->adjust_brightness($primary_color, -30);

        return '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;">
    <div style="background: linear-gradient(135deg, ' . esc_attr($primary_color) . ' 0%, ' . esc_attr($dark_color) . ' 100%); padding: 30px; text-align: center;">
        <h1 style="color: #fff; margin: 0; font-size: 24px;">🛡️ ' . __('New Quote Request', 'pentest-quote-form') . '</h1>
    </div>

    <div style="padding: 30px; background: #f9f9f9;">
        <div style="background: #fff; border-radius: 8px; padding: 20px; margin-bottom: 20px; border-left: 4px solid ' . esc_attr($primary_color) . ';">
            <h3 style="margin: 0 0 10px 0; color: ' . esc_attr($primary_color) . ';">📋 ' . __('Requested Tests', 'pentest-quote-form') . '</h3>
            <ul style="margin: 0; padding-left: 20px;">' . $tests_html . '</ul>
        </div>

        <div style="background: #fff; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
            <h3 style="margin: 0 0 10px 0; color: ' . esc_attr($primary_color) . ';">📝 ' . __('Test Details', 'pentest-quote-form') . '</h3>
            <pre style="margin: 0; white-space: pre-wrap; background: #f5f5f5; padding: 15px; border-radius: 4px; font-family: monospace; font-size: 13px;">' . esc_html($test_details) . '</pre>
        </div>

        <div style="background: #fff; border-radius: 8px; padding: 20px;">
            <h3 style="margin: 0 0 15px 0; color: ' . esc_attr($primary_color) . ';">👤 ' . __('Contact Information', 'pentest-quote-form') . '</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>' . __('Company:', 'pentest-quote-form') . '</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;">' . esc_html($data['company']) . '</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>' . __('Contact Person:', 'pentest-quote-form') . '</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;">' . esc_html($data['first_name']) . '</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>' . __('Email:', 'pentest-quote-form') . '</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><a href="mailto:' . esc_attr($data['email']) . '">' . esc_html($data['email']) . '</a></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>' . __('Phone:', 'pentest-quote-form') . '</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;">' . esc_html($data['phone']) . '</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>' . __('Date:', 'pentest-quote-form') . '</strong></td>
                    <td style="padding: 8px 0;">' . esc_html($data['submitted_at']) . '</td>
                </tr>
            </table>
        </div>
    </div>

    <div style="padding: 20px; text-align: center; background: #333; color: #999; font-size: 12px;">
        <p style="margin: 0;">' . sprintf(__('This email was sent from %s website.', 'pentest-quote-form'), esc_html($site_name)) . '</p>
        <p style="margin: 5px 0 0 0;">IP: ' . esc_html($data['user_ip']) . '</p>
    </div>
</body>
</html>';
    }

    private function send_auto_reply($data) {
        $site_name = get_bloginfo('name');
        $subject = "$site_name - " . __('We Received Your Quote Request', 'pentest-quote-form');
        $message = sprintf(__('Dear %s,', 'pentest-quote-form'), $data['first_name']) . "\n\n";
        $message .= __('Your penetration test quote request has been successfully received.', 'pentest-quote-form') . "\n";
        $message .= __('Our team will contact you shortly.', 'pentest-quote-form') . "\n\n";
        $message .= __('Thank you,', 'pentest-quote-form') . "\n$site_name";
        wp_mail($data['email'], $subject, $message, array('Content-Type: text/plain; charset=UTF-8'));
    }

    /**
     * Send form data to webhooks
     */
    private function send_to_webhooks($form_data) {
        $webhooks = $this->get_setting('webhooks', array());

        if (empty($webhooks) || !is_array($webhooks)) {
            return;
        }

        // Create structured payload
        $payload = $this->build_webhook_payload($form_data);

        // Send to each active webhook
        foreach ($webhooks as $webhook) {
            if (empty($webhook['active']) || empty($webhook['url'])) {
                continue;
            }
            $this->send_single_webhook($webhook, $payload);
        }
    }

    /**
     * Build structured JSON payload for webhook
     */
    private function build_webhook_payload($form_data) {
        $categories = $this->get_categories();
        $test_types = $this->get_test_types();

        // Get selected categories
        $selected_ids = isset($form_data['test_types']) ? $form_data['test_types'] : array();
        $selected_categories = array();
        foreach ($selected_ids as $id) {
            if (isset($test_types[$id])) {
                $selected_categories[] = array(
                    'id' => $id,
                    'name' => $test_types[$id]
                );
            }
        }

        // Main structure
        $payload = array(
            // Meta bilgiler
            'meta' => array(
                'source' => 'pentest-quote-form',
                'version' => PTF_VERSION,
                'site_name' => get_bloginfo('name'),
                'site_url' => home_url(),
                'submitted_at' => $form_data['submitted_at'] ?? current_time('mysql'),
                'page_url' => $form_data['page_url'] ?? '',
                'user_ip' => $form_data['user_ip'] ?? '',
            ),

            // Contact information
            'contact' => array(
                'name' => $form_data['first_name'] ?? '',
                'email' => $form_data['email'] ?? '',
                'phone' => $form_data['phone'] ?? '',
                'company' => $form_data['company'] ?? '',
            ),

            // Selected test categories
            'selected_categories' => $selected_categories,

            // Category-based question-answers
            'answers' => array(),
        );

        // Collect answers for each selected category
        foreach ($categories as $category) {
            if (!in_array($category['id'], $selected_ids)) {
                continue;
            }

            $category_data = array(
                'category_id' => $category['id'],
                'category_name' => $category['name'],
                'questions' => array(),
            );

            if (!empty($category['questions'])) {
                foreach ($category['questions'] as $question) {
                    $qid = $question['id'];
                    $answer = isset($form_data[$qid]) ? $form_data[$qid] : null;

                    // Skip empty answers
                    if ($answer === null || $answer === '' || (is_array($answer) && empty($answer))) {
                        continue;
                    }

                    // Cevap etiketini bul
                    $answer_label = $answer;
                    if (!empty($question['options'])) {
                        if (is_array($answer)) {
                            $answer_label = array();
                            foreach ($answer as $val) {
                                foreach ($question['options'] as $opt) {
                                    if ($opt['id'] === $val) {
                                        $answer_label[] = $opt['label'];
                                        break;
                                    }
                                }
                            }
                        } else {
                            foreach ($question['options'] as $opt) {
                                if ($opt['id'] === $answer) {
                                    $answer_label = $opt['label'];
                                    break;
                                }
                            }
                        }
                    }


                    $category_data['questions'][] = array(
                        'id' => $qid,
                        'question' => $question['question'],
                        'type' => $question['type'],
                        'answer' => $answer,
                        'answer_label' => $answer_label,
                    );
                }
            }

            if (!empty($category_data['questions'])) {
                $payload['answers'][] = $category_data;
            }
        }

        // Flat format (for simple integrations)
        $payload['flat'] = array(
            'name' => $form_data['first_name'] ?? '',
            'email' => $form_data['email'] ?? '',
            'phone' => $form_data['phone'] ?? '',
            'company' => $form_data['company'] ?? '',
            'test_types' => $selected_ids,
            'test_types_labels' => array_column($selected_categories, 'name'),
            'submitted_at' => $form_data['submitted_at'] ?? current_time('mysql'),
            'page_url' => $form_data['page_url'] ?? '',
        );

        // Add all question answers to flat
        foreach ($form_data as $key => $value) {
            if (!in_array($key, array('first_name', 'last_name', 'email', 'phone', 'company', 'test_types', 'kvkk_consent', 'user_ip', 'submitted_at', 'page_url'))) {
                $payload['flat'][$key] = $value;
            }
        }

        return $payload;
    }

    /**
     * Get nested array value (e.g., contact.name) with sanitization
     */
    private function get_nested_value($array, $path) {
        $keys = explode('.', sanitize_text_field($path));
        $value = $array;
        foreach ($keys as $key) {
            $safe_key = sanitize_key($key);
            if (is_array($value) && isset($value[$safe_key])) {
                $value = $value[$safe_key];
            } else {
                return null;
            }
        }
        return $value;
    }

    /**
     * Validate webhook URL to prevent SSRF attacks
     */
    private function is_valid_webhook_url($url) {
        // Must be a valid URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Parse URL components
        $parsed = wp_parse_url($url);

        // Must use HTTPS (or HTTP for development)
        if (!isset($parsed['scheme']) || !in_array($parsed['scheme'], array('https', 'http'), true)) {
            return false;
        }

        // Get host
        $host = isset($parsed['host']) ? $parsed['host'] : '';
        if (empty($host)) {
            return false;
        }

        // Block localhost and internal IPs
        $blocked_hosts = array(
            'localhost',
            '127.0.0.1',
            '0.0.0.0',
            '::1',
            'metadata.google.internal',
            '169.254.169.254', // AWS/GCP metadata
        );

        if (in_array(strtolower($host), $blocked_hosts, true)) {
            return false;
        }

        // Resolve hostname and check for internal IPs
        $ip = gethostbyname($host);
        if ($ip !== $host) {
            // Check if IP is in private/reserved ranges
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Send data to a single webhook with SSRF protection
     */
    private function send_single_webhook($webhook, $payload) {
        $url = $webhook['url'];

        // SSRF protection - validate URL before making request
        if (!$this->is_valid_webhook_url($url)) {
            error_log('PTF Webhook Error: Invalid or blocked URL - ' . esc_url($url));
            return;
        }

        $method = isset($webhook['method']) ? strtoupper($webhook['method']) : 'POST';

        // Validate method
        $allowed_methods = array('POST', 'PUT', 'PATCH');
        if (!in_array($method, $allowed_methods, true)) {
            $method = 'POST';
        }

        $headers = array(
            'Content-Type' => 'application/json',
            'User-Agent' => 'Pentest-Quote-Form/' . PTF_VERSION,
        );

        // Add custom headers (sanitize keys and values)
        if (!empty($webhook['headers']) && is_array($webhook['headers'])) {
            foreach ($webhook['headers'] as $key => $value) {
                // Only allow safe header names
                $safe_key = preg_replace('/[^a-zA-Z0-9\-]/', '', $key);
                if (!empty($safe_key)) {
                    $headers[$safe_key] = sanitize_text_field($value);
                }
            }
        }

        // Authentication
        if (!empty($webhook['auth_type']) && $webhook['auth_type'] !== 'none' && !empty($webhook['auth_value'])) {
            switch ($webhook['auth_type']) {
                case 'bearer':
                    $headers['Authorization'] = 'Bearer ' . sanitize_text_field($webhook['auth_value']);
                    break;
                case 'basic':
                    $headers['Authorization'] = 'Basic ' . base64_encode(sanitize_text_field($webhook['auth_value']));
                    break;
                case 'api_key':
                    $headers['X-API-Key'] = sanitize_text_field($webhook['auth_value']);
                    break;
            }
        }

        // Apply field mapping if exists
        $final_payload = $payload;
        if (!empty($webhook['field_mapping']) && is_array($webhook['field_mapping'])) {
            $mapped = array();
            foreach ($webhook['field_mapping'] as $api_field => $form_field) {
                // Sanitize field names
                $safe_api_field = sanitize_key($api_field);
                $safe_form_field = sanitize_text_field($form_field);

                $value = $this->get_nested_value($payload, $safe_form_field);
                if ($value === null && isset($payload['flat'][$safe_form_field])) {
                    $value = $payload['flat'][$safe_form_field];
                }
                if ($value !== null) {
                    $mapped[$safe_api_field] = $value;
                }
            }
            if (!empty($mapped)) {
                $final_payload = $mapped;
            }
        }

        // Send request
        $response = wp_remote_request($url, array(
            'method' => $method,
            'timeout' => 30,
            'headers' => $headers,
            'body' => json_encode($final_payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ));

        // Hata logla
        if (is_wp_error($response)) {
            error_log('PTF Webhook Error (' . ($webhook['name'] ?? 'Unknown') . '): ' . $response->get_error_message());
        } else {
            $code = wp_remote_retrieve_response_code($response);
            if ($code < 200 || $code >= 300) {
                error_log('PTF Webhook Error (' . ($webhook['name'] ?? 'Unknown') . '): HTTP ' . $code);
            }
        }
    }

    /**
     * Webhook test AJAX handler with security
     */
    public static function handle_webhook_test() {
        // Verify nonce with proper sanitization
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'ptf_test_webhooks')) {
            wp_send_json_error(array('message' => __('Security verification failed.', 'pentest-quote-form')));
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'pentest-quote-form')));
            return;
        }

        $webhooks_json = isset($_POST['webhooks']) ? wp_unslash($_POST['webhooks']) : '[]';
        $webhooks = json_decode($webhooks_json, true);

        if (!is_array($webhooks) || empty($webhooks)) {
            wp_send_json_error(array('message' => __('No webhook found to test.', 'pentest-quote-form')));
            return;
        }

        // Limit webhooks to test (prevent abuse)
        $webhooks = array_slice($webhooks, 0, 10);

        // Test payload - new structured format
        $test_payload = array(
            'meta' => array(
                'source' => 'pentest-quote-form',
                'version' => PTF_VERSION,
                'site_name' => get_bloginfo('name'),
                'site_url' => home_url(),
                'submitted_at' => current_time('mysql'),
                'page_url' => home_url('/test-page'),
                'user_ip' => '127.0.0.1',
                '_test' => true,
            ),
            'contact' => array(
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '+1 555 123 4567',
                'company' => 'Test Company Inc.',
            ),
            'selected_categories' => array(
                array('id' => 'web-application', 'name' => 'Web Application Penetration Test'),
                array('id' => 'api-security', 'name' => 'API Security Test'),
            ),
            'answers' => array(
                array(
                    'category_id' => 'web-application',
                    'category_name' => 'Web Application Penetration Test',
                    'questions' => array(
                        array(
                            'id' => 'url_count',
                            'question' => 'How many URLs will be tested?',
                            'type' => 'number',
                            'answer' => 5,
                            'answer_label' => 5,
                        ),
                    ),
                ),
            ),
            'flat' => array(
                'first_name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '+1 555 123 4567',
                'company' => 'Test Company Inc.',
                'test_types' => array('web-application', 'api-security'),
                'test_types_labels' => array('Web Application Penetration Test', 'API Security Test'),
                'submitted_at' => current_time('mysql'),
                'page_url' => home_url('/test-page'),
                'url_count' => 5,
            ),
        );

        // Create instance for URL validation
        $instance = self::get_instance();
        $results = array();

        foreach ($webhooks as $webhook) {
            $webhook_url = isset($webhook['url']) ? esc_url_raw($webhook['url']) : '';
            $webhook_name = isset($webhook['name']) ? sanitize_text_field($webhook['name']) : 'Unnamed';

            if (empty($webhook_url)) {
                $results[] = array(
                    'name' => $webhook_name,
                    'success' => false,
                    'message' => __('URL not defined', 'pentest-quote-form'),
                    'response_code' => null,
                );
                continue;
            }

            // SSRF protection - validate URL
            if (!$instance->is_valid_webhook_url($webhook_url)) {
                $results[] = array(
                    'name' => $webhook_name,
                    'success' => false,
                    'message' => __('Invalid or blocked URL', 'pentest-quote-form'),
                    'response_code' => null,
                );
                continue;
            }

            // Prepare headers with sanitization
            $headers = array(
                'Content-Type' => 'application/json',
                'User-Agent' => 'Pentest-Quote-Form/' . PTF_VERSION . ' (Test)',
            );

            if (!empty($webhook['headers']) && is_array($webhook['headers'])) {
                foreach ($webhook['headers'] as $key => $value) {
                    $safe_key = preg_replace('/[^a-zA-Z0-9\-]/', '', $key);
                    if (!empty($safe_key)) {
                        $headers[$safe_key] = sanitize_text_field($value);
                    }
                }
            }

            // Authentication
            if (!empty($webhook['auth_type']) && $webhook['auth_type'] !== 'none' && !empty($webhook['auth_value'])) {
                $auth_value = sanitize_text_field($webhook['auth_value']);
                switch ($webhook['auth_type']) {
                    case 'bearer':
                        $headers['Authorization'] = 'Bearer ' . $auth_value;
                        break;
                    case 'basic':
                        $headers['Authorization'] = 'Basic ' . base64_encode($auth_value);
                        break;
                    case 'api_key':
                        $headers['X-API-Key'] = $webhook['auth_value'];
                        break;
                }
            }

            // Submitilecek payload
            $final_payload = $test_payload;

            // Field mapping varsa uygula
            if (!empty($webhook['field_mapping']) && is_array($webhook['field_mapping'])) {
                $mapped_payload = array();
                $flat_data = $test_payload['flat'];

                foreach ($webhook['field_mapping'] as $api_field => $form_field) {
                    // Nested path support
                    $keys = explode('.', $form_field);
                    $value = $test_payload;
                    foreach ($keys as $key) {
                        if (is_array($value) && isset($value[$key])) {
                            $value = $value[$key];
                        } else {
                            $value = isset($flat_data[$form_field]) ? $flat_data[$form_field] : null;
                            break;
                        }
                    }

                    if ($value !== null && $value !== $test_payload) {
                        $mapped_payload[$api_field] = $value;
                    }
                }

                if (!empty($mapped_payload)) {
                    $mapped_payload['_test'] = true;
                    $final_payload = $mapped_payload;
                }
            }

            // Send request
            $method = isset($webhook['method']) ? strtoupper($webhook['method']) : 'POST';
            $response = wp_remote_request($webhook['url'], array(
                'method' => $method,
                'timeout' => 15,
                'headers' => $headers,
                'body' => json_encode($final_payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            ));

            if (is_wp_error($response)) {
                $results[] = array(
                    'name' => $webhook['name'] ?? 'Unnamed',
                    'success' => false,
                    'message' => $response->get_error_message(),
                    'response_code' => null,
                );
            } else {
                $response_code = wp_remote_retrieve_response_code($response);
                $success = ($response_code >= 200 && $response_code < 300);

                $results[] = array(
                    'name' => $webhook['name'] ?? 'Unnamed',
                    'success' => $success,
                    'message' => $success
                        ? __('Success', 'pentest-quote-form')
                        : __('Error:', 'pentest-quote-form') . ' ' . wp_remote_retrieve_response_message($response),
                    'response_code' => $response_code,
                );
            }
        }

        wp_send_json_success(array('results' => $results));
    }

    private function get_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) return sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
        return sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
    }

    public function render_popup_form() {
        // Get field labels for header
        $field_labels = $this->get_setting('field_labels', array());
        $form_title = isset($field_labels['form_header']['title']) && !empty($field_labels['form_header']['title'])
            ? $field_labels['form_header']['title']
            : __('Get Quick Quote', 'pentest-quote-form');
        $form_subtitle = isset($field_labels['form_header']['subtitle']) && !empty($field_labels['form_header']['subtitle'])
            ? $field_labels['form_header']['subtitle']
            : __('Get a quote for your cybersecurity needs', 'pentest-quote-form');
        ?>
        <div id="ptf-popup-overlay" class="ptf-popup-overlay" style="display: none;">
            <div class="ptf-popup-container">
                <button type="button" class="ptf-popup-close" aria-label="<?php esc_attr_e('Close', 'pentest-quote-form'); ?>"><span>&times;</span></button>
                <div class="ptf-popup-header">
                    <h2 class="ptf-popup-title"><?php echo esc_html($form_title); ?></h2>
                    <p class="ptf-popup-subtitle"><?php echo esc_html($form_subtitle); ?></p>
                </div>
                <?php echo $this->get_form_html('popup'); ?>
            </div>
        </div>
        <?php
    }

    public function render_inline_form($atts) {
        // Get field labels for default header values
        $field_labels = $this->get_setting('field_labels', array());
        $default_title = isset($field_labels['form_header']['title']) && !empty($field_labels['form_header']['title'])
            ? $field_labels['form_header']['title']
            : __('Get Quick Quote', 'pentest-quote-form');
        $default_subtitle = isset($field_labels['form_header']['subtitle']) && !empty($field_labels['form_header']['subtitle'])
            ? $field_labels['form_header']['subtitle']
            : __('Get a quote for your cybersecurity needs', 'pentest-quote-form');

        $atts = shortcode_atts(array(
            'title' => $default_title,
            'subtitle' => $default_subtitle,
            'class' => '',
            'primary' => '',
            'secondary' => ''
        ), $atts);

        // Shortcode'da renk belirtilmişse inline stil oluştur
        $style_tag = '';
        if (!empty($atts['primary']) || !empty($atts['secondary'])) {
            $primary = !empty($atts['primary']) ? $atts['primary'] : $this->get_setting('primary_color', '#2F7CFF');
            $secondary = !empty($atts['secondary']) ? $atts['secondary'] : $this->get_setting('secondary_color', '#B7FF10');
            $style_tag = $this->get_scoped_color_css($primary, $secondary, 'inline-' . uniqid());
        }

        $wrapper_id = 'ptf-inline-' . uniqid();

        ob_start();
        ?>
        <?php echo $style_tag; ?>
        <div id="<?php echo esc_attr($wrapper_id); ?>" class="ptf-form-wrapper ptf-form-inline <?php echo esc_attr($atts['class']); ?>">
            <?php if (!empty($atts['title'])): ?>
                <h3 class="ptf-form-title"><?php echo esc_html($atts['title']); ?></h3>
            <?php endif; ?>
            <?php if (!empty($atts['subtitle'])): ?>
                <p class="ptf-form-subtitle"><?php echo esc_html($atts['subtitle']); ?></p>
            <?php endif; ?>
            <?php echo $this->get_form_html('inline'); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function get_scoped_color_css($primary, $secondary, $scope_id) {
        $primary_dark = $this->adjust_brightness($primary, -30);

        return '<style>
        #ptf-inline-' . esc_attr($scope_id) . ' .ptf-progress-step.active .step-number,
        #ptf-inline-' . esc_attr($scope_id) . ' .ptf-progress-step.completed .step-number {
            background: linear-gradient(135deg, ' . $primary . ' 0%, ' . $primary_dark . ' 100%) !important;
        }
        #ptf-inline-' . esc_attr($scope_id) . ' .ptf-progress-line::after {
            background: linear-gradient(135deg, ' . $primary . ' 0%, ' . $primary_dark . ' 100%) !important;
        }
        #ptf-inline-' . esc_attr($scope_id) . ' .ptf-checkbox-item:hover {
            border-color: ' . $primary . ' !important;
        }
        #ptf-inline-' . esc_attr($scope_id) . ' .ptf-checkbox-item input[type="checkbox"]:checked + .ptf-checkbox-mark {
            background: linear-gradient(135deg, ' . $primary . ' 0%, ' . $primary_dark . ' 100%) !important;
            border-color: ' . $primary . ' !important;
        }
        #ptf-inline-' . esc_attr($scope_id) . ' .ptf-form-group input:focus,
        #ptf-inline-' . esc_attr($scope_id) . ' .ptf-form-group textarea:focus {
            border-color: ' . $primary . ' !important;
        }
        #ptf-inline-' . esc_attr($scope_id) . ' .ptf-consent-label input[type="checkbox"]:checked + .ptf-checkbox-mark {
            background: linear-gradient(135deg, ' . $primary . ' 0%, ' . $primary_dark . ' 100%) !important;
            border-color: ' . $primary . ' !important;
        }
        #ptf-inline-' . esc_attr($scope_id) . ' .ptf-consent-text a {
            color: ' . $primary . ' !important;
        }
        #ptf-inline-' . esc_attr($scope_id) . ' .ptf-btn-next,
        #ptf-inline-' . esc_attr($scope_id) . ' .ptf-btn-submit {
            background: linear-gradient(135deg, ' . $primary . ' 0%, ' . $primary_dark . ' 100%) !important;
        }
        #ptf-inline-' . esc_attr($scope_id) . ' .ptf-success-icon {
            color: ' . $secondary . ' !important;
        }
        </style>';
    }

    public function render_popup_trigger($atts) {
        $default_text = $this->get_setting('button_text', __('Get Quick Quote', 'pentest-quote-form'));
        $default_size = $this->get_setting('button_size', 'medium');

        $atts = shortcode_atts(array(
            'text' => $default_text,
            'class' => '',
            'style' => '',
            'primary' => '',
            'secondary' => '',
            'size' => $default_size,
            'padding_y' => '',
            'padding_x' => '',
            'font_size' => ''
        ), $atts);

        // Build button classes
        $button_classes = 'ptf-popup-trigger';

        // Only add size class if not custom
        if ($atts['size'] !== 'custom') {
            $button_classes .= ' ptf-btn-' . esc_attr($atts['size']);
        }

        if (!empty($atts['class'])) {
            $button_classes .= ' ' . $atts['class'];
        }

        // Create inline style
        $custom_style = $atts['style'];

        // Custom color
        if (!empty($atts['primary'])) {
            $primary_dark = $this->adjust_brightness($atts['primary'], -30);
            $custom_style .= " background: linear-gradient(135deg, {$atts['primary']} 0%, {$primary_dark} 100%);";
            $custom_style .= " box-shadow: 0 4px 15px " . $this->hex_to_rgba($atts['primary'], 0.3) . ";";
        }

        // Custom size via shortcode attributes
        if (!empty($atts['padding_y']) || !empty($atts['padding_x'])) {
            $py = !empty($atts['padding_y']) ? intval($atts['padding_y']) : 16;
            $px = !empty($atts['padding_x']) ? intval($atts['padding_x']) : 32;
            $custom_style .= " padding: {$py}px {$px}px;";
        }
        // Custom size from settings (when size is 'custom')
        elseif ($atts['size'] === 'custom') {
            $py = intval($this->get_setting('button_padding_y', 16));
            $px = intval($this->get_setting('button_padding_x', 32));
            $custom_style .= " padding: {$py}px {$px}px;";
        }

        // Custom font size
        if (!empty($atts['font_size'])) {
            $custom_style .= " font-size: " . intval($atts['font_size']) . "px;";
        } elseif ($atts['size'] === 'custom') {
            $fs = intval($this->get_setting('button_font_size', 16));
            $custom_style .= " font-size: {$fs}px;";
        }

        return sprintf(
            '<button type="button" class="%s" style="%s">%s</button>',
            esc_attr(trim($button_classes)),
            esc_attr(trim($custom_style)),
            esc_html($atts['text'])
        );
    }

    private function get_form_html($context = 'inline') {
        $kvkk_url = $this->get_setting('kvkk_url', '/privacy-notice');
        $privacy_url = $this->get_setting('privacy_url', '/privacy-policy');
        $success_message = $this->get_setting('success_message', __('Your quote request has been received successfully. Our expert team will contact you shortly.', 'pentest-quote-form'));

        // Get field labels from settings
        $field_labels = $this->get_setting('field_labels', array());
        $default_labels = array(
            // Form header (title and subtitle)
            'form_header' => array(
                'title' => __('Get Quick Quote', 'pentest-quote-form'),
                'subtitle' => __('Get a quote for your cybersecurity needs', 'pentest-quote-form'),
            ),
            // Step names for progress bar
            'step1' => array(
                'title' => __('Test Selection', 'pentest-quote-form'),
            ),
            'step2' => array(
                'title' => __('Test Details', 'pentest-quote-form'),
            ),
            'step3' => array(
                'title' => __('Contact Information', 'pentest-quote-form'),
            ),
            // Step 1 - Test Selection
            'test_selection' => array(
                'title' => __('Test Type Selection', 'pentest-quote-form'),
                'description' => __('Which security test(s) would you like a quote for?', 'pentest-quote-form'),
                'multi_select_hint' => __('(You can select multiple)', 'pentest-quote-form'),
            ),
            // Step 2 - Test Details
            'test_details' => array(
                'title' => __('Test Details', 'pentest-quote-form'),
                'description' => __('Please provide details about the selected tests.', 'pentest-quote-form'),
            ),
            // Step 3 - Contact Information
            'contact_step' => array(
                'title' => __('Contact Information', 'pentest-quote-form'),
                'description' => __('Please enter your information so we can contact you.', 'pentest-quote-form'),
            ),
            // Form fields
            'company' => array(
                'label' => __('Company Name', 'pentest-quote-form'),
                'placeholder' => __('Company name', 'pentest-quote-form'),
            ),
            'first_name' => array(
                'label' => __('Contact Person', 'pentest-quote-form'),
                'placeholder' => __('Your Full Name', 'pentest-quote-form'),
            ),
            'email' => array(
                'label' => __('Email', 'pentest-quote-form'),
                'placeholder' => 'corporate@yourcompany.com',
                'hint' => __('Only corporate email addresses are accepted.', 'pentest-quote-form'),
            ),
            'phone' => array(
                'label' => __('Phone', 'pentest-quote-form'),
                'placeholder' => '+1 555 XXX XXXX',
            ),
            // Privacy consent
            'kvkk_consent' => array(
                'text' => __("I have read, understood and accept the", 'pentest-quote-form'),
                'and_text' => __("and", 'pentest-quote-form'),
                'privacy_notice' => __('Privacy Notice', 'pentest-quote-form'),
                'privacy_policy' => __('Privacy Policy', 'pentest-quote-form'),
            ),
            // Buttons
            'buttons' => array(
                'next' => __('Continue', 'pentest-quote-form'),
                'prev' => __('Back', 'pentest-quote-form'),
                'submit' => __('Submit', 'pentest-quote-form'),
            ),
            // Success & Loading messages
            'messages' => array(
                'success_title' => __('Thank You!', 'pentest-quote-form'),
                'loading' => __('Sending...', 'pentest-quote-form'),
            ),
            // Validation messages
            'validation' => array(
                'required' => __('This field is required.', 'pentest-quote-form'),
                'email' => __('Please enter a valid email address.', 'pentest-quote-form'),
                'corporate_email' => __('Please enter your corporate email address. Personal email addresses are not accepted.', 'pentest-quote-form'),
                'phone' => __('Please enter a valid phone number.', 'pentest-quote-form'),
                'checkbox_required' => __('You must accept this to continue.', 'pentest-quote-form'),
                'test_type_required' => __('Please select at least one test type.', 'pentest-quote-form'),
                'error' => __('An error occurred. Please try again.', 'pentest-quote-form'),
                'recaptcha_error' => __('reCAPTCHA verification failed. Please try again.', 'pentest-quote-form'),
            ),
        );
        $field_labels = wp_parse_args($field_labels, $default_labels);
        // Deep merge for nested arrays
        foreach ($default_labels as $key => $value) {
            if (is_array($value) && isset($field_labels[$key])) {
                $field_labels[$key] = wp_parse_args($field_labels[$key], $value);
            }
        }

        // Dynamic categories and questions
        $categories = $this->get_categories();

        // Check if categories have questions
        $has_category_questions = false;
        foreach ($categories as $category) {
            if (!empty($category['questions'])) {
                $has_category_questions = true;
                break;
            }
        }

        ob_start();
        ?>
        <form class="ptf-multistep-form" data-context="<?php echo esc_attr($context); ?>" novalidate>
            <input type="hidden" name="action" value="ptf_submit_form">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('ptf_form_nonce'); ?>">
            <input type="hidden" name="page_url" value="<?php echo esc_url(get_permalink()); ?>">

            <div class="ptf-form-progress">
                <div class="ptf-progress-step active" data-step="1">
                    <span class="step-number">1</span>
                    <span class="step-label"><?php echo esc_html($field_labels['step1']['title']); ?></span>
                </div>
                <?php if ($has_category_questions): ?>
                <div class="ptf-progress-line"></div>
                <div class="ptf-progress-step" data-step="2">
                    <span class="step-number">2</span>
                    <span class="step-label"><?php echo esc_html($field_labels['step2']['title']); ?></span>
                </div>
                <?php endif; ?>
                <div class="ptf-progress-line"></div>
                <div class="ptf-progress-step" data-step="<?php echo $has_category_questions ? '3' : '2'; ?>">
                    <span class="step-number"><?php echo $has_category_questions ? '3' : '2'; ?></span>
                    <span class="step-label"><?php echo esc_html($field_labels['step3']['title']); ?></span>
                </div>
            </div>

            <!-- Page 1 - Test Type Selection -->
            <div class="ptf-form-step active" data-step="1">
                <h4 class="ptf-step-title"><?php echo esc_html($field_labels['test_selection']['title']); ?></h4>
                <p class="ptf-step-description"><?php echo esc_html($field_labels['test_selection']['description']); ?></p>

                <div class="ptf-form-row">
                    <div class="ptf-form-group ptf-checkbox-group">
                        <label class="ptf-group-label">
                            <span class="ptf-multi-select-hint"><?php echo esc_html($field_labels['test_selection']['multi_select_hint']); ?></span>
                            <span class="required">*</span>
                        </label>
                        <div class="ptf-checkbox-list">
                            <?php foreach ($categories as $category): ?>
                                <label class="ptf-checkbox-item">
                                    <input type="checkbox" name="test_types[]" value="<?php echo esc_attr($category['id']); ?>">
                                    <span class="ptf-checkbox-mark"></span>
                                    <span class="ptf-checkbox-label"><?php echo esc_html($category['name']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <span class="ptf-field-error" data-field="test_types"></span>
                    </div>
                </div>
                <div class="ptf-form-actions">
                    <button type="button" class="ptf-btn ptf-btn-next"><?php echo esc_html($field_labels['buttons']['next']); ?> <span class="ptf-btn-arrow">→</span></button>
                </div>
            </div>

            <!-- Page 2 - Test Details (Dynamic) - Only show if categories have questions -->
            <?php if ($has_category_questions): ?>
            <div class="ptf-form-step" data-step="2">
                <h4 class="ptf-step-title"><?php echo esc_html($field_labels['test_details']['title']); ?></h4>
                <p class="ptf-step-description"><?php echo esc_html($field_labels['test_details']['description']); ?></p>

                <?php
                // Dynamic category questions
                foreach ($categories as $category):
                    if (empty($category['questions'])) continue;
                ?>
                <div class="ptf-test-questions" data-test-type="<?php echo esc_attr($category['id']); ?>" style="display: none;">
                    <div class="ptf-test-section-header">
                        <span class="ptf-test-icon"><?php echo esc_html($category['icon'] ?? '📋'); ?></span>
                        <h5><?php echo esc_html($category['name']); ?></h5>
                    </div>
                    <?php echo $this->render_category_questions($category['questions']); ?>
                </div>
                <?php endforeach; ?>


                <div class="ptf-form-actions ptf-form-actions-dual">
                    <button type="button" class="ptf-btn ptf-btn-prev"><span class="ptf-btn-arrow">←</span> <?php echo esc_html($field_labels['buttons']['prev']); ?></button>
                    <button type="button" class="ptf-btn ptf-btn-next"><?php echo esc_html($field_labels['buttons']['next']); ?> <span class="ptf-btn-arrow">→</span></button>
                </div>
            </div>
            <?php endif; ?>

            <!-- Page 3 - Contact Information (or Page 2 if no category questions) -->
            <div class="ptf-form-step" data-step="<?php echo $has_category_questions ? '3' : '2'; ?>">
                <h4 class="ptf-step-title"><?php echo esc_html($field_labels['contact_step']['title']); ?></h4>
                <p class="ptf-step-description"><?php echo esc_html($field_labels['contact_step']['description']); ?></p>

                <div class="ptf-form-row ptf-form-row-2">
                    <div class="ptf-form-group">
                        <label><?php echo esc_html($field_labels['company']['label']); ?> <span class="required">*</span></label>
                        <input type="text" name="company" required placeholder="<?php echo esc_attr($field_labels['company']['placeholder']); ?>">
                        <span class="ptf-field-error"></span>
                    </div>
                    <div class="ptf-form-group">
                        <label><?php echo esc_html($field_labels['first_name']['label']); ?> <span class="required">*</span></label>
                        <input type="text" name="first_name" required placeholder="<?php echo esc_attr($field_labels['first_name']['placeholder']); ?>">
                        <span class="ptf-field-error"></span>
                    </div>
                </div>

                <div class="ptf-form-row ptf-form-row-2">
                    <div class="ptf-form-group">
                        <label><?php echo esc_html($field_labels['email']['label']); ?> <span class="required">*</span></label>
                        <input type="email" name="email" required placeholder="<?php echo esc_attr($field_labels['email']['placeholder']); ?>" data-corporate-only="true">
                        <span class="ptf-field-error"></span>
                        <span class="ptf-field-hint"><?php echo esc_html($field_labels['email']['hint']); ?></span>
                    </div>
                    <div class="ptf-form-group">
                        <label><?php echo esc_html($field_labels['phone']['label']); ?> <span class="required">*</span></label>
                        <input type="tel" name="phone" required placeholder="<?php echo esc_attr($field_labels['phone']['placeholder']); ?>">
                        <span class="ptf-field-error"></span>
                    </div>
                </div>

                <div class="ptf-form-row">
                    <div class="ptf-form-group ptf-consent-group">
                        <label class="ptf-consent-label">
                            <input type="checkbox" name="kvkk_consent" value="1" required>
                            <span class="ptf-checkbox-mark"></span>
                            <span class="ptf-consent-text">
                                <?php echo esc_html($field_labels['kvkk_consent']['text']); ?>
                                <a href="<?php echo esc_url($kvkk_url); ?>" target="_blank"><?php echo esc_html($field_labels['kvkk_consent']['privacy_notice']); ?></a>
                                <?php echo esc_html($field_labels['kvkk_consent']['and_text']); ?>
                                <a href="<?php echo esc_url($privacy_url); ?>" target="_blank"><?php echo esc_html($field_labels['kvkk_consent']['privacy_policy']); ?></a>.
                            </span>
                        </label>
                        <span class="ptf-field-error" data-field="kvkk_consent"></span>
                    </div>
                </div>

                <div class="ptf-form-actions ptf-form-actions-dual">
                    <button type="button" class="ptf-btn ptf-btn-prev"><span class="ptf-btn-arrow">←</span> <?php echo esc_html($field_labels['buttons']['prev']); ?></button>
                    <button type="submit" class="ptf-btn ptf-btn-submit" disabled><?php echo esc_html($field_labels['buttons']['submit']); ?></button>
                </div>
            </div>

            <div class="ptf-form-message ptf-form-error-message" style="display: none;">
                <div class="ptf-message-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <p class="ptf-message-text"></p>
                <button type="button" class="ptf-message-close">&times;</button>
            </div>

            <div class="ptf-form-success" style="display: none;">
                <div class="ptf-success-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <h3><?php echo esc_html($field_labels['messages']['success_title']); ?></h3>
                <p><?php echo esc_html($success_message); ?></p>
            </div>

            <div class="ptf-form-loading" style="display: none;">
                <div class="ptf-spinner"></div>
                <p><?php echo esc_html($field_labels['messages']['loading']); ?></p>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * Render category questions
     */
    private function render_category_questions($questions) {
        if (empty($questions)) return '';

        ob_start();

        // Group questions in pairs
        $question_pairs = array_chunk($questions, 2);

        foreach ($question_pairs as $pair) {
            $row_class = count($pair) == 2 ? 'ptf-form-row ptf-form-row-2' : 'ptf-form-row';
            echo '<div class="' . esc_attr($row_class) . '">';

            foreach ($pair as $question) {
                echo $this->render_single_question($question);
            }

            echo '</div>';
        }

        return ob_get_clean();
    }

    /**
     * Render a single question
     */
    private function render_single_question($question) {
        $key = $question['id'];
        $label = $question['question'];
        $type = $question['type'];
        $required = !empty($question['required']);
        $placeholder = $question['placeholder'] ?? '';
        $options = $question['options'] ?? array();

        ob_start();
        ?>
        <div class="ptf-form-group">
            <label>
                <?php echo esc_html($label); ?>
                <?php if ($required): ?><span class="required">*</span><?php endif; ?>
            </label>
            <?php
            switch ($type) {
                case 'text':
                case 'email':
                case 'tel':
                case 'date':
                    ?>
                    <input type="<?php echo esc_attr($type); ?>"
                           name="<?php echo esc_attr($key); ?>"
                           <?php echo $required ? 'required' : ''; ?>
                           placeholder="<?php echo esc_attr($placeholder); ?>">
                    <?php
                    break;

                case 'number':
                    ?>
                    <input type="number"
                           name="<?php echo esc_attr($key); ?>"
                           min="1"
                           <?php echo $required ? 'required' : ''; ?>
                           placeholder="<?php echo esc_attr($placeholder); ?>">
                    <?php
                    break;

                case 'textarea':
                    ?>
                    <textarea name="<?php echo esc_attr($key); ?>"
                              rows="3"
                              <?php echo $required ? 'required' : ''; ?>
                              placeholder="<?php echo esc_attr($placeholder); ?>"></textarea>
                    <?php
                    break;

                case 'select':
                    ?>
                    <select name="<?php echo esc_attr($key); ?>" <?php echo $required ? 'required' : ''; ?>>
                        <option value=""><?php esc_html_e('Select', 'pentest-quote-form'); ?></option>
                        <?php foreach ($options as $opt): ?>
                        <option value="<?php echo esc_attr($opt['id']); ?>"><?php echo esc_html($opt['label']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php
                    break;

                case 'radio':
                    ?>
                    <div class="ptf-radio-group">
                        <?php foreach ($options as $opt): ?>
                        <label class="ptf-radio-item">
                            <input type="radio" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($opt['id']); ?>" <?php echo $required ? 'required' : ''; ?>>
                            <span class="ptf-radio-label"><?php echo esc_html($opt['label']); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <?php
                    break;

                case 'checkbox':
                    ?>
                    <div class="ptf-checkbox-group-inline">
                        <?php foreach ($options as $opt): ?>
                        <label class="ptf-checkbox-item-inline">
                            <input type="checkbox" name="<?php echo esc_attr($key); ?>[]" value="<?php echo esc_attr($opt['id']); ?>">
                            <span class="ptf-checkbox-mark-small"></span>
                            <span class="ptf-checkbox-label"><?php echo esc_html($opt['label']); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <?php
                    break;

                default:
                    ?>
                    <input type="text"
                           name="<?php echo esc_attr($key); ?>"
                           <?php echo $required ? 'required' : ''; ?>
                           placeholder="<?php echo esc_attr($placeholder); ?>">
                    <?php
            }
            ?>
            <span class="ptf-field-error"></span>
        </div>
        <?php
        return ob_get_clean();
    }
}

