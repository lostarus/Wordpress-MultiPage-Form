/**
 * Admin Settings Page Scripts
 * Pentest Quote Form - Settings Page
 */

(function ($) {
    'use strict';

    // Global variables (passed from PHP via wp_localize_script)
    const ajaxUrl = typeof ptfSettingsAdmin !== 'undefined' ? ptfSettingsAdmin.ajaxUrl : ajaxurl;
    const nonce = typeof ptfSettingsAdmin !== 'undefined' ? ptfSettingsAdmin.nonce : '';
    const i18n = typeof ptfSettingsAdmin !== 'undefined' ? ptfSettingsAdmin.i18n : {};

    /**
     * Settings Manager Class
     */
    class PTFSettingsManager {
        constructor() {
            this.webhooks = [];
            this.bindEvents();
            this.initColorPickers();
            this.loadWebhooksFromJson();
        }

        /**
         * Bind all event handlers
         */
        bindEvents() {
            // Color picker change - update preview
            $(document).on('change', '.ptf-color-picker', this.updateColorPreview.bind(this));

            // Webhook template buttons
            $('.webhook-template-btn').on('click', this.addWebhookTemplate.bind(this));

            // Toggle JSON editor
            window.toggleJsonEditor = this.toggleJsonEditor.bind(this);

            // Validate JSON
            window.validateJson = this.validateJson.bind(this);

            // Format JSON
            window.formatJson = this.formatJson.bind(this);

            // Show JSON help
            window.showJsonHelp = this.showJsonHelp.bind(this);

            // Add webhook button
            $('#add-webhook-btn').on('click', () => this.openWebhookModal(-1));

            // Edit webhook
            $(document).on('click', '.edit-webhook-btn', (e) => {
                const index = $(e.currentTarget).data('index');
                this.openWebhookModal(index);
            });

            // Delete webhook
            $(document).on('click', '.delete-webhook-btn', (e) => {
                const index = $(e.currentTarget).data('index');
                this.deleteWebhook(index);
            });

            // Test webhooks
            $('#test-webhooks-btn').on('click', this.testWebhooks.bind(this));

            // Modal close
            $(document).on('click', '.webhook-modal-close, .webhook-modal-overlay', (e) => {
                if ($(e.target).is('.webhook-modal-close, .webhook-modal-overlay')) {
                    this.closeWebhookModal();
                }
            });

            // Save webhook in modal
            $(document).on('click', '#save-webhook-btn', this.saveWebhookFromModal.bind(this));

            // Data storage checkboxes
            $('input[name="ptf_settings[save_to_database]"], input[name="ptf_settings[send_email_notification]"]')
                .on('change', this.checkDataStorageWarning.bind(this));

            // Button size toggle
            $('#button_size').on('change', function() {
                if ($(this).val() === 'custom') {
                    $('#custom-button-size-row').show();
                } else {
                    $('#custom-button-size-row').hide();
                }
            });

            // Font family toggle
            $('#font_family').on('change', function() {
                if ($(this).val() === 'custom') {
                    $('#custom-font-row').show();
                } else {
                    $('#custom-font-row').hide();
                }
            });

            // Salesforce enable/disable toggle
            $('#enable_salesforce').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#salesforce-config-section').show();
                } else {
                    $('#salesforce-config-section').hide();
                }
            });

            // Salesforce auth flow toggle (show/hide password grant fields)
            $('#salesforce_auth_flow').on('change', function() {
                if ($(this).val() === 'password') {
                    $('#salesforce-password-grant-fields').show();
                } else {
                    $('#salesforce-password-grant-fields').hide();
                }
            });
        }

        /**
         * Initialize color pickers
         */
        initColorPickers() {
            if ($.fn.wpColorPicker) {
                $('.ptf-color-picker').wpColorPicker({
                    change: () => {
                        setTimeout(() => this.updateColorPreview(), 100);
                    }
                });
            }
        }

        /**
         * Update color preview
         */
        updateColorPreview() {
            const primary = $('input[name="ptf_settings[primary_color]"]').val() || '#2F7CFF';
            const secondary = $('input[name="ptf_settings[secondary_color]"]').val() || '#B7FF10';

            $('.preview-btn-primary').css('background', `linear-gradient(135deg, ${primary} 0%, ${this.adjustBrightness(primary, -30)} 100%)`);
            $('.preview-success').css('color', secondary);
        }

        /**
         * Adjust color brightness
         */
        adjustBrightness(hex, steps) {
            hex = hex.replace('#', '');
            let r = Math.max(0, Math.min(255, parseInt(hex.substr(0, 2), 16) + steps));
            let g = Math.max(0, Math.min(255, parseInt(hex.substr(2, 2), 16) + steps));
            let b = Math.max(0, Math.min(255, parseInt(hex.substr(4, 2), 16) + steps));
            return '#' + [r, g, b].map(x => x.toString(16).padStart(2, '0')).join('');
        }

        /**
         * Load webhooks from JSON textarea
         */
        loadWebhooksFromJson() {
            const jsonText = $('#webhooks_json').val();
            if (jsonText) {
                try {
                    this.webhooks = JSON.parse(jsonText);
                    this.renderWebhooksList();
                } catch (e) {
                    console.error('Failed to parse webhooks JSON:', e);
                    this.webhooks = [];
                }
            }
        }

        /**
         * Add webhook from template
         */
        addWebhookTemplate(e) {
            const type = $(e.currentTarget).data('type');
            const templates = {
                power_automate: {
                    name: 'Power Automate',
                    type: 'power_automate',
                    url: '',
                    method: 'POST',
                    active: true,
                    headers: {},
                    auth_type: 'none',
                    auth_value: ''
                },
                zapier: {
                    name: 'Zapier',
                    type: 'zapier',
                    url: '',
                    method: 'POST',
                    active: true,
                    headers: {},
                    auth_type: 'none',
                    auth_value: ''
                },
                make: {
                    name: 'Make (Integromat)',
                    type: 'make',
                    url: '',
                    method: 'POST',
                    active: true,
                    headers: {},
                    auth_type: 'none',
                    auth_value: ''
                },
                custom: {
                    name: 'Custom API',
                    type: 'custom',
                    url: '',
                    method: 'POST',
                    active: true,
                    headers: {'Content-Type': 'application/json'},
                    auth_type: 'none',
                    auth_value: ''
                }
            };

            const template = templates[type] || templates.custom;
            this.webhooks.push(template);
            this.updateWebhooksJson();
            this.renderWebhooksList();

            // Open modal to edit
            this.openWebhookModal(this.webhooks.length - 1);
        }

        /**
         * Render webhooks list table
         */
        renderWebhooksList() {
            const $container = $('#webhooks-list');
            if (!$container.length) return;

            if (this.webhooks.length === 0) {
                $container.html(`<p class="no-webhooks">${i18n.noWebhooks || 'No webhooks configured yet.'}</p>`);
                return;
            }

            let html = `
                <table class="webhooks-table widefat">
                    <thead>
                        <tr>
                            <th>${i18n.name || 'Name'}</th>
                            <th>${i18n.type || 'Type'}</th>
                            <th>${i18n.url || 'URL'}</th>
                            <th>${i18n.status || 'Status'}</th>
                            <th style="width: 120px;">${i18n.actions || 'Actions'}</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            this.webhooks.forEach((webhook, index) => {
                const statusClass = webhook.active ? 'webhook-status-active' : 'webhook-status-inactive';
                const statusText = webhook.active ? (i18n.active || 'Active') : (i18n.inactive || 'Inactive');
                const shortUrl = webhook.url ? (webhook.url.length > 40 ? webhook.url.substr(0, 40) + '...' : webhook.url) : '-';

                html += `
                    <tr>
                        <td><strong>${this.escapeHtml(webhook.name)}</strong></td>
                        <td>${this.escapeHtml(webhook.type || 'custom')}</td>
                        <td><code title="${this.escapeHtml(webhook.url)}">${this.escapeHtml(shortUrl)}</code></td>
                        <td class="${statusClass}">${statusText}</td>
                        <td>
                            <button type="button" class="button button-small edit-webhook-btn" data-index="${index}" title="${i18n.edit || 'Edit'}">
                                <span class="dashicons dashicons-edit"></span>
                            </button>
                            <button type="button" class="button button-small delete-webhook-btn" data-index="${index}" title="${i18n.delete || 'Delete'}">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </td>
                    </tr>
                `;
            });

            html += '</tbody></table>';
            $container.html(html);
        }

        /**
         * Open webhook edit modal
         */
        openWebhookModal(index) {
            const isNew = index === -1;
            const webhook = isNew ? {
                name: '',
                type: 'custom',
                url: '',
                method: 'POST',
                active: true,
                headers: {},
                auth_type: 'none',
                auth_value: ''
            } : this.webhooks[index];

            const modalHtml = `
                <div class="webhook-modal-overlay" id="webhook-modal">
                    <div class="webhook-modal">
                        <div class="webhook-modal-header">
                            <h3>${isNew ? (i18n.addWebhook || 'Add Webhook') : (i18n.editWebhook || 'Edit Webhook')}</h3>
                            <button type="button" class="webhook-modal-close">&times;</button>
                        </div>
                        <div class="webhook-modal-body">
                            <input type="hidden" id="webhook-edit-index" value="${index}">
                            
                            <div class="webhook-form-row">
                                <label for="webhook-name">${i18n.name || 'Name'}</label>
                                <input type="text" id="webhook-name" class="regular-text" value="${this.escapeHtml(webhook.name)}">
                            </div>
                            
                            <div class="webhook-form-row">
                                <label for="webhook-type">${i18n.type || 'Type'}</label>
                                <select id="webhook-type">
                                    <option value="custom" ${webhook.type === 'custom' ? 'selected' : ''}>Custom API</option>
                                    <option value="power_automate" ${webhook.type === 'power_automate' ? 'selected' : ''}>Power Automate</option>
                                    <option value="zapier" ${webhook.type === 'zapier' ? 'selected' : ''}>Zapier</option>
                                    <option value="make" ${webhook.type === 'make' ? 'selected' : ''}>Make (Integromat)</option>
                                </select>
                            </div>
                            
                            <div class="webhook-form-row">
                                <label for="webhook-url">${i18n.url || 'URL'}</label>
                                <input type="url" id="webhook-url" class="large-text" value="${this.escapeHtml(webhook.url)}" placeholder="https://...">
                            </div>
                            
                            <div class="webhook-form-row">
                                <label for="webhook-method">${i18n.method || 'Method'}</label>
                                <select id="webhook-method">
                                    <option value="POST" ${webhook.method === 'POST' ? 'selected' : ''}>POST</option>
                                    <option value="PUT" ${webhook.method === 'PUT' ? 'selected' : ''}>PUT</option>
                                    <option value="PATCH" ${webhook.method === 'PATCH' ? 'selected' : ''}>PATCH</option>
                                </select>
                            </div>
                            
                            <div class="webhook-form-row">
                                <label for="webhook-auth-type">${i18n.authType || 'Authentication'}</label>
                                <select id="webhook-auth-type">
                                    <option value="none" ${webhook.auth_type === 'none' ? 'selected' : ''}>${i18n.noAuth || 'None'}</option>
                                    <option value="bearer" ${webhook.auth_type === 'bearer' ? 'selected' : ''}>Bearer Token</option>
                                    <option value="basic" ${webhook.auth_type === 'basic' ? 'selected' : ''}>Basic Auth</option>
                                    <option value="api_key" ${webhook.auth_type === 'api_key' ? 'selected' : ''}>API Key</option>
                                </select>
                            </div>
                            
                            <div class="webhook-form-row" id="webhook-auth-value-row" style="display: ${webhook.auth_type !== 'none' ? 'block' : 'none'};">
                                <label for="webhook-auth-value">${i18n.authValue || 'Auth Value'}</label>
                                <input type="text" id="webhook-auth-value" class="regular-text" value="${this.escapeHtml(webhook.auth_value || '')}">
                                <p class="description">${i18n.authValueDesc || 'Token, username:password, or API key'}</p>
                            </div>
                            
                            <div class="webhook-form-row">
                                <label>
                                    <input type="checkbox" id="webhook-active" ${webhook.active ? 'checked' : ''}>
                                    ${i18n.active || 'Active'}
                                </label>
                            </div>
                        </div>
                        <div class="webhook-modal-footer">
                            <button type="button" class="button webhook-modal-close">${i18n.cancel || 'Cancel'}</button>
                            <button type="button" class="button button-primary" id="save-webhook-btn">${i18n.save || 'Save'}</button>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(modalHtml);

            // Auth type change handler
            $('#webhook-auth-type').on('change', function () {
                $('#webhook-auth-value-row').toggle($(this).val() !== 'none');
            });
        }

        /**
         * Close webhook modal
         */
        closeWebhookModal() {
            $('#webhook-modal').remove();
        }

        /**
         * Save webhook from modal
         */
        saveWebhookFromModal() {
            const index = parseInt($('#webhook-edit-index').val());
            const webhook = {
                name: $('#webhook-name').val(),
                type: $('#webhook-type').val(),
                url: $('#webhook-url').val(),
                method: $('#webhook-method').val(),
                auth_type: $('#webhook-auth-type').val(),
                auth_value: $('#webhook-auth-value').val(),
                active: $('#webhook-active').is(':checked'),
                headers: {}
            };

            if (!webhook.name || !webhook.url) {
                alert(i18n.requiredFields || 'Name and URL are required.');
                return;
            }

            if (index === -1) {
                this.webhooks.push(webhook);
            } else {
                this.webhooks[index] = webhook;
            }

            this.updateWebhooksJson();
            this.renderWebhooksList();
            this.closeWebhookModal();
        }

        /**
         * Delete webhook
         */
        deleteWebhook(index) {
            if (!confirm(i18n.confirmDelete || 'Are you sure you want to delete this webhook?')) {
                return;
            }

            this.webhooks.splice(index, 1);
            this.updateWebhooksJson();
            this.renderWebhooksList();
        }

        /**
         * Update webhooks JSON textarea
         */
        updateWebhooksJson() {
            $('#webhooks_json').val(JSON.stringify(this.webhooks, null, 2));
        }

        /**
         * Toggle JSON editor visibility
         */
        toggleJsonEditor() {
            $('#json-editor-container').toggle();
        }

        /**
         * Validate JSON in editor
         */
        validateJson() {
            const $textarea = $('#webhooks_json');
            const $result = $('#json-validation-result');

            try {
                JSON.parse($textarea.val());
                $result.text('✓ ' + (i18n.validJson || 'Valid JSON')).removeClass('invalid').addClass('valid');
                return true;
            } catch (e) {
                $result.text('✗ ' + (i18n.invalidJson || 'Invalid JSON:') + ' ' + e.message).removeClass('valid').addClass('invalid');
                return false;
            }
        }

        /**
         * Format JSON in editor
         */
        formatJson() {
            const $textarea = $('#webhooks_json');
            try {
                const json = JSON.parse($textarea.val());
                $textarea.val(JSON.stringify(json, null, 2));
                this.validateJson();
            } catch (e) {
                alert(i18n.formatError || 'Cannot format invalid JSON');
            }
        }

        /**
         * Show JSON help modal
         */
        showJsonHelp() {
            $('#json-help-modal').show();
        }

        /**
         * Test webhooks
         */
        testWebhooks() {
            const $btn = $('#test-webhooks-btn');
            const $results = $('#webhook-test-results');

            $btn.prop('disabled', true).text(i18n.testing || 'Testing...');
            $results.html('<p>' + (i18n.testingWebhooks || 'Testing webhooks...') + '</p>');

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ptf_test_webhooks',
                    nonce: nonce,
                    webhooks: JSON.stringify(this.webhooks.filter(w => w.active))
                },
                success: (response) => {
                    $btn.prop('disabled', false).text(i18n.testWebhooks || 'Test Webhooks');

                    if (response.success && response.data) {
                        let html = '<h4>' + (i18n.testResults || 'Test Results:') + '</h4>';
                        response.data.forEach(result => {
                            const statusClass = result.success ? 'success' : 'error';
                            html += `
                                <div class="test-result-item ${statusClass}">
                                    <strong>${this.escapeHtml(result.name)}</strong>: 
                                    ${result.success ? '✓' : '✗'} ${this.escapeHtml(result.message)}
                                    ${result.response_code ? ' (HTTP ' + result.response_code + ')' : ''}
                                </div>
                            `;
                        });
                        $results.html(html);
                    } else {
                        $results.html('<p class="error">' + (response.data?.message || i18n.testError || 'Test failed') + '</p>');
                    }
                },
                error: () => {
                    $btn.prop('disabled', false).text(i18n.testWebhooks || 'Test Webhooks');
                    $results.html('<p class="error">' + (i18n.ajaxError || 'Connection error') + '</p>');
                }
            });
        }

        /**
         * Check data storage warning
         */
        checkDataStorageWarning() {
            const saveToDb = $('input[name="ptf_settings[save_to_database]"]').is(':checked');
            const sendEmail = $('input[name="ptf_settings[send_email_notification]"]').is(':checked');

            if (!saveToDb && !sendEmail) {
                $('#data-storage-warning').show();
            } else {
                $('#data-storage-warning').hide();
            }
        }

        /**
         * Escape HTML entities (uses shared utility)
         */
        escapeHtml(text) {
            return window.PTFUtils ? PTFUtils.escapeHtml(text) : this._escapeHtmlFallback(text);
        }

        /**
         * Fallback HTML escaper if utils not loaded
         */
        _escapeHtmlFallback(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }

    // Initialize on document ready
    $(document).ready(function () {
        if ($('.ptf-settings-container').length) {
            new PTFSettingsManager();
        }
    });

})(jQuery);
