<?php

defined('ABSPATH') || exit;

$current_user;
if (is_user_logged_in()) {
    $current_user = wp_get_current_user();
}
$appliedCoupons = WC()->cart->get_applied_coupons();
$allCoupons = get_posts(
    array(
        'posts_per_page' => -1,
        'orderby' => 'name',
        'order' => 'asc',
        'post_type' => 'shop_coupon',
        'post_status' => 'publish',
    )
);
?>
<div id="skyfinityqc_checkout_modal" class="skyfinityqc-skyfinity-quick-modal">
    <div class="skyfinityqc-modal-content">
        <div class="skyfinityqc-model-bg-wrap"></div>
        <div class="skyfinityqc-modal-left-block-wrap">
            <div id="skyfinityqc_popup_loader">
                <svg version="1.1" id="L2" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                    x="0px" y="0px" viewBox="0 0 100 100" enable-background="new 0 0 100 100" xml:space="preserve">
                    <circle fill="currntColor" stroke="#fff" stroke-width="4" stroke-miterlimit="10" cx="50" cy="50"
                        r="48" />
                    <line fill="none" stroke-linecap="round" stroke="#fff" stroke-width="4" stroke-miterlimit="10"
                        x1="50" y1="50" x2="85" y2="50.5">
                        <animateTransform attributeName="transform" dur="2s" type="rotate" from="0 50 50" to="360 50 50"
                            repeatCount="indefinite" />
                    </line>
                    <line fill="none" stroke-linecap="round" stroke="#fff" stroke-width="4" stroke-miterlimit="10"
                        x1="50" y1="50" x2="49.5" y2="74">
                        <animateTransform attributeName="transform" dur="15s" type="rotate" from="0 50 50"
                            to="360 50 50" repeatCount="indefinite" />
                    </line>
                </svg>
            </div>
            <div class="skyfinityqc-modal-left-block-header ">
                <h5>
                <?php 
                    $site_title = get_bloginfo('name');
                    echo esc_html($site_title !== '' ? $site_title : __('Skyfinity Quick Checkout', 'skyfinity-quick-checkout'));
                ?>
                </h5>
                <p class="color-white skyfinityqc_language_selector_wrap">
                    <select class="skyfinityqc_language_selector">
                        <option value="en">English</option>
                    </select>
                </p>
                <span class="mobile-popup-close popup-close close" title="Close">&times;</span>
            </div>
            <div class="skyfinityqc-modal-header">
                <!-- Form Steps / Progress Bar -->
                <!-- <div class=" d-flex"> -->
                    <!-- <button type="button" class="d-inline-block transparent-btn color-white step-back-button">
                        <svg xmlns="http://www.w3.org/2000/svg" height="48" viewBox="0 96 960 960" width="48"
                            fill="inherit">
                            <path d="M480 896 160 576l320-320 42 42-248 248h526v60H274l248 248-42 42Z" />
                            <div xmlns="" id="divScriptsUsed" style="display: none" />
                        </svg>
                    </button> -->
                    <ul class="form-stepper form-stepper-horizontal mx-auto pl-0">
                        <?php
                        foreach (skyfinityqc_get_checkout_steps() as $step) {
                            ?>
                            <li class="<?php echo $step['step_no'] == '1' ? 'form-stepper-active' : 'form-stepper-unfinished'; ?> form-stepper-list"
                                step="<?php echo esc_attr($step['step_no']); ?>">
                                <a class="mx-2">
                                    <span class="label">
                                        <?php echo esc_attr('0'.$step['step_no']); ?>
                                        <span class="step-name-label">
                                            <?php echo esc_html($step['step_name']); ?>
                                        </span>
                                    </span>
                                </a>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                <!-- </div> -->
               
            </div>
            <div class="skyfinity-quick-popup-container">
                
                <div class="" id="skyfinity-quick-step-form-container" data-currentStep="1">
                    <!-- Step Wise Form Content -->
                    <form id="userAccountSetupForm" name="userAccountSetupForm" enctype="multipart/form-data" method="POST">
                        <?php
                        $all_steps = skyfinityqc_get_checkout_steps();
                        foreach ($all_steps as $key => $step) {
                            ?>
                            <section id="step-<?php echo esc_attr($step['step_no']); ?>"
                                class="form-step <?php echo $step['step_no'] == '1' ? '' : 'd-none'; ?>">
                                <?php // do_action(`skyfinity_quick_checkout_step_{$step['slug']}`); ?>
                                <?php get_skyfinityqc_step_content($step['step_no']); ?>
                            </section>
                            <?php
                        }
                        ?>
                    </form>
                </div>
            </div>
        </div>
        <div class="skyfinityqc-modal-right-block-wrap">
            <div id="skyfinityqc_popup_loader_right"></div>
            <div class="right-wrap">
                <h3 class="order_summary_title"><?php echo __("Order Summary", "skyfinity-quick-checkout"); ?> <span class="popup-close close" title="Close">&times;</span></h3>
            </div>
            <div class="confirm-close-quick-checkout">
                <h6>Cancel order?</h6>
                <p>All progress will be lost and the order will not be completed.</p>
                <div class="close-quick-checkout-action">
                    <a href="#" class="confirm-yes transparentbutton">Yes, cancel</a>
                    <a href="#" class="confirm-no">No</a>
                </div>
            </div>
            <div class="cart-summary"></div>
            
        </div>
        <div class="thankyou-screen">
            <?php echo __('Thank you for your Order, you will receive order information in your registred email address. Redirecting...'); ?>
        </div>
    </div>
</div>