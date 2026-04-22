(function($) {
    'use strict';

    $(document).ready(function() {
        initCaptchaWidgets();
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
