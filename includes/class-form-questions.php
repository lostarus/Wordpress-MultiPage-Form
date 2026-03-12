<?php
/**
 * Form Questions Management
 * Dynamically manage test categories and questions
 */

if (!defined('ABSPATH')) {
    exit;
}

class PTF_Form_Questions {

    private static $instance = null;
    private $option_name = 'ptf_questions';

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('admin_menu', array($this, 'add_questions_page'), 25);
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_ptf_save_questions', array($this, 'ajax_save_questions'));
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'ptf-questions') === false) {
            return;
        }

        // jQuery UI
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_style('wp-jquery-ui-dialog');

        // Admin Questions CSS
        wp_enqueue_style(
            'ptf-admin-questions',
            PTF_PLUGIN_URL . 'assets/css/admin-questions.css',
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

        // Admin Questions JS
        wp_enqueue_script(
            'ptf-admin-questions',
            PTF_PLUGIN_URL . 'assets/js/admin-questions.js',
            array('jquery', 'jquery-ui-sortable', 'ptf-admin-utils'),
            PTF_VERSION,
            true
        );

        // Localize script with translations and data
        wp_localize_script('ptf-admin-questions', 'ptfQuestionsAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ptf_questions_nonce'),
            'i18n' => array(
                'confirmDeleteCategory' => __('Are you sure you want to delete this category?', 'pentest-quote-form'),
                'confirmDeleteQuestion' => __('Are you sure you want to delete this question?', 'pentest-quote-form'),
                'confirmLoadSample' => __('Existing categories will be deleted and sample data will be loaded. Continue?', 'pentest-quote-form'),
                'newCategory' => __('New Category', 'pentest-quote-form'),
                'newQuestion' => __('New Question', 'pentest-quote-form'),
                'categoryName' => __('Category Name', 'pentest-quote-form'),
                'categoryId' => __('ID (Unique Key)', 'pentest-quote-form'),
                'icon' => __('Icon (Emoji)', 'pentest-quote-form'),
                'questions' => __('questions', 'pentest-quote-form'),
                'active' => __('Active', 'pentest-quote-form'),
                'addQuestion' => __('Add Question', 'pentest-quote-form'),
                'questionText' => __('Question Text', 'pentest-quote-form'),
                'questionId' => __('Question ID', 'pentest-quote-form'),
                'answerType' => __('Answer Type', 'pentest-quote-form'),
                'placeholder' => __('Placeholder', 'pentest-quote-form'),
                'required' => __('Required', 'pentest-quote-form'),
                'options' => __('Options', 'pentest-quote-form'),
                'addOption' => __('Add Option', 'pentest-quote-form'),
                'optionId' => __('Option ID', 'pentest-quote-form'),
                'optionText' => __('Option text', 'pentest-quote-form'),
                'saved' => __('Saved!', 'pentest-quote-form'),
                'saveError' => __('Save failed', 'pentest-quote-form'),
                'ajaxError' => __('AJAX Error:', 'pentest-quote-form'),
                'sampleLoaded' => __('Sample data loaded!', 'pentest-quote-form'),
                'categories' => __('categories', 'pentest-quote-form'),
                'typeText' => __('Text (Short)', 'pentest-quote-form'),
                'typeTextarea' => __('Text (Long)', 'pentest-quote-form'),
                'typeNumber' => __('Number', 'pentest-quote-form'),
                'typeSelect' => __('Dropdown', 'pentest-quote-form'),
                'typeRadio' => __('Single Select', 'pentest-quote-form'),
                'typeCheckbox' => __('Multiple Select', 'pentest-quote-form'),
                'typeYesNo' => __('Yes / No', 'pentest-quote-form'),
            ),
        ));
    }

    /**
     * Add to menu
     */
    public function add_questions_page() {
        add_submenu_page(
            'ptf-submissions',
            __('Question Management', 'pentest-quote-form'),
            __('Question Management', 'pentest-quote-form'),
            'manage_options',
            'ptf-questions',
            array($this, 'render_questions_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'ptf_questions_group',
            $this->option_name,
            array($this, 'sanitize_questions')
        );
    }

    /**
     * Answer types
     */
    public static function get_answer_types() {
        return array(
            'text' => __('Text (Short)', 'pentest-quote-form'),
            'textarea' => __('Text (Long)', 'pentest-quote-form'),
            'number' => __('Number', 'pentest-quote-form'),
            'select' => __('Dropdown', 'pentest-quote-form'),
            'radio' => __('Single Select (Radio)', 'pentest-quote-form'),
            'checkbox' => __('Multiple Select (Checkbox)', 'pentest-quote-form'),
            'date' => __('Date', 'pentest-quote-form'),
            'email' => __('Email', 'pentest-quote-form'),
            'tel' => __('Phone', 'pentest-quote-form'),
        );
    }

    /**
     * Default categories - Empty start (only sample category)
     */
    public static function get_default_categories() {
        return array();
    }

    /**
     * Sample category and questions (for first installation)
     */
    public static function get_sample_data() {
        return array(
            'categories' => array(
                array(
                    'id' => 'web-application',
                    'name' => 'Web Application Penetration Test',
                    'icon' => '🌐',
                    'active' => true,
                    'order' => 0,
                    'questions' => array(
                        array(
                            'id' => 'url_count',
                            'question' => 'How many URLs will be tested?',
                            'type' => 'number',
                            'required' => false,
                            'placeholder' => 'e.g., 5',
                            'options' => array(),
                            'order' => 0,
                        ),
                        array(
                            'id' => 'has_auth',
                            'question' => 'Are there pages requiring authentication?',
                            'type' => 'select',
                            'required' => false,
                            'placeholder' => '',
                            'options' => array(
                                array('id' => 'yes', 'label' => 'Yes'),
                                array('id' => 'no', 'label' => 'No'),
                            ),
                            'order' => 1,
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * Get database table name
     */
    private static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'ptf_questions';
    }

    /**
     * Get categories from database
     */
    public static function get_categories() {
        global $wpdb;
        $table = self::get_table_name();

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return self::get_default_categories();
        }

        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT option_value FROM $table WHERE option_key = %s", 'categories'),
            ARRAY_A
        );

        if ($row && !empty($row['option_value'])) {
            $data = json_decode($row['option_value'], true);
            if (is_array($data) && isset($data['categories'])) {
                return $data['categories'];
            }
        }

        return self::get_default_categories();
    }

    /**
     * Save categories to database
     */
    public static function save_categories($data) {
        global $wpdb;
        $table = self::get_table_name();

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
            return false;
        }

        $json_data = json_encode($data, JSON_UNESCAPED_UNICODE);

        // Check if record exists
        $exists = $wpdb->get_var(
            $wpdb->prepare("SELECT id FROM $table WHERE option_key = %s", 'categories')
        );

        if ($exists) {
            // Update
            $result = $wpdb->update(
                $table,
                array('option_value' => $json_data),
                array('option_key' => 'categories'),
                array('%s'),
                array('%s')
            );
        } else {
            // Insert
            $result = $wpdb->insert(
                $table,
                array(
                    'option_key' => 'categories',
                    'option_value' => $json_data
                ),
                array('%s', '%s')
            );
        }

        return $result !== false;
    }

    /**
     * Get active categories (for form usage)
     */
    public static function get_active_categories() {
        $categories = self::get_categories();
        $active = array_filter($categories, function($cat) {
            return !empty($cat['active']);
        });

        // Sort by order
        usort($active, function($a, $b) {
            $orderA = isset($a['order']) ? $a['order'] : 0;
            $orderB = isset($b['order']) ? $b['order'] : 0;
            return $orderA - $orderB;
        });

        return $active;
    }

    /**
     * Get test types (key => label format, for backward compatibility)
     */
    public static function get_test_types() {
        $categories = self::get_active_categories();
        $result = array();
        foreach ($categories as $cat) {
            $result[$cat['id']] = $cat['name'];
        }
        return $result;
    }

    /**
     * Get questions for a specific category
     */
    public static function get_category_questions($category_id) {
        $categories = self::get_categories();
        foreach ($categories as $cat) {
            if ($cat['id'] === $category_id) {
                $questions = isset($cat['questions']) ? $cat['questions'] : array();
                // Sort by order
                usort($questions, function($a, $b) {
                    $orderA = isset($a['order']) ? $a['order'] : 0;
                    $orderB = isset($b['order']) ? $b['order'] : 0;
                    return $orderA - $orderB;
                });
                return $questions;
            }
        }
        return array();
    }

    /**
     * Sanitize data
     */
    public function sanitize_questions($input) {
        $sanitized = array('categories' => array());

        if (!isset($input['categories']) || !is_array($input['categories'])) {
            return $sanitized;
        }

        $order = 0;
        foreach ($input['categories'] as $cat) {
            if (empty($cat['id']) || empty($cat['name'])) continue;

            $sanitized_cat = array(
                'id' => sanitize_key($cat['id']),
                'name' => sanitize_text_field($cat['name']),
                'icon' => isset($cat['icon']) ? sanitize_text_field($cat['icon']) : '📋',
                'active' => !empty($cat['active']),
                'order' => $order++,
                'questions' => array(),
            );

            // Process questions
            if (isset($cat['questions']) && is_array($cat['questions'])) {
                $q_order = 0;
                foreach ($cat['questions'] as $q) {
                    if (empty($q['id']) || empty($q['question'])) continue;

                    $sanitized_q = array(
                        'id' => sanitize_key($q['id']),
                        'question' => sanitize_text_field($q['question']),
                        'type' => sanitize_key($q['type']),
                        'required' => !empty($q['required']),
                        'placeholder' => isset($q['placeholder']) ? sanitize_text_field($q['placeholder']) : '',
                        'order' => $q_order++,
                        'options' => array(),
                    );

                    // Process options
                    if (isset($q['options']) && is_array($q['options'])) {
                        foreach ($q['options'] as $opt) {
                            if (empty($opt['id']) || empty($opt['label'])) continue;
                            $sanitized_q['options'][] = array(
                                'id' => sanitize_key($opt['id']),
                                'label' => sanitize_text_field($opt['label']),
                            );
                        }
                    }

                    $sanitized_cat['questions'][] = $sanitized_q;
                }
            }

            $sanitized['categories'][] = $sanitized_cat;
        }

        return $sanitized;
    }

    /**
     * AJAX save handler with security
     */
    public function ajax_save_questions() {
        // Output buffering - prevent PHP warnings from breaking JSON
        ob_start();

        // Nonce verification with proper sanitization
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'ptf_questions_nonce')) {
            ob_end_clean();
            wp_send_json_error(array('message' => __('Security verification failed.', 'pentest-quote-form')));
            return;
        }

        // Permission check
        if (!current_user_can('manage_options')) {
            ob_end_clean();
            wp_send_json_error(array('message' => __('Permission denied.', 'pentest-quote-form')));
            return;
        }

        // Get data with proper sanitization
        $raw_data = isset($_POST['questions']) ? wp_unslash($_POST['questions']) : '';

        if (empty($raw_data)) {
            ob_end_clean();
            wp_send_json_error(array('message' => __('No data received.', 'pentest-quote-form')));
            return;
        }

        $data = json_decode($raw_data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            ob_end_clean();
            wp_send_json_error(array('message' => __('JSON parse error:', 'pentest-quote-form') . ' ' . json_last_error_msg()));
            return;
        }

        if (!is_array($data)) {
            ob_end_clean();
            wp_send_json_error(array('message' => __('Invalid data format.', 'pentest-quote-form')));
            return;
        }

        // Sanitize data
        $sanitized = $this->sanitize_questions($data);
        $cat_count = isset($sanitized['categories']) ? count($sanitized['categories']) : 0;

        // Save to our table
        $result = self::save_categories($sanitized);

        ob_end_clean();

        if ($result) {
            wp_send_json_success(array(
                'message' => __('Settings saved.', 'pentest-quote-form'),
                'categories_saved' => $cat_count
            ));
        } else {
            global $wpdb;
            wp_send_json_error(array(
                'message' => __('Save failed - database error.', 'pentest-quote-form'),
                'db_error' => $wpdb->last_error
            ));
        }
    }

    /**
     * Admin sayfasını render et
     */
    public function render_questions_page() {
        $categories = self::get_categories();
        $answer_types = self::get_answer_types();
        ?>
        <div class="wrap ptf-questions-admin">
            <h1>
                <span class="dashicons dashicons-editor-help" style="margin-right: 8px;"></span>
                <?php esc_html_e('Question Management', 'pentest-quote-form'); ?>
            </h1>

            <p class="description" style="margin-bottom: 20px;">
                <?php esc_html_e('Manage test categories and their questions here. Categories are shown on the first page, and questions for selected categories on the second page.', 'pentest-quote-form'); ?>
            </p>

            <div id="ptf-questions-app">
                <!-- Category List -->
                <div class="ptf-categories-wrapper">
                    <div class="ptf-section-header">
                        <h2><?php esc_html_e('Test Categories', 'pentest-quote-form'); ?></h2>
                        <div class="header-actions">
                            <button type="button" class="button" id="load-sample-data" style="margin-right: 10px;">
                                <span class="dashicons dashicons-database-import"></span>
                                <?php esc_html_e('Load Sample Data', 'pentest-quote-form'); ?>
                            </button>
                            <button type="button" class="button button-primary" id="add-category">
                                <span class="dashicons dashicons-plus-alt"></span>
                                <?php esc_html_e('Add Category', 'pentest-quote-form'); ?>
                            </button>
                        </div>
                    </div>

                    <div id="categories-list" class="ptf-accordion">
                        <?php if (empty($categories)): ?>
                        <div class="no-categories-message">
                            <p><?php esc_html_e('No categories added yet. Click "Add Category" to start or use "Load Sample Data" for a quick start.', 'pentest-quote-form'); ?></p>
                        </div>
                        <?php else: ?>
                        <?php foreach ($categories as $cat_index => $category): ?>
                        <div class="ptf-category-item" data-category-index="<?php echo $cat_index; ?>">
                            <div class="ptf-category-header">
                                <span class="sort-handle dashicons dashicons-menu"></span>
                                <span class="category-icon"><?php echo esc_html($category['icon']); ?></span>
                                <span class="category-name"><?php echo esc_html($category['name']); ?></span>
                                <span class="category-id">(<?php echo esc_html($category['id']); ?>)</span>
                                <span class="category-question-count"><?php echo count($category['questions'] ?? array()); ?> <?php esc_html_e('questions', 'pentest-quote-form'); ?></span>
                                <label class="category-active-toggle">
                                    <input type="checkbox" class="category-active" <?php checked(!empty($category['active'])); ?>>
                                    <?php esc_html_e('Active', 'pentest-quote-form'); ?>
                                </label>
                                <button type="button" class="button button-small toggle-category">
                                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                                </button>
                                <button type="button" class="button button-link-delete delete-category">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                            <div class="ptf-category-content" style="display: none;">
                                <div class="category-details">
                                    <div class="detail-row">
                                        <label><?php esc_html_e('Category Name', 'pentest-quote-form'); ?></label>
                                        <input type="text" class="category-name-input large-text" value="<?php echo esc_attr($category['name']); ?>">
                                    </div>
                                    <div class="detail-row">
                                        <label><?php esc_html_e('ID (Unique Key)', 'pentest-quote-form'); ?></label>
                                        <input type="text" class="category-id-input regular-text" value="<?php echo esc_attr($category['id']); ?>">
                                    </div>
                                    <div class="detail-row">
                                        <label><?php esc_html_e('Icon (Emoji)', 'pentest-quote-form'); ?></label>
                                        <input type="text" class="category-icon-input" value="<?php echo esc_attr($category['icon']); ?>" style="width: 60px;">
                                    </div>
                                </div>

                                <div class="category-questions">
                                    <h4>
                                        <?php esc_html_e('Questions', 'pentest-quote-form'); ?>
                                        <button type="button" class="button button-small add-question">
                                            <span class="dashicons dashicons-plus"></span>
                                            <?php esc_html_e('Add Question', 'pentest-quote-form'); ?>
                                        </button>
                                    </h4>
                                    <div class="questions-list">
                                        <?php if (!empty($category['questions'])): ?>
                                        <?php foreach ($category['questions'] as $q_index => $question): ?>
                                        <div class="question-item" data-question-index="<?php echo $q_index; ?>">
                                            <div class="question-header">
                                                <span class="sort-handle dashicons dashicons-menu"></span>
                                                <span class="question-text"><?php echo esc_html($question['question']); ?></span>
                                                <span class="question-type-badge"><?php echo esc_html($answer_types[$question['type']] ?? $question['type']); ?></span>
                                                <?php if (!empty($question['required'])): ?>
                                                <span class="required-badge"><?php esc_html_e('Required', 'pentest-quote-form'); ?></span>
                                                <?php endif; ?>
                                                <button type="button" class="button button-small edit-question">
                                                    <span class="dashicons dashicons-edit"></span>
                                                </button>
                                                <button type="button" class="button button-link-delete delete-question">
                                                    <span class="dashicons dashicons-trash"></span>
                                                </button>
                                            </div>
                                            <div class="question-details" style="display: none;">
                                                <?php $this->render_question_form($question, $answer_types); ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Save Butonu -->
                <div class="ptf-save-section">
                    <button type="button" class="button button-primary button-hero" id="save-all-questions">
                        <span class="dashicons dashicons-saved"></span>
                        <?php esc_html_e('Save All Changes', 'pentest-quote-form'); ?>
                    </button>
                    <span class="spinner" style="float: none; margin-left: 10px;"></span>
                    <span id="save-status" style="margin-left: 10px;"></span>
                </div>
            </div>
        </div>

        <?php
    }
    /**
     * Render question form
     */
    private function render_question_form($question, $answer_types) {
        $show_options = in_array($question["type"], array("select", "radio", "checkbox"));
        ?>
        <div class="question-form-grid">
            <div class="form-row full-width">
                <label><?php esc_html_e("Question Text", "pentest-quote-form"); ?></label>
                <input type="text" class="question-text-input large-text" value="<?php echo esc_attr($question["question"]); ?>">
            </div>
            <div class="form-row">
                <label><?php esc_html_e("Question ID", "pentest-quote-form"); ?></label>
                <input type="text" class="question-id-input regular-text" value="<?php echo esc_attr($question["id"]); ?>">
            </div>
            <div class="form-row">
                <label><?php esc_html_e("Answer Type", "pentest-quote-form"); ?></label>
                <select class="question-type-select">
                    <?php foreach ($answer_types as $key => $label): ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($question["type"], $key); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <label><?php esc_html_e("Placeholder", "pentest-quote-form"); ?></label>
                <input type="text" class="question-placeholder regular-text" value="<?php echo esc_attr($question["placeholder"] ?? ""); ?>">
            </div>
            <div class="form-row">
                <label>
                    <input type="checkbox" class="question-required" <?php checked(!empty($question["required"])); ?>>
                    <?php esc_html_e("Required Field", "pentest-quote-form"); ?>
                </label>
            </div>
        </div>
        <div class="options-container" style="<?php echo $show_options ? "" : "display: none;"; ?>">
            <h5><?php esc_html_e("Options", "pentest-quote-form"); ?></h5>
            <div class="options-list">
                <?php if (!empty($question["options"])): ?>
                <?php foreach ($question["options"] as $opt): ?>
                <div class="option-item">
                    <input type="text" class="option-label-input regular-text" value="<?php echo esc_attr($opt["label"]); ?>" placeholder="<?php esc_attr_e("Option text", "pentest-quote-form"); ?>">
                    <input type="text" class="option-id-input" value="<?php echo esc_attr($opt["id"]); ?>" placeholder="<?php esc_attr_e("ID", "pentest-quote-form"); ?>" style="width: 150px;">
                    <button type="button" class="button button-link-delete remove-option">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button type="button" class="button button-small add-option add-option-btn">
                <span class="dashicons dashicons-plus"></span>
                <?php esc_html_e("Add Option", "pentest-quote-form"); ?>
            </button>
        </div>
        <?php
    }
}
// Initialize class
PTF_Form_Questions::get_instance();

