<script>
    let nIntervId;
    let start;
    function skyfinityqc_startCheckAuth(requestId) {
        // check if an interval has already been set up
        if (!nIntervId) {
            start = Date.now();
            nIntervId = setInterval(function () {
                skyfinityqc_checkTruecallerAuthAjax(requestId);
            }, 5000);
        }
    }

    function skyfinityqc_checkTruecallerAuthAjax(requestId) {
        if (Date.now() - start > (1000 * 60 * 3)) {
            skyfinityqc_stopCheckAuth();
            return;
        }
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: request_globals.request_url,
            data: {
                action: "skyfinityqc_truecaller_auth_check",
                nonce: '<?php echo wp_create_nonce("truecaller_auth_nonce") ?>',
                requestId: requestId
            },
            success: function (response) {
                if (response.success == true) {
                    skyfinityqc_stopCheckAuth();
                    let profile = response.profile;
                    jQuery('#userAccountSetupForm input[name="mobile"]').val(profile.phoneNumbers[0] ? profile.phoneNumbers[0] : '');
                    jQuery('#userAccountSetupForm input[name="email"]').val(profile.onlineIdentities.email ? profile.onlineIdentities.email : '');
                    if (response.user_id) {
                        navigateToFormStep(2);
                    }
                    jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                } else {
                    jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');

                }
            }
        });
    }

    function skyfinityqc_stopCheckAuth() {
        clearInterval(nIntervId);
        // release our intervalID from the variable
        nIntervId = null;
        start = null;
    }

    function skyfinityqc_onTrucallerButtonClick(e) {
        e.preventDefault();
        jQuery("#skyfinityqc_trucaller_error").text("");

        jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').addClass('show');
        var nonce = jQuery(this).attr("data-nonce");
        let urlTruecallerApp = `truecallersdk://truesdk/web_verify?requestNonce=<?php echo esc_html($uniqid); ?>&partnerKey=<?php echo esc_html(isset($optionsValues['true_caller_key']) ? $optionsValues['true_caller_key'] : ''); ?>&partnerName=<?php echo esc_html(isset($optionsValues['true_caller_appname']) ? $optionsValues['true_caller_appname'] : ''); ?>`;
        window.location = urlTruecallerApp;
        setTimeout(function () {
            if (document.hasFocus()) {
                // Truecaller app not present on the device and you redirect the user 
                // to your alternate verification page
                jQuery("#skyfinityqc_trucaller_error").text("Truecaller app not found or Data unavailable, Please check and try again.");
                jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
            } else {

                // Truecaller app present on the device and the profile overlay opens
                // The user clicks on verify & you'll receive the user's access token to fetch the profile on your 
                // callback URL - post which, you can refresh the session at your frontend and complete the user verification
                // window.location = '<?php echo esc_html($redirect_current_url); ?>';
                skyfinityqc_startCheckAuth(<?php echo esc_html($uniqid); ?>);
            }
        }, 600);
    }

    function skyfinityqc_onClickSendSMS(e) {
        e.preventDefault();
        if (jQuery(".skyfinityqc_coupons_list_wrap").is(':visible')) {
            jQuery('.skyfinityqc_coupons_list_wrap').slideUp('slow')
            jQuery('.account_wrap').slideDown();
        }
        let mobile = jQuery('#userAccountSetupForm #mobile').val();
        let country_code = jQuery('#userAccountSetupForm #country_code').val();
        let email = jQuery('#userAccountSetupForm #email').val();
        var filter = /^\d*(?:\.\d{1,2})?$/;
        let flag = true;
        if (country_code == "") {
            flag = false;
            jQuery("#userAccountSetupForm #country_code").addClass("error");
            jQuery("#mobile_error").text("Country Code is Required");
        } else {
            jQuery("#userAccountSetupForm #country_code").removeClass("error");
        }
        if (mobile == "") {
            jQuery("#userAccountSetupForm #mobile").addClass("error");
            jQuery("#mobile_error").text("Mobile is Required");
            flag = false;
        } else if (!filter.test(mobile) || mobile.length != 10) {
            jQuery("#userAccountSetupForm #mobile").addClass("error");
            jQuery("#mobile_error").text("Enter Valid Mobile Number");
            flag = false;
        } else {
            jQuery("#userAccountSetupForm #mobile").removeClass("error");
            jQuery("#mobile_error").text("");
        }

        if (email == "") {
            jQuery("#userAccountSetupForm #email").addClass("error");
            jQuery("#email_error").text("Email is Required");
            flag = false;
        } else if (!validateEmail(email)) {
            jQuery("#userAccountSetupForm #email").addClass("error");
            jQuery("#email_error").text("Enter Valid Email Address");
            flag = false;
        } else {
            jQuery("#userAccountSetupForm #email").removeClass("error");
            jQuery("#email_error").text("");

        }
        if (flag) {
            jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').addClass('show');

            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: request_globals.request_url,
                data: {
                    action: "skyfinityqc_send_sms_otp",
                    nonce: '<?php echo wp_create_nonce("sms_otp_nonce") ?>',
                    email: email,
                    mobile: mobile,
                    country_code: country_code
                },
                success: function (response) {
                    if (response.success == true) {
                        jQuery('.verify-otp-wrap').show();
                        jQuery('#btn-send-otp').hide();
                        jQuery('#btn-verify-otp').show();
                        jQuery('.mobile-email-input-wrap').hide();
                        jQuery('.verify-mobile-number').text(mobile);
                        jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');


                        var pause = 1000 * 60;
                        var countDownDate = new Date().getTime() + pause
                        var x = setInterval(function () {
                            // Get today's date and time
                            var now = new Date().getTime();
                            var distance = countDownDate - now;
                            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                            // Display the result in the element with id="demo"
                            document.getElementById("resend-time-wrap").innerHTML = 'Resend in ' + seconds;

                            // If the count down is finished, write some text
                            if (distance < 0) {
                                clearInterval(x);
                                document.getElementById("resend-time-wrap").innerHTML = "still not received? click on Resend button for request again";
                            }
                        }, 1000);


                        setTimeout(() => {
                            jQuery('.btn-resend-opt').attr('disabled', false);
                        }, pause);
                    } else {

                        jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                        jQuery("#userAccountSetupForm #mobile").addClass("error");
                        jQuery("#mobile_error").text(response?.message);
                    }
                }
            });
        }
    }

    function edit_phone_number(e) {
        e.preventDefault();
        jQuery('.verify-otp-wrap').hide();
        jQuery('#btn-send-otp').show();
        jQuery('#btn-verify-otp').hide();
        jQuery('.mobile-email-input-wrap').show();
    }


    function skyfinityqc_onClickVerify(e) {
        e.preventDefault();
        let mobile = jQuery('#userAccountSetupForm #mobile').val();
        let country_code = jQuery('#userAccountSetupForm #country_code').val();
        let email = jQuery('#userAccountSetupForm #email').val();
        let verify_otp = jQuery('#verify_otp').val();
        let flag = true;
        if (verify_otp == "") {
            flag = false;
            jQuery("#verify_otp").addClass("error");
            jQuery("#verify_otp_error").text("OTP Code is Required");
        } else {
            jQuery("#verify_otp").removeClass("error");
            jQuery("#verify_otp_error").text("");
        }
        if (flag) {
            jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').addClass('show');

            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: request_globals.request_url,
                data: {
                    action: "skyfinityqc_verify_sms_otp",
                    nonce: '<?php echo wp_create_nonce("verify_otp_nonce") ?>',
                    mobile: mobile,
                    email: email,
                    country_code: country_code,
                    verify_otp: verify_otp
                },
                success: function (response) {
                    if (response.success == true) {
                        // hide verification fields and show addresses  
                        jQuery('.verify-otp-wrap').hide();
                        jQuery('#btn-send-otp').show();
                        jQuery('#btn-verify-otp').hide();
                        jQuery('.mobile-email-input-wrap').show();
                        jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                        navigateToFormStep(2);
                        setTimeout(function () {
                            refresh_summary_step_content(function () {
                                jQuery('.skyfinityqc-add-address-form .phone-with-code').val('+' + country_code + mobile);
                                refresh_phone_number_script();
                            });
                        }, 2000);

                    } else {
                        jQuery("#verify_otp").addClass("error");
                        jQuery("#verify_otp_error").text(response.userMessage);
                        jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                    }
                }
            });
        }
    }

    function skyfinityqc_onClickRsendOtp(e) {
        e.preventDefault();
        let mobile = jQuery('#mobile').val();
        let country_code = jQuery('#country_code').val();
        jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').addClass('show');

        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: request_globals.request_url,
            data: {
                action: "skyfinityqc_resend_otp",
                nonce: '<?php echo wp_create_nonce("resend_otp_nonce") ?>',
                mobile: mobile,
                country_code: country_code,
            },
            success: function (response) {
                if (response.success == true) {
                    // hide verification fields and show addresses  
                    jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                } else {
                    jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                }
            }
        });
    }

    function validateAddressForm(e){
        e.preventDefault();
        let address_id = jQuery('.skyfinityqc-add-address-form #address_id').val();
        let full_name = jQuery('.skyfinityqc-add-address-form #full_name').val();
        let country_code = jQuery('.skyfinityqc-add-address-form #country_code').val();
        let phone_number = jQuery('.skyfinityqc-add-address-form #phone_number').val();
        let pincode = jQuery('.skyfinityqc-add-address-form #pincode').val();
        let address_1 = jQuery('.skyfinityqc-add-address-form #address_1').val();
        let state = jQuery('.skyfinityqc-add-address-form #state').val();
        let city = jQuery('.skyfinityqc-add-address-form #city').val();
        let address_2 = jQuery('.skyfinityqc-add-address-form #address_2').val();
        let country = jQuery('.skyfinityqc-add-address-form #country').val();
        let address_type = jQuery('.skyfinityqc-add-address-form #address_type').val();
        let landmark = jQuery('.skyfinityqc-add-address-form #landmark').val();
        let flag = true;
        if (full_name == '') {
            jQuery("#full_name").addClass('error');
            jQuery("#full_name_error").text('Full Name is Required');
            flag = false;
        } else {
            jQuery("#full_name").removeClass('error');
            jQuery("#full_name_error").text('');
        }
        // if (phone_number == '') {
        //     jQuery("#phone_number").addClass('error');
        //     jQuery("#phone_number_error").text('Phone Number is Required');
        //     flag = false;
        // } else {
        //     jQuery("#phone_number").removeClass('error');
        //     jQuery("#phone_number_error").text('');
        // }
        // if (country_code == '') {
        //     jQuery("#country_code").addClass('error');
        //     jQuery("#phone_number_error").text('Country Code is Required');
        //     flag = false;
        // } else {
        //     jQuery("#country_code").removeClass('error');
        // }

        if (pincode == '') {
            jQuery("#pincode").addClass('error');
            jQuery("#pincode_error").text('Pincode is Required');
            flag = false;
        } else {
            jQuery("#pincode").removeClass('error');
            jQuery("#pincode_error").text('');
        }
        if (address_1 == '') {
            jQuery("#address_1").addClass('error');
            jQuery("#address_1_error").text('Address is Required');
            flag = false;
        } else {
            jQuery("#address_1").removeClass('error');
            jQuery("#address_1_error").text('');
        }
        if (address_2 == '') {
            jQuery("#address_2").addClass('error');
            jQuery("#address_2_error").text('Area is Required');
            flag = false;
        } else {
            jQuery("#address_2").removeClass('error');
            jQuery("#address_2_error").text('')
        }
        if (state == '') {
            jQuery("#state").addClass('error');
            jQuery("#state_error").text('State is Required');
            flag = false;
        } else {
            jQuery("#state").removeClass('error');
            jQuery("#state_error").text('')
        }
        if (city == '') {
            jQuery("#city").addClass('error');
            jQuery("#city_error").text('City is Required');
            flag = false;
        } else {
            jQuery("#city").removeClass('error');
            jQuery("#city_error").text('')
        }
        if (country == '') {
            jQuery("#country").addClass('error');
            jQuery("#country_error").text('Country is Required');
            flag = false;
        } else {
            jQuery("#country").removeClass('error');
            jQuery("#country_error").text('')
        }

        if (address_type == '') {
            jQuery("#address_type").addClass('error');
            jQuery("#address_type_error").text('Address Type is Required');
            flag = false;
        } else {
            jQuery("#address_type").removeClass('error');
            jQuery("#address_type_error").text('')
        }
        return flag;
    }

    function skyfinityqc_onAddAddress(e) {
        e.preventDefault();
        let address_id = jQuery('.skyfinityqc-add-address-form #address_id').val();
        let full_name = jQuery('.skyfinityqc-add-address-form #full_name').val();
        let country_code = jQuery('.skyfinityqc-add-address-form #country_code').val();
        let phone_number = jQuery('.skyfinityqc-add-address-form #phone_number').val();
        let pincode = jQuery('.skyfinityqc-add-address-form #pincode').val();
        let address_1 = jQuery('.skyfinityqc-add-address-form #address_1').val();
        let state = jQuery('.skyfinityqc-add-address-form #state').val();
        let city = jQuery('.skyfinityqc-add-address-form #city').val();
        let address_2 = jQuery('.skyfinityqc-add-address-form #address_2').val();
        let country = jQuery('.skyfinityqc-add-address-form #country').val();
        let address_type = jQuery('.skyfinityqc-add-address-form #address_type').val();
        let landmark = jQuery('.skyfinityqc-add-address-form #landmark').val();
        
        if (true == validateAddressForm(e)) {
            jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').addClass('show');
            
            if (jQuery('#save_address').is(':checked')) {
                jQuery('.btn-add-address').attr('disabled', true);
                jQuery.ajax({
                    type: "post",
                    dataType: "json",
                    url: request_globals.request_url,
                    data: {
                        action: "skyfinityqc_add_address",
                        address_id: address_id,
                        full_name: full_name,
                        country_code: country_code,
                        phone_number: phone_number,
                        pincode: pincode,
                        address_1: address_1,
                        address_2: address_2,
                        state: state,
                        city: city,
                        country: country,
                        address_type: address_type,
                        landmark: landmark,
                        saveAddress: jQuery("#save_address").is(":checked"),
                    },
                    success: function (response) {
                        if (response.success == true) {
                            jQuery(".skyfinityqc-add-address-form").slideToggle();
                            jQuery(".step-address-buttons-wrap").show();
                            jQuery(".new-address-buttons-wrap").hide();
                            refresh_address_list(function () {
                                if (response.newAddressId != '') {
                                    jQuery('.address-list .address-item #shipping_address_' + response.newAddressId).prop('checked', true);
                                } else {
                                    jQuery('.address-list .address-item:first-child').prop('checked', true);
                                }
                                checkSelectedAddress();
                            });
                            emptyAddFormValue();
                            jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                            jQuery('.btn-add-address').attr('disabled', false);

                        } else {
                            jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                            jQuery('.btn-add-address').attr('disabled', false);
                        }
                    },
                    error: function (error) {
                        jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                        jQuery('.btn-add-address').attr('disabled', false);
                        console.log(error);
                    }
                });
            } else {
                jQuery.ajax({
                    type: "post",
                    dataType: "json",
                    url: request_globals.request_url,
                    data: {
                        action: "skyfinityqc_set_address_to_checkout",
                        address_id: address_id,
                        full_name: full_name,
                        country_code: country_code,
                        phone_number: phone_number,
                        pincode: pincode,
                        address_1: address_1,
                        address_2: address_2,
                        state: state,
                        city: city,
                        country: country,
                        address_type: address_type,
                        landmark: landmark,
                    },
                    success: function (response) {
                        if (response.success == true) {
                            jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                            const currentStep = jQuery('#skyfinity-quick-step-form-container').attr('data-currentstep');
                            navigateToFormStep(Number(currentStep) + 1, false);
                        } else {
                            jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                        }
                    },
                    error: function (request, status, error) {
                        console.log('error', request, status, error);
                        jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                    }
                });
            }
        }
    }

    function removeAddress(id, btn) {
        if (id) {
            jQuery(btn).attr('disabled', true);
            jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').addClass('show');
            jQuery.ajax({
                type: "post",
                dataType: "json",
                url: request_globals.request_url,
                data: {
                    action: "skyfinityqc_remove_address",
                    id: id,
                },
                success: function (response) {
                    if (response.success == true) {
                        jQuery(btn).closest('.address-item').remove();
                        jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                    } else {
                        jQuery(btn).attr('disabled', false);
                        jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                    }
                },
                error: function (error) {
                    console.log('error', error);
                    jQuery(btn).attr('disabled', false);
                    jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                }
            });
        }
    }

    function refresh_address_list(cb = false) {
        jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').addClass('show');

        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: request_globals.request_url,
            data: {
                action: "skyfinityqc_get_address_list",
            },
            success: function (response) {
                console.log('response', response);
                if (response.success == true) {
                    if (response.address_list_html)
                        jQuery('.address-list').html(response.address_list_html);
                    refresh_phone_number_addAddress_script();
                    if (response.address_list.length <= 0) {
                        jQuery('.add-new-address,.first_time_hide_save').hide();
                        jQuery(".skyfinityqc-add-address-form").slideDown();
                        jQuery(".step-address-buttons-wrap").hide();
                        jQuery(".new-address-buttons-wrap").show();
                    } else {
                        jQuery('.add-new-address,.first_time_hide_save').show();
                    }
                    if (cb)
                        cb();
                    jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                } else {
                    jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                }
            },
            error: function (request, status, error) {
                console.log('error', request, status, error);
                jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
            }
        });
    }
    function refresh_summary_step_content(cb = false) {
        // jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').addClass('show');
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: request_globals.request_url,
            data: {
                action: "skyfinityqc_get_summary_page_content",
            },
            success: function (response) {
                if (response.success == true) {
                    if (response.html_content)
                        jQuery('#step-1').html(response.html_content);
                    refresh_button_action();
                    if (cb)
                        cb();
                    // jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                } else {
                    jQuery(btn).attr('disabled', false);
                    // jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                }
            }
        });
    }

    function refresh_payment_list(cb = false) {
        jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').addClass('show');
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: request_globals.request_url,
            data: {
                action: "skyfinityqc_get_payment_list",
            },
            success: function (response) {
                if (response.success == true) {
                    if (response.html_content)
                        jQuery('.payment-method-list').html(response.html_content);
                    if (cb)
                        cb();
                    jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                } else {
                    jQuery(btn).attr('disabled', false);
                    jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                }
            }
        });
    }
    function refresh_coupons_list(cb = false) {
        jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').addClass('show');
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: request_globals.request_url,
            data: {
                action: "skyfinityqc_get_coupons_list_content",
            },
            success: function (response) {
                if (response.success == true) {
                    if (response.html_content)
                        jQuery('.coupons-wrapper').html(response.html_content);
                    if (cb)
                        cb();
                    jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                } else {
                    jQuery(btn).attr('disabled', false);
                    jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                }
            }
        });
    }
    function skyfinityqc_apply_woocommerce_coupon(code, cb = false, errorcb = false) {
        jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').addClass('show')
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: request_globals.request_url,
            data: {
                action: "skyfinityqc_apply_coupon",
                code: code
            },
            success: function (response) {
                if (response.success == true) {
                    if (cb)
                    cb(response);
                    jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                    jQuery(document.body).trigger('wc_fragment_refresh');
                } else {
                    if (errorcb)
                    errorcb(response)
                    jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                }
                jQuery(document.body).trigger('wc_fragment_refresh');
            },
            error: function (xhr, status, errorThrown) {
                if (errorcb)
                    errorcb(errorThrown)
                jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
            }
        });
    }
    function skyfinityqc_remove_woocommerce_coupon(code, cb = false, errorcb = false) {
        jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').addClass('show');
        return new Promise(function(resolve, reject) {
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: request_globals.request_url,
            data: {
                action: "skyfinityqc_remove_coupon",
                code: code
            },
            success: function (response) {
               if (response.success == true) {
                    if (cb)
                    cb(response);
                    jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                } else {
                    if (errorcb)
                    errorcb(response)
                    jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                }
                jQuery(document.body).trigger('wc_fragment_refresh');
            },
            error: function (xhr, status, errorThrown) {
                if (errorcb)
                    errorcb(errorThrown)
                jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
            }
        });
        });
    }

    function skyfinityqc_get_cart_contents(requestId) {
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: request_globals.request_url,
            data: {
                action: "skyfinityqc_get_cart_contents",
                nonce: '<?php echo wp_create_nonce("skyfinityqc_get_cart_contents") ?>',
            },
            success: function (response) {
                jQuery('.cart-summary').html(response?.html_content);
            }
        });
    }

    function emptyAddFormValue() {
        // jQuery(".skyfinityqc-add-address-form #country_code").val(country_code);
        // jQuery(".skyfinityqc-add-address-form #phone_number").val(phone_number);
        jQuery(".skyfinityqc-add-address-form #full_name").val('').removeClass("error");
        jQuery(".skyfinityqc-add-address-form #pincode").val('').removeClass("error");
        jQuery(".skyfinityqc-add-address-form #address_1").val('').removeClass("error");
        jQuery(".skyfinityqc-add-address-form #address_2").val('').removeClass("error");
        jQuery(".skyfinityqc-add-address-form #city").val('').removeClass("error");
        jQuery(".skyfinityqc-add-address-form #state").val('').removeClass("error");
        jQuery(".skyfinityqc-add-address-form #address_type").val('').removeClass("error");
        jQuery(".skyfinityqc-add-address-form #address_id").val('');
        jQuery(".skyfinityqc-add-address-form #country").val('').removeClass("error");
        jQuery(".skyfinityqc-add-address-form #landmark").val('').removeClass("error");
        jQuery(".skyfinityqc-add-address-form input[name='address_type_select']").attr('checked', false);
        jQuery(".skyfinityqc-add-address-form #address_type_other").val('').removeClass("error");
        jQuery(".skyfinityqc-add-address-form .error-message").text('');
        jQuery(".skyfinityqc-add-address-form #save_address").prop('checked', true);
    }

    function checkSelectedAddress() {
        jQuery(".address-list .address-item").removeClass("selected");
        jQuery(".address-list .address-item").each(function () {
            if (jQuery(this).find('input[name="shipping_address"]').is(':checked')) {
                jQuery(this).addClass('selected');
            }
        });
    };

    function onEditAddressItem(button) {
        var actionMenu = jQuery(button).closest('.address-actions').find('.address-action-menu');
        jQuery(actionMenu).toggleClass('show');
        const div = document.querySelector('.skyfinityqc-address-wrap');
        div.scroll({
            top: 0,
            behavior: 'smooth'
        });
        emptyAddFormValue();
        var id = jQuery(button).attr('data-id');
        let full_name = jQuery(button).attr('data-full_name');
        let phone_number = jQuery(button).attr('data-phone_number');
        let country_code = jQuery(button).attr('data-country_code');
        let address_1 = jQuery(button).attr('data-address_1');
        let address_2 = jQuery(button).attr('data-address_2');
        let city = jQuery(button).attr('data-city');
        let state = jQuery(button).attr('data-state');
        let pincode = jQuery(button).attr('data-pincode');
        let country = jQuery(button).attr('data-country');
        let address_type = jQuery(button).attr('data-address_type');
        let landmark = jQuery(button).attr('data-landmark');

        jQuery(".skyfinityqc-add-address-form #full_name").val(full_name);
        jQuery(".skyfinityqc-add-address-form #country_code").val(country_code);
        jQuery(".skyfinityqc-add-address-form #phone_number").val(phone_number);
        jQuery(".skyfinityqc-add-address-form #pincode").val(pincode);
        jQuery(".skyfinityqc-add-address-form #address_1").val(address_1);
        jQuery(".skyfinityqc-add-address-form #address_2").val(address_2);
        jQuery(".skyfinityqc-add-address-form #city").val(city);
        jQuery(".skyfinityqc-add-address-form #state").val(state);
        jQuery(".skyfinityqc-add-address-form #country").val(country);
        jQuery(".skyfinityqc-add-address-form #address_type").val(address_type);
        jQuery(".address_type_selections .select-address-type").attr('checked', false);

        if (address_type == 'Home' || address_type == 'Office') {
            jQuery('.address_type_selections #address_type_' + address_type).attr('checked', true);
        } else {
            jQuery('.address_type_selections #address_type_Other_sel').attr('checked', true);
            jQuery('#address_type_other').val(address_type);
            jQuery('#address_type_other').show();
        }
        checkAddressTypeValue();
        jQuery(".skyfinityqc-add-address-form #landmark").val(landmark);
        jQuery(".skyfinityqc-add-address-form #address_id").val(id);

        jQuery(".skyfinityqc-add-address-form").slideDown();
        jQuery(".step-address-buttons-wrap").hide();
        jQuery(".new-address-buttons-wrap").show();
    }

    jQuery(document).ready(function () {
        jQuery('body').addClass("skyfinityqc-quick-checkout");
        jQuery("#mobile").inputFilter(function (value) {
            return /^\d*$/.test(value);    // Allow digits only, using a RegExp
        }, "Only digits allowed");

        jQuery(".truecallerstart").click(skyfinityqc_onTrucallerButtonClick);
        jQuery(".btn-send-sms-otp").click(skyfinityqc_onClickSendSMS);
        jQuery(".btn-send-verify-otp-api").click(skyfinityqc_onClickVerify);
        jQuery(".btn-resend-opt").click(skyfinityqc_onClickRsendOtp);
        jQuery(document).on('click', ".edit-phonenumber", edit_phone_number)
        jQuery(document).on("click", ".btn-add-address", skyfinityqc_onAddAddress);
        jQuery(document).on("click", ".add-new-address", function (event) {
            event.preventDefault();
            emptyAddFormValue();
            const isAddressFormVisible = jQuery(".skyfinityqc-add-address-form").is(":visible");
            jQuery(".skyfinityqc-add-address-form").slideToggle();
            jQuery(".step-address-buttons-wrap,.new-address-buttons-wrap").toggle();
        });
        jQuery(document).on('change keydown', ".validatefield", function (e) {
            jQuery(this).removeClass('error');
            const errorDiv = jQuery(this).attr('data-error-id');
            if (jQuery('#' + errorDiv)) {
                jQuery('#' + errorDiv).text("");
            }
        });
        jQuery(document).on('click', ".skyfinityqc_edit_address", function (e) {
            event.preventDefault();
            var button = this;
            onEditAddressItem(button);
        });
        jQuery(document).on("click", ".btn-cancel-add-address", function (event) {
            event.preventDefault();
            emptyAddFormValue();
            if ( jQuery('.skyfinityqc-address-wrap .address-list').children().length <= 0 ) {
                const currentStep = jQuery('#skyfinity-quick-step-form-container').attr('data-currentstep');
                navigateToFormStep(Number(currentStep) - 1, false);    
            }
            jQuery(".skyfinityqc-add-address-form").slideUp();
            jQuery(".step-address-buttons-wrap").show();
            jQuery(".new-address-buttons-wrap").hide();
        })
        jQuery(document).on('click', ".skyfinityqc_remove_address", function (event) {
            event.preventDefault();
            var id = jQuery(this).attr('data-id');
            removeAddress(id, this);
        });

        // checkSelectedAddress();
        // skyfinityqc_get_cart_contents();
        // refresh_payment_list();
        // refresh_coupons_list();
        jQuery('.address-list').on('change', 'input[name="shipping_address"]', function (event) {
            checkSelectedAddress();
            // jQuery('.address-list .address-item').removeClass('selected');
            // if (event.target.checked) {
            //     jQuery(this).closest('.address-item').addClass('selected');
            // }
        });
        jQuery(document).on('click', "#landmark_label", function () {
            jQuery("#landmark").toggle();
        });

        jQuery(document).on("click", ".toggal_coupon", function (e) {
            e.preventDefault();
            jQuery('.account_wrap').slideToggle();
            jQuery('.skyfinityqc_coupons_list_wrap').slideToggle()

        });

        jQuery(document).on('click', '.close_coupon_wrap', function (e) {
            e.preventDefault();
            jQuery('.account_wrap').slideDown();
            jQuery('.skyfinityqc_coupons_list_wrap').slideUp()
        });

        jQuery(document).on('click', ".skyfinityqc_cpnBtn", function (e) {
            e.preventDefault();
            let coupon_code = jQuery(this).attr('data-coupon-code');
            jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').addClass('show');
            skyfinityqc_apply_woocommerce_coupon(coupon_code, () => {
                skyfinityqc_get_cart_contents();
                refresh_coupons_list();
                jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
            }, () => {
                jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');

            });
        });
        jQuery(document).on('click', ".skyfinityqc_remove_coupon_btn", function (e) {
            e.preventDefault();
            let coupon_code = jQuery(this).attr('data-coupon-code');
            jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').addClass('show');
            skyfinityqc_remove_woocommerce_coupon(coupon_code, () => {
                skyfinityqc_get_cart_contents();
                refresh_coupons_list();
                jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
            }, () => {
                jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');

            });
        });
        jQuery(document).on('blur', ".coupon_field_wrap #coupon", function (e) {
            let coupon_field_wrap = jQuery(this).closest('.coupon_field_wrap');
            let coupon_input_box = jQuery(coupon_field_wrap).find('.coupon-input-box');
            if (jQuery(coupon_input_box).hasClass('error')) {
                jQuery(coupon_input_box).removeClass('error');
                jQuery(coupon_field_wrap).find('#coupon_error').text('');
            }
        });
        jQuery(document).on('click', ".skyfinityqc_custom_cpnBtn", function (e) {
            e.preventDefault();
            let coupon_field_wrap = jQuery(this).closest('.coupon_field_wrap');
            let coupon_input_box = jQuery(coupon_field_wrap).find('.coupon-input-box');
            let coupon_code_input = jQuery(coupon_field_wrap).find('#coupon');
            let coupon_code = jQuery(coupon_code_input).val();
            jQuery(coupon_input_box).removeClass('error');
            jQuery(coupon_field_wrap).find('#coupon_error').text('');
            if (coupon_code !== '') {
                jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').addClass('show');
                skyfinityqc_apply_woocommerce_coupon(coupon_code, (res) => {
                    skyfinityqc_get_cart_contents();
                    refresh_coupons_list();
                    jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                }, (err) => {
                    jQuery(coupon_input_box).addClass('error');
                    jQuery(coupon_field_wrap).find('#coupon_error').text(err?.Message);
                    jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                });
            } else {
                jQuery(coupon_input_box).addClass('error');
                jQuery(coupon_field_wrap).find('#coupon_error').text("Coupon Code is Required");
            }
        });

        jQuery(document).on('click', '.showDesc', function (event) {
            jQuery(this).closest('.payment-method-item-wrap').toggleClass('show-desc');
            jQuery(this).closest('.payment-method-item-wrap').find('.arrow-btn .arrow').toggleClass('right down');
        });
        jQuery(document).on('change', 'input[name="payment_method"]', function (event) {
            jQuery('.payment-method-item-wrap label').removeClass('error');
            jQuery("#payment-method-error").text('')
            if (event.target.checked) {
                jQuery('.payment-method-item-wrap').removeClass('selected');
                jQuery(this).closest('.payment-method-item-wrap').addClass('selected');
            }
        });

        jQuery(document).on('click', '.step-back-button', function () {
            const stepWrapper = jQuery('#skyfinity-quick-step-form-container');
            const currentStep = jQuery(stepWrapper).attr('data-currentstep');
            if (currentStep == "1") {
                jQuery('.confirm-close-quick-checkout').addClass('show')
                // jQuery("#skyfinityqc_checkout_modal").removeClass("show");
            } else if (currentStep == "2") {
                emptyAddFormValue();
                navigateToFormStep(Number(currentStep) - 1, false);
            } else {
                navigateToFormStep(Number(currentStep) - 1, false);
            }
        });
        jQuery(document).ajaxComplete(function (event, xhr, settings) {
            if (settings.url === '/?wc-ajax=remove_coupon') {
                location.reload();
            }
        });
        // ajax add to cart for skyfinity quick checkout popup
        jQuery(document).on('click', '.ajax_add_to_cart_button', function(e){ 
            e.preventDefault();
            $thisbutton = jQuery(this);
            product_id = jQuery($thisbutton).attr('data-product_id');
            variation_id = jQuery($thisbutton).attr('data-variation_id');
            var data = {
                action: 'skyfinityqc_wc_ajax_add_to_cart',
                product_id: product_id,
                product_sku: '',
                quantity: 1,
                variation_id: variation_id,
            };
            jQuery.ajax({
                type: 'post',
                url: wc_add_to_cart_params.ajax_url,
                data: data,
                beforeSend: function (response) {
                    jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').addClass('show');
                },
                complete: function (response) {
                    jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                    // $thisbutton.addClass('added').removeClass('loading');
                }, 
                success: function (response) { 
                    if (response.error & response.product_url) {
                        window.location = response.product_url;
                        return;
                    } else { 
                        jQuery(document.body).trigger('wc_fragment_refresh');
                    } 
                }, 
            }); 
        });
        // ajax add to cart for single product page
        jQuery(document).on('click', '.single_add_to_cart_button', function (e) {
            e.preventDefault();
            var $thisbutton = jQuery(this),
                    $form = $thisbutton.closest('form.cart'),
                    id = $thisbutton.val(),
                    product_qty = $form.find('input[name=quantity]').val() || 1,
                    product_id = $form.find('input[name=product_id]').val() || id,
                    variation_id = $form.find('input[name=variation_id]').val() || 0;

            var data = {
                action: 'skyfinityqc_wc_ajax_add_to_cart',
                product_id: product_id,
                product_sku: '',
                quantity: product_qty,
                variation_id: variation_id,
            };

            jQuery(document.body).trigger('adding_to_cart', [$thisbutton, data]);
            jQuery.ajax({
                type: 'post',
                url: wc_add_to_cart_params.ajax_url,
                data: data,
                beforeSend: function (response) {
                    $thisbutton.removeClass('added').addClass('loading');
                },
                complete: function (response) {
                    $thisbutton.addClass('added').removeClass('loading');
                },
                success: function (response) {
                    if (response.error && response.product_url) {
                        window.location = response.product_url;
                        return;
                    } else {
                        jQuery(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);
                        jQuery(document.body).trigger('wc_fragment_refresh');
                    }
                },
            });

            return false;
        });
    });
    
</script>