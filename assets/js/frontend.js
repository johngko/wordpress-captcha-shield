(function() {
    'use strict';

    function fillHiddenInputs(widget, lotNumber, signToken) {
        var container = widget.closest('.captcha-shield-container');
        if (!container) return;

        var lotInput = container.querySelector('input[name="captcha_shield_lot_number"]');
        var tokenInput = container.querySelector('input[name="captcha_shield_sign_token"]');

        if (lotInput) {
            lotInput.value = lotNumber || '';
        }
        if (tokenInput) {
            tokenInput.value = signToken || '';
        }
    }

    function clearHiddenInputs(widget) {
        fillHiddenInputs(widget, '', '');
    }

    function initWidget(widget) {
        widget.addEventListener('success', function(e) {
            var detail = e.detail;
            fillHiddenInputs(widget, detail.lot_number, detail.sign_token);
        });

        widget.addEventListener('fail', function(e) {
            clearHiddenInputs(widget);
        });
    }

    function init() {
        var widgets = document.querySelectorAll('captcha-widget');
        for (var i = 0; i < widgets.length; i++) {
            initWidget(widgets[i]);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.captchaShieldReset = function(container) {
        if (!container) {
            container = document;
        }
        var widget = container.querySelector('captcha-widget');
        if (widget && typeof widget.reset === 'function') {
            widget.reset();
        }
        clearHiddenInputs(widget);
    };
})();
