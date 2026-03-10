/**
 * Multi-Step Popup Form Scripts
 * Pentest Quote Form - Cybersecurity Penetration Test Form
 */

(function ($) {
    'use strict';

    // Form Controller
    class PTFMultiStepForm {
        constructor(form) {
            this.form = $(form);
            this.currentStep = 1;
            this.totalSteps = 3;
            this.isSubmitting = false;

            this.init();
        }

        init() {
            this.bindEvents();
            this.updateProgress();
            this.form.attr('data-current-step', this.currentStep);
            this.updateSubmitButtonState();
        }

        bindEvents() {
            // Next button
            this.form.on('click', '.ptf-btn-next', (e) => {
                e.preventDefault();
                this.nextStep();
            });

            // Previous button
            this.form.on('click', '.ptf-btn-prev', (e) => {
                e.preventDefault();
                this.prevStep();
            });

            // Form submission
            this.form.on('submit', (e) => {
                e.preventDefault();
                this.submitForm();
            });

            // Test type checkbox change - show/hide dynamic questions
            this.form.on('change', 'input[name="test_types[]"]', () => {
                this.updateDynamicQuestions();
            });

            // Real-time validation
            this.form.on('blur', 'input, select, textarea', (e) => {
                this.validateField($(e.target));
                this.updateSubmitButtonState();
            });

            // Email special check (during input)
            this.form.on('input', 'input[data-corporate-only="true"]', (e) => {
                this.checkCorporateEmail($(e.target));
                this.updateSubmitButtonState();
            });

            // Checkbox change
            this.form.on('change', 'input[type="checkbox"]', (e) => {
                this.updateSubmitButtonState();

                // Clear error for privacy consent checkbox
                if ($(e.target).attr('name') === 'kvkk_consent') {
                    const formGroup = $(e.target).closest('.ptf-consent-group');
                    if ($(e.target).is(':checked')) {
                        formGroup.removeClass('has-error');
                        formGroup.find('.ptf-field-error').text('');
                    }
                }
            });

            // Clear error (on focus)
            this.form.on('focus', 'input, select, textarea', (e) => {
                const formGroup = $(e.target).closest('.ptf-form-group');
                if (!$(e.target).attr('data-corporate-only')) {
                    formGroup.removeClass('has-error');
                }
            });

            // Enter key to go to next step
            this.form.on('keypress', 'input', (e) => {
                if (e.which === 13 && this.currentStep < this.totalSteps) {
                    e.preventDefault();
                    this.nextStep();
                }
            });

            // Close error message
            this.form.on('click', '.ptf-message-close', (e) => {
                $(e.target).closest('.ptf-form-message').slideUp(200);
            });
        }

        /**
         * Show/hide dynamic questions based on selected test types
         */
        updateDynamicQuestions() {
            const selectedTests = this.form.find('input[name="test_types[]"]:checked').map(function () {
                return $(this).val();
            }).get();

            // Hide all test questions (except general info)
            this.form.find('.ptf-test-questions').not('.ptf-general-questions').hide();

            // Show questions for selected tests
            selectedTests.forEach(testType => {
                this.form.find(`.ptf-test-questions[data-test-type="${testType}"]`).show();
            });
        }

        nextStep() {
            if (this.validateStep(this.currentStep)) {
                // Update dynamic questions when moving to step 2
                if (this.currentStep === 1) {
                    this.updateDynamicQuestions();
                }
                this.currentStep++;
                this.showStep(this.currentStep);
                this.updateProgress();
                this.updateSubmitButtonState();
            }
        }

        prevStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
                this.showStep(this.currentStep);
                this.updateProgress();
            }
        }

        showStep(step) {
            // Hide all steps
            this.form.find('.ptf-form-step').removeClass('active');

            // Show target step
            this.form.find(`.ptf-form-step[data-step="${step}"]`).addClass('active');

            // Update data attribute (for CSS animations)
            this.form.attr('data-current-step', step);

            // Scroll to form top (if not popup)
            if (!this.form.closest('.ptf-popup-container').length) {
                $('html, body').animate({
                    scrollTop: this.form.offset().top - 100
                }, 300);
            }
        }

        updateProgress() {
            const progressSteps = this.form.find('.ptf-progress-step');

            progressSteps.each((index, el) => {
                const stepNum = $(el).data('step');
                $(el).removeClass('active completed');

                if (stepNum < this.currentStep) {
                    $(el).addClass('completed');
                } else if (stepNum === this.currentStep) {
                    $(el).addClass('active');
                }
            });
        }

        validateStep(step) {
            const stepEl = this.form.find(`.ptf-form-step[data-step="${step}"]`);
            let isValid = true;

            if (step === 1) {
                // Test types validation
                const checkedTests = stepEl.find('input[name="test_types[]"]:checked');
                if (checkedTests.length === 0) {
                    isValid = false;
                    const errorEl = stepEl.find('.ptf-field-error[data-field="test_types"]');
                    errorEl.text(ptfForm.messages.test_type_required);
                    stepEl.find('.ptf-checkbox-group').addClass('has-error');
                } else {
                    stepEl.find('.ptf-checkbox-group').removeClass('has-error');
                    stepEl.find('.ptf-field-error[data-field="test_types"]').text('');
                }
            } else if (step === 2) {
                // Test details step - validate required fields in visible categories
                const visibleSections = stepEl.find('.ptf-test-questions:visible');
                visibleSections.each((index, section) => {
                    $(section).find('[required]').each((i, field) => {
                        if (!this.validateField($(field))) {
                            isValid = false;
                        }
                    });
                });
            } else if (step === 3) {
                // Validate all required fields
                const requiredFields = stepEl.find('[required]');
                requiredFields.each((index, field) => {
                    if (!this.validateField($(field))) {
                        isValid = false;
                    }
                });

                // Corporate email validation
                const emailField = stepEl.find('input[name="email"]');
                if (emailField.val() && !this.isCorporateEmail(emailField.val())) {
                    isValid = false;
                }

                // Privacy consent validation
                const kvkkCheckbox = stepEl.find('input[name="kvkk_consent"]');
                if (!kvkkCheckbox.is(':checked')) {
                    isValid = false;
                    const formGroup = kvkkCheckbox.closest('.ptf-consent-group');
                    formGroup.addClass('has-error');
                    formGroup.find('.ptf-field-error').text(ptfForm.messages.checkbox_required);
                }
            }

            // Focus first error field
            if (!isValid) {
                stepEl.find('.ptf-form-group.has-error:first input, .ptf-form-group.has-error:first select, .ptf-form-group.has-error:first textarea').focus();
            }

            return isValid;
        }

        validateField(field) {
            const formGroup = field.closest('.ptf-form-group');
            const errorEl = formGroup.find('.ptf-field-error');
            const value = field.val() ? field.val().trim() : '';
            const type = field.attr('type');
            let isValid = true;
            let errorMessage = '';

            // Required field validation
            if (field.prop('required') && !value) {
                isValid = false;
                errorMessage = ptfForm.messages.required;
            }

            // Email format validation
            else if (type === 'email' && value) {
                if (!this.isValidEmail(value)) {
                    isValid = false;
                    errorMessage = ptfForm.messages.email;
                } else if (field.attr('data-corporate-only') === 'true' && !this.isCorporateEmail(value)) {
                    isValid = false;
                    errorMessage = ptfForm.messages.corporate_email;
                }
            }

            // Phone format validation
            else if (type === 'tel' && value && !this.isValidPhone(value)) {
                isValid = false;
                errorMessage = ptfForm.messages.phone;
            }

            // Update error state
            if (!isValid) {
                formGroup.addClass('has-error');
                errorEl.text(errorMessage);
            } else {
                formGroup.removeClass('has-error');
                errorEl.text('');
            }

            return isValid;
        }

        checkCorporateEmail(field) {
            const value = field.val().trim();
            const formGroup = field.closest('.ptf-form-group');
            const errorEl = formGroup.find('.ptf-field-error');

            if (value && value.includes('@')) {
                if (!this.isCorporateEmail(value)) {
                    formGroup.addClass('has-error');
                    errorEl.text(ptfForm.messages.corporate_email);
                    return false;
                } else if (this.isValidEmail(value)) {
                    formGroup.removeClass('has-error');
                    errorEl.text('');
                    return true;
                }
            }
            return true;
        }

        isCorporateEmail(email) {
            if (!email || !email.includes('@')) return false;

            const domain = email.split('@')[1].toLowerCase();
            return !ptfForm.blockedDomains.includes(domain);
        }

        isValidEmail(email) {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return regex.test(email);
        }

        isValidPhone(phone) {
            const regex = /^[\d\s+\-()]{10,20}$/;
            return regex.test(phone);
        }

        updateSubmitButtonState() {
            const submitBtn = this.form.find('.ptf-btn-submit');
            const step3 = this.form.find('.ptf-form-step[data-step="3"]');

            // Only check on step 3
            if (this.currentStep !== 3) {
                return;
            }

            // Check required fields
            const firstName = step3.find('input[name="first_name"]').val();
            const email = step3.find('input[name="email"]').val();
            const company = step3.find('input[name="company"]').val();
            const phone = step3.find('input[name="phone"]').val();
            const kvkkChecked = step3.find('input[name="kvkk_consent"]').is(':checked');

            // Check if email is corporate
            const isCorporate = email ? this.isCorporateEmail(email) : false;
            const isEmailValid = email ? this.isValidEmail(email) : false;

            // Enable button if all conditions are met
            if (firstName && email && company && phone && kvkkChecked && isCorporate && isEmailValid) {
                submitBtn.prop('disabled', false);
            } else {
                submitBtn.prop('disabled', true);
            }
        }

        submitForm() {
            // Validate all steps
            for (let step = 1; step <= this.totalSteps; step++) {
                if (!this.validateStep(step)) {
                    this.showStep(step);
                    this.currentStep = step;
                    this.updateProgress();
                    return;
                }
            }

            if (this.isSubmitting) return;
            this.isSubmitting = true;

            // Show loading
            this.showLoading(true);

            // If reCAPTCHA is enabled, get token
            if (ptfForm.recaptcha && ptfForm.recaptcha.enabled && typeof grecaptcha !== 'undefined') {
                try {
                    grecaptcha.ready(() => {
                        try {
                            grecaptcha.execute(ptfForm.recaptcha.siteKey, {action: 'submit_quote_form'})
                                .then((token) => {
                                    this.sendFormData(token);
                                })
                                .catch((error) => {
                                    console.error('reCAPTCHA error:', error);
                                    // Show error to user instead of silent fail
                                    this.isSubmitting = false;
                                    this.showLoading(false);
                                    this.showError(ptfForm.messages.recaptcha_error || 'reCAPTCHA verification failed. Please try again.');
                                });
                        } catch (error) {
                            console.error('reCAPTCHA execute error:', error);
                            this.isSubmitting = false;
                            this.showLoading(false);
                            this.showError(ptfForm.messages.recaptcha_error || 'reCAPTCHA verification failed. Please check site configuration.');
                        }
                    });
                } catch (error) {
                    console.error('reCAPTCHA ready error:', error);
                    this.isSubmitting = false;
                    this.showLoading(false);
                    this.showError(ptfForm.messages.recaptcha_error || 'reCAPTCHA failed to initialize.');
                }
            } else {
                // Submit directly if no reCAPTCHA
                this.sendFormData('');
            }
        }

        sendFormData(recaptchaToken) {
            // Collect form data
            let formData = this.form.serialize();

            // Add reCAPTCHA token
            if (recaptchaToken) {
                formData += '&recaptcha_token=' + encodeURIComponent(recaptchaToken);
            }

            // Send via AJAX
            $.ajax({
                url: ptfForm.ajaxurl,
                type: 'POST',
                data: formData,
                success: (response) => {
                    this.isSubmitting = false;
                    this.showLoading(false);

                    if (response.success) {
                        this.showSuccess(response.data.message);
                    } else {
                        // Special reCAPTCHA error message
                        if (response.data.recaptcha_failed) {
                            this.showError(ptfForm.messages.recaptcha_error || response.data.message);
                        } else {
                            this.showError(response.data.message, response.data.errors);
                        }
                    }
                },
                error: (xhr, status, error) => {
                    this.isSubmitting = false;
                    this.showLoading(false);
                    this.showError(ptfForm.messages.error);
                    console.error('Form submission error:', error);
                }
            });
        }

        showLoading(show) {
            const loadingEl = this.form.find('.ptf-form-loading');
            const submitBtn = this.form.find('.ptf-btn-submit');

            if (show) {
                loadingEl.show();
                submitBtn.prop('disabled', true).text(ptfForm.messages.sending);
            } else {
                loadingEl.hide();
                submitBtn.prop('disabled', false);
            }
        }

        showSuccess(message) {
            // Hide form steps and progress
            this.form.find('.ptf-form-step').hide();
            this.form.find('.ptf-form-progress').hide();

            // Show success message
            const successEl = this.form.find('.ptf-form-success');
            if (message) {
                successEl.find('p').text(message);
            }
            successEl.show();

            // Close popup after 5 seconds (if it's a popup)
            const popup = this.form.closest('.ptf-popup-overlay');
            if (popup.length) {
                setTimeout(() => {
                    PTFPopup.close();
                    // Reset form
                    setTimeout(() => this.resetForm(), 500);
                }, 3000);
            }
        }

        showError(message, fieldErrors = {}) {
            // Show general error message in the form
            if (message) {
                const errorEl = this.form.find('.ptf-form-error-message');
                errorEl.find('.ptf-message-text').text(message);
                errorEl.slideDown(200);

                // Auto hide after 5 seconds
                setTimeout(() => {
                    errorEl.slideUp(200);
                }, 5000);
            }

            // Field-specific errors
            if (fieldErrors && Object.keys(fieldErrors).length > 0) {
                for (const field in fieldErrors) {
                    const input = this.form.find(`[name="${field}"]`);
                    const formGroup = input.closest('.ptf-form-group');
                    formGroup.addClass('has-error');
                    formGroup.find('.ptf-field-error').text(fieldErrors[field]);
                }

                // Go to first error step
                const firstError = this.form.find('.ptf-form-group.has-error:first');
                const errorStep = firstError.closest('.ptf-form-step').data('step');
                if (errorStep && errorStep !== this.currentStep) {
                    this.showStep(errorStep);
                    this.currentStep = errorStep;
                    this.updateProgress();
                }
            }
        }

        resetForm() {
            // Reset form
            this.form[0].reset();
            this.currentStep = 1;

            // Reset view
            this.form.find('.ptf-form-step').hide();
            this.form.find(`.ptf-form-step[data-step="1"]`).addClass('active').show();
            this.form.find('.ptf-form-progress').show();
            this.form.find('.ptf-form-success').hide();
            this.form.find('.ptf-form-error-message').hide();
            this.form.find('.ptf-form-group').removeClass('has-error');
            this.form.find('.ptf-btn-submit').prop('disabled', true);

            // Hide dynamic questions
            this.form.find('.ptf-test-questions').not('.ptf-general-questions').hide();

            this.updateProgress();
            this.form.attr('data-current-step', 1);
        }
    }

    // Popup Controller
    const PTFPopup = {
        overlay: null,

        init() {
            this.overlay = $('#ptf-popup-overlay');
            this.bindEvents();
        },

        bindEvents() {
            // Popup triggers
            $(document).on('click', '.ptf-popup-trigger', (e) => {
                e.preventDefault();
                this.open();
            });

            // Close button
            $(document).on('click', '.ptf-popup-close', (e) => {
                e.preventDefault();
                this.close();
            });

            // Close on overlay click
            this.overlay.on('click', (e) => {
                if ($(e.target).is('.ptf-popup-overlay')) {
                    this.close();
                }
            });

            // Close on ESC key
            $(document).on('keydown', (e) => {
                if (e.key === 'Escape' && this.overlay.hasClass('active')) {
                    this.close();
                }
            });
        },

        open() {
            this.overlay.css('display', 'flex');

            // Small delay for animation
            setTimeout(() => {
                this.overlay.addClass('active');
            }, 10);

            // Prevent body scroll
            $('body').css('overflow', 'hidden');

            // Focus first input
            setTimeout(() => {
                this.overlay.find('input:visible:first').focus();
            }, 300);
        },

        close() {
            this.overlay.removeClass('active');

            // Hide after animation
            setTimeout(() => {
                this.overlay.css('display', 'none');
            }, 300);

            // Restore body scroll
            $('body').css('overflow', '');
        }
    };

    // Initialize on page load
    $(document).ready(function () {
        // Initialize all forms
        $('.ptf-multistep-form').each(function () {
            new PTFMultiStepForm(this);
        });

        // Initialize popup
        PTFPopup.init();
    });

    // Global access
    window.PTFPopup = PTFPopup;

})(jQuery);
