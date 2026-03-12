/**
 * Admin Questions Management Scripts
 * Pentest Quote Form - Question Management Page
 */

(function ($) {
    'use strict';

    // Global variables (passed from PHP via wp_localize_script)
    const ajaxUrl = typeof ptfQuestionsAdmin !== 'undefined' ? ptfQuestionsAdmin.ajaxUrl : ajaxurl;
    const nonce = typeof ptfQuestionsAdmin !== 'undefined' ? ptfQuestionsAdmin.nonce : '';
    const i18n = typeof ptfQuestionsAdmin !== 'undefined' ? ptfQuestionsAdmin.i18n : {};

    /**
     * Questions Manager Class
     */
    class PTFQuestionsManager {
        constructor() {
            this.bindEvents();
            this.initSortable();
        }

        /**
         * Bind all event handlers
         */
        bindEvents() {
            // Toggle category content
            $(document).on('click', '.toggle-category', this.toggleCategory.bind(this));

            // Delete category
            $(document).on('click', '.delete-category', this.deleteCategory.bind(this));

            // Add new category
            $('#add-category').on('click', this.addCategory.bind(this));

            // Load sample data
            $('#load-sample-data').on('click', this.loadSampleData.bind(this));

            // Edit question
            $(document).on('click', '.edit-question', this.toggleQuestion.bind(this));

            // Delete question
            $(document).on('click', '.delete-question', this.deleteQuestion.bind(this));

            // Add question
            $(document).on('click', '.add-question', this.addQuestion.bind(this));

            // Add option
            $(document).on('click', '.add-option', this.addOption.bind(this));

            // Remove option
            $(document).on('click', '.remove-option', this.removeOption.bind(this));

            // Question type change
            $(document).on('change', '.question-type-select', this.onQuestionTypeChange.bind(this));

            // Category name input - update header
            $(document).on('input', '.category-name-input', this.updateCategoryHeader.bind(this));

            // Category name blur - auto generate ID
            $(document).on('blur', '.category-name-input', this.autoCategoryId.bind(this));

            // Category ID input - update header
            $(document).on('input', '.category-id-input', this.updateCategoryIdHeader.bind(this));

            // Question text input - update header
            $(document).on('input', '.question-text-input', this.updateQuestionHeader.bind(this));

            // Question text blur - auto generate ID
            $(document).on('blur', '.question-text-input', this.autoQuestionId.bind(this));

            // Category icon input - update header
            $(document).on('input', '.category-icon-input', this.updateCategoryIconHeader.bind(this));

            // Save all
            $('#save-all-questions').on('click', this.saveAll.bind(this));
        }

        /**
         * Initialize sortable for drag & drop
         */
        initSortable() {
            $('#categories-list').sortable({
                handle: '.ptf-category-header .sort-handle',
                placeholder: 'ui-sortable-placeholder',
                opacity: 0.8,
                update: () => {
                    // Can handle sort update here
                }
            });

            $('.questions-list').sortable({
                handle: '.question-header .sort-handle',
                placeholder: 'ui-sortable-placeholder',
                opacity: 0.8,
                connectWith: '.questions-list',
                update: () => {
                    this.updateQuestionCounts();
                }
            });
        }

        /**
         * Toggle category content visibility
         */
        toggleCategory(e) {
            const $btn = $(e.currentTarget);
            const $content = $btn.closest('.ptf-category-item').find('.ptf-category-content');
            const $icon = $btn.find('.dashicons');

            $content.slideToggle(200);
            $icon.toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
        }

        /**
         * Delete category
         */
        deleteCategory(e) {
            if (!confirm(i18n.confirmDeleteCategory || 'Are you sure you want to delete this category?')) {
                return;
            }
            $(e.currentTarget).closest('.ptf-category-item').fadeOut(300, function () {
                $(this).remove();
            });
        }

        /**
         * Add new category
         */
        addCategory() {
            const newIndex = Date.now();
            const html = this.getCategoryTemplate(newIndex);

            // Remove "no categories" message if exists
            $('.no-categories-message').remove();

            // Add new category
            $('#categories-list').append(html);

            // Scroll to new category
            const $newCat = $('#categories-list .ptf-category-item:last');
            $newCat.find('.ptf-category-content').show();

            $('html, body').animate({
                scrollTop: $newCat.offset().top - 100
            }, 300);

            // Focus on name input
            $newCat.find('.category-name-input').focus();

            // Reinitialize sortable
            this.initSortable();
        }

        /**
         * Load sample data
         */
        loadSampleData() {
            if (!confirm(i18n.confirmLoadSample || 'Existing categories will be deleted and sample data will be loaded. Continue?')) {
                return;
            }

            // Sample data
            const sampleData = {
                categories: [
                    {
                        id: 'web-application',
                        name: 'Web Application Penetration Test',
                        icon: '🌐',
                        active: true,
                        questions: [
                            {
                                id: 'url_count',
                                question: 'How many URLs will be tested?',
                                type: 'number',
                                required: false,
                                placeholder: 'e.g., 5'
                            },
                            {
                                id: 'has_auth',
                                question: 'Are there pages requiring authentication?',
                                type: 'select',
                                required: false,
                                options: [
                                    { id: 'yes', label: 'Yes' },
                                    { id: 'no', label: 'No' }
                                ]
                            },
                            {
                                id: 'user_roles',
                                question: 'How many different user roles exist?',
                                type: 'number',
                                required: false,
                                placeholder: 'e.g., 3'
                            }
                        ]
                    },
                    {
                        id: 'mobile-application',
                        name: 'Mobile Application Penetration Test',
                        icon: '📱',
                        active: true,
                        questions: [
                            {
                                id: 'platforms',
                                question: 'Which platforms?',
                                type: 'checkbox',
                                required: true,
                                options: [
                                    {id: 'ios', label: 'iOS'},
                                    {id: 'android', label: 'Android'}
                                ]
                            },
                            {id: 'app_count', question: 'How many applications?', type: 'number', required: false}
                        ]
                    },
                    {
                        id: 'api-security',
                        name: 'API Security Test',
                        icon: '🔌',
                        active: true,
                        questions: [
                            {
                                id: 'api_type', question: 'API Type', type: 'select', required: false, options: [
                                    {id: 'rest', label: 'REST API'},
                                    {id: 'graphql', label: 'GraphQL'},
                                    {id: 'soap', label: 'SOAP'}
                                ]
                            },
                            {
                                id: 'endpoint_count',
                                question: 'Approximate number of endpoints?',
                                type: 'number',
                                required: false
                            }
                        ]
                    }
                ]
            };

            // Clear existing and build new
            $('#categories-list').empty();

            sampleData.categories.forEach((cat, index) => {
                const html = this.getCategoryTemplateWithData(cat, index);
                $('#categories-list').append(html);
            });

            // Reinitialize sortable
            this.initSortable();

            // Show success message
            $('#save-status').text('✓ ' + (i18n.sampleLoaded || 'Sample data loaded!')).addClass('success');
            setTimeout(() => {
                $('#save-status').text('').removeClass('success');
            }, 3000);
        }

        /**
         * Toggle question details
         */
        toggleQuestion(e) {
            const $details = $(e.currentTarget).closest('.question-item').find('.question-details');
            $details.slideToggle(200);
        }

        /**
         * Delete question
         */
        deleteQuestion(e) {
            if (!confirm(i18n.confirmDeleteQuestion || 'Are you sure you want to delete this question?')) {
                return;
            }
            const $item = $(e.currentTarget).closest('.question-item');
            $item.fadeOut(300, () => {
                $item.remove();
                this.updateQuestionCounts();
            });
        }

        /**
         * Add new question
         */
        addQuestion(e) {
            const $questionsContainer = $(e.currentTarget).closest('.category-questions').find('.questions-list');
            const newIndex = Date.now();
            const html = this.getQuestionTemplate(newIndex);

            $questionsContainer.append(html);

            const $newQuestion = $questionsContainer.find('.question-item:last');
            $newQuestion.find('.question-details').show();
            $newQuestion.find('.question-text-input').focus();

            this.updateQuestionCounts();
        }

        /**
         * Add option to select/radio/checkbox
         */
        addOption(e) {
            const $optionsList = $(e.currentTarget).closest('.options-container').find('.options-list');
            const html = `
                <div class="option-item">
                    <span class="option-sort-handle dashicons dashicons-menu"></span>
                    <input type="text" class="option-id-input" placeholder="${i18n.optionId || 'Option ID'}" style="width: 100px;">
                    <input type="text" class="option-label-input regular-text" placeholder="${i18n.optionText || 'Option text'}">
                    <span class="remove-option dashicons dashicons-dismiss"></span>
                </div>
            `;
            $optionsList.append(html);
        }

        /**
         * Remove option
         */
        removeOption(e) {
            $(e.currentTarget).closest('.option-item').remove();
        }

        /**
         * Handle question type change
         */
        onQuestionTypeChange(e) {
            const $select = $(e.currentTarget);
            const type = $select.val();
            const $optionsContainer = $select.closest('.question-details').find('.options-container');

            // Show/hide options based on type
            if (['select', 'radio', 'checkbox'].includes(type)) {
                $optionsContainer.show();
            } else {
                $optionsContainer.hide();
            }
        }

        /**
         * Update category header when name changes
         */
        updateCategoryHeader(e) {
            const name = $(e.currentTarget).val();
            $(e.currentTarget).closest('.ptf-category-item').find('.ptf-category-header .category-name').text(name);
        }

        /**
         * Auto generate category ID from name
         */
        autoCategoryId(e) {
            const $idInput = $(e.currentTarget).closest('.ptf-category-content').find('.category-id-input');
            if ($idInput.val() === '' || $idInput.val().startsWith('category_')) {
                const id = this.generateKey($(e.currentTarget).val());
                $idInput.val(id);
                $(e.currentTarget).closest('.ptf-category-item').find('.ptf-category-header .category-id').text('(' + id + ')');
            }
        }

        /**
         * Update category ID in header
         */
        updateCategoryIdHeader(e) {
            const id = $(e.currentTarget).val();
            $(e.currentTarget).closest('.ptf-category-item').find('.ptf-category-header .category-id').text('(' + id + ')');
        }

        /**
         * Update question header when text changes
         */
        updateQuestionHeader(e) {
            const text = $(e.currentTarget).val();
            $(e.currentTarget).closest('.question-item').find('.question-header .question-text').text(text);
        }

        /**
         * Auto generate question ID from text
         */
        autoQuestionId(e) {
            const $idInput = $(e.currentTarget).closest('.question-details').find('.question-id-input');
            if ($idInput.val() === '' || $idInput.val().startsWith('question_')) {
                const id = this.generateKey($(e.currentTarget).val());
                if (id) {
                    $idInput.val(id);
                }
            }
        }

        /**
         * Update category icon in header
         */
        updateCategoryIconHeader(e) {
            const icon = $(e.currentTarget).val();
            $(e.currentTarget).closest('.ptf-category-item').find('.ptf-category-header .category-icon').text(icon);
        }

        /**
         * Update question counts
         */
        updateQuestionCounts() {
            $('.ptf-category-item').each(function () {
                const count = $(this).find('.questions-list .question-item').length;
                $(this).find('.category-question-count').text(count + ' ' + (i18n.questions || 'questions'));
            });
        }

        /**
         * Save all data
         */
        saveAll(e) {
            e.preventDefault();

            const $btn = $(e.currentTarget);
            const $spinner = $btn.siblings('.spinner');
            const $status = $('#save-status');

            $btn.prop('disabled', true);
            $spinner.addClass('is-active');
            $status.text('').removeClass('success error');

            const data = this.collectAllData();

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'ptf_save_questions',
                    nonce: nonce,
                    questions: JSON.stringify(data)
                },
                success: (response) => {
                    $spinner.removeClass('is-active');
                    $btn.prop('disabled', false);

                    if (response.success) {
                        const msg = '✓ ' + (i18n.saved || 'Saved!');
                        if (response.data && response.data.categories_saved !== undefined) {
                            $status.text(msg + ' (' + response.data.categories_saved + ' ' + (i18n.categories || 'categories') + ')').addClass('success');
                        } else {
                            $status.text(msg).addClass('success');
                        }
                    } else {
                        $status.text('✗ ' + (response.data?.message || i18n.saveError || 'Save failed')).addClass('error');
                    }
                },
                error: (xhr, status, error) => {
                    $spinner.removeClass('is-active');
                    $btn.prop('disabled', false);
                    $status.text('✗ ' + (i18n.ajaxError || 'AJAX Error:') + ' ' + error).addClass('error');
                }
            });
        }

        /**
         * Collect all form data
         */
        collectAllData() {
            const data = {categories: []};

            $('#categories-list .ptf-category-item').each(function () {
                const $cat = $(this);
                const category = {
                    id: $cat.find('.category-id-input').val() || 'category_' + Date.now(),
                    name: $cat.find('.category-name-input').val() || 'Unnamed Category',
                    icon: $cat.find('.category-icon-input').val() || '📋',
                    active: $cat.find('.category-active').is(':checked'),
                    questions: []
                };

                $cat.find('.questions-list .question-item').each(function () {
                    const $q = $(this);
                    const question = {
                        id: $q.find('.question-id-input').val() || 'question_' + Date.now(),
                        question: $q.find('.question-text-input').val() || '',
                        type: $q.find('.question-type-select').val() || 'text',
                        required: $q.find('.question-required').is(':checked'),
                        placeholder: $q.find('.question-placeholder').val() || '',
                        options: []
                    };

                    // Collect options
                    $q.find('.options-list .option-item').each(function () {
                        const $opt = $(this);
                        const optId = $opt.find('.option-id-input').val();
                        const optLabel = $opt.find('.option-label-input').val();
                        if (optId || optLabel) {
                            question.options.push({
                                id: optId || 'opt_' + Date.now(),
                                label: optLabel || optId
                            });
                        }
                    });

                    if (question.question) {
                        category.questions.push(question);
                    }
                });

                data.categories.push(category);
            });

            return data;
        }

        /**
         * Generate URL-safe key from text (uses shared utility)
         */
        generateKey(text) {
            return window.PTFUtils ? PTFUtils.generateKey(text) : this._generateKeyFallback(text);
        }

        /**
         * Fallback key generator if utils not loaded
         */
        _generateKeyFallback(text) {
            if (!text) return '';
            return text.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '').substring(0, 50);
        }

        /**
         * Get category HTML template
         */
        getCategoryTemplate(index) {
            return `
                <div class="ptf-category-item" data-category-index="${index}">
                    <div class="ptf-category-header">
                        <span class="sort-handle dashicons dashicons-menu"></span>
                        <span class="category-icon">📋</span>
                        <span class="category-name">${i18n.newCategory || 'New Category'}</span>
                        <span class="category-id">(category_${index})</span>
                        <span class="category-question-count">0 ${i18n.questions || 'questions'}</span>
                        <label class="category-active-toggle">
                            <input type="checkbox" class="category-active" checked>
                            ${i18n.active || 'Active'}
                        </label>
                        <button type="button" class="button button-small toggle-category">
                            <span class="dashicons dashicons-arrow-up-alt2"></span>
                        </button>
                        <button type="button" class="button button-link-delete delete-category">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                    <div class="ptf-category-content">
                        <div class="category-details">
                            <div class="detail-row">
                                <label>${i18n.categoryName || 'Category Name'}</label>
                                <input type="text" class="category-name-input large-text" value="">
                            </div>
                            <div class="detail-row">
                                <label>${i18n.categoryId || 'ID (Unique Key)'}</label>
                                <input type="text" class="category-id-input regular-text" value="category_${index}">
                            </div>
                            <div class="detail-row">
                                <label>${i18n.icon || 'Icon (Emoji)'}</label>
                                <input type="text" class="category-icon-input" value="📋" style="width: 60px;">
                            </div>
                        </div>
                        <div class="category-questions">
                            <h4>
                                ${i18n.questions || 'Questions'}
                                <button type="button" class="button button-small add-question">
                                    <span class="dashicons dashicons-plus"></span>
                                    ${i18n.addQuestion || 'Add Question'}
                                </button>
                            </h4>
                            <div class="questions-list"></div>
                        </div>
                    </div>
                </div>
            `;
        }

        /**
         * Get category template with data
         */
        getCategoryTemplateWithData(cat, index) {
            let questionsHtml = '';
            if (cat.questions && cat.questions.length > 0) {
                cat.questions.forEach((q, qIndex) => {
                    questionsHtml += this.getQuestionTemplateWithData(q, qIndex);
                });
            }

            return `
                <div class="ptf-category-item" data-category-index="${index}">
                    <div class="ptf-category-header">
                        <span class="sort-handle dashicons dashicons-menu"></span>
                        <span class="category-icon">${this.escapeHtml(cat.icon || '📋')}</span>
                        <span class="category-name">${this.escapeHtml(cat.name)}</span>
                        <span class="category-id">(${this.escapeHtml(cat.id)})</span>
                        <span class="category-question-count">${cat.questions ? cat.questions.length : 0} ${i18n.questions || 'questions'}</span>
                        <label class="category-active-toggle">
                            <input type="checkbox" class="category-active" ${cat.active ? 'checked' : ''}>
                            ${i18n.active || 'Active'}
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
                                <label>${i18n.categoryName || 'Category Name'}</label>
                                <input type="text" class="category-name-input large-text" value="${this.escapeHtml(cat.name)}">
                            </div>
                            <div class="detail-row">
                                <label>${i18n.categoryId || 'ID (Unique Key)'}</label>
                                <input type="text" class="category-id-input regular-text" value="${this.escapeHtml(cat.id)}">
                            </div>
                            <div class="detail-row">
                                <label>${i18n.icon || 'Icon (Emoji)'}</label>
                                <input type="text" class="category-icon-input" value="${this.escapeHtml(cat.icon || '📋')}" style="width: 60px;">
                            </div>
                        </div>
                        <div class="category-questions">
                            <h4>
                                ${i18n.questions || 'Questions'}
                                <button type="button" class="button button-small add-question">
                                    <span class="dashicons dashicons-plus"></span>
                                    ${i18n.addQuestion || 'Add Question'}
                                </button>
                            </h4>
                            <div class="questions-list">${questionsHtml}</div>
                        </div>
                    </div>
                </div>
            `;
        }

        /**
         * Get question HTML template
         */
        getQuestionTemplate(index) {
            return `
                <div class="question-item" data-question-index="${index}">
                    <div class="question-header">
                        <span class="sort-handle dashicons dashicons-menu"></span>
                        <span class="question-text">${i18n.newQuestion || 'New Question'}</span>
                        <span class="question-type-badge">TEXT</span>
                        <button type="button" class="button button-small edit-question">
                            <span class="dashicons dashicons-edit"></span>
                        </button>
                        <button type="button" class="button button-link-delete delete-question">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                    <div class="question-details">
                        <div class="detail-grid">
                            <div class="detail-row">
                                <label>${i18n.questionText || 'Question Text'}</label>
                                <input type="text" class="question-text-input large-text" value="">
                            </div>
                            <div class="detail-row">
                                <label>${i18n.questionId || 'Question ID'}</label>
                                <input type="text" class="question-id-input regular-text" value="question_${index}">
                            </div>
                            <div class="detail-row">
                                <label>${i18n.answerType || 'Answer Type'}</label>
                                <select class="question-type-select">
                                    <option value="text">${i18n.typeText || 'Text (Short)'}</option>
                                    <option value="textarea">${i18n.typeTextarea || 'Text (Long)'}</option>
                                    <option value="number">${i18n.typeNumber || 'Number'}</option>
                                    <option value="select">${i18n.typeSelect || 'Dropdown'}</option>
                                    <option value="radio">${i18n.typeRadio || 'Single Select'}</option>
                                    <option value="checkbox">${i18n.typeCheckbox || 'Multiple Select'}</option>
                                </select>
                            </div>
                            <div class="detail-row">
                                <label>${i18n.placeholder || 'Placeholder'}</label>
                                <input type="text" class="question-placeholder regular-text" value="">
                            </div>
                        </div>
                        <div class="detail-row">
                            <label>
                                <input type="checkbox" class="question-required">
                                ${i18n.required || 'Required'}
                            </label>
                        </div>
                        <div class="options-container" style="display: none;">
                            <label>${i18n.options || 'Options'}</label>
                            <div class="options-list"></div>
                            <button type="button" class="button button-small add-option">
                                <span class="dashicons dashicons-plus"></span>
                                ${i18n.addOption || 'Add Option'}
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        /**
         * Get question template with data
         */
        getQuestionTemplateWithData(q, index) {
            const showOptions = ['select', 'radio', 'checkbox'].includes(q.type);
            let optionsHtml = '';

            if (q.options && q.options.length > 0) {
                q.options.forEach(opt => {
                    optionsHtml += `
                        <div class="option-item">
                            <span class="option-sort-handle dashicons dashicons-menu"></span>
                            <input type="text" class="option-id-input" value="${this.escapeHtml(opt.id)}" style="width: 100px;">
                            <input type="text" class="option-label-input regular-text" value="${this.escapeHtml(opt.label)}">
                            <span class="remove-option dashicons dashicons-dismiss"></span>
                        </div>
                    `;
                });
            }

            return `
                <div class="question-item" data-question-index="${index}">
                    <div class="question-header">
                        <span class="sort-handle dashicons dashicons-menu"></span>
                        <span class="question-text">${this.escapeHtml(q.question)}</span>
                        <span class="question-type-badge">${q.type.toUpperCase()}</span>
                        ${q.required ? `<span class="required-badge">${i18n.required || 'Required'}</span>` : ''}
                        <button type="button" class="button button-small edit-question">
                            <span class="dashicons dashicons-edit"></span>
                        </button>
                        <button type="button" class="button button-link-delete delete-question">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                    <div class="question-details" style="display: none;">
                        <div class="detail-grid">
                            <div class="detail-row">
                                <label>${i18n.questionText || 'Question Text'}</label>
                                <input type="text" class="question-text-input large-text" value="${this.escapeHtml(q.question)}">
                            </div>
                            <div class="detail-row">
                                <label>${i18n.questionId || 'Question ID'}</label>
                                <input type="text" class="question-id-input regular-text" value="${this.escapeHtml(q.id)}">
                            </div>
                            <div class="detail-row">
                                <label>${i18n.answerType || 'Answer Type'}</label>
                                <select class="question-type-select">
                                    <option value="text" ${q.type === 'text' ? 'selected' : ''}>${i18n.typeText || 'Text (Short)'}</option>
                                    <option value="textarea" ${q.type === 'textarea' ? 'selected' : ''}>${i18n.typeTextarea || 'Text (Long)'}</option>
                                    <option value="number" ${q.type === 'number' ? 'selected' : ''}>${i18n.typeNumber || 'Number'}</option>
                                    <option value="select" ${q.type === 'select' ? 'selected' : ''}>${i18n.typeSelect || 'Dropdown'}</option>
                                    <option value="radio" ${q.type === 'radio' ? 'selected' : ''}>${i18n.typeRadio || 'Single Select'}</option>
                                    <option value="checkbox" ${q.type === 'checkbox' ? 'selected' : ''}>${i18n.typeCheckbox || 'Multiple Select'}</option>
                                </select>
                            </div>
                            <div class="detail-row">
                                <label>${i18n.placeholder || 'Placeholder'}</label>
                                <input type="text" class="question-placeholder regular-text" value="${this.escapeHtml(q.placeholder || '')}">
                            </div>
                        </div>
                        <div class="detail-row">
                            <label>
                                <input type="checkbox" class="question-required" ${q.required ? 'checked' : ''}>
                                ${i18n.required || 'Required'}
                            </label>
                        </div>
                        <div class="options-container" style="display: ${showOptions ? 'block' : 'none'};">
                            <label>${i18n.options || 'Options'}</label>
                            <div class="options-list">${optionsHtml}</div>
                            <button type="button" class="button button-small add-option">
                                <span class="dashicons dashicons-plus"></span>
                                ${i18n.addOption || 'Add Option'}
                            </button>
                        </div>
                    </div>
                </div>
            `;
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
        if ($('.ptf-questions-admin').length) {
            new PTFQuestionsManager();
        }
    });

})(jQuery);


