(function($) {
    'use strict';

    $(document).ready(function() {
        initCaptchaWidgets();
        initFormIntegration();
    });

    function initCaptchaWidgets() {
        var widgets = document.querySelectorAll('captcha-widget');

        widgets.forEach(function(widget) {
            widget.addEventListener('success', function(e) {
                var detail = e.detail;
                var container = widget.closest('.captcha-shield-container');

                if (container) {
                    var lotInput = container.querySelector('input[name="captcha_shield_lot_number"]');
                    var tokenInput = container.querySelector('input[name="captcha_shield_sign_token"]');

                    if (lotInput) {
                        lotInput.value = detail.lot_number || '';
                    }
                    if (tokenInput) {
                        tokenInput.value = detail.sign_token || '';
                    }
                }
            });

            widget.addEventListener('fail', function(e) {
                var container = widget.closest('.captcha-shield-container');

                if (container) {
                    var lotInput = container.querySelector('input[name="captcha_shield_lot_number"]');
                    var tokenInput = container.querySelector('input[name="captcha_shield_sign_token"]');

                    if (lotInput) {
                        lotInput.value = '';
                    }
                    if (tokenInput) {
                        tokenInput.value = '';
                    }
                }
            });
        });
    }

    function initFormIntegration() {
        var widgets = document.querySelectorAll('captcha-widget');

        widgets.forEach(function(widget) {
            var displayMode = widget.getAttribute('display-mode') || 'popup';
            var container = widget.closest('.captcha-shield-container');
            if (!container) return;

            var form = container.closest('form');
            if (!form) return;

            if (displayMode === 'invisible') {
                var submitBtn = form.querySelector('input[type="submit"], button[type="submit"]');
                if (submitBtn && !widget.getAttribute('submit-element')) {
                    widget.setAttribute('submit-element', '#' + (submitBtn.id || generateUniqueId(submitBtn)));
                }
            }

            if (displayMode === 'bind') {
                var submitBtn = form.querySelector('input[type="submit"], button[type="submit"]');
                if (submitBtn && !widget.getAttribute('bind-element')) {
                    widget.setAttribute('bind-element', '#' + (submitBtn.id || generateUniqueId(submitBtn)));
                }
            }
        });
    }

    function generateUniqueId(element) {
        if (!element.id) {
            element.id = 'captcha-shield-btn-' + Math.random().toString(36).substr(2, 9);
        }
        return element.id;
    }

    window.captchaShieldReset = function(container) {
        if (!container) {
            container = document;
        }
        var widget = container.querySelector('captcha-widget');
        if (widget && typeof widget.reset === 'function') {
            widget.reset();
        }

        var lotInput = container.querySelector('input[name="captcha_shield_lot_number"]');
        var tokenInput = container.querySelector('input[name="captcha_shield_sign_token"]');
        if (lotInput) lotInput.value = '';
        if (tokenInput) tokenInput.value = '';
    };

})(jQuery);
