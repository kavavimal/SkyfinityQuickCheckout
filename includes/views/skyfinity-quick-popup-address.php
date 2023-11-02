<?php

defined('ABSPATH') || exit;

?>

<!-- <div class="step_title_wrap">
    <h4 class="">
        02
    </h4>
    <h5 class="step-title">
        <?php // echo __("Address", "skyfinity-quick-checkout"); 
        ?>
    </h5>
</div> -->
<?php
$country_code = '';
$phoneNumber = '';
$fname = '';
$address_1 = '';
$address_2 = '';
$city = '';
$state = '';
$country = '';
$postcode = '';
$address_type = '';
$landmark = '';
if (is_user_logged_in() && $current_user) {
    $phoneNumber = get_user_meta($current_user->ID, 'billing_phone', true);
    $country_code = get_user_meta($current_user->ID, 'billing_phone_country_code', true);
    $first_name = get_user_meta($current_user->ID, 'first_name', true);
    $last_name = get_user_meta($current_user->ID, 'last_name', true);
    if ($first_name !== '') {
        $fname .= $first_name;
    }
    if ($last_name !== '') {
        $fname .= $last_name;
    }
    $email = $current_user->user_email;
}
?>
<div class="mt-1">
    <div class="skyfinityqc-address-wrap">
        <button class="add-new-address button d-flex align-item-center" type="button">
            <p class="d-flex align-item-center justify-center">
                <svg class="sq-icon-image" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g id="Edit / Add_Plus_Square">
                        <path id="Vector" d="M8 12H12M12 12H16M12 12V16M12 12V8M4 16.8002V7.2002C4 6.08009 4 5.51962 4.21799 5.0918C4.40973 4.71547 4.71547 4.40973 5.0918 4.21799C5.51962 4 6.08009 4 7.2002 4H16.8002C17.9203 4 18.4801 4 18.9079 4.21799C19.2842 4.40973 19.5905 4.71547 19.7822 5.0918C20.0002 5.51962 20.0002 6.07967 20.0002 7.19978V16.7998C20.0002 17.9199 20.0002 18.48 19.7822 18.9078C19.5905 19.2841 19.2842 19.5905 18.9079 19.7822C18.4805 20 17.9215 20 16.8036 20H7.19691C6.07899 20 5.5192 20 5.0918 19.7822C4.71547 19.5905 4.40973 19.2842 4.21799 18.9079C4 18.4801 4 17.9203 4 16.8002Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </g>
                </svg>
                Add New Address
            </p>
        </button>
        <div class="skyfinityqc-add-address-form skyfinityqc-address-fields" style="display:none">
            <div class="sm-flex-column">
                <div class="flex1 mt-1 fields-wrap">
                    <label for="full_name">
                        <?php echo __("Full Name", 'skyfinity-quick-checkout'); ?>
                    </label>
                    <input class="form-control validatefield" type="text" id="full_name" name="full_name" placeholder="Full Name" value="<?php echo esc_attr($fname); ?>" data-error-id="full_name_error" required />
                    <span id="full_name_error" class="error-message"></span>
                </div>
                <div class="flex1 mt-1 fields-wrap">
                    <label for="mobile">
                        <?php echo __("Phone Number", 'skyfinity-quick-checkout'); ?>
                    </label>
                    <div class="phone-input">
                        <input class="form-control country-code" type="hidden" id="country_code" name="country_code" value="<?php echo esc_attr($country_code); ?>" readonly />
                        <input class="form-control phone-number phone-with-code validatefield" type="text" id="phone_number" name="phone_number" value="<?php echo esc_attr(isset($phoneNumber) && $phoneNumber != '' ? '+' . $phoneNumber : ''); ?>" placeholder="1234567899" data-error-id="phone_number_error" readonly />
                    </div>
                    <span id="phone_number_error" class="error-message"></span>
                </div>
            </div>
            <div class="sm-flex-column">
                <div class="flex1 mt-1 fields-wrap">
                    <label for="pincode">
                        <?php echo __("Pincode", 'skyfinity-quick-checkout'); ?>
                    </label>
                    <input class="form-control validatefield" type="text" id="pincode" name="pincode" placeholder="301001" value="<?php echo esc_attr($postcode); ?>" data-error-id="pincode_error" required />
                    <span id="pincode_error" class="error-message"></span>
                </div>
                <div class="flex1 mt-1 fields-wrap">
                    <label for="address_1">
                        <?php echo __("House Number", 'skyfinity-quick-checkout'); ?>
                    </label>
                    <input class="form-control validatefield" type="text" id="address_1" name="address_1" placeholder="House Number , Apartment*" value="<?php echo esc_attr($address_1); ?>" data-error-id="address_1_error" required />
                    <span id="address_1_error" class="error-message"></span>
                </div>
            </div>
            <div class="d-flex sm-flex-column">
                <div class="flex1 mt-1 fields-wrap">
                    <label for="state">
                        <?php echo __("State", 'skyfinity-quick-checkout'); ?>
                    </label>
                    <input class="form-control validatefield" type="text" id="state" name="state" placeholder="State" value="<?php echo esc_attr($state); ?>" data-error-id="state_error" required />
                    <span id="state_error" class="error-message"></span>
                </div>
                <div class="flex1 mt-1 fields-wrap">
                    <label for="city">
                        <?php echo __("City", 'skyfinity-quick-checkout'); ?>
                    </label>
                    <input class="form-control validatefield" type="text" id="city" name="city" placeholder="City" value="<?php echo esc_attr($city); ?>" data-error-id="city_error" required />
                    <span id="city_error" class="error-message"></span>
                </div>
            </div>
            <div class="flex1 mt-1 fields-wrap">
                <label for="address_2">
                    <?php echo __("Area, Colony, Street", 'skyfinity-quick-checkout'); ?>
                </label>
                <input class="form-control validatefield" type="text" id="address_2" name="address_2" placeholder="Area, Colony, Street" value="<?php echo esc_attr($address_2); ?>" data-error-id="address_2_error" required />
                <span id="address_2_error" class="error-message"></span>
            </div>
            <div class="flex1 mt-1 fields-wrap">
                <label for="country">
                    <?php echo __("Country", 'skyfinity-quick-checkout'); ?>
                </label>
                <input class="form-control validatefield" type="text" id="country" name="country" placeholder="Country" value="<?php echo esc_attr($country); ?>" data-error-id="country_error" required />
                <span id="country_error" class="error-message"></span>
            </div>
            <div class="flex1 mt-1 fields-wrap">
                <label for="address_type">
                    <?php echo __("Address Type", 'skyfinity-quick-checkout'); ?>
                </label>
                <ul class="address_type_selections">
                    <li>
                        <label>
                            <input type="radio" id="address_type_Home" name="address_type_select" class="select-address-type" value="Home" />

                            <span class="title d-flex align-item-center justify-center"> <svg class="sq-icon-image" fill="currentColor" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <path d="M31.772 16.043l-15.012-15.724c-0.189-0.197-0.449-0.307-0.721-0.307s-0.533 0.111-0.722 0.307l-15.089 15.724c-0.383 0.398-0.369 1.031 0.029 1.414 0.399 0.382 1.031 0.371 1.414-0.029l1.344-1.401v14.963c0 0.552 0.448 1 1 1h6.986c0.551 0 0.998-0.445 1-0.997l0.031-9.989h7.969v9.986c0 0.552 0.448 1 1 1h6.983c0.552 0 1-0.448 1-1v-14.968l1.343 1.407c0.197 0.204 0.459 0.308 0.722 0.308 0.249 0 0.499-0.092 0.692-0.279 0.398-0.382 0.411-1.015 0.029-1.413zM26.985 14.213v15.776h-4.983v-9.986c0-0.552-0.448-1-1-1h-9.965c-0.551 0-0.998 0.445-1 0.997l-0.031 9.989h-4.989v-15.777c0-0.082-0.013-0.162-0.032-0.239l11.055-11.52 10.982 11.507c-0.021 0.081-0.036 0.165-0.036 0.252z">
                                        </path>
                                    </g>
                                </svg>Home</span>
                        </label>
                    </li>
                    <li>
                        <label><input type="radio" id="address_type_Office" name="address_type_select" class="select-address-type" value="Office" />
                            <span class="title d-flex align-item-center justify-center">
                                <svg class="sq-icon-image" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <defs>
                                            <style>
                                                .cls-1,
                                                .cls-2 {
                                                    fill: none;
                                                    stroke: currentColor;
                                                    stroke-miterlimit: 10;
                                                    stroke-width: 2px;
                                                }

                                                .cls-2 {
                                                    stroke-linecap: square;
                                                }
                                            </style>
                                        </defs>
                                        <g id="briefcase_alternatif" data-name="briefcase alternatif">
                                            <path class="cls-1" d="M12,14.86h0a3.44,3.44,0,0,1-1.77-.49L1.5,9.14v-1A1.91,1.91,0,0,1,3.41,6.27H20.59A1.91,1.91,0,0,1,22.5,8.18v1l-8.73,5.23A3.44,3.44,0,0,1,12,14.86Z">
                                            </path>
                                            <line class="cls-2" x1="12" y1="13.91" x2="12" y2="15.82"></line>
                                            <polygon class="cls-2" points="15.82 6.27 8.18 6.27 9.14 2.46 14.86 2.46 15.82 6.27"></polygon>
                                            <rect class="cls-1" x="1.5" y="6.27" width="21" height="15.27" rx="1.91">
                                            </rect>
                                        </g>
                                    </g>
                                </svg>
                                Office</span>
                        </label>
                    </li>
                    <li>
                        <label><input type="radio" id="address_type_Other_sel" name="address_type_select" class="select-address-type" value="Other" />
                            <span class="title d-flex align-item-center justify-center">
                                <svg class="sq-icon-image" fill="currentColor" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <path d="M8 .52a5.39 5.39 0 0 0-5.59 5.15c0 5 3.88 8.61 5.17 9.66a.66.66 0 0 0 .84 0c1.29-1.05 5.17-4.63 5.17-9.66A5.39 5.39 0 0 0 8 .52zM8 14a15.2 15.2 0 0 1-2.46-2.76 9.85 9.85 0 0 1-1.88-5.57A4.14 4.14 0 0 1 8 1.77a4.14 4.14 0 0 1 4.34 3.9c0 4.08-2.96 7.16-4.34 8.33z">
                                        </path>
                                        <path d="M8 2.54a2.73 2.73 0 0 0-2.84 2.65A2.74 2.74 0 0 0 8 7.84a2.75 2.75 0 0 0 2.83-2.65A2.74 2.74 0 0 0 8 2.54zm0 4.05a1.49 1.49 0 0 1-1.57-1.4A1.49 1.49 0 0 1 8 3.79a1.5 1.5 0 0 1 1.58 1.4A1.5 1.5 0 0 1 8 6.59z">
                                        </path>
                                    </g>
                                </svg>Other</span>
                        </label>
                    </li>
                </ul>
                <input class="form-control validatefield" type="text" id="address_type_other" name="address_type_other" placeholder="Home" value="<?php echo esc_attr($address_type); ?>" data-error-id="address_type_error" />
                <input class="form-control" type="hidden" id="address_type" name="address_type" placeholder="Home" value="<?php echo esc_attr($address_type); ?>" data-error-id="address_type_error" />
                <span id="address_type_error" class="error-message"></span>
            </div>
            <div class="d-flex sm-flex-column">
                <div class="flex1 mt-1 fields-wrap">
                    <label id="landmark_label" class="landmark" for="landmark">
                        <?php echo __("+ Add Landmark (Optional)", 'skyfinity-quick-checkout'); ?>
                    </label>
                    <input class="form-control" style="display: none" type="text" id="landmark" name="landmark" placeholder="Landmark" value="<?php echo esc_attr($landmark); ?>" />
                </div>

                <div class="flex1 mt-1 fields-wrap text-align-right">
                    <label for="save_address" class="checkbox-container save_address">
                        <input type="checkbox" id="save_address" name="save_address" value="true" checked="true" />
                        <span class="checkmark"></span>
                        <?php echo __("Save Address", 'skyfinity-quick-checkout'); ?>
                    </label>
                </div>
            </div>
            <div class="flex1 mt-1 fields-wrap first_time_hide_save">
                <input type="hidden" id="address_id" name="address_id" value="" />

            </div>
            <div id="loader"></div>
        </div>
        <div class="address-list">
        </div>
        <span class="error-message" id="address_error_message"></span>
    </div>
</div>
<div class="mt-1 d-flex card-primary m-bottom-fixed">
    <div id="skyfinityqc-modal-total-content-address">
        <?php echo WC()->cart->get_total(); ?>
    </div>
    <div class="new-address-buttons-wrap" style="display: none">
        <button type="button" class="button btn-cancel-add-address" name="cancel_add_address">
            <?php echo __("Cancel", 'skyfinity-quick-checkout'); ?>
        </button>
        <button type="button" class="button btn-add-address" name="add_address">
            <?php echo __("Save", 'skyfinity-quick-checkout'); ?>
        </button>
    </div>
    <div class="step-address-buttons-wrap">
        <button class="button btn-navigate-form-step" type="button" step_number="1">
            <?php echo __("Back", "skyfinity-quick-checkout"); ?>
        </button>
        <button class="button btn-navigate-form-step" type="button" data-step="Address" data-next="true" step_number="3"><?php echo __("Continue", "skyfinity-quick-checkout"); ?></button>
    </div>
</div>
<script>
    // function setShippingAddress(id) {
    //     jQuery('#address_error_message').text('');
    //     jQuery.ajax({
    //         type: "post",
    //         dataType: "json",
    //         url: request_globals.request_url,
    //         data: {
    //             action: "skyfinityqc_set_address_to_checkout",
    //             id: id
    //         },
    //         success: function(response) {
    //             jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
    //             if (response.success == true) {
    //                 // 
    //             } else {
    //                 // 
    //             }
    //         }
    //     });
    // }

    // function fetchAddressFromGoogle(pincode) {
    //     jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').addClass('show');
    //     jQuery.ajax({
    //         type: "post",
    //         dataType: "json",
    //         url: request_globals.request_url,
    //         data: {
    //             action: "skyfinityqc_get_address_by_postcode",
    //             pincode: pincode
    //         },
    //         success: function(response) {
    //             if (response.success == true) {
    //                 jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
    //                 if (response.address) {
    //                     jQuery('#city').val(response.address.city);
    //                     jQuery('#state').val(response.address.state);
    //                     jQuery('#country').val(response.address.country);
    //                 }
    //             } else {
    //                 jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
    //                 jQuery('#city').val('');
    //                 jQuery('#state').val('');
    //                 jQuery('#country').val('');
    //             }
    //         }
    //     });
    // }

    // function checkAddressTypeValue() {
    //     let val = jQuery('input[name="address_type_select"]:checked').val();
    //     if (val === 'Other') {
    //         jQuery('#address_type_other').show();
    //     } else {
    //         jQuery('#address_type_other').hide();

    //     }
    // }

    // jQuery(document).ready(function() {
    //     jQuery(document).on("change", '#pincode', async function(e) {
    //         const value = e.target.value;
    //         if (value?.length >= 4) {
    //             fetchAddressFromGoogle(value);
    //         }
    //     });
    //     checkAddressTypeValue();
    //     jQuery(document).on("change", '#address_type_other', function(e) {
    //         jQuery('#address_type').val(e.target.value);
    //     });
    //     jQuery(document).on("change", 'input[name="address_type_select"]', function(e) {
    //         let val = '';
    //         if (jQuery(this).is(':checked')) {
    //             val = e.target.value;
    //             if (val != 'Other') {
    //                 jQuery('#address_type').val(val);
    //                 jQuery('#address_type_error').text('');
    //             }
    //         }
    //         checkAddressTypeValue();
    //     });
    //     jQuery(document).on('click', '.address-action-icon', function() {
    //         var actionMenu = jQuery(this).closest('.address-actions').find('.address-action-menu');
    //         jQuery(actionMenu).toggleClass('show');
    //     });
    //     jQuery(document).mouseup(function(e) {
    //         var container = jQuery(".address-action-menu");
    //         var buttons = jQuery(".address-action-icon");

    //         // if the target of the click isn't the container nor a descendant of the container
    //         if (!container.is(e.target) && container.has(e.target).length === 0 && !buttons.is(e.target) && buttons.has(e.target).length === 0) {
    //             container.removeClass('show');
    //         }
    //     });
    // });
</script>