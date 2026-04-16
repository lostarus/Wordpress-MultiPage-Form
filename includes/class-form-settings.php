<?php
/**
 * Form Settings - WordPress Admin Panel
 * Manage email, colors and other settings
 */

if (!defined('ABSPATH')) {
    exit;
}

class PTF_Form_Settings {

    private static $instance = null;
    private $option_name = 'ptf_settings';

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'), 20);
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_color_picker'));
    }

    /**
     * Default test types
     */
    public static function get_default_test_types() {
        return array(
            array('key' => 'internal-network', 'label' => 'Internal Network Penetration Test', 'active' => true),
            array('key' => 'external-network', 'label' => 'External Network / Internet Penetration Test', 'active' => true),
            array('key' => 'web-application', 'label' => 'Web Application Penetration Test', 'active' => true),
            array('key' => 'api-security', 'label' => 'API Security Test', 'active' => true),
            array('key' => 'mobile-application', 'label' => 'Mobile Application Penetration Test', 'active' => true),
            array('key' => 'wireless-network', 'label' => 'Wireless Network (Wi-Fi) Penetration Test', 'active' => true),
            array('key' => 'social-engineering', 'label' => 'Social Engineering Test', 'active' => true),
            array('key' => 'ddos-simulation', 'label' => 'DDoS Resilience Test', 'active' => true),
        );
    }

    /**
     * Get test types (key => label format)
     */
    public static function get_test_types() {
        $settings = get_option('ptf_settings', array());
        $test_types = isset($settings['test_types']) ? $settings['test_types'] : self::get_default_test_types();

        $result = array();
        foreach ($test_types as $type) {
            if (!empty($type['active'])) {
                $result[$type['key']] = $type['label'];
            }
        }
        return $result;
    }

    /**
     * Get all test types (including active/inactive)
     */
    public static function get_all_test_types() {
        $settings = get_option('ptf_settings', array());
        return isset($settings['test_types']) ? $settings['test_types'] : self::get_default_test_types();
    }

    /**
     * Default settings
     */
    public static function get_defaults() {
        return array(
            'notification_email' => get_option('admin_email'),
            'primary_color' => '#2F7CFF',
            'secondary_color' => '#B7FF10',
            'button_text_color' => '#ffffff',
            'button_size' => 'medium',
            'button_padding_y' => 16,
            'button_padding_x' => 32,
            'button_font_size' => 16,
            'button_text' => __('Get Quick Quote', 'pentest-quote-form'),
            // Font settings
            'font_family' => 'inherit',
            'font_family_custom' => '',
            'heading_font_size' => 26,
            'body_font_size' => 15,
            'label_font_size' => 14,
            'success_message' => __('Your quote request has been received successfully. Our expert team will contact you shortly.', 'pentest-quote-form'),
            'kvkk_url' => '/privacy-notice',
            'privacy_url' => '/privacy-policy',
            'send_auto_reply' => '1',
            'recaptcha_site_key' => '',
            'recaptcha_secret_key' => '',
            'test_types' => self::get_default_test_types(),
            // Data saving settings
            'save_to_database' => '1',
            'send_email_notification' => '1',
            // Webhook/API integrations
            'enable_webhooks' => '0',
            'webhooks' => array(),
            // Salesforce direct integration
            'enable_salesforce' => '0',
            'salesforce_login_url' => 'https://login.salesforce.com',
            'salesforce_client_id' => '',
            'salesforce_client_secret' => '',
            'salesforce_username' => '',
            'salesforce_password' => '',
            'salesforce_object' => 'Lead',
            'salesforce_api_version' => 'v59.0',
            'salesforce_field_mapping' => array(
                'Company'     => 'company',
                'LastName'    => 'first_name',
                'Email'       => 'email',
                'Phone'       => 'phone',
                'Description' => 'test_types_text',
            ),
            // Static form field labels and placeholders
            'field_labels' => array(
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
                // Step 2 - Test Details (dynamic questions step)
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
            ),
        );
    }

    /**
     * Get setting
     */
    public static function get_setting($key) {
        $settings = get_option('ptf_settings', array());
        $defaults = self::get_defaults();

        // Return setting if exists (including empty string or '0')
        if (isset($settings[$key])) {
            return $settings[$key];
        }

        return isset($defaults[$key]) ? $defaults[$key] : '';
    }

    /**
     * Get all settings
     */
    public static function get_all_settings() {
        $settings = get_option('ptf_settings', array());
        return wp_parse_args($settings, self::get_defaults());
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_color_picker($hook) {
        if (strpos($hook, 'ptf-settings') === false) {
            return;
        }

        // WordPress color picker
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('jquery-ui-sortable');

        // Admin Settings CSS
        wp_enqueue_style(
            'ptf-admin-settings',
            PTF_PLUGIN_URL . 'assets/css/admin-settings.css',
            array(),
            PTF_VERSION
        );

        // Admin Utilities JS (shared functions)
        wp_enqueue_script(
            'ptf-admin-utils',
            PTF_PLUGIN_URL . 'assets/js/admin-utils.js',
            array(),
            PTF_VERSION,
            true
        );

        // Admin Settings JS
        wp_enqueue_script(
            'ptf-admin-settings',
            PTF_PLUGIN_URL . 'assets/js/admin-settings.js',
            array('jquery', 'wp-color-picker', 'ptf-admin-utils'),
            PTF_VERSION,
            true
        );

        // Localize script
        wp_localize_script('ptf-admin-settings', 'ptfSettingsAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ptf_test_webhooks'),
            'i18n' => array(
                'noWebhooks' => __('No webhooks configured yet.', 'pentest-quote-form'),
                'name' => __('Name', 'pentest-quote-form'),
                'type' => __('Type', 'pentest-quote-form'),
                'url' => __('URL', 'pentest-quote-form'),
                'status' => __('Status', 'pentest-quote-form'),
                'actions' => __('Actions', 'pentest-quote-form'),
                'active' => __('Active', 'pentest-quote-form'),
                'inactive' => __('Inactive', 'pentest-quote-form'),
                'edit' => __('Edit', 'pentest-quote-form'),
                'delete' => __('Delete', 'pentest-quote-form'),
                'addWebhook' => __('Add Webhook', 'pentest-quote-form'),
                'editWebhook' => __('Edit Webhook', 'pentest-quote-form'),
                'method' => __('Method', 'pentest-quote-form'),
                'authType' => __('Authentication', 'pentest-quote-form'),
                'noAuth' => __('None', 'pentest-quote-form'),
                'authValue' => __('Auth Value', 'pentest-quote-form'),
                'authValueDesc' => __('Token, username:password, or API key', 'pentest-quote-form'),
                'cancel' => __('Cancel', 'pentest-quote-form'),
                'save' => __('Save', 'pentest-quote-form'),
                'requiredFields' => __('Name and URL are required.', 'pentest-quote-form'),
                'confirmDelete' => __('Are you sure you want to delete this webhook?', 'pentest-quote-form'),
                'validJson' => __('Valid JSON', 'pentest-quote-form'),
                'invalidJson' => __('Invalid JSON:', 'pentest-quote-form'),
                'formatError' => __('Cannot format invalid JSON', 'pentest-quote-form'),
                'testing' => __('Testing...', 'pentest-quote-form'),
                'testWebhooks' => __('Test Webhooks', 'pentest-quote-form'),
                'testingWebhooks' => __('Testing webhooks...', 'pentest-quote-form'),
                'testResults' => __('Test Results:', 'pentest-quote-form'),
                'testError' => __('Test failed', 'pentest-quote-form'),
                'ajaxError' => __('Connection error', 'pentest-quote-form'),
            ),
        ));
    }

    /**
     * Add settings page to menu
     */
    public function add_settings_page() {
        add_submenu_page(
            'ptf-submissions',
            __('Form Settings', 'pentest-quote-form'),
            __('Settings', 'pentest-quote-form'),
            'manage_options',
            'ptf-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'ptf_settings_group',
            $this->option_name,
            array($this, 'sanitize_settings')
        );
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();

        $sanitized['notification_email'] = isset($input['notification_email'])
            ? sanitize_text_field($input['notification_email']) : '';

        $sanitized['primary_color'] = isset($input['primary_color'])
            ? sanitize_hex_color($input['primary_color']) : '#2F7CFF';

        $sanitized['secondary_color'] = isset($input['secondary_color'])
            ? sanitize_hex_color($input['secondary_color']) : '#B7FF10';

        $sanitized['button_text_color'] = isset($input['button_text_color'])
            ? sanitize_hex_color($input['button_text_color']) : '#ffffff';

        $sanitized['button_size'] = isset($input['button_size']) && in_array($input['button_size'], array('small', 'medium', 'large', 'xlarge', 'custom'))
            ? $input['button_size'] : 'medium';

        // Custom button size values
        $sanitized['button_padding_y'] = isset($input['button_padding_y'])
            ? max(4, min(60, intval($input['button_padding_y']))) : 16;
        $sanitized['button_padding_x'] = isset($input['button_padding_x'])
            ? max(8, min(100, intval($input['button_padding_x']))) : 32;
        $sanitized['button_font_size'] = isset($input['button_font_size'])
            ? max(10, min(32, intval($input['button_font_size']))) : 16;

        // Font settings
        $allowed_fonts = array('inherit', 'system', 'inter', 'roboto', 'opensans', 'lato', 'poppins', 'montserrat', 'nunito', 'custom');
        $sanitized['font_family'] = isset($input['font_family']) && in_array($input['font_family'], $allowed_fonts)
            ? $input['font_family'] : 'inherit';
        $sanitized['font_family_custom'] = isset($input['font_family_custom'])
            ? sanitize_text_field($input['font_family_custom']) : '';
        $sanitized['heading_font_size'] = isset($input['heading_font_size'])
            ? max(14, min(48, intval($input['heading_font_size']))) : 26;
        $sanitized['body_font_size'] = isset($input['body_font_size'])
            ? max(10, min(24, intval($input['body_font_size']))) : 15;
        $sanitized['label_font_size'] = isset($input['label_font_size'])
            ? max(10, min(20, intval($input['label_font_size']))) : 14;

        $sanitized['button_text'] = isset($input['button_text'])
            ? sanitize_text_field($input['button_text']) : __('Get Quick Quote', 'pentest-quote-form');

        $sanitized['success_message'] = isset($input['success_message'])
            ? sanitize_textarea_field($input['success_message']) : '';

        $sanitized['kvkk_url'] = isset($input['kvkk_url'])
            ? esc_url_raw($input['kvkk_url']) : '/kvkk-aydinlatma-metni';

        $sanitized['privacy_url'] = isset($input['privacy_url'])
            ? esc_url_raw($input['privacy_url']) : '/gizlilik-politikasi';

        $sanitized['send_auto_reply'] = isset($input['send_auto_reply']) ? '1' : '0';

        $sanitized['recaptcha_site_key'] = isset($input['recaptcha_site_key'])
            ? sanitize_text_field($input['recaptcha_site_key']) : '';

        $sanitized['recaptcha_secret_key'] = isset($input['recaptcha_secret_key'])
            ? sanitize_text_field($input['recaptcha_secret_key']) : '';

        // Data saving settings
        $sanitized['save_to_database'] = isset($input['save_to_database']) ? '1' : '0';
        $sanitized['send_email_notification'] = isset($input['send_email_notification']) ? '1' : '0';

        // Webhook/API integrations
        $sanitized['enable_webhooks'] = isset($input['enable_webhooks']) ? '1' : '0';

        // Salesforce direct integration
        $sanitized['enable_salesforce'] = isset($input['enable_salesforce']) ? '1' : '0';

        $allowed_sf_login_urls = array('https://login.salesforce.com', 'https://test.salesforce.com');
        $sf_login_url = isset($input['salesforce_login_url']) ? esc_url_raw(trim($input['salesforce_login_url'])) : 'https://login.salesforce.com';
        $sanitized['salesforce_login_url'] = in_array($sf_login_url, $allowed_sf_login_urls) ? $sf_login_url : 'https://login.salesforce.com';

        $sanitized['salesforce_client_id']     = isset($input['salesforce_client_id'])     ? sanitize_text_field($input['salesforce_client_id'])     : '';
        $sanitized['salesforce_client_secret'] = isset($input['salesforce_client_secret']) ? sanitize_text_field($input['salesforce_client_secret']) : '';
        $sanitized['salesforce_username']      = isset($input['salesforce_username'])      ? sanitize_email($input['salesforce_username'])           : '';
        // Password + Security Token — stored as-is (encrypted storage is out of scope, same pattern as other secrets)
        $sanitized['salesforce_password']      = isset($input['salesforce_password'])      ? sanitize_text_field($input['salesforce_password'])      : '';

        $allowed_sf_objects = array('Lead', 'Contact', 'Account', 'Opportunity', 'Case');
        $sf_object = isset($input['salesforce_object']) ? sanitize_text_field($input['salesforce_object']) : 'Lead';
        $sanitized['salesforce_object'] = in_array($sf_object, $allowed_sf_objects) ? $sf_object : 'Lead';

        $sanitized['salesforce_api_version'] = isset($input['salesforce_api_version']) ? sanitize_text_field($input['salesforce_api_version']) : 'v59.0';

        // Salesforce field mapping (JSON textarea → associative array)
        if (isset($input['salesforce_field_mapping_json']) && !empty($input['salesforce_field_mapping_json'])) {
            $sf_mapping = json_decode(stripslashes($input['salesforce_field_mapping_json']), true);
            if (is_array($sf_mapping)) {
                $sanitized_mapping = array();
                foreach ($sf_mapping as $sf_field => $form_field) {
                    $sanitized_mapping[sanitize_text_field($sf_field)] = sanitize_text_field($form_field);
                }
                $sanitized['salesforce_field_mapping'] = $sanitized_mapping;
            } else {
                $sanitized['salesforce_field_mapping'] = array();
            }
        } else {
            $current = get_option('ptf_settings', array());
            $sanitized['salesforce_field_mapping'] = isset($current['salesforce_field_mapping']) ? $current['salesforce_field_mapping'] : array();
        }

        // Process webhooks JSON
        if (isset($input['webhooks_json']) && !empty($input['webhooks_json'])) {
            $webhooks_data = json_decode(stripslashes($input['webhooks_json']), true);
            if (is_array($webhooks_data)) {
                $sanitized_webhooks = array();
                foreach ($webhooks_data as $webhook) {
                    if (!empty($webhook['name']) && !empty($webhook['url'])) {
                        $sanitized_webhooks[] = array(
                            'name' => sanitize_text_field($webhook['name']),
                            'url' => esc_url_raw($webhook['url']),
                            'method' => isset($webhook['method']) && in_array($webhook['method'], array('POST', 'PUT', 'PATCH')) ? $webhook['method'] : 'POST',
                            'headers' => isset($webhook['headers']) && is_array($webhook['headers']) ? array_map('sanitize_text_field', $webhook['headers']) : array(),
                            'auth_type' => isset($webhook['auth_type']) && in_array($webhook['auth_type'], array('none', 'bearer', 'basic', 'api_key')) ? $webhook['auth_type'] : 'none',
                            'auth_value' => isset($webhook['auth_value']) ? sanitize_text_field($webhook['auth_value']) : '',
                            'field_mapping' => isset($webhook['field_mapping']) && is_array($webhook['field_mapping']) ? $webhook['field_mapping'] : array(),
                            'active' => isset($webhook['active']) ? (bool) $webhook['active'] : true,
                            'type' => isset($webhook['type']) && in_array($webhook['type'], array('custom', 'power_automate', 'zapier', 'make')) ? $webhook['type'] : 'custom',
                        );
                    }
                }
                $sanitized['webhooks'] = $sanitized_webhooks;
            } else {
                $sanitized['webhooks'] = array();
            }
        } else {
            // Keep existing webhooks
            $current = get_option('ptf_settings', array());
            $sanitized['webhooks'] = isset($current['webhooks']) ? $current['webhooks'] : array();
        }

        // Process test types
        if (isset($input['test_types']) && is_array($input['test_types'])) {
            $sanitized_types = array();
            foreach ($input['test_types'] as $type) {
                if (!empty($type['key']) && !empty($type['label'])) {
                    $sanitized_types[] = array(
                        'key' => sanitize_key($type['key']),
                        'label' => sanitize_text_field($type['label']),
                        'active' => isset($type['active']) ? true : false,
                    );
                }
            }
            $sanitized['test_types'] = $sanitized_types;
        } else {
            $sanitized['test_types'] = self::get_default_test_types();
        }

        // Process field labels
        $defaults = self::get_defaults();
        $default_labels = $defaults['field_labels'];
        $sanitized['field_labels'] = array();

        if (isset($input['field_labels']) && is_array($input['field_labels'])) {
            foreach ($default_labels as $field_key => $field_defaults) {
                $sanitized['field_labels'][$field_key] = array();
                foreach ($field_defaults as $prop => $default_value) {
                    if (isset($input['field_labels'][$field_key][$prop])) {
                        $sanitized['field_labels'][$field_key][$prop] = sanitize_text_field($input['field_labels'][$field_key][$prop]);
                    } else {
                        $sanitized['field_labels'][$field_key][$prop] = $default_value;
                    }
                }
            }
        } else {
            $sanitized['field_labels'] = $default_labels;
        }

        return $sanitized;
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        $settings = self::get_all_settings();
        ?>
        <div class="wrap">
            <h1>
                <span class="dashicons dashicons-admin-settings" style="margin-right: 8px;"></span>
                <?php esc_html_e('Pentest Quote Form Settings', 'pentest-quote-form'); ?>
            </h1>

            <form method="post" action="options.php">
                <?php settings_fields('ptf_settings_group'); ?>

                <div class="ptf-settings-container">
                    <!-- Email Settings -->
                    <div class="ptf-settings-section">
                        <h2><span class="dashicons dashicons-email"></span> <?php esc_html_e('Email Settings', 'pentest-quote-form'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="notification_email"><?php esc_html_e('Notification Email Address', 'pentest-quote-form'); ?></label>
                                </th>
                                <td>
                                    <input type="text"
                                           id="notification_email"
                                           name="ptf_settings[notification_email]"
                                           value="<?php echo esc_attr($settings['notification_email']); ?>"
                                           class="regular-text"
                                           placeholder="ornek@firmaniz.com">
                                    <p class="description">
                                        <?php esc_html_e('Email address to receive notifications when form is submitted.', 'pentest-quote-form'); ?><br>
                                        <?php esc_html_e('For multiple addresses, separate with comma:', 'pentest-quote-form'); ?> <code>satis@firma.com, teknik@firma.com</code>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Auto Reply', 'pentest-quote-form'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox"
                                               name="ptf_settings[send_auto_reply]"
                                               value="1"
                                               <?php checked($settings['send_auto_reply'], '1'); ?>>
                                        <?php esc_html_e('Send automatic confirmation email to form submitter', 'pentest-quote-form'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Data Storage Settings -->
                    <div class="ptf-settings-section">
                        <h2><span class="dashicons dashicons-database"></span> <?php esc_html_e('Data Storage Settings', 'pentest-quote-form'); ?></h2>
                        <p class="description" style="margin-bottom: 15px;">
                            <?php esc_html_e('Define how form submissions will be processed. At least one option must be active.', 'pentest-quote-form'); ?>
                        </p>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e('Save to Database', 'pentest-quote-form'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox"
                                               name="ptf_settings[save_to_database]"
                                               value="1"
                                               id="save_to_database"
                                               <?php checked($settings['save_to_database'], '1'); ?>>
                                        <?php esc_html_e('Save form data to WordPress database', 'pentest-quote-form'); ?>
                                    </label>
                                    <p class="description">
                                        <?php esc_html_e('When active, you can view all form submissions from the "Quote Requests" page.', 'pentest-quote-form'); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Send Email Notification', 'pentest-quote-form'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox"
                                               name="ptf_settings[send_email_notification]"
                                               value="1"
                                               id="send_email_notification"
                                               <?php checked($settings['send_email_notification'], '1'); ?>>
                                        <?php esc_html_e('Send notification email when form is submitted', 'pentest-quote-form'); ?>
                                    </label>
                                    <p class="description">
                                        <?php esc_html_e('When active, notification will be sent to the email address specified above.', 'pentest-quote-form'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                        <div class="ptf-data-storage-warning" id="data-storage-warning" style="display: none; margin-top: 15px; padding: 10px; border-radius: 4px; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;">
                            ⚠️ <?php esc_html_e('Warning: At least one option must be active! Otherwise form data will not be saved anywhere.', 'pentest-quote-form'); ?>
                        </div>
                    </div>

                    <!-- Color Settings -->
                    <div class="ptf-settings-section">
                        <h2><span class="dashicons dashicons-art"></span> <?php esc_html_e('Color Settings', 'pentest-quote-form'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="primary_color"><?php esc_html_e('Primary Color', 'pentest-quote-form'); ?></label>
                                </th>
                                <td>
                                    <input type="text"
                                           id="primary_color"
                                           name="ptf_settings[primary_color]"
                                           value="<?php echo esc_attr($settings['primary_color']); ?>"
                                           class="ptf-color-picker"
                                           data-default-color="#2F7CFF">
                                    <p class="description"><?php esc_html_e('Used for buttons, active elements and highlights.', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="secondary_color"><?php esc_html_e('Secondary Color', 'pentest-quote-form'); ?></label>
                                </th>
                                <td>
                                    <input type="text"
                                           id="secondary_color"
                                           name="ptf_settings[secondary_color]"
                                           value="<?php echo esc_attr($settings['secondary_color']); ?>"
                                           class="ptf-color-picker"
                                           data-default-color="#B7FF10">
                                    <p class="description"><?php esc_html_e('Used for success icons and highlight elements.', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="button_text_color"><?php esc_html_e('Button Text Color', 'pentest-quote-form'); ?></label>
                                </th>
                                <td>
                                    <input type="text"
                                           id="button_text_color"
                                           name="ptf_settings[button_text_color]"
                                           value="<?php echo esc_attr($settings['button_text_color']); ?>"
                                           class="ptf-color-picker"
                                           data-default-color="#ffffff">
                                    <p class="description"><?php esc_html_e('Text color for the popup trigger button. Use contrasting color for better readability.', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="button_size"><?php esc_html_e('Button Size', 'pentest-quote-form'); ?></label>
                                </th>
                                <td>
                                    <select id="button_size" name="ptf_settings[button_size]">
                                        <option value="small" <?php selected($settings['button_size'], 'small'); ?>><?php esc_html_e('Small', 'pentest-quote-form'); ?></option>
                                        <option value="medium" <?php selected($settings['button_size'], 'medium'); ?>><?php esc_html_e('Medium', 'pentest-quote-form'); ?></option>
                                        <option value="large" <?php selected($settings['button_size'], 'large'); ?>><?php esc_html_e('Large', 'pentest-quote-form'); ?></option>
                                        <option value="xlarge" <?php selected($settings['button_size'], 'xlarge'); ?>><?php esc_html_e('Extra Large', 'pentest-quote-form'); ?></option>
                                        <option value="custom" <?php selected($settings['button_size'], 'custom'); ?>><?php esc_html_e('Custom (px)', 'pentest-quote-form'); ?></option>
                                    </select>
                                    <p class="description"><?php esc_html_e('Size of the popup trigger button.', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                            <tr id="custom-button-size-row" style="<?php echo $settings['button_size'] === 'custom' ? '' : 'display:none;'; ?>">
                                <th scope="row">
                                    <label for="button_padding_y"><?php esc_html_e('Custom Button Padding', 'pentest-quote-form'); ?></label>
                                </th>
                                <td>
                                    <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                                        <div>
                                            <label for="button_padding_y" style="font-weight: normal; font-size: 12px;"><?php esc_html_e('Vertical (px)', 'pentest-quote-form'); ?></label><br>
                                            <input type="number"
                                                   id="button_padding_y"
                                                   name="ptf_settings[button_padding_y]"
                                                   value="<?php echo esc_attr($settings['button_padding_y'] ?? 16); ?>"
                                                   min="4"
                                                   max="60"
                                                   style="width: 80px;">
                                        </div>
                                        <div>
                                            <label for="button_padding_x" style="font-weight: normal; font-size: 12px;"><?php esc_html_e('Horizontal (px)', 'pentest-quote-form'); ?></label><br>
                                            <input type="number"
                                                   id="button_padding_x"
                                                   name="ptf_settings[button_padding_x]"
                                                   value="<?php echo esc_attr($settings['button_padding_x'] ?? 32); ?>"
                                                   min="8"
                                                   max="100"
                                                   style="width: 80px;">
                                        </div>
                                        <div>
                                            <label for="button_font_size" style="font-weight: normal; font-size: 12px;"><?php esc_html_e('Font Size (px)', 'pentest-quote-form'); ?></label><br>
                                            <input type="number"
                                                   id="button_font_size"
                                                   name="ptf_settings[button_font_size]"
                                                   value="<?php echo esc_attr($settings['button_font_size'] ?? 16); ?>"
                                                   min="10"
                                                   max="32"
                                                   style="width: 80px;">
                                        </div>
                                    </div>
                                    <p class="description"><?php esc_html_e('Enter custom padding and font size values in pixels.', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                        </table>

                        <!-- Typography Settings -->
                        <h4 style="margin-top: 30px; margin-bottom: 15px; border-bottom: 2px solid #2F7CFF; padding-bottom: 8px; color: #2F7CFF;">
                            <span class="dashicons dashicons-editor-textcolor" style="vertical-align: middle;"></span>
                            <?php esc_html_e('Typography', 'pentest-quote-form'); ?>
                        </h4>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="font_family"><?php esc_html_e('Font Family', 'pentest-quote-form'); ?></label>
                                </th>
                                <td>
                                    <select id="font_family" name="ptf_settings[font_family]">
                                        <option value="inherit" <?php selected($settings['font_family'] ?? 'inherit', 'inherit'); ?>><?php esc_html_e('Inherit from theme', 'pentest-quote-form'); ?></option>
                                        <option value="system" <?php selected($settings['font_family'] ?? 'inherit', 'system'); ?>>System UI (San Francisco, Segoe UI)</option>
                                        <option value="inter" <?php selected($settings['font_family'] ?? 'inherit', 'inter'); ?>>Inter</option>
                                        <option value="roboto" <?php selected($settings['font_family'] ?? 'inherit', 'roboto'); ?>>Roboto</option>
                                        <option value="opensans" <?php selected($settings['font_family'] ?? 'inherit', 'opensans'); ?>>Open Sans</option>
                                        <option value="lato" <?php selected($settings['font_family'] ?? 'inherit', 'lato'); ?>>Lato</option>
                                        <option value="poppins" <?php selected($settings['font_family'] ?? 'inherit', 'poppins'); ?>>Poppins</option>
                                        <option value="montserrat" <?php selected($settings['font_family'] ?? 'inherit', 'montserrat'); ?>>Montserrat</option>
                                        <option value="nunito" <?php selected($settings['font_family'] ?? 'inherit', 'nunito'); ?>>Nunito</option>
                                        <option value="custom" <?php selected($settings['font_family'] ?? 'inherit', 'custom'); ?>><?php esc_html_e('Custom', 'pentest-quote-form'); ?></option>
                                    </select>
                                    <p class="description"><?php esc_html_e('Select a font family for the form. Google Fonts will be loaded automatically.', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                            <tr id="custom-font-row" style="<?php echo ($settings['font_family'] ?? 'inherit') === 'custom' ? '' : 'display:none;'; ?>">
                                <th scope="row">
                                    <label for="font_family_custom"><?php esc_html_e('Custom Font Family', 'pentest-quote-form'); ?></label>
                                </th>
                                <td>
                                    <input type="text"
                                           id="font_family_custom"
                                           name="ptf_settings[font_family_custom]"
                                           value="<?php echo esc_attr($settings['font_family_custom'] ?? ''); ?>"
                                           class="regular-text"
                                           placeholder="'Your Font', sans-serif">
                                    <p class="description"><?php esc_html_e('Enter custom font-family CSS value. Make sure the font is loaded on your site.', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label><?php esc_html_e('Font Sizes (px)', 'pentest-quote-form'); ?></label>
                                </th>
                                <td>
                                    <div style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap;">
                                        <div>
                                            <label for="heading_font_size" style="font-weight: normal; font-size: 12px; display: block; margin-bottom: 4px;"><?php esc_html_e('Headings', 'pentest-quote-form'); ?></label>
                                            <input type="number"
                                                   id="heading_font_size"
                                                   name="ptf_settings[heading_font_size]"
                                                   value="<?php echo esc_attr($settings['heading_font_size'] ?? 26); ?>"
                                                   min="14"
                                                   max="48"
                                                   style="width: 80px;">
                                        </div>
                                        <div>
                                            <label for="body_font_size" style="font-weight: normal; font-size: 12px; display: block; margin-bottom: 4px;"><?php esc_html_e('Body Text', 'pentest-quote-form'); ?></label>
                                            <input type="number"
                                                   id="body_font_size"
                                                   name="ptf_settings[body_font_size]"
                                                   value="<?php echo esc_attr($settings['body_font_size'] ?? 15); ?>"
                                                   min="10"
                                                   max="24"
                                                   style="width: 80px;">
                                        </div>
                                        <div>
                                            <label for="label_font_size" style="font-weight: normal; font-size: 12px; display: block; margin-bottom: 4px;"><?php esc_html_e('Labels', 'pentest-quote-form'); ?></label>
                                            <input type="number"
                                                   id="label_font_size"
                                                   name="ptf_settings[label_font_size]"
                                                   value="<?php echo esc_attr($settings['label_font_size'] ?? 14); ?>"
                                                   min="10"
                                                   max="20"
                                                   style="width: 80px;">
                                        </div>
                                    </div>
                                    <p class="description"><?php esc_html_e('Customize font sizes for different elements.', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                        </table>

                        <!-- Renk Preview -->
                        <div class="ptf-color-preview">
                            <h4><?php esc_html_e('Preview', 'pentest-quote-form'); ?></h4>
                            <div class="preview-buttons">
                                <button type="button" class="preview-btn preview-btn-primary" id="preview-primary">
                                    <?php esc_html_e('Get Quick Quote', 'pentest-quote-form'); ?>
                                </button>
                                <span class="preview-success" id="preview-secondary">✓ <?php esc_html_e('Success', 'pentest-quote-form'); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Text Settings -->
                    <div class="ptf-settings-section">
                        <h2><span class="dashicons dashicons-editor-textcolor"></span> <?php esc_html_e('Text Settings', 'pentest-quote-form'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="button_text"><?php esc_html_e('Default Button Text', 'pentest-quote-form'); ?></label>
                                </th>
                                <td>
                                    <input type="text"
                                           id="button_text"
                                           name="ptf_settings[button_text]"
                                           value="<?php echo esc_attr($settings['button_text']); ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="success_message"><?php esc_html_e('Success Message', 'pentest-quote-form'); ?></label>
                                </th>
                                <td>
                                    <textarea id="success_message"
                                              name="ptf_settings[success_message]"
                                              rows="3"
                                              class="large-text"><?php echo esc_textarea($settings['success_message']); ?></textarea>
                                    <p class="description"><?php esc_html_e('Message displayed after form is successfully submitted.', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Form Field Labels Settings -->
                    <div class="ptf-settings-section">
                        <h2><span class="dashicons dashicons-forms"></span> <?php esc_html_e('Form Labels & Texts', 'pentest-quote-form'); ?></h2>
                        <p class="description" style="margin-bottom: 15px;">
                            <?php esc_html_e('Customize all form labels, placeholders, button texts and step names.', 'pentest-quote-form'); ?>
                        </p>
                        <?php
                        $field_labels = isset($settings['field_labels']) ? $settings['field_labels'] : array();
                        $defaults = self::get_defaults();
                        $default_labels = $defaults['field_labels'];
                        ?>

                        <!-- Form Header -->
                        <h4 style="margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #2F7CFF; padding-bottom: 5px; color: #2F7CFF;">
                            <span class="dashicons dashicons-heading" style="vertical-align: middle;"></span>
                            <?php esc_html_e('Form Header', 'pentest-quote-form'); ?>
                        </h4>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Form Title', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][form_header][title]"
                                           value="<?php echo esc_attr($field_labels['form_header']['title'] ?? ($default_labels['form_header']['title'] ?? 'Get Quick Quote')); ?>"
                                           class="regular-text">
                                    <p class="description"><?php esc_html_e('Default: Get Quick Quote', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Form Subtitle', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][form_header][subtitle]"
                                           value="<?php echo esc_attr($field_labels['form_header']['subtitle'] ?? ($default_labels['form_header']['subtitle'] ?? 'Get a quote for your cybersecurity needs')); ?>"
                                           class="large-text">
                                    <p class="description"><?php esc_html_e('Default: Get a quote for your cybersecurity needs', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                        </table>

                        <!-- Progress Bar Step Names -->
                        <h4 style="margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #2F7CFF; padding-bottom: 5px; color: #2F7CFF;">
                            <span class="dashicons dashicons-editor-ol" style="vertical-align: middle;"></span>
                            <?php esc_html_e('Progress Bar Step Names', 'pentest-quote-form'); ?>
                        </h4>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Step 1 Name', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][step1][title]"
                                           value="<?php echo esc_attr($field_labels['step1']['title'] ?? $default_labels['step1']['title']); ?>"
                                           class="regular-text">
                                    <p class="description"><?php esc_html_e('Default: Test Selection', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Step 2 Name', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][step2][title]"
                                           value="<?php echo esc_attr($field_labels['step2']['title'] ?? $default_labels['step2']['title']); ?>"
                                           class="regular-text">
                                    <p class="description"><?php esc_html_e('Default: Test Details', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Step 3 Name', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][step3][title]"
                                           value="<?php echo esc_attr($field_labels['step3']['title'] ?? $default_labels['step3']['title']); ?>"
                                           class="regular-text">
                                    <p class="description"><?php esc_html_e('Default: Contact Information', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                        </table>

                        <!-- Step 1 - Test Selection -->
                        <h4 style="margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #2F7CFF; padding-bottom: 5px; color: #2F7CFF;">
                            <span class="dashicons dashicons-yes-alt" style="vertical-align: middle;"></span>
                            <?php esc_html_e('Step 1: Test Selection Page', 'pentest-quote-form'); ?>
                        </h4>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Page Title', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][test_selection][title]"
                                           value="<?php echo esc_attr($field_labels['test_selection']['title'] ?? $default_labels['test_selection']['title']); ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Page Description', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][test_selection][description]"
                                           value="<?php echo esc_attr($field_labels['test_selection']['description'] ?? $default_labels['test_selection']['description']); ?>"
                                           class="large-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Multi-select Hint', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][test_selection][multi_select_hint]"
                                           value="<?php echo esc_attr($field_labels['test_selection']['multi_select_hint'] ?? $default_labels['test_selection']['multi_select_hint']); ?>"
                                           class="regular-text">
                                    <p class="description"><?php esc_html_e('Default: (You can select multiple)', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                        </table>

                        <!-- Step 2 - Test Details (if exists) -->
                        <h4 style="margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #2F7CFF; padding-bottom: 5px; color: #2F7CFF;">
                            <span class="dashicons dashicons-list-view" style="vertical-align: middle;"></span>
                            <?php esc_html_e('Step 2: Test Details Page', 'pentest-quote-form'); ?>
                        </h4>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Page Title', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][test_details][title]"
                                           value="<?php echo esc_attr($field_labels['test_details']['title'] ?? $default_labels['test_details']['title']); ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Page Description', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][test_details][description]"
                                           value="<?php echo esc_attr($field_labels['test_details']['description'] ?? $default_labels['test_details']['description']); ?>"
                                           class="large-text">
                                </td>
                            </tr>
                        </table>

                        <!-- Step 3 - Contact Information -->
                        <h4 style="margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #2F7CFF; padding-bottom: 5px; color: #2F7CFF;">
                            <span class="dashicons dashicons-id-alt" style="vertical-align: middle;"></span>
                            <?php esc_html_e('Step 3: Contact Information Page', 'pentest-quote-form'); ?>
                        </h4>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Page Title', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][contact_step][title]"
                                           value="<?php echo esc_attr($field_labels['contact_step']['title'] ?? $default_labels['contact_step']['title']); ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Page Description', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][contact_step][description]"
                                           value="<?php echo esc_attr($field_labels['contact_step']['description'] ?? $default_labels['contact_step']['description']); ?>"
                                           class="large-text">
                                </td>
                            </tr>
                        </table>

                        <!-- Form Fields -->
                        <h4 style="margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #2F7CFF; padding-bottom: 5px; color: #2F7CFF;">
                            <span class="dashicons dashicons-edit" style="vertical-align: middle;"></span>
                            <?php esc_html_e('Form Field Labels & Placeholders', 'pentest-quote-form'); ?>
                        </h4>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Company - Label', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][company][label]"
                                           value="<?php echo esc_attr($field_labels['company']['label'] ?? $default_labels['company']['label']); ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Company - Placeholder', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][company][placeholder]"
                                           value="<?php echo esc_attr($field_labels['company']['placeholder'] ?? $default_labels['company']['placeholder']); ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Contact Person - Label', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][first_name][label]"
                                           value="<?php echo esc_attr($field_labels['first_name']['label'] ?? $default_labels['first_name']['label']); ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Contact Person - Placeholder', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][first_name][placeholder]"
                                           value="<?php echo esc_attr($field_labels['first_name']['placeholder'] ?? $default_labels['first_name']['placeholder']); ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Email - Label', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][email][label]"
                                           value="<?php echo esc_attr($field_labels['email']['label'] ?? $default_labels['email']['label']); ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Email - Placeholder', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][email][placeholder]"
                                           value="<?php echo esc_attr($field_labels['email']['placeholder'] ?? $default_labels['email']['placeholder']); ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Email - Hint Text', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][email][hint]"
                                           value="<?php echo esc_attr($field_labels['email']['hint'] ?? $default_labels['email']['hint']); ?>"
                                           class="large-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Phone - Label', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][phone][label]"
                                           value="<?php echo esc_attr($field_labels['phone']['label'] ?? $default_labels['phone']['label']); ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Phone - Placeholder', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][phone][placeholder]"
                                           value="<?php echo esc_attr($field_labels['phone']['placeholder'] ?? $default_labels['phone']['placeholder']); ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                        </table>

                        <!-- Privacy Consent -->
                        <h4 style="margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #2F7CFF; padding-bottom: 5px; color: #2F7CFF;">
                            <span class="dashicons dashicons-shield" style="vertical-align: middle;"></span>
                            <?php esc_html_e('Privacy Consent Texts', 'pentest-quote-form'); ?>
                        </h4>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Consent Text Start', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][kvkk_consent][text]"
                                           value="<?php echo esc_attr($field_labels['kvkk_consent']['text'] ?? $default_labels['kvkk_consent']['text']); ?>"
                                           class="large-text">
                                    <p class="description"><?php esc_html_e('Text before the first link', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Privacy Notice Link Text', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][kvkk_consent][privacy_notice]"
                                           value="<?php echo esc_attr($field_labels['kvkk_consent']['privacy_notice'] ?? $default_labels['kvkk_consent']['privacy_notice']); ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('"And" Text', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][kvkk_consent][and_text]"
                                           value="<?php echo esc_attr($field_labels['kvkk_consent']['and_text'] ?? $default_labels['kvkk_consent']['and_text']); ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Privacy Policy Link Text', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][kvkk_consent][privacy_policy]"
                                           value="<?php echo esc_attr($field_labels['kvkk_consent']['privacy_policy'] ?? $default_labels['kvkk_consent']['privacy_policy']); ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                        </table>

                        <!-- Buttons -->
                        <h4 style="margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #2F7CFF; padding-bottom: 5px; color: #2F7CFF;">
                            <span class="dashicons dashicons-button" style="vertical-align: middle;"></span>
                            <?php esc_html_e('Button Texts', 'pentest-quote-form'); ?>
                        </h4>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Next/Continue Button', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][buttons][next]"
                                           value="<?php echo esc_attr($field_labels['buttons']['next'] ?? $default_labels['buttons']['next']); ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Back Button', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][buttons][prev]"
                                           value="<?php echo esc_attr($field_labels['buttons']['prev'] ?? $default_labels['buttons']['prev']); ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Submit Button', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][buttons][submit]"
                                           value="<?php echo esc_attr($field_labels['buttons']['submit'] ?? $default_labels['buttons']['submit']); ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                        </table>

                        <!-- Messages -->
                        <h4 style="margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #2F7CFF; padding-bottom: 5px; color: #2F7CFF;">
                            <span class="dashicons dashicons-megaphone" style="vertical-align: middle;"></span>
                            <?php esc_html_e('Messages', 'pentest-quote-form'); ?>
                        </h4>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Success Title', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][messages][success_title]"
                                           value="<?php echo esc_attr($field_labels['messages']['success_title'] ?? $default_labels['messages']['success_title']); ?>"
                                           class="regular-text">
                                    <p class="description"><?php esc_html_e('Default: Thank You!', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Loading Text', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][messages][loading]"
                                           value="<?php echo esc_attr($field_labels['messages']['loading'] ?? $default_labels['messages']['loading']); ?>"
                                           class="regular-text">
                                    <p class="description"><?php esc_html_e('Default: Sending...', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                        </table>

                        <!-- Validation Messages -->
                        <h4 style="margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #2F7CFF; padding-bottom: 5px; color: #2F7CFF;">
                            <span class="dashicons dashicons-warning" style="vertical-align: middle;"></span>
                            <?php esc_html_e('Validation Messages', 'pentest-quote-form'); ?>
                        </h4>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Required Field', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][validation][required]"
                                           value="<?php echo esc_attr($field_labels['validation']['required'] ?? $default_labels['validation']['required']); ?>"
                                           class="regular-text">
                                    <p class="description"><?php esc_html_e('Default: This field is required.', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Invalid Email', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][validation][email]"
                                           value="<?php echo esc_attr($field_labels['validation']['email'] ?? $default_labels['validation']['email']); ?>"
                                           class="large-text">
                                    <p class="description"><?php esc_html_e('Default: Please enter a valid email address.', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Corporate Email Required', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][validation][corporate_email]"
                                           value="<?php echo esc_attr($field_labels['validation']['corporate_email'] ?? $default_labels['validation']['corporate_email']); ?>"
                                           class="large-text">
                                    <p class="description"><?php esc_html_e('Default: Please enter your corporate email address. Personal email addresses are not accepted.', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Invalid Phone', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][validation][phone]"
                                           value="<?php echo esc_attr($field_labels['validation']['phone'] ?? $default_labels['validation']['phone']); ?>"
                                           class="regular-text">
                                    <p class="description"><?php esc_html_e('Default: Please enter a valid phone number.', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Checkbox Required', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][validation][checkbox_required]"
                                           value="<?php echo esc_attr($field_labels['validation']['checkbox_required'] ?? $default_labels['validation']['checkbox_required']); ?>"
                                           class="regular-text">
                                    <p class="description"><?php esc_html_e('Default: You must accept this to continue.', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('Test Type Required', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][validation][test_type_required]"
                                           value="<?php echo esc_attr($field_labels['validation']['test_type_required'] ?? $default_labels['validation']['test_type_required']); ?>"
                                           class="large-text">
                                    <p class="description"><?php esc_html_e('Default: Please select at least one test type.', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('General Error', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][validation][error]"
                                           value="<?php echo esc_attr($field_labels['validation']['error'] ?? $default_labels['validation']['error']); ?>"
                                           class="regular-text">
                                    <p class="description"><?php esc_html_e('Default: An error occurred. Please try again.', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label><?php esc_html_e('reCAPTCHA Error', 'pentest-quote-form'); ?></label></th>
                                <td>
                                    <input type="text" name="ptf_settings[field_labels][validation][recaptcha_error]"
                                           value="<?php echo esc_attr($field_labels['validation']['recaptcha_error'] ?? $default_labels['validation']['recaptcha_error']); ?>"
                                           class="regular-text">
                                    <p class="description"><?php esc_html_e('Default: reCAPTCHA verification failed. Please try again.', 'pentest-quote-form'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Link Settings -->
                    <div class="ptf-settings-section">
                        <h2><span class="dashicons dashicons-admin-links"></span> <?php esc_html_e('Privacy KVKK & Gizlilik Linkleri Policy Links', 'pentest-quote-form'); ?></h2>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="kvkk_url"><?php esc_html_e('Privacy Notice URL', 'pentest-quote-form'); ?></label>
                                </th>
                                <td>
                                    <input type="text"
                                           id="kvkk_url"
                                           name="ptf_settings[kvkk_url]"
                                           value="<?php echo esc_attr($settings['kvkk_url']); ?>"
                                           class="regular-text"
                                           placeholder="/kvkk-aydinlatma-metni">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="privacy_url"><?php esc_html_e('Privacy Policy URL', 'pentest-quote-form'); ?></label>
                                </th>
                                <td>
                                    <input type="text"
                                           id="privacy_url"
                                           name="ptf_settings[privacy_url]"
                                           value="<?php echo esc_attr($settings['privacy_url']); ?>"
                                           class="regular-text"
                                           placeholder="/gizlilik-politikasi">
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- reCAPTCHA Settings -->
                    <div class="ptf-settings-section">
                        <h2><span class="dashicons dashicons-shield"></span> <?php esc_html_e('reCAPTCHA Settings (Optional)', 'pentest-quote-form'); ?></h2>
                        <p class="description" style="margin-bottom: 15px;">
                            <?php esc_html_e('You can use Google reCAPTCHA v3 for bot protection.', 'pentest-quote-form'); ?>
                            <?php esc_html_e('To get keys:', 'pentest-quote-form'); ?> <a href="https://www.google.com/recaptcha/admin/create" target="_blank">Google reCAPTCHA Admin</a>
                            (<?php esc_html_e('Select reCAPTCHA v3', 'pentest-quote-form'); ?>)
                        </p>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="recaptcha_site_key">Site Key</label>
                                </th>
                                <td>
                                    <input type="text"
                                           id="recaptcha_site_key"
                                           name="ptf_settings[recaptcha_site_key]"
                                           value="<?php echo esc_attr($settings['recaptcha_site_key']); ?>"
                                           class="large-text"
                                           placeholder="6Lc...">
                                    <p class="description">Google reCAPTCHA v3 Site Key</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="recaptcha_secret_key">Secret Key</label>
                                </th>
                                <td>
                                    <input type="password"
                                           id="recaptcha_secret_key"
                                           name="ptf_settings[recaptcha_secret_key]"
                                           value="<?php echo esc_attr($settings['recaptcha_secret_key']); ?>"
                                           class="large-text"
                                           placeholder="6Lc...">
                                    <p class="description">Google reCAPTCHA v3 Secret Key</p>
                                </td>
                            </tr>
                        </table>
                        <?php
                        $recaptcha_active = !empty($settings['recaptcha_site_key']) && !empty($settings['recaptcha_secret_key']);
                        ?>
                        <div class="ptf-recaptcha-status" style="margin-top: 15px; padding: 10px; border-radius: 4px; <?php echo $recaptcha_active ? 'background: #d4edda; border: 1px solid #c3e6cb;' : 'background: #fff3cd; border: 1px solid #ffeeba;'; ?>">
                            <?php if ($recaptcha_active): ?>
                                <span style="color: #155724;">✅ <?php esc_html_e('reCAPTCHA active', 'pentest-quote-form'); ?></span>
                            <?php else: ?>
                                <span style="color: #856404;">⚠️ <?php esc_html_e('reCAPTCHA not configured (form will work without bot protection)', 'pentest-quote-form'); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Webhook/API Integrations -->
                    <div class="ptf-settings-section">
                        <h2><span class="dashicons dashicons-rest-api"></span> <?php esc_html_e('Webhook / API Integrations', 'pentest-quote-form'); ?></h2>
                        <p class="description" style="margin-bottom: 15px;">
                            <?php esc_html_e('Automatically send form data to external systems (Power Automate, Zapier, Make, custom API, etc.).', 'pentest-quote-form'); ?>
                        </p>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e('Webhook Integrations', 'pentest-quote-form'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox"
                                               name="ptf_settings[enable_webhooks]"
                                               value="1"
                                               id="enable_webhooks"
                                               <?php checked($settings['enable_webhooks'], '1'); ?>>
                                        <?php esc_html_e('Enable Webhook/API integrations', 'pentest-quote-form'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>

                        <div id="webhooks-config-section" style="<?php echo $settings['enable_webhooks'] !== '1' ? 'display:none;' : ''; ?> margin-top: 20px;">

                            <!-- Hızlı Şablon Butonları -->
                            <div class="webhook-templates" style="margin-bottom: 20px;">
                                <h4 style="margin-bottom: 10px;"><?php esc_html_e('Quick Template Add:', 'pentest-quote-form'); ?></h4>
                                <button type="button" class="button" onclick="addWebhookTemplate('power_automate')">
                                    <span class="dashicons dashicons-cloud" style="vertical-align: middle;"></span> Power Automate
                                </button>
                                <button type="button" class="button" onclick="addWebhookTemplate('zapier')">
                                    <span class="dashicons dashicons-randomize" style="vertical-align: middle;"></span> Zapier
                                </button>
                                <button type="button" class="button" onclick="addWebhookTemplate('make')">
                                    <span class="dashicons dashicons-admin-generic" style="vertical-align: middle;"></span> Make (Integromat)
                                </button>
                                <button type="button" class="button" onclick="addWebhookTemplate('custom')">
                                    <span class="dashicons dashicons-admin-tools" style="vertical-align: middle;"></span> <?php esc_html_e('Custom API', 'pentest-quote-form'); ?>
                                </button>
                            </div>

                            <!-- Webhook List -->
                            <div id="webhooks-list" class="webhooks-list">
                                <?php
                                $webhooks = isset($settings['webhooks']) ? $settings['webhooks'] : array();
                                if (empty($webhooks)) {
                                    echo '<p class="no-webhooks-message">' . esc_html__('Henüz webhook tanımlanmamış. Yukarıdaki şablonlardan birini kullanarak veya JSON editörü ile ekleyebilirsiniz.', 'pentest-quote-form') . '</p>';
                                }
                                ?>
                            </div>

                            <!-- JSON Editor -->
                            <div class="webhook-json-editor" style="margin-top: 20px;">
                                <h4>
                                    <?php esc_html_e('JSON Configuration', 'pentest-quote-form'); ?>
                                    <button type="button" class="button button-small" onclick="toggleJsonEditor()" style="margin-left: 10px;">
                                        <span class="dashicons dashicons-editor-code" style="vertical-align: middle;"></span>
                                        <?php esc_html_e('Toggle JSON Editor', 'pentest-quote-form'); ?>
                                    </button>
                                </h4>
                                <div id="json-editor-container" style="display: none; margin-top: 10px;">
                                    <textarea id="webhooks_json"
                                              name="ptf_settings[webhooks_json]"
                                              rows="15"
                                              class="large-text code"
                                              style="font-family: monospace; font-size: 12px;"
                                              placeholder='[
  {
    "name": "Power Automate",
    "type": "power_automate",
    "url": "https://prod-xx.westeurope.logic.azure.com/workflows/...",
    "method": "POST",
    "active": true
  }
]'><?php echo esc_textarea(json_encode($webhooks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></textarea>
                                    <p class="description">
                                        <?php esc_html_e('You can edit webhook configuration in JSON format.', 'pentest-quote-form'); ?>
                                        <a href="#" onclick="showJsonHelp(); return false;"><?php esc_html_e('Help with JSON format', 'pentest-quote-form'); ?></a>
                                    </p>
                                    <button type="button" class="button" onclick="validateJson()"><?php esc_html_e('Validate JSON', 'pentest-quote-form'); ?></button>
                                    <button type="button" class="button" onclick="formatJson()"><?php esc_html_e('Format', 'pentest-quote-form'); ?></button>
                                    <span id="json-validation-result" style="margin-left: 10px;"></span>
                                </div>
                            </div>

                            <!-- JSON Help Modal -->
                            <div id="json-help-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 100000;">
                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #fff; padding: 30px; border-radius: 8px; max-width: 800px; max-height: 80vh; overflow-y: auto;">
                                    <h3 style="margin-top: 0;"><?php esc_html_e('Webhook JSON Configuration Format', 'pentest-quote-form'); ?></h3>
                                    <button type="button" onclick="document.getElementById('json-help-modal').style.display='none'" style="position: absolute; top: 10px; right: 10px; background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>

                                    <h4><?php esc_html_e('Basic Structure:', 'pentest-quote-form'); ?></h4>
                                    <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 11px;">[
  {
    "name": "Webhook Name",
    "type": "custom|power_automate|zapier|make",
    "url": "https://api.example.com/webhook",
    "method": "POST|PUT|PATCH",
    "active": true,
    "auth_type": "none|bearer|basic|api_key",
    "auth_value": "token veya credentials",
    "headers": {
      "Content-Type": "application/json",
      "X-Custom-Header": "value"
    },
    "field_mapping": {
      "api_field_name": "form_field_name",
      "company_name": "company",
      "contact_email": "email"
    }
  }
]</pre>

                                    <h4><?php esc_html_e('Power Automate Örneği:', 'pentest-quote-form'); ?></h4>
                                    <pre style="background: #e8f4fc; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 11px;">{
  "name": "Power Automate - Quote Notification",
  "type": "power_automate",
  "url": "https://prod-xx.westeurope.logic.azure.com/workflows/xxx/triggers/manual/paths/invoke?api-version=2016-06-01&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=xxx",
  "method": "POST",
  "active": true
}</pre>

                                    <h4><?php esc_html_e('Custom API + Bearer Auth Örneği:', 'pentest-quote-form'); ?></h4>
                                    <pre style="background: #f0f8e8; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 11px;">{
  "name": "CRM Entegrasyonu",
  "type": "custom",
  "url": "https://api.crm.com/v1/leads",
  "method": "POST",
  "active": true,
  "auth_type": "bearer",
  "auth_value": "your-api-token-here",
  "headers": {
    "Content-Type": "application/json",
    "X-Source": "ptf-form"
  },
  "field_mapping": {
    "lead_name": "first_name",
    "lead_email": "email",
    "lead_phone": "phone",
    "company": "company",
    "services": "test_types"
  }
}</pre>

                                    <h4><?php esc_html_e('Kullanılabilir Form Alanları:', 'pentest-quote-form'); ?></h4>
                                    <ul style="font-size: 13px;">
                                        <li><code>first_name</code> - <?php esc_html_e('İlgili kişi adı', 'pentest-quote-form'); ?></li>
                                        <li><code>email</code> - <?php esc_html_e('E-posta adresi', 'pentest-quote-form'); ?></li>
                                        <li><code>phone</code> - <?php esc_html_e('Telefon numarası', 'pentest-quote-form'); ?></li>
                                        <li><code>company</code> - <?php esc_html_e('Kurum adı', 'pentest-quote-form'); ?></li>
                                        <li><code>test_types</code> - <?php esc_html_e('Seçilen test türleri (array)', 'pentest-quote-form'); ?></li>
                                        <li><code>submitted_at</code> - <?php esc_html_e('Gönderim tarihi', 'pentest-quote-form'); ?></li>
                                        <li><code>page_url</code> - <?php esc_html_e('Formun gönderildiği sayfa', 'pentest-quote-form'); ?></li>
                                        <li><em><?php esc_html_e('+ Dinamik soru alanları (soru key değerleri)', 'pentest-quote-form'); ?></em></li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Test Butonu -->
                            <div class="webhook-test-section" style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 4px;">
                                <h4 style="margin-top: 0;"><?php esc_html_e('Webhook Testi', 'pentest-quote-form'); ?></h4>
                                <p class="description"><?php esc_html_e('Yapılandırılmış webhook\'ları test verisi ile deneyin.', 'pentest-quote-form'); ?></p>
                                <button type="button" class="button button-secondary" id="test-webhooks-btn" onclick="testWebhooks()">
                                    <span class="dashicons dashicons-controls-play" style="vertical-align: middle;"></span>
                                    <?php esc_html_e('Webhook\'ları Test Et', 'pentest-quote-form'); ?>
                                </button>
                                <div id="webhook-test-results" style="margin-top: 15px; display: none;"></div>
                            </div>
                        </div>
                    </div>


                    <!-- Salesforce Direct Integration -->
                    <div class="ptf-settings-section">
                        <h2><span class="dashicons dashicons-cloud" style="color:#00A1E0;"></span> <?php esc_html_e('Salesforce Direct Integration', 'pentest-quote-form'); ?></h2>
                        <p class="description" style="margin-bottom: 15px;">
                            <?php esc_html_e('Create a Lead/Contact/Opportunity directly in Salesforce when the form is submitted. Uses OAuth 2.0 Username-Password flow.', 'pentest-quote-form'); ?>
                            <?php printf(
                                '<a href="%s" target="_blank">%s</a>',
                                'https://help.salesforce.com/s/articleView?id=sf.connected_app_create.htm',
                                esc_html__('How to create a Connected App', 'pentest-quote-form')
                            ); ?>
                        </p>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e('Enable Salesforce Integration', 'pentest-quote-form'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox"
                                               name="ptf_settings[enable_salesforce]"
                                               value="1"
                                               id="enable_salesforce"
                                               <?php checked($settings['enable_salesforce'] ?? '0', '1'); ?>>
                                        <?php esc_html_e('Send form submissions directly to Salesforce', 'pentest-quote-form'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>

                        <div id="salesforce-config-section" style="<?php echo ($settings['enable_salesforce'] ?? '0') !== '1' ? 'display:none;' : ''; ?> margin-top: 20px;">

                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="salesforce_login_url"><?php esc_html_e('Login URL', 'pentest-quote-form'); ?></label>
                                    </th>
                                    <td>
                                        <select id="salesforce_login_url" name="ptf_settings[salesforce_login_url]">
                                            <option value="https://login.salesforce.com" <?php selected($settings['salesforce_login_url'] ?? 'https://login.salesforce.com', 'https://login.salesforce.com'); ?>>
                                                https://login.salesforce.com (<?php esc_html_e('Production', 'pentest-quote-form'); ?>)
                                            </option>
                                            <option value="https://test.salesforce.com" <?php selected($settings['salesforce_login_url'] ?? 'https://login.salesforce.com', 'https://test.salesforce.com'); ?>>
                                                https://test.salesforce.com (<?php esc_html_e('Sandbox', 'pentest-quote-form'); ?>)
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="salesforce_client_id"><?php esc_html_e('Consumer Key (Client ID)', 'pentest-quote-form'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text"
                                               id="salesforce_client_id"
                                               name="ptf_settings[salesforce_client_id]"
                                               value="<?php echo esc_attr($settings['salesforce_client_id'] ?? ''); ?>"
                                               class="large-text"
                                               autocomplete="off"
                                               placeholder="3MVG9...">
                                        <p class="description"><?php esc_html_e('Found in your Salesforce Connected App settings.', 'pentest-quote-form'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="salesforce_client_secret"><?php esc_html_e('Consumer Secret (Client Secret)', 'pentest-quote-form'); ?></label>
                                    </th>
                                    <td>
                                        <input type="password"
                                               id="salesforce_client_secret"
                                               name="ptf_settings[salesforce_client_secret]"
                                               value="<?php echo esc_attr($settings['salesforce_client_secret'] ?? ''); ?>"
                                               class="large-text"
                                               autocomplete="off">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="salesforce_username"><?php esc_html_e('Salesforce Username', 'pentest-quote-form'); ?></label>
                                    </th>
                                    <td>
                                        <input type="email"
                                               id="salesforce_username"
                                               name="ptf_settings[salesforce_username]"
                                               value="<?php echo esc_attr($settings['salesforce_username'] ?? ''); ?>"
                                               class="regular-text"
                                               placeholder="user@example.com">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="salesforce_password"><?php esc_html_e('Password + Security Token', 'pentest-quote-form'); ?></label>
                                    </th>
                                    <td>
                                        <input type="password"
                                               id="salesforce_password"
                                               name="ptf_settings[salesforce_password]"
                                               value="<?php echo esc_attr($settings['salesforce_password'] ?? ''); ?>"
                                               class="regular-text"
                                               autocomplete="off">
                                        <p class="description">
                                            <?php esc_html_e('Concatenate your Salesforce password and security token without spaces.', 'pentest-quote-form'); ?>
                                            <?php esc_html_e('Example: MyPassword1ABC123xyz (password + token)', 'pentest-quote-form'); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="salesforce_object"><?php esc_html_e('Salesforce Object', 'pentest-quote-form'); ?></label>
                                    </th>
                                    <td>
                                        <select id="salesforce_object" name="ptf_settings[salesforce_object]">
                                            <option value="Lead"        <?php selected($settings['salesforce_object'] ?? 'Lead', 'Lead'); ?>>Lead</option>
                                            <option value="Contact"     <?php selected($settings['salesforce_object'] ?? 'Lead', 'Contact'); ?>>Contact</option>
                                            <option value="Account"     <?php selected($settings['salesforce_object'] ?? 'Lead', 'Account'); ?>>Account</option>
                                            <option value="Opportunity" <?php selected($settings['salesforce_object'] ?? 'Lead', 'Opportunity'); ?>>Opportunity</option>
                                            <option value="Case"        <?php selected($settings['salesforce_object'] ?? 'Lead', 'Case'); ?>>Case</option>
                                        </select>
                                        <p class="description"><?php esc_html_e('The Salesforce object type to create on each submission. Lead is recommended.', 'pentest-quote-form'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="salesforce_api_version"><?php esc_html_e('API Version', 'pentest-quote-form'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text"
                                               id="salesforce_api_version"
                                               name="ptf_settings[salesforce_api_version]"
                                               value="<?php echo esc_attr($settings['salesforce_api_version'] ?? 'v59.0'); ?>"
                                               class="small-text"
                                               placeholder="v59.0">
                                        <p class="description"><?php esc_html_e('Default: v59.0. Check your Salesforce org\'s API version if needed.', 'pentest-quote-form'); ?></p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Field Mapping -->
                            <h4 style="margin-top: 25px; margin-bottom: 10px; border-bottom: 2px solid #00A1E0; padding-bottom: 5px; color: #00A1E0;">
                                <span class="dashicons dashicons-editor-table" style="vertical-align: middle;"></span>
                                <?php esc_html_e('Field Mapping (Salesforce Field → Form Field)', 'pentest-quote-form'); ?>
                            </h4>
                            <p class="description" style="margin-bottom: 12px;">
                                <?php esc_html_e('Map Salesforce API field names to form field names. Edit the JSON below.', 'pentest-quote-form'); ?>
                            </p>
                            <?php
                            $sf_mapping = $settings['salesforce_field_mapping'] ?? array(
                                'Company'     => 'company',
                                'LastName'    => 'first_name',
                                'Email'       => 'email',
                                'Phone'       => 'phone',
                                'Description' => 'test_types_text',
                            );
                            ?>
                            <textarea id="salesforce_field_mapping_json"
                                      name="ptf_settings[salesforce_field_mapping_json]"
                                      rows="10"
                                      class="large-text code"
                                      style="font-family: monospace; font-size: 12px;"><?php echo esc_textarea(json_encode($sf_mapping, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></textarea>
                            <p class="description">
                                <?php esc_html_e('Available form fields:', 'pentest-quote-form'); ?>
                                <code>first_name</code>, <code>email</code>, <code>phone</code>, <code>company</code>,
                                <code>test_types_text</code> (<?php esc_html_e('readable test type list', 'pentest-quote-form'); ?>),
                                <code>submitted_at</code>, <code>page_url</code>
                            </p>

                            <!-- Status indicator -->
                            <?php
                            $sf_configured = !empty($settings['salesforce_client_id'])
                                && !empty($settings['salesforce_client_secret'])
                                && !empty($settings['salesforce_username'])
                                && !empty($settings['salesforce_password']);
                            ?>
                            <div style="margin-top: 15px; padding: 10px; border-radius: 4px;
                                        <?php echo $sf_configured
                                            ? 'background: #d4edda; border: 1px solid #c3e6cb;'
                                            : 'background: #fff3cd; border: 1px solid #ffeeba;'; ?>">
                                <?php if ($sf_configured): ?>
                                    <span style="color: #155724;">✅ <?php esc_html_e('Salesforce credentials configured. Connection will be tested on the next form submission.', 'pentest-quote-form'); ?></span>
                                <?php else: ?>
                                    <span style="color: #856404;">⚠️ <?php esc_html_e('Salesforce credentials incomplete. Please fill in all required fields.', 'pentest-quote-form'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Shortcode Bilgisi -->
                    <div class="ptf-settings-section ptf-shortcode-info">
                        <h2><span class="dashicons dashicons-shortcode"></span> <?php esc_html_e('Shortcode Kullanımı', 'pentest-quote-form'); ?></h2>
                        <div class="shortcode-examples">
                            <div class="shortcode-item">
                                <h4><?php esc_html_e('Popup Button', 'pentest-quote-form'); ?></h4>
                                <code>[ptf_popup_trigger]</code>
                                <p><?php esc_html_e('Varsayılan ayarlarla popup butonu', 'pentest-quote-form'); ?></p>
                            </div>
                            <div class="shortcode-item">
                                <h4><?php esc_html_e('Özel Metin ile', 'pentest-quote-form'); ?></h4>
                                <code>[ptf_popup_trigger text="Get Quote"]</code>
                            </div>
                            <div class="shortcode-item">
                                <h4><?php esc_html_e('Özel Renklerle', 'pentest-quote-form'); ?></h4>
                                <code>[ptf_popup_trigger text="Get Quote" primary="#FF5733" secondary="#33FF57"]</code>
                            </div>
                            <div class="shortcode-item">
                                <h4><?php esc_html_e('Inline Form', 'pentest-quote-form'); ?></h4>
                                <code>[ptf_multistep_form]</code>
                            </div>
                            <div class="shortcode-item">
                                <h4><?php esc_html_e('Özel Renkli Inline Form', 'pentest-quote-form'); ?></h4>
                                <code>[ptf_multistep_form primary="#FF5733" secondary="#33FF57"]</code>
                            </div>
                        </div>
                    </div>
                </div>

                <?php submit_button(__('Save Settings', 'pentest-quote-form')); ?>
            </form>
        </div>

        <style>
        .ptf-settings-container {
            max-width: 900px;
        }
        .ptf-settings-section {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .ptf-settings-section h2 {
            margin: 0 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .ptf-settings-section h2 .dashicons {
            color: #2F7CFF;
        }
        .ptf-color-preview {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 4px;
            margin-top: 20px;
        }
        .ptf-color-preview h4 {
            margin: 0 0 15px 0;
        }
        .preview-buttons {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .preview-btn-primary {
            padding: 12px 24px;
            border: none;
            border-radius: 25px;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
        }
        .preview-success {
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 600;
        }
        .ptf-shortcode-info .shortcode-examples {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
        }
        .shortcode-item {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            border-left: 3px solid #2F7CFF;
        }
        .shortcode-item h4 {
            margin: 0 0 8px 0;
            font-size: 13px;
            color: #666;
        }
        .shortcode-item code {
            display: block;
            background: #fff;
            padding: 10px;
            border-radius: 4px;
            font-size: 12px;
            word-break: break-all;
        }
        .shortcode-item p {
            margin: 8px 0 0 0;
            font-size: 12px;
            color: #888;
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Store current colors for live preview
            var currentColors = {
                primary: $('#primary_color').val() || '#2F7CFF',
                secondary: $('#secondary_color').val() || '#B7FF10',
                buttonText: $('#button_text_color').val() || '#ffffff'
            };

            // Color picker başlat
            $('#primary_color').wpColorPicker({
                change: function(event, ui) {
                    currentColors.primary = ui.color.toString();
                    updatePreview();
                },
                clear: function() {
                    currentColors.primary = '#2F7CFF';
                    updatePreview();
                }
            });

            $('#secondary_color').wpColorPicker({
                change: function(event, ui) {
                    currentColors.secondary = ui.color.toString();
                    updatePreview();
                },
                clear: function() {
                    currentColors.secondary = '#B7FF10';
                    updatePreview();
                }
            });

            $('#button_text_color').wpColorPicker({
                change: function(event, ui) {
                    currentColors.buttonText = ui.color.toString();
                    updatePreview();
                },
                clear: function() {
                    currentColors.buttonText = '#ffffff';
                    updatePreview();
                }
            });

            function updatePreview() {
                var primary = currentColors.primary;
                var secondary = currentColors.secondary;
                var buttonTextColor = currentColors.buttonText;
                var buttonSize = $('#button_size').val() || 'medium';

                var sizeStyles = {
                    'small': { padding: '12px 24px', fontSize: '14px' },
                    'medium': { padding: '16px 32px', fontSize: '16px' },
                    'large': { padding: '20px 40px', fontSize: '18px' },
                    'xlarge': { padding: '24px 48px', fontSize: '20px' },
                    'custom': {
                        padding: ($('#button_padding_y').val() || 16) + 'px ' + ($('#button_padding_x').val() || 32) + 'px',
                        fontSize: ($('#button_font_size').val() || 16) + 'px'
                    }
                };

                $('#preview-primary').css({
                    'background': 'linear-gradient(135deg, ' + primary + ' 0%, ' + adjustColor(primary, -20) + ' 100%)',
                    'color': buttonTextColor,
                    'padding': sizeStyles[buttonSize].padding,
                    'fontSize': sizeStyles[buttonSize].fontSize
                });
                $('#preview-secondary').css({
                    'background': secondary,
                    'color': isLightColor(secondary) ? '#333' : '#fff'
                });
            }

            // Button size change event
            $('#button_size').on('change', function() {
                if ($(this).val() === 'custom') {
                    $('#custom-button-size-row').show();
                } else {
                    $('#custom-button-size-row').hide();
                }
                updatePreview();
            });

            // Custom size input change events
            $('#button_padding_y, #button_padding_x, #button_font_size').on('input', function() {
                updatePreview();
            });

            function adjustColor(color, amount) {
                var usePound = false;
                if (color[0] === "#") {
                    color = color.slice(1);
                    usePound = true;
                }
                var num = parseInt(color, 16);
                var r = Math.max(Math.min((num >> 16) + amount, 255), 0);
                var g = Math.max(Math.min(((num >> 8) & 0x00FF) + amount, 255), 0);
                var b = Math.max(Math.min((num & 0x0000FF) + amount, 255), 0);
                return (usePound ? "#" : "") + (0x1000000 + r * 0x10000 + g * 0x100 + b).toString(16).slice(1);
            }

            function isLightColor(color) {
                var hex = color.replace('#', '');
                var r = parseInt(hex.substr(0, 2), 16);
                var g = parseInt(hex.substr(2, 2), 16);
                var b = parseInt(hex.substr(4, 2), 16);
                var brightness = ((r * 299) + (g * 587) + (b * 114)) / 1000;
                return brightness > 155;
            }

            // İlk yüklemede önizlemeyi güncelle
            setTimeout(updatePreview, 100);

            // Veri kaydetme seçenekleri kontrolü
            function checkDataStorageOptions() {
                var saveToDb = $('#save_to_database').is(':checked');
                var sendEmail = $('#send_email_notification').is(':checked');

                if (!saveToDb && !sendEmail) {
                    $('#data-storage-warning').show();
                } else {
                    $('#data-storage-warning').hide();
                }
            }

            // Sayfa yüklendiğinde kontrol et
            checkDataStorageOptions();

            // Checkbox değişikliklerinde kontrol et
            $('#save_to_database, #send_email_notification').on('change', function() {
                checkDataStorageOptions();
            });

            // Webhook enable/disable toggle
            $('#enable_webhooks').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#webhooks-config-section').slideDown();
                } else {
                    $('#webhooks-config-section').slideUp();
                }
            });

            // Salesforce enable/disable toggle
            $('#enable_salesforce').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#salesforce-config-section').slideDown();
                } else {
                    $('#salesforce-config-section').slideUp();
                }
            });

            // Webhooks listesini render et
            renderWebhooksList();
        });

        // Webhook şablonları
        var webhookTemplates = {
            power_automate: {
                name: 'Power Automate',
                type: 'power_automate',
                url: '',
                method: 'POST',
                active: true,
                auth_type: 'none',
                auth_value: '',
                headers: {},
                field_mapping: {}
            },
            zapier: {
                name: 'Zapier Webhook',
                type: 'zapier',
                url: '',
                method: 'POST',
                active: true,
                auth_type: 'none',
                auth_value: '',
                headers: {},
                field_mapping: {}
            },
            make: {
                name: 'Make (Integromat)',
                type: 'make',
                url: '',
                method: 'POST',
                active: true,
                auth_type: 'none',
                auth_value: '',
                headers: {},
                field_mapping: {}
            },
            custom: {
                name: '<?php esc_html_e('Custom API', 'pentest-quote-form'); ?>',
                type: 'custom',
                url: '',
                method: 'POST',
                active: true,
                auth_type: 'none',
                auth_value: '',
                headers: {'Content-Type': 'application/json'},
                field_mapping: {}
            }
        };

        function getWebhooksFromJson() {
            try {
                var json = jQuery('#webhooks_json').val();
                if (!json || json.trim() === '' || json.trim() === '[]' || json.trim() === 'null') {
                    return [];
                }
                return JSON.parse(json);
            } catch (e) {
                return [];
            }
        }

        function saveWebhooksToJson(webhooks) {
            jQuery('#webhooks_json').val(JSON.stringify(webhooks, null, 2));
            renderWebhooksList();
        }

        function addWebhookTemplate(type) {
            var webhooks = getWebhooksFromJson();
            var template = JSON.parse(JSON.stringify(webhookTemplates[type]));

            // Benzersiz isim oluştur
            var count = webhooks.filter(function(w) { return w.type === type; }).length + 1;
            if (count > 1) {
                template.name = template.name + ' ' + count;
            }

            // Open modal (index = -1 for new webhook)
            openWebhookModal(template, -1);
        }

        function renderWebhooksList() {
            var webhooks = getWebhooksFromJson();
            var container = jQuery('#webhooks-list');

            if (webhooks.length === 0) {
                container.html('<p class="no-webhooks-message"><?php esc_html_e('Henüz webhook tanımlanmamış. Yukarıdaki şablonlardan birini kullanarak veya JSON editörü ile ekleyebilirsiniz.', 'pentest-quote-form'); ?></p>');
                return;
            }

            var html = '<table class="wp-list-table widefat fixed striped" style="margin-top: 10px;">';
            html += '<thead><tr>';
            html += '<th style="width: 30px;"></th>';
            html += '<th><?php esc_html_e('Ad', 'pentest-quote-form'); ?></th>';
            html += '<th><?php esc_html_e('Type', 'pentest-quote-form'); ?></th>';
            html += '<th><?php esc_html_e('URL', 'pentest-quote-form'); ?></th>';
            html += '<th><?php esc_html_e('Durum', 'pentest-quote-form'); ?></th>';
            html += '<th style="width: 120px;"><?php esc_html_e('Actions', 'pentest-quote-form'); ?></th>';
            html += '</tr></thead><tbody>';

            webhooks.forEach(function(webhook, index) {
                var typeLabels = {
                    'power_automate': '<span style="color: #0078d4;">⚡ Power Automate</span>',
                    'zapier': '<span style="color: #ff4a00;">🔗 Zapier</span>',
                    'make': '<span style="color: #6d4aff;">⚙️ Make</span>',
                    'custom': '<span style="color: #333;">🔧 <?php esc_html_e('Özel', 'pentest-quote-form'); ?></span>'
                };

                var statusBadge = webhook.active
                    ? '<span style="background: #d4edda; color: #155724; padding: 2px 8px; border-radius: 3px; font-size: 11px;"><?php esc_html_e('Aktif', 'pentest-quote-form'); ?></span>'
                    : '<span style="background: #f8d7da; color: #721c24; padding: 2px 8px; border-radius: 3px; font-size: 11px;"><?php esc_html_e('Pasif', 'pentest-quote-form'); ?></span>';

                var urlShort = webhook.url ? (webhook.url.length > 40 ? webhook.url.substring(0, 40) + '...' : webhook.url) : '<em style="color: #999;"><?php esc_html_e('URL girilmemiş', 'pentest-quote-form'); ?></em>';

                html += '<tr>';
                html += '<td><span class="dashicons dashicons-menu" style="color: #ccc; cursor: move;"></span></td>';
                html += '<td><strong>' + escapeHtml(webhook.name) + '</strong></td>';
                html += '<td>' + (typeLabels[webhook.type] || webhook.type) + '</td>';
                html += '<td title="' + escapeHtml(webhook.url || '') + '"><code style="font-size: 11px;">' + escapeHtml(urlShort) + '</code></td>';
                html += '<td>' + statusBadge + '</td>';
                html += '<td>';
                html += '<button type="button" class="button button-small" onclick="editWebhook(' + index + ')" title="<?php esc_html_e('Edit', 'pentest-quote-form'); ?>"><span class="dashicons dashicons-edit" style="vertical-align: middle;"></span></button> ';
                html += '<button type="button" class="button button-small" onclick="toggleWebhook(' + index + ')" title="<?php esc_html_e('Aktif/Pasif', 'pentest-quote-form'); ?>"><span class="dashicons dashicons-visibility" style="vertical-align: middle;"></span></button> ';
                html += '<button type="button" class="button button-small" onclick="deleteWebhook(' + index + ')" title="<?php esc_html_e('Sil', 'pentest-quote-form'); ?>" style="color: #a00;"><span class="dashicons dashicons-trash" style="vertical-align: middle;"></span></button>';
                html += '</td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
            container.html(html);
        }

        function escapeHtml(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function editWebhook(index) {
            var webhooks = getWebhooksFromJson();
            var webhook = webhooks[index];
            openWebhookModal(webhook, index);
        }

        function openWebhookModal(webhook, index) {
            // Modal HTML oluştur
            var isNew = (index === -1);
            var modalTitle = isNew ? '<?php esc_html_e('Add New Webhook', 'pentest-quote-form'); ?>' : '<?php esc_html_e('Webhook Düzenle', 'pentest-quote-form'); ?>';

            var modalHtml = '<div id="webhook-edit-modal" class="webhook-modal-overlay">' +
                '<div class="webhook-modal">' +
                    '<div class="webhook-modal-header">' +
                        '<h3>' + modalTitle + '</h3>' +
                        '<button type="button" class="webhook-modal-close" onclick="closeWebhookModal()">&times;</button>' +
                    '</div>' +
                    '<div class="webhook-modal-body">' +
                        '<div class="webhook-form-row">' +
                            '<label><?php esc_html_e('Webhook Adı', 'pentest-quote-form'); ?> <span class="required">*</span></label>' +
                            '<input type="text" id="webhook-name" value="' + escapeHtml(webhook.name || '') + '" placeholder="<?php esc_html_e('Örn: Power Automate - CRM', 'pentest-quote-form'); ?>">' +
                        '</div>' +
                        '<div class="webhook-form-row">' +
                            '<label><?php esc_html_e('Webhook Türü', 'pentest-quote-form'); ?></label>' +
                            '<select id="webhook-type">' +
                                '<option value="custom"' + (webhook.type === 'custom' ? ' selected' : '') + '><?php esc_html_e('Custom API', 'pentest-quote-form'); ?></option>' +
                                '<option value="power_automate"' + (webhook.type === 'power_automate' ? ' selected' : '') + '>Power Automate</option>' +
                                '<option value="zapier"' + (webhook.type === 'zapier' ? ' selected' : '') + '>Zapier</option>' +
                                '<option value="make"' + (webhook.type === 'make' ? ' selected' : '') + '>Make (Integromat)</option>' +
                            '</select>' +
                        '</div>' +
                        '<div class="webhook-form-row">' +
                            '<label><?php esc_html_e('Webhook URL', 'pentest-quote-form'); ?> <span class="required">*</span></label>' +
                            '<input type="url" id="webhook-url" value="' + escapeHtml(webhook.url || '') + '" placeholder="https://...">' +
                            '<p class="field-description"><?php esc_html_e('Örn: https://prod-xx.westeurope.logic.azure.com/workflows/...', 'pentest-quote-form'); ?></p>' +
                        '</div>' +
                        '<div class="webhook-form-row">' +
                            '<label><?php esc_html_e('HTTP Metodu', 'pentest-quote-form'); ?></label>' +
                            '<select id="webhook-method">' +
                                '<option value="POST"' + (webhook.method === 'POST' ? ' selected' : '') + '>POST</option>' +
                                '<option value="PUT"' + (webhook.method === 'PUT' ? ' selected' : '') + '>PUT</option>' +
                                '<option value="PATCH"' + (webhook.method === 'PATCH' ? ' selected' : '') + '>PATCH</option>' +
                            '</select>' +
                        '</div>' +
                        '<div class="webhook-form-row">' +
                            '<label><?php esc_html_e('Kimlik Doğrulama', 'pentest-quote-form'); ?></label>' +
                            '<select id="webhook-auth-type" onchange="toggleAuthValue()">' +
                                '<option value="none"' + (webhook.auth_type === 'none' || !webhook.auth_type ? ' selected' : '') + '><?php esc_html_e('Yok', 'pentest-quote-form'); ?></option>' +
                                '<option value="bearer"' + (webhook.auth_type === 'bearer' ? ' selected' : '') + '>Bearer Token</option>' +
                                '<option value="basic"' + (webhook.auth_type === 'basic' ? ' selected' : '') + '>Basic Auth (Base64)</option>' +
                                '<option value="api_key"' + (webhook.auth_type === 'api_key' ? ' selected' : '') + '>API Key</option>' +
                            '</select>' +
                        '</div>' +
                        '<div class="webhook-form-row" id="webhook-auth-value-row" style="' + (webhook.auth_type && webhook.auth_type !== 'none' ? '' : 'display:none;') + '">' +
                            '<label id="webhook-auth-value-label"><?php esc_html_e('Token / Anahtar', 'pentest-quote-form'); ?></label>' +
                            '<input type="text" id="webhook-auth-value" value="' + escapeHtml(webhook.auth_value || '') + '" placeholder="<?php esc_html_e('Token veya anahtar değeri', 'pentest-quote-form'); ?>">' +
                            '<p class="field-description" id="webhook-auth-description"></p>' +
                        '</div>' +
                        '<div class="webhook-form-row">' +
                            '<label><?php esc_html_e('Özel HTTP Header\'lar (Opsiyonel)', 'pentest-quote-form'); ?></label>' +
                            '<div id="webhook-headers-container"></div>' +
                            '<button type="button" class="button button-small" onclick="addHeaderRow()">' +
                                '<span class="dashicons dashicons-plus-alt" style="vertical-align: middle;"></span> <?php esc_html_e('Header Ekle', 'pentest-quote-form'); ?>' +
                            '</button>' +
                        '</div>' +
                        '<div class="webhook-form-row">' +
                            '<label>' +
                                '<input type="checkbox" id="webhook-active"' + (webhook.active !== false ? ' checked' : '') + '>' +
                                ' <?php esc_html_e('Webhook Aktif', 'pentest-quote-form'); ?>' +
                            '</label>' +
                        '</div>' +
                        '<hr style="margin: 20px 0;">' +
                        '<div class="webhook-form-row">' +
                            '<label><?php esc_html_e('Alan Eşleme (Field Mapping) - Opsiyonel', 'pentest-quote-form'); ?></label>' +
                            '<p class="field-description" style="margin-bottom: 10px;"><?php esc_html_e('API farklı alan adları bekliyorsa, form alanlarını API alanlarına eşleyin.', 'pentest-quote-form'); ?></p>' +
                            '<div id="webhook-field-mapping-container"></div>' +
                            '<button type="button" class="button button-small" onclick="addFieldMappingRow()">' +
                                '<span class="dashicons dashicons-plus-alt" style="vertical-align: middle;"></span> <?php esc_html_e('Eşleme Ekle', 'pentest-quote-form'); ?>' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                    '<div class="webhook-modal-footer">' +
                        '<button type="button" class="button" onclick="closeWebhookModal()"><?php esc_html_e('İptal', 'pentest-quote-form'); ?></button>' +
                        '<button type="button" class="button button-primary" onclick="saveWebhookFromModal(' + index + ')"><?php esc_html_e('Kaydet', 'pentest-quote-form'); ?></button>' +
                    '</div>' +
                '</div>' +
            '</div>';

            // Modal stillerini ekle
            if (!document.getElementById('webhook-modal-styles')) {
                var styles = document.createElement('style');
                styles.id = 'webhook-modal-styles';
                styles.textContent = '.webhook-modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 100001; display: flex; align-items: center; justify-content: center; }' +
                    '.webhook-modal { background: #fff; border-radius: 8px; width: 90%; max-width: 600px; max-height: 90vh; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.3); }' +
                    '.webhook-modal-header { padding: 15px 20px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f9f9f9; }' +
                    '.webhook-modal-header h3 { margin: 0; font-size: 16px; }' +
                    '.webhook-modal-close { background: none; border: none; font-size: 24px; cursor: pointer; color: #666; line-height: 1; padding: 0; }' +
                    '.webhook-modal-close:hover { color: #000; }' +
                    '.webhook-modal-body { padding: 20px; max-height: 60vh; overflow-y: auto; }' +
                    '.webhook-modal-footer { padding: 15px 20px; border-top: 1px solid #ddd; display: flex; justify-content: flex-end; gap: 10px; background: #f9f9f9; }' +
                    '.webhook-form-row { margin-bottom: 15px; }' +
                    '.webhook-form-row label { display: block; font-weight: 600; margin-bottom: 5px; }' +
                    '.webhook-form-row input[type="text"], .webhook-form-row input[type="url"], .webhook-form-row select { width: 100%; padding: 8px 10px; border: 1px solid #ddd; border-radius: 4px; }' +
                    '.webhook-form-row input:focus, .webhook-form-row select:focus { border-color: #2F7CFF; outline: none; box-shadow: 0 0 0 2px rgba(47, 124, 255, 0.2); }' +
                    '.webhook-form-row .required { color: #dc3545; }' +
                    '.webhook-form-row .field-description { font-size: 12px; color: #666; margin-top: 5px; }' +
                    '.header-row, .field-mapping-row { display: flex; gap: 10px; margin-bottom: 8px; align-items: center; }' +
                    '.header-row input, .field-mapping-row input, .field-mapping-row select { flex: 1; padding: 6px 8px; border: 1px solid #ddd; border-radius: 4px; }' +
                    '.header-row button, .field-mapping-row button { flex-shrink: 0; }';
                document.head.appendChild(styles);
            }

            // Modal'ı ekle
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Mevcut header'ları yükle
            if (webhook.headers && typeof webhook.headers === 'object') {
                Object.keys(webhook.headers).forEach(function(key) {
                    addHeaderRow(key, webhook.headers[key]);
                });
            }

            // Mevcut field mapping'leri yükle
            if (webhook.field_mapping && typeof webhook.field_mapping === 'object') {
                Object.keys(webhook.field_mapping).forEach(function(key) {
                    addFieldMappingRow(key, webhook.field_mapping[key]);
                });
            }

            // Auth tipine göre açıklamayı güncelle
            toggleAuthValue();
        }

        function closeWebhookModal() {
            var modal = document.getElementById('webhook-edit-modal');
            if (modal) {
                modal.remove();
            }
        }

        function toggleAuthValue() {
            var authType = document.getElementById('webhook-auth-type');
            if (!authType) return;
            var authTypeValue = authType.value;
            var authValueRow = document.getElementById('webhook-auth-value-row');
            var authLabel = document.getElementById('webhook-auth-value-label');
            var authDesc = document.getElementById('webhook-auth-description');

            if (authTypeValue === 'none') {
                authValueRow.style.display = 'none';
            } else {
                authValueRow.style.display = 'block';
                switch(authTypeValue) {
                    case 'bearer':
                        authLabel.textContent = 'Bearer Token';
                        authDesc.textContent = '<?php esc_html_e('Header: Authorization: Bearer <token>', 'pentest-quote-form'); ?>';
                        break;
                    case 'basic':
                        authLabel.textContent = 'Basic Auth (Base64)';
                        authDesc.textContent = '<?php esc_html_e('Base64 encoded username:password', 'pentest-quote-form'); ?>';
                        break;
                    case 'api_key':
                        authLabel.textContent = 'API Key';
                        authDesc.textContent = '<?php esc_html_e('Header: X-API-Key: <key>', 'pentest-quote-form'); ?>';
                        break;
                }
            }
        }

        function addHeaderRow(key, value) {
            var container = document.getElementById('webhook-headers-container');
            if (!container) return;
            var html = '<div class="header-row">' +
                '<input type="text" class="header-key" placeholder="<?php esc_html_e('Header Adı', 'pentest-quote-form'); ?>" value="' + escapeHtml(key || '') + '">' +
                '<input type="text" class="header-value" placeholder="<?php esc_html_e('Header Değeri', 'pentest-quote-form'); ?>" value="' + escapeHtml(value || '') + '">' +
                '<button type="button" class="button button-small" onclick="this.parentElement.remove()" style="color:#a00;"><span class="dashicons dashicons-no" style="vertical-align:middle;"></span></button>' +
            '</div>';
            container.insertAdjacentHTML('beforeend', html);
        }

        function addFieldMappingRow(apiField, formField) {
            var container = document.getElementById('webhook-field-mapping-container');
            if (!container) return;

            // New structured fields (with nested path support)
            var formFields = [
                // Contact
                {value: 'contact.name', label: 'contact.name (İletişim Adı)'},
                {value: 'contact.email', label: 'contact.email (E-posta)'},
                {value: 'contact.phone', label: 'contact.phone (Telefon)'},
                {value: 'contact.company', label: 'contact.company (Şirket)'},
                // Meta
                {value: 'meta.submitted_at', label: 'meta.submitted_at (Gönderim Tarihi)'},
                {value: 'meta.page_url', label: 'meta.page_url (Sayfa URL)'},
                {value: 'meta.site_name', label: 'meta.site_name (Site Adı)'},
                {value: 'meta.site_url', label: 'meta.site_url (Site URL)'},
                // Selected Categories
                {value: 'selected_categories.ids', label: 'selected_categories.ids (Kategori ID\'ler)'},
                {value: 'selected_categories.names', label: 'selected_categories.names (Kategori Adları)'},
                // Flat (geriye uyumluluk)
                {value: 'first_name', label: 'first_name (flat)'},
                {value: 'email', label: 'email (flat)'},
                {value: 'phone', label: 'phone (flat)'},
                {value: 'company', label: 'company (flat)'},
                {value: 'test_types', label: 'test_types (flat)'},
                {value: 'test_types_labels', label: 'test_types_labels (flat)'},
                {value: 'submitted_at', label: 'submitted_at (flat)'},
                {value: 'page_url', label: 'page_url (flat)'},
            ];

            var optionsHtml = '<option value=""><?php esc_html_e('Form alanı seçin', 'pentest-quote-form'); ?></option>';
            formFields.forEach(function(field) {
                var selected = (formField === field.value) ? ' selected' : '';
                optionsHtml += '<option value="' + field.value + '"' + selected + '>' + field.label + '</option>';
            });

            var html = '<div class="field-mapping-row">' +
                '<input type="text" class="mapping-api-field" placeholder="<?php esc_html_e('API Alan Adı', 'pentest-quote-form'); ?>" value="' + escapeHtml(apiField || '') + '">' +
                '<select class="mapping-form-field">' + optionsHtml + '</select>' +
                '<button type="button" class="button button-small" onclick="this.parentElement.remove()" style="color:#a00;"><span class="dashicons dashicons-no" style="vertical-align:middle;"></span></button>' +
            '</div>';
            container.insertAdjacentHTML('beforeend', html);
        }

        function saveWebhookFromModal(index) {
            var name = document.getElementById('webhook-name').value.trim();
            var url = document.getElementById('webhook-url').value.trim();

            if (!name) {
                alert('<?php esc_html_e('Webhook adı zorunludur.', 'pentest-quote-form'); ?>');
                document.getElementById('webhook-name').focus();
                return;
            }
            if (!url) {
                alert('<?php esc_html_e('Webhook URL zorunludur.', 'pentest-quote-form'); ?>');
                document.getElementById('webhook-url').focus();
                return;
            }

            // Header'ları topla
            var headers = {};
            document.querySelectorAll('.header-row').forEach(function(row) {
                var key = row.querySelector('.header-key').value.trim();
                var value = row.querySelector('.header-value').value.trim();
                if (key) {
                    headers[key] = value;
                }
            });

            // Field mapping'leri topla
            var fieldMapping = {};
            document.querySelectorAll('.field-mapping-row').forEach(function(row) {
                var apiField = row.querySelector('.mapping-api-field').value.trim();
                var formField = row.querySelector('.mapping-form-field').value;
                if (apiField && formField) {
                    fieldMapping[apiField] = formField;
                }
            });

            var webhook = {
                name: name,
                type: document.getElementById('webhook-type').value,
                url: url,
                method: document.getElementById('webhook-method').value,
                auth_type: document.getElementById('webhook-auth-type').value,
                auth_value: document.getElementById('webhook-auth-value').value.trim(),
                headers: headers,
                field_mapping: fieldMapping,
                active: document.getElementById('webhook-active').checked
            };

            var webhooks = getWebhooksFromJson();

            if (index === -1) {
                webhooks.push(webhook);
            } else {
                webhooks[index] = webhook;
            }

            saveWebhooksToJson(webhooks);
            closeWebhookModal();
        }

        function toggleWebhook(index) {
            var webhooks = getWebhooksFromJson();
            webhooks[index].active = !webhooks[index].active;
            saveWebhooksToJson(webhooks);
        }

        function deleteWebhook(index) {
            if (confirm('<?php esc_html_e('Bu webhook\'u silmek istediğinizden emin misiniz?', 'pentest-quote-form'); ?>')) {
                var webhooks = getWebhooksFromJson();
                webhooks.splice(index, 1);
                saveWebhooksToJson(webhooks);
            }
        }

        function toggleJsonEditor() {
            jQuery('#json-editor-container').slideToggle();
        }

        function showJsonHelp() {
            document.getElementById('json-help-modal').style.display = 'block';
        }

        function validateJson() {
            var textarea = document.getElementById('webhooks_json');
            var result = document.getElementById('json-validation-result');

            try {
                JSON.parse(textarea.value);
                result.innerHTML = '<span style="color: #155724;">✅ <?php esc_html_e('Geçerli JSON', 'pentest-quote-form'); ?></span>';
                renderWebhooksList();
            } catch (e) {
                result.innerHTML = '<span style="color: #721c24;">❌ <?php esc_html_e('Geçersiz JSON:', 'pentest-quote-form'); ?> ' + e.message + '</span>';
            }
        }

        function formatJson() {
            var textarea = document.getElementById('webhooks_json');
            try {
                var json = JSON.parse(textarea.value);
                textarea.value = JSON.stringify(json, null, 2);
                validateJson();
            } catch (e) {
                alert('<?php esc_html_e('JSON formatlanamadı. Önce geçerli bir JSON girin.', 'pentest-quote-form'); ?>');
            }
        }

        function testWebhooks() {
            var btn = document.getElementById('test-webhooks-btn');
            var results = document.getElementById('webhook-test-results');

            btn.disabled = true;
            btn.innerHTML = '<span class="dashicons dashicons-update" style="vertical-align: middle; animation: rotation 1s infinite linear;"></span> <?php esc_html_e('Test ediliyor...', 'pentest-quote-form'); ?>';
            results.style.display = 'block';
            results.innerHTML = '<p><?php esc_html_e('Webhook\'lar test ediliyor...', 'pentest-quote-form'); ?></p>';

            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ptf_test_webhooks',
                    nonce: '<?php echo wp_create_nonce('ptf_test_webhooks'); ?>',
                    webhooks: jQuery('#webhooks_json').val()
                },
                success: function(response) {
                    btn.disabled = false;
                    btn.innerHTML = '<span class="dashicons dashicons-controls-play" style="vertical-align: middle;"></span> <?php esc_html_e('Webhook\'ları Test Et', 'pentest-quote-form'); ?>';

                    if (response.success) {
                        var html = '<h4><?php esc_html_e('Test Sonuçları:', 'pentest-quote-form'); ?></h4>';
                        response.data.results.forEach(function(r) {
                            var statusColor = r.success ? '#155724' : '#721c24';
                            var statusBg = r.success ? '#d4edda' : '#f8d7da';
                            var statusIcon = r.success ? '✅' : '❌';

                            html += '<div style="background: ' + statusBg + '; padding: 10px; margin-bottom: 5px; border-radius: 4px;">';
                            html += '<strong>' + statusIcon + ' ' + escapeHtml(r.name) + '</strong><br>';
                            html += '<small style="color: ' + statusColor + ';">' + escapeHtml(r.message) + '</small>';
                            if (r.response_code) {
                                html += ' <code>HTTP ' + r.response_code + '</code>';
                            }
                            html += '</div>';
                        });
                        results.innerHTML = html;
                    } else {
                        results.innerHTML = '<p style="color: #721c24;">❌ ' + response.data.message + '</p>';
                    }
                },
                error: function() {
                    btn.disabled = false;
                    btn.innerHTML = '<span class="dashicons dashicons-controls-play" style="vertical-align: middle;"></span> <?php esc_html_e('Webhook\'ları Test Et', 'pentest-quote-form'); ?>';
                    results.innerHTML = '<p style="color: #721c24;">❌ <?php esc_html_e('Test sırasında bir hata oluştu.', 'pentest-quote-form'); ?></p>';
                }
            });
        }

        // CSS for rotation animation
        var style = document.createElement('style');
        style.textContent = '@keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
        document.head.appendChild(style);
        </script>
        <?php
    }
}

// Ayarlar sınıfını başlat
PTF_Form_Settings::get_instance();

