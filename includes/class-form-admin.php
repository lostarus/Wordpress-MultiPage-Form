<?php
/**
 * Form Submissions Admin Page
 * View Penetration Test Quote Requests in Admin Panel
 */

if (!defined('ABSPATH')) {
    exit;
}

class PTF_Form_Admin {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'), 10);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        add_action('wp_ajax_ptf_delete_submission', array($this, 'delete_submission'));
        add_action('wp_ajax_ptf_update_submission_status', array($this, 'update_status'));
        add_action('wp_ajax_ptf_export_submissions', array($this, 'export_csv'));
    }

    public function add_admin_menu() {
        add_menu_page(
            __('Quote Requests', 'pentest-quote-form'),
            __('Quote Requests', 'pentest-quote-form'),
            'manage_options',
            'ptf-submissions',
            array($this, 'render_admin_page'),
            'dashicons-shield',
            30
        );
    }

    public function enqueue_admin_styles($hook) {
        if ($hook !== 'toplevel_page_ptf-submissions') {
            return;
        }

        // Inline CSS - no external file required
        wp_add_inline_style('wp-admin', $this->get_admin_css());
    }

    private function get_admin_css() {
        return '
        /* Button Icon Alignment */
        .ptf-form-admin .button {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .ptf-form-admin .button .dashicons {
            font-size: 16px;
            width: 16px;
            height: 16px;
            line-height: 1;
        }
        .ptf-form-admin .wp-heading-inline {
            display: flex;
            align-items: center;
        }
        .ptf-admin-filters {
            margin: 20px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .ptf-admin-filters .subsubsub {
            margin: 0;
            float: none;
        }
        .ptf-no-submissions {
            text-align: center;
            padding: 60px 20px;
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }
        .ptf-submissions-table .column-name { width: 18%; }
        .ptf-submissions-table .column-email { width: 20%; }
        .ptf-submissions-table .column-tests { width: 22%; }
        .ptf-submissions-table .column-date { width: 12%; }
        .ptf-submissions-table .column-status { width: 12%; }
        .ptf-submissions-table .column-actions { width: 16%; }
        .ptf-submissions-table .company-name {
            color: #666;
            font-size: 12px;
        }
        .ptf-submissions-table tr.status-new td:first-child {
            border-left: 3px solid #2F7CFF;
        }
        .ptf-status-select {
            width: 100%;
            padding: 4px 8px;
        }
        .ptf-submission-details {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 4px;
        }
        .ptf-submission-details .detail-section {
            margin-bottom: 20px;
        }
        .ptf-submission-details h4 {
            margin: 0 0 10px 0;
            color: #1d2327;
        }
        .ptf-submission-details .test-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .ptf-submission-details .test-badge {
            background: #2F7CFF;
            color: #fff;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
        .ptf-submission-details .target-scope {
            background: #fff;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            white-space: pre-wrap;
        }
        .ptf-submission-details .detail-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        .ptf-submission-details .detail-item {
            background: #fff;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .ptf-submission-details .detail-item strong {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-size: 11px;
            text-transform: uppercase;
        }
        @media (max-width: 782px) {
            .ptf-submission-details .detail-grid {
                grid-template-columns: 1fr;
            }
        }
        ';
    }

    public function render_admin_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ptf_submissions';

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            echo '<div class="wrap"><h1>' . esc_html__('Quote Requests', 'pentest-quote-form') . '</h1>';
            echo '<div class="ptf-no-submissions">';
            echo '<span class="dashicons dashicons-shield" style="font-size: 48px; color: #ccc;"></span>';
            echo '<p>' . esc_html__('No quote requests received yet or table not created.', 'pentest-quote-form') . '</p>';
            echo '<p><small>' . __('Table name:', 'pentest-quote-form') . ' ' . esc_html($table_name) . '</small></p>';
            echo '</div></div>';
            return;
        }

        // Filtering
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        // Pagination
        $per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;

        // Build query
        $where = array('1=1');

        if (!empty($status_filter)) {
            $where[] = $wpdb->prepare('status = %s', $status_filter);
        }

        if (!empty($search)) {
            $where[] = $wpdb->prepare(
                '(first_name LIKE %s OR last_name LIKE %s OR email LIKE %s OR company LIKE %s OR test_types LIKE %s)',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }

        $where_sql = implode(' AND ', $where);

        // Total records - count query is safe since $where_sql is built with prepare()
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM `{$table_name}` WHERE {$where_sql}");
        $total_pages = ceil($total_items / $per_page);

        // Get records with proper limits
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $submissions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM `{$table_name}` WHERE {$where_sql} ORDER BY submitted_at DESC LIMIT %d, %d",
                $offset,
                $per_page
            )
        );

        // Status counts
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $status_counts = $wpdb->get_results(
            "SELECT status, COUNT(*) as count FROM `{$table_name}` GROUP BY status",
            OBJECT_K
        );

        ?>
        <div class="wrap ptf-form-admin">
            <h1 class="wp-heading-inline">
                <span class="dashicons dashicons-shield" style="margin-right: 8px;"></span>
                <?php esc_html_e('Penetration Test Quote Requests', 'pentest-quote-form'); ?>
            </h1>

            <?php if ($total_items > 0): ?>
                <a href="<?php echo admin_url('admin-ajax.php?action=ptf_export_submissions&nonce=' . wp_create_nonce('ptf_export')); ?>" class="page-title-action">
                    <?php esc_html_e('Download as CSV', 'pentest-quote-form'); ?>
                </a>
            <?php endif; ?>

            <hr class="wp-header-end">

            <!-- Filters -->
            <div class="ptf-admin-filters">
                <ul class="subsubsub">
                    <li>
                        <a href="<?php echo admin_url('admin.php?page=ptf-submissions'); ?>"
                           class="<?php echo empty($status_filter) ? 'current' : ''; ?>">
                            <?php esc_html_e('All', 'pentest-quote-form'); ?>
                            <span class="count">(<?php echo $total_items; ?>)</span>
                        </a> |
                    </li>
                    <li>
                        <a href="<?php echo admin_url('admin.php?page=ptf-submissions&status=new'); ?>"
                           class="<?php echo $status_filter === 'new' ? 'current' : ''; ?>">
                            <?php esc_html_e('New', 'pentest-quote-form'); ?>
                            <span class="count">(<?php echo isset($status_counts['new']) ? $status_counts['new']->count : 0; ?>)</span>
                        </a> |
                    </li>
                    <li>
                        <a href="<?php echo admin_url('admin.php?page=ptf-submissions&status=read'); ?>"
                           class="<?php echo $status_filter === 'read' ? 'current' : ''; ?>">
                            <?php esc_html_e('Reviewed', 'pentest-quote-form'); ?>
                            <span class="count">(<?php echo isset($status_counts['read']) ? $status_counts['read']->count : 0; ?>)</span>
                        </a> |
                    </li>
                    <li>
                        <a href="<?php echo admin_url('admin.php?page=ptf-submissions&status=replied'); ?>"
                           class="<?php echo $status_filter === 'replied' ? 'current' : ''; ?>">
                            <?php esc_html_e('Quote Sent', 'pentest-quote-form'); ?>
                            <span class="count">(<?php echo isset($status_counts['replied']) ? $status_counts['replied']->count : 0; ?>)</span>
                        </a>
                    </li>
                </ul>

                <form method="get" class="search-form">
                    <input type="hidden" name="page" value="ptf-submissions">
                    <?php if (!empty($status_filter)): ?>
                        <input type="hidden" name="status" value="<?php echo esc_attr($status_filter); ?>">
                    <?php endif; ?>
                    <p class="search-box">
                        <input type="search" name="s" value="<?php echo esc_attr($search); ?>"
                               placeholder="<?php esc_attr_e('Search name, company, email or test type...', 'pentest-quote-form'); ?>">
                        <input type="submit" class="button" value="<?php esc_attr_e('Search', 'pentest-quote-form'); ?>">
                    </p>
                </form>
            </div>

            <?php if (empty($submissions)): ?>
                <div class="ptf-no-submissions">
                    <span class="dashicons dashicons-shield" style="font-size: 48px; color: #ccc;"></span>
                    <p><?php esc_html_e('No quote requests found to display.', 'pentest-quote-form'); ?></p>
                </div>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped ptf-submissions-table">
                    <thead>
                        <tr>
                            <th class="column-name"><?php esc_html_e('Person / Company', 'pentest-quote-form'); ?></th>
                            <th class="column-email"><?php esc_html_e('Contact', 'pentest-quote-form'); ?></th>
                            <th class="column-tests"><?php esc_html_e('Test Types', 'pentest-quote-form'); ?></th>
                            <th class="column-date"><?php esc_html_e('Date', 'pentest-quote-form'); ?></th>
                            <th class="column-status"><?php esc_html_e('Status', 'pentest-quote-form'); ?></th>
                            <th class="column-actions"><?php esc_html_e('Actions', 'pentest-quote-form'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $submission): ?>
                            <tr data-id="<?php echo esc_attr($submission->id); ?>" class="status-<?php echo esc_attr($submission->status); ?>">
                                <td class="column-name">
                                    <strong><?php echo esc_html($submission->first_name . ' ' . $submission->last_name); ?></strong>
                                    <br><span class="company-name"><?php echo esc_html($submission->company); ?></span>
                                </td>
                                <td class="column-email">
                                    <a href="mailto:<?php echo esc_attr($submission->email); ?>">
                                        <?php echo esc_html($submission->email); ?>
                                    </a>
                                    <?php if (!empty($submission->phone)): ?>
                                        <br><small><?php echo esc_html($submission->phone); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="column-tests">
                                    <div class="test-types-list">
                                        <?php
                                        $tests = explode(', ', $submission->test_types);
                                        $display_tests = array_slice($tests, 0, 2);
                                        echo esc_html(implode(', ', $display_tests));
                                        if (count($tests) > 2) {
                                            echo '<br><small>+' . (count($tests) - 2) . ' ' . esc_html__('more', 'pentest-quote-form') . '</small>';
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td class="column-date">
                                    <?php echo esc_html(date_i18n('d M Y', strtotime($submission->submitted_at))); ?>
                                    <br><small><?php echo esc_html(date_i18n('H:i', strtotime($submission->submitted_at))); ?></small>
                                </td>
                                <td class="column-status">
                                    <select class="ptf-status-select" data-id="<?php echo esc_attr($submission->id); ?>">
                                        <option value="new" <?php selected($submission->status, 'new'); ?>><?php esc_html_e('New', 'pentest-quote-form'); ?></option>
                                        <option value="read" <?php selected($submission->status, 'read'); ?>><?php esc_html_e('Reviewed', 'pentest-quote-form'); ?></option>
                                        <option value="replied" <?php selected($submission->status, 'replied'); ?>><?php esc_html_e('Quote Sent', 'pentest-quote-form'); ?></option>
                                    </select>
                                </td>
                                <td class="column-actions">
                                    <button type="button" class="button ptf-view-details" data-id="<?php echo esc_attr($submission->id); ?>">
                                        <?php esc_html_e('Details', 'pentest-quote-form'); ?>
                                    </button>
                                    <button type="button" class="button button-link-delete ptf-delete-submission" data-id="<?php echo esc_attr($submission->id); ?>">
                                        <?php esc_html_e('Delete', 'pentest-quote-form'); ?>
                                    </button>
                                </td>
                            </tr>
                            <tr class="ptf-details-row" id="details-<?php echo esc_attr($submission->id); ?>" style="display:none;">
                                <td colspan="6">
                                    <div class="ptf-submission-details">
                                        <div class="detail-section">
                                            <h4><?php esc_html_e('Requested Tests', 'pentest-quote-form'); ?></h4>
                                            <div class="test-badges">
                                                <?php
                                                $tests = explode(', ', $submission->test_types);
                                                foreach ($tests as $test):
                                                ?>
                                                    <span class="test-badge"><?php echo esc_html($test); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                        <div class="detail-section">
                                            <h4><?php esc_html_e('Target / Scope', 'pentest-quote-form'); ?></h4>
                                            <div class="target-scope">
                                                <?php echo nl2br(esc_html($submission->target_scope)); ?>
                                            </div>
                                        </div>

                                        <div class="detail-grid">
                                            <div class="detail-item">
                                                <strong><?php esc_html_e('Page URL:', 'pentest-quote-form'); ?></strong>
                                                <a href="<?php echo esc_url($submission->page_url); ?>" target="_blank">
                                                    <?php echo esc_html($submission->page_url ?: '-'); ?>
                                                </a>
                                            </div>
                                            <div class="detail-item">
                                                <strong><?php esc_html_e('IP Address:', 'pentest-quote-form'); ?></strong>
                                                <?php echo esc_html($submission->user_ip ?: '-'); ?>
                                            </div>
                                            <div class="detail-item">
                                                <strong><?php esc_html_e('Privacy Consent:', 'pentest-quote-form'); ?></strong>
                                                <?php echo $submission->kvkk_consent ? '✅ ' . esc_html__('Accepted', 'pentest-quote-form') : '❌ ' . esc_html__('Not Accepted', 'pentest-quote-form'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="tablenav bottom">
                        <div class="tablenav-pages">
                            <?php
                            $page_links = paginate_links(array(
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;',
                                'total' => $total_pages,
                                'current' => $current_page
                            ));

                            if ($page_links) {
                                echo '<span class="displaying-num">' . sprintf(
                                    _n('%s request', '%s requests', $total_items, 'pentest-quote-form'),
                                    number_format_i18n($total_items)
                                ) . '</span>';
                                echo '<span class="pagination-links">' . $page_links . '</span>';
                            }
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Show/hide details
            $('.ptf-view-details').on('click', function() {
                var id = $(this).data('id');
                $('#details-' + id).toggle();
            });

            // Update status
            $('.ptf-status-select').on('change', function() {
                var id = $(this).data('id');
                var status = $(this).val();

                $.post(ajaxurl, {
                    action: 'ptf_update_submission_status',
                    nonce: <?php echo wp_json_encode(wp_create_nonce('ptf_admin')); ?>,
                    id: id,
                    status: status
                });
            });

            // Delete
            $('.ptf-delete-submission').on('click', function() {
                if (!confirm(<?php echo wp_json_encode(__('Are you sure you want to delete this quote request?', 'pentest-quote-form')); ?>)) {
                    return;
                }

                var id = $(this).data('id');
                var row = $(this).closest('tr');

                $.post(ajaxurl, {
                    action: 'ptf_delete_submission',
                    nonce: <?php echo wp_json_encode(wp_create_nonce('ptf_admin')); ?>,
                    id: id
                }, function(response) {
                    if (response.success) {
                        row.fadeOut(function() {
                            $(this).remove();
                            $('#details-' + id).remove();
                        });
                    }
                });
            });
        });
        </script>
        <?php
    }

    public function delete_submission() {
        // Verify nonce with proper sanitization
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ptf_admin')) {
            wp_send_json_error(array('message' => __('Security verification failed.', 'pentest-quote-form')));
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'pentest-quote-form')));
            return;
        }

        if (!isset($_POST['id'])) {
            wp_send_json_error(array('message' => __('Invalid request.', 'pentest-quote-form')));
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'ptf_submissions';
        $id = absint($_POST['id']);

        if ($id <= 0) {
            wp_send_json_error(array('message' => __('Invalid ID.', 'pentest-quote-form')));
            return;
        }

        $result = $wpdb->delete($table_name, array('id' => $id), array('%d'));

        if ($result) {
            wp_send_json_success(array('message' => __('Submission deleted successfully.', 'pentest-quote-form')));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete submission.', 'pentest-quote-form')));
        }
    }

    public function update_status() {
        // Verify nonce with proper sanitization
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ptf_admin')) {
            wp_send_json_error(array('message' => __('Security verification failed.', 'pentest-quote-form')));
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'pentest-quote-form')));
            return;
        }

        if (!isset($_POST['id']) || !isset($_POST['status'])) {
            wp_send_json_error(array('message' => __('Invalid request.', 'pentest-quote-form')));
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'ptf_submissions';
        $id = absint($_POST['id']);
        $status = sanitize_key(wp_unslash($_POST['status']));

        if ($id <= 0) {
            wp_send_json_error(array('message' => __('Invalid ID.', 'pentest-quote-form')));
            return;
        }

        $allowed_statuses = array('new', 'read', 'replied');
        if (!in_array($status, $allowed_statuses, true)) {
            wp_send_json_error(array('message' => __('Invalid status.', 'pentest-quote-form')));
            return;
        }

        $result = $wpdb->update(
            $table_name,
            array('status' => $status),
            array('id' => $id),
            array('%s'),
            array('%d')
        );

        if ($result !== false) {
            wp_send_json_success(array('message' => __('Status updated successfully.', 'pentest-quote-form')));
        } else {
            wp_send_json_error(array('message' => __('Failed to update status.', 'pentest-quote-form')));
        }
    }

    public function export_csv() {
        // Verify nonce with proper sanitization
        if (!isset($_GET['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['nonce'])), 'ptf_export')) {
            wp_die(esc_html__('Security verification failed.', 'pentest-quote-form'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'pentest-quote-form'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'ptf_submissions';

        // Use prepared statement
        $submissions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM `{$table_name}` ORDER BY submitted_at DESC LIMIT %d",
                10000 // Reasonable limit to prevent memory issues
            )
        );

        // Sanitize filename
        $filename = 'pentest-quote-requests-' . gmdate('Y-m-d') . '.csv';

        // CSV headers with security headers
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-Content-Type-Options: nosniff');

        $output = fopen('php://output', 'w');

        // UTF-8 BOM
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header row
        fputcsv($output, array(
            'ID',
            __('First Name', 'pentest-quote-form'),
            __('Last Name', 'pentest-quote-form'),
            __('Email', 'pentest-quote-form'),
            __('Phone', 'pentest-quote-form'),
            __('Company', 'pentest-quote-form'),
            __('Test Types', 'pentest-quote-form'),
            __('Target / Scope', 'pentest-quote-form'),
            __('Privacy Consent', 'pentest-quote-form'),
            __('Page URL', 'pentest-quote-form'),
            __('IP Address', 'pentest-quote-form'),
            __('Submission Date', 'pentest-quote-form'),
            __('Status', 'pentest-quote-form')
        ));

        // Data
        foreach ($submissions as $row) {
            fputcsv($output, array(
                $row->id,
                $row->first_name,
                $row->last_name,
                $row->email,
                $row->phone,
                $row->company,
                $row->test_types,
                $row->target_scope,
                $row->kvkk_consent ? __('Yes', 'pentest-quote-form') : __('No', 'pentest-quote-form'),
                $row->page_url,
                $row->user_ip,
                $row->submitted_at,
                $row->status
            ));
        }

        fclose($output);
        exit;
    }
}

// Initialize admin class
PTF_Form_Admin::get_instance();

