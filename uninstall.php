<?php
/**
 * Plugin Uninstall
 * This file runs when the plugin is deleted
 */

// Exit if not called by WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('ptf_settings');
delete_option('ptf_version');
delete_option('ptf_table_created');
delete_option('ptf_questions'); // Old wp_options record (if exists)

// To delete database tables (optional - uncomment to enable)
// global $wpdb;
// $submissions_table = $wpdb->prefix . 'ptf_submissions';
// $questions_table = $wpdb->prefix . 'ptf_questions';
// $wpdb->query("DROP TABLE IF EXISTS $submissions_table");
// $wpdb->query("DROP TABLE IF EXISTS $questions_table");
