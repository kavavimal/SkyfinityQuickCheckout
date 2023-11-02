<?php

defined('ABSPATH') || exit;

?>
<script>
    function refresh_phone_number_script() {
        var input = document.querySelector(".phone-with-code");
        const errorMsg = jQuery(input).closest('.fields-wrap').find("#mobile_error");
        // here, the index maps to the error code returned from getValidationError - see readme
        const errorMap = ["Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];
        var iti = window.intlTelInput(input, {
            initialCountry: "auto",
            geoIpLookup: callback => {
                fetch("https://ipapi.co/json")
                    .then(res => res.json())
                    .then(data => callback(data.country_code))
                    .catch(() => callback("us"));
            },
            utilsScript: "<?php echo SKYFINITYQC_ASSETS_URL; ?>intl-tel-input/js/utils.js"
        });
        input.addEventListener('countrychange', () => {
            jQuery("#country_code").val(iti.getSelectedCountryData().dialCode);
        });
        const reset = () => {
            jQuery(input).removeClass("error");
            jQuery(errorMsg).text('');
            jQuery(errorMsg).addClass("hide");
        };
        // on blur: validate
        input.addEventListener('blur', () => {
            reset();
            if (input.value.trim()) {
                if (!iti.isValidNumber()) {
                    jQuery(input).addClass("error");
                    const errorCode = iti.getValidationError();
                    jQuery(errorMsg).text(errorMap[errorCode]);
                    jQuery(errorMsg).removeClass("hide");
                }
            }
        });
        // on keyup / change flag: reset
        input.addEventListener('change', reset);
        input.addEventListener('keyup', reset);
    }
    function refresh_phone_number_addAddress_script() {
        var input = document.querySelector(".skyfinityqc-add-address-form  .phone-with-code");
        const errorMsg = jQuery(input).closest('.fields-wrap').find("#phone_number_error");
        // here, the index maps to the error code returned from getValidationError - see readme
        const errorMap = ["Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];
        var iti = window.intlTelInput(input, {
            initialCountry: "auto",
            geoIpLookup: callback => {
                fetch("https://ipapi.co/json")
                    .then(res => res.json())
                    .then(data => callback(data.country_code))
                    .catch(() => callback("us"));
            },
            utilsScript: "<?php echo SKYFINITYQC_ASSETS_URL; ?>intl-tel-input/js/utils.js"
        });
        input.addEventListener('countrychange', () => {
            jQuery("#country_code").val(iti.getSelectedCountryData().dialCode);
        });
        const reset = () => {
            jQuery(input).removeClass("error");
            jQuery(errorMsg).text('');
            jQuery(errorMsg).addClass("hide");
        };
        // on blur: validate
        input.addEventListener('blur', () => {
            reset();
            if (input.value.trim()) {
                if (!iti.isValidNumber()) {
                    jQuery(input).addClass("error");
                    const errorCode = iti.getValidationError();
                    jQuery(errorMsg).text(errorMap[errorCode]);
                    jQuery(errorMsg).removeClass("hide");
                }
            }
        });
        // on keyup / change flag: reset
        input.addEventListener('change', reset);
        input.addEventListener('keyup', reset);
    }
    jQuery(document).ready(function () {
        refresh_phone_number_script();
        refresh_phone_number_addAddress_script();
    });
</script>