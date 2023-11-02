<?php

defined('ABSPATH') || exit;

$country_code = '91';
$email = '';
$current_user;
if (is_user_logged_in()) {
    $current_user = wp_get_current_user();
    $phoneNumber = get_user_meta($current_user->ID, 'billing_phone', true);
    $country_code = get_user_meta($current_user->ID, 'billing_phone_country_code', true);
    $email = $current_user->user_email;
}
$show_coupon = false;
// $coupon_posts = array();
$plugin_options = get_option('skyfinity_quick_checkout_setting');
$isCouponEnabledWoo = get_option( 'woocommerce_enable_coupons' );
if ($isCouponEnabledWoo && isset($plugin_options['fetch_coupon']) && $plugin_options['fetch_coupon'] == 1) {
    $show_coupon = true;
    // $coupon_posts = get_posts(
    //     array(
    //         'posts_per_page' => -1,
    //         'orderby' => 'name',
    //         'order' => 'asc',
    //         'post_type' => 'shop_coupon',
    //         'post_status' => 'publish',
    //     )
    // );
}
?>
<!-- Step 1 input fields -->
<div class="mobile-email-input-wrap">
    <div class="account_wrap show">
        <!-- <div class="step_title_wrap">
            <h4 class="">
                01
            </h4>
            <h5 class="step-title">
                <?php // echo __("SUMMARY", "skyfinity-quick-checkout"); ?>
            </h5>
        </div> -->
        <div class="mt-1 fields-wrap">
            <label for="mobile">
                <?php echo __("Phone Number", 'skyfinity-quick-checkout'); ?>
            </label>
            <div class="phone-input">
                <input class="form-control country-code" type="hidden" id="country_code" name="country_code"
                    value="<?php echo esc_attr($country_code); ?>" required />
                <input class="form-control phone-number phone-with-code" type="text" id="mobile" name="mobile"
                    value="<?php echo esc_attr(isset($phoneNumber) && $phoneNumber != '' ? '+' . $phoneNumber : ''); ?>"
                    placeholder="Enter Your Phone Number" required <?php echo is_user_logged_in() && isset($phoneNumber) && $phoneNumber != '' ? 'readonly' : ''; ?> />
            </div>
            <span id="mobile_error" class="error-message"></span>
        </div>
        <div class="mt-1 fields-wrap">
            <label for="email">
                <?php echo __("E-mail Id", 'skyfinity-quick-checkout'); ?>
            </label>
            <input class="form-control validatefield" type="email" id="email" name="email"
                placeholder="Enter Your Email Id" data-error-id="email_error" value="<?php echo esc_attr($email); ?>" required
                <?php echo is_user_logged_in() && isset($email) && $email != '' ? 'readonly' : ''; ?> />
            <span id="email_error" class="error-message"></span>
        </div>
        <?php
        if (!is_user_logged_in() && check_is_mobile() && 
            isset($plugin_options['login_with_truecaller']) && $plugin_options['login_with_truecaller'] == '1' && 
            isset($plugin_options['true_caller_key']) && $plugin_options['true_caller_key'] != '' && 
            isset($plugin_options['true_caller_appname']) && $plugin_options['true_caller_appname'] != '') { ?>
            <p class="mt-1 text-center"> OR </p>
            <div class="text-center">
                <button 
                    class="button truecallerstart" 
                    type="button"
                    data-nonce="<?php echo wp_create_nonce("truecaller_nonce"); ?>">
                    <?php echo __('Login using Truecaller', 'skyfinity_quick_checkout'); ?>
                </button>

                <span id="skyfinityqc_trucaller_error" class="error-message"></span>
            </div>
        <?php } ?>
        <div class="mt-1 have_a_coupons_wrapper toggal_coupon">
            <p class="d-flex align-item-center ">
                <svg class="sq-icon-image" viewBox="0 0 32.00 32.00" enable-background="new 0 0 32 32" version="1.1"
                    xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                    fill="currentColor" transform="rotate(-45)matrix(-1, 0, 0, -1, 0, 0)">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round" stroke="#CCCCCC"
                        stroke-width="0.064"></g>
                    <g id="SVGRepo_iconCarrier">
                        <g id="Layer_1"></g>
                        <g id="Layer_2">
                            <g>
                                <path d=" M23,26h7v-4v0c-1.7,0-3-1.4-3-3s1.3-3,3-3v0v-4h-7" fill="none" stroke="#000000"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10"
                                    stroke-width="1.504"></path>
                                <path d=" M20,12H2v4v0c1.7,0,3,1.4,3,3s-1.3,3-3,3v0v4h18" fill="none" stroke="#000000"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10"
                                    stroke-width="1.504"></path>
                                <line fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-miterlimit="10" stroke-width="1.504" x1="23" x2="23" y1="12" y2="14"></line>
                                <line fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-miterlimit="10" stroke-width="1.504" x1="23" x2="23" y1="24" y2="26"></line>
                                <line fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-miterlimit="10" stroke-width="1.504" x1="8" x2="18" y1="16" y2="16"></line>
                                <line fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-miterlimit="10" stroke-width="1.504" x1="13" x2="18" y1="20" y2="20"></line>
                                <line fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-miterlimit="10" stroke-width="1.504" x1="22" x2="22" y1="6" y2="8"></line>
                                <line fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-miterlimit="10" stroke-width="1.504" x1="23" x2="23" y1="17" y2="21"></line>
                                <line fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-miterlimit="10" stroke-width="1.504" x1="26" x2="25" y1="8" y2="9"></line>
                                <line fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-miterlimit="10" stroke-width="1.504" x1="18" x2="19" y1="8" y2="9"></line>
                            </g>
                        </g>
                    </g>
                </svg>
                <?php echo __("Have a Coupon?", "skyfinity-quick-checkout");
                if ($show_coupon && isset($plugin_options['selected_coupons'])) { ?> (
                    <?php echo esc_html(count($plugin_options['selected_coupons'])); ?> available)
                <?php } ?>
            </p>
            <!-- <button type="button" class="toggal_coupon"><span class="arrow right"></span></button> -->
        </div>
        <div class="mt-1 fields-wrap">
            <label for="instruction" id="instructions-label" onclick="toggleInstructions()">
                +Instructions for your order
            </label>
            <input class="form-control validatefield" type="text" id="instruction" name="instruction"
                placeholder="Your Instructions here" data-error-id="instruction_error" value="" required
                style="display: none;" />
            <span id="instruction_error" class="error-message"></span>
        </div>
    </div>
    <div class="coupons_wrap">
        <div class="skyfinityqc_coupons_list_wrap ">
            <h6 class="d-flex justify-start coupon_step_title">
                <button type="button" class="d-inline-block close_coupon_wrap transparent-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" height="48" viewBox="0 96 960 960" width="48"
                        fill="inherit">
                        <path d="M480 896 160 576l320-320 42 42-248 248h526v60H274l248 248-42 42Z" />
                        <div xmlns="" id="divScriptsUsed" style="display: none" />
                    </svg>
                </button>Add Coupons Here
            </h6>
            <div class="mt-1 fields-wrap coupon_field_wrap">
                <label for="coupon">
                    <?php echo __("Apply Coupon", 'skyfinity-quick-checkout'); ?>
                </label>
                <div class="d-flex coupon-input-box">
                    <input class="form-control" type="text" id="coupon" name="coupon"  placeholder="Enter Coupon Code" value="" />
                    <!-- <button type="button" class="button skyfinityqc_custom_cpnBtn">Apply Coupon</button> -->
                </div>
                <span id="coupon_error" class="error-message"></span>
            </div>
            <?php
            if (isset($plugin_options['fetch_coupon']) && $plugin_options['fetch_coupon'] == 1) { ?>
                <div class="have_a_coupons_wrapper toggal_coupon">
                    <p class="d-flex align-item-center">
                        <svg class="sq-icon-image" viewBox="0 0 32.00 32.00" enable-background="new 0 0 32 32" version="1.1"
                            xml:space="preserve" xmlns="http://www.w3.org/2000/svg"
                            xmlns:xlink="http://www.w3.org/1999/xlink" fill="currentColor"
                            transform="rotate(-45)matrix(-1, 0, 0, -1, 0, 0)">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round" stroke="#CCCCCC"
                                stroke-width="0.064"></g>
                            <g id="SVGRepo_iconCarrier">
                                <g id="Layer_1"></g>
                                <g id="Layer_2">
                                    <g>
                                        <path d=" M23,26h7v-4v0c-1.7,0-3-1.4-3-3s1.3-3,3-3v0v-4h-7" fill="none"
                                            stroke="#000000" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-miterlimit="10" stroke-width="1.504"></path>
                                        <path d=" M20,12H2v4v0c1.7,0,3,1.4,3,3s-1.3,3-3,3v0v4h18" fill="none"
                                            stroke="#000000" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-miterlimit="10" stroke-width="1.504"></path>
                                        <line fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-miterlimit="10" stroke-width="1.504" x1="23" x2="23" y1="12" y2="14">
                                        </line>
                                        <line fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-miterlimit="10" stroke-width="1.504" x1="23" x2="23" y1="24" y2="26">
                                        </line>
                                        <line fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-miterlimit="10" stroke-width="1.504" x1="8" x2="18" y1="16" y2="16">
                                        </line>
                                        <line fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-miterlimit="10" stroke-width="1.504" x1="13" x2="18" y1="20" y2="20">
                                        </line>
                                        <line fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-miterlimit="10" stroke-width="1.504" x1="22" x2="22" y1="6" y2="8">
                                        </line>
                                        <line fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-miterlimit="10" stroke-width="1.504" x1="23" x2="23" y1="17" y2="21">
                                        </line>
                                        <line fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-miterlimit="10" stroke-width="1.504" x1="26" x2="25" y1="8" y2="9">
                                        </line>
                                        <line fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-miterlimit="10" stroke-width="1.504" x1="18" x2="19" y1="8" y2="9">
                                        </line>
                                    </g>
                                </g>
                            </g>
                        </svg>
                        <?php echo __("Available Coupons", "skyfinity-quick-checkout"); ?>
                    </p>
                    <!-- <button type="button" class="toggal_coupon active"><span class="arrow down"></span></button> -->
                </div>
            <?php } ?>
            <div class="coupons-wrapper">
            </div>
        </div>
    </div>
</div>
<div class="verify-otp-wrap" style="display: none;">
    <p>
        <?php echo __("One Time Password have been sent to your Mobile number", 'skyfinity-quick-checkout'); ?>
        <span class="verify-mobile-number"></span> <button type="button"
            class="edit-phonenumber button-text">Edit</button>
    </p>
    <div class="mt-1 fields-wrap">
        <label for="email">
            <?php echo __("OTP Code*", 'skyfinity-quick-checkout'); ?>
        </label>
        <input class="form-control verify_otp validatefield" autocomplete="one-time-code" type="text" id="verify_otp"
            data-error-id="verify_otp_error" name="verify_otp" required />
        <span id="verify_otp_error" class="error-message"></span>
        <p id="resend-time-wrap" class=" time-message"></p>
    </div>
    <button class="button btn-resend-opt" type="button" disabled>Resend OTP</button>
</div>
<div class="mt-1 d-flex card-primary m-bottom-fixed">
    <div id="skyfinityqc-modal-total-content-summary">
        <?php echo WC()->cart->get_total(); ?>
    </div>
    <div>
        <?php
        if (!is_user_logged_in() || (!isset($phoneNumber) || $phoneNumber === '')) { ?>
            <button class="button btn-send-sms-otp" id="btn-send-otp" type="button">
                <?php echo __("Continue", "skyfinity-quick-checkout"); ?>
            </button>
            <button class="button btn-send-verify-otp-api" id="btn-verify-otp" type="button" style="display: none;">
                <?php echo __("Verify OTP", "skyfinity-quick-checkout"); ?>
            </button>
        <?php } else { ?>
            <button class="button btn-navigate-form-step" type="button" data-step="<?php echo 'Summary'; ?>"
                data-next="true" step_number="2"><?php echo __("Continue", "skyfinity-quick-checkout"); ?>
            </button>
        <?php } ?>
    </div>
</div>
<?php /*
<script>
if ('OTPCredential' in window) {
window.addEventListener('DOMContentLoaded', e => {
const input = document.querySelector('input[autocomplete="one-time-code"]');
if (!input) return;
// Cancel the Web OTP API if the form is submitted manually.
const ac = new AbortController();
const form = input.closest('form');
if (form) {
form.addEventListener('submit', e => {
// Cancel the Web OTP API.
ac.abort();
});
}
// Invoke the Web OTP API
navigator.credentials.get({
otp: { transport: ['sms'] },
signal: ac.signal
}).then(otp => {
// alert('otp', JSON.stringify(otp));
input.value = otp.code;
// Automatically submit the form when an OTP is obtained.
// if (form) form.submit();
}).catch(err => {
// alert('err', JSON.stringify(err));
console.log(err);
});
});
}
</script>
*/