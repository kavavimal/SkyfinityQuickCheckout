<?php

defined('ABSPATH') || exit;

$active_theme_colors = skyfinityqc_get_active_theme_colors();
global $woocommerce;
$currency = get_woocommerce_currency();
$available_active_payment_methods = $available_active_cod_payment_methods = array();

$payment_options = get_option('skyfinity_quick_payment_setting');
if (isset($payment_options['payment_method_cod']) && $payment_options['payment_method_cod'] == true) {
    $available_active_cod_payment_methods[] = array(
        'id' => 'COD',
        'display_name' => __("Cash On Delivery", "skyfinity-quick-checkout"),
        'image_url' => SKYFINITYQC_URL . 'assets/images/cod.svg',
        'description' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centurie"
    );
}

$current_user = '';
$phoneNumber = '';
$email = '';
if (is_user_logged_in()) {
    $current_user = wp_get_current_user();
    $phoneNumber = get_user_meta($current_user->ID, 'billing_phone', true);
    $email = $current_user->user_email;
}

if (isset($payment_options['payment_method_razor_pay']) && $payment_options['payment_method_razor_pay'] == true) {
    $available_active_payment_methods[] = array(
        'id' => 'razor_pay',
        'display_name' => __("Razor Pay", "skyfinity-quick-checkout"),
        'image_url' => SKYFINITYQC_URL . 'assets/images/razorpay.svg',
        'description' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centurie"
    );
}
$payment_method_list = apply_filters('skyfinityqc_payment_method_list', $available_active_payment_methods);

if (isset($payment_method_list) && count($payment_method_list) > 0) {
?>
    <h6 class="skyfinityqc-payment-heading">
        <?php echo __("Pay using Debit Card/ Credit Card/ UPI, ..etc", 'skyfinity-quick-checkout'); ?>
    </h6>
    <div class="skyfinityqc-payment-method-list-group">
        <?php
        foreach ($payment_method_list as $key => $payment_method) { ?>
            <div class="payment-method-item-wrap" data-payment-method="<?php echo esc_attr($payment_method['id']); ?>">
                <label for="payment-method-<?php echo esc_attr($payment_method['id']); ?>" class="payment-method-item">
                    <input type="radio" id="payment-method-<?php echo esc_attr($payment_method['id']); ?>" name="payment_method" value="<?php echo esc_attr($payment_method['id']); ?>" />
                    <div class="payment-image">
                        <img src="<?php echo esc_attr($payment_method['image_url']); ?>" alt="<?php echo esc_attr($payment_method['display_name']); ?>" />
                    </div>
                    <div class="name">
                        <?php echo esc_html($payment_method['display_name']); ?>
                    </div>
                    <!-- <button type="button" class="arrow-btn payment-btn"><span class="arrow right"></span></button> -->
                    <p class="arrow-btn payment-btn"><span class="arrow right"></span></p>
                </label>
                <div class="payment-description">
                    <?php // echo $payment_method['description']; 
                    ?>
                </div>
                <?php echo esc_html(isset($payment_method['extra_details']) ? $payment_method['extra_details'] : ''); ?>
            </div>
        <?php
        }
        ?>
    </div>
<?php
}


$payment_method_cod_list = apply_filters('skyfinityqc_payment_method_cod_list', $available_active_cod_payment_methods);

if (isset($payment_method_cod_list) && count($payment_method_cod_list) > 0) {
?>
    <h6 class="skyfinityqc-payment-heading">
        <?php echo __("Cash On Delivery", 'skyfinity-quick-checkout'); ?>
    </h6>
    <div class="skyfinityqc-payment-method-list-group">
        <?php
        foreach ($payment_method_cod_list as $key => $payment_method) { ?>
            <div class="payment-method-item-wrap" data-payment-method="<?php echo esc_attr($payment_method['id']); ?>">
                <label for="payment-method-<?php echo esc_attr($payment_method['id']); ?>" class="payment-method-item">
                    <input type="radio" id="payment-method-<?php echo esc_attr($payment_method['id']); ?>" name="payment_method" value="<?php echo esc_attr($payment_method['id']); ?>" />
                    <div class="payment-image">
                        <img src="<?php echo esc_attr($payment_method['image_url']); ?>" alt="<?php echo esc_attr($payment_method['display_name']); ?>" />
                    </div>
                    <div class="name">
                        <?php echo esc_html($payment_method['display_name']); ?>
                    </div>
                    <!-- <button type="button" class="arrow-btn payment-btn" ><span class="arrow right"></span></button> -->
                    <p class="arrow-btn payment-btn"><span class="arrow right"></span></p>
                </label>
                <div class="payment-description">
                    <?php //echo $payment_method['description']; 
                    ?>
                </div>
                <?php echo esc_html(isset($payment_method['extra_details']) ? $payment_method['extra_details'] : ''); ?>
            </div>
        <?php
        }
        ?>
    </div>
<?php
}

do_action('skyfinityqc_after_payment_method');
?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script type="text/javascript">
    function do_checkout_on_payments_complete(paymentRes) {
        const instruction = jQuery('#instruction').val();
        jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').addClass('show');
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: request_globals.request_url,
            data: {
                action: "skyfinityqc_create_order_after_razorpay_order",
                paymentResponse: paymentRes,
                instruction: instruction
            },
            success: function(response) {
                if (response.success == true) {
                    if (response.thankyou_url) {
                        window.location.replace(response.thankyou_url);
                    } else {
                        jQuery('.skyfinity-quick-popup-container').hide();
                        jQuery('.thankyou-screen').show();
                        jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    }
                } else {
                    jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                }
            }
        });
    }

    function setPaymentMethodInCart(payment_method) {
        jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').addClass('show');
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: request_globals.request_url,
            data: {
                action: "skyfinityqc_change_payment_method_selected",
                payment_method: payment_method,
            },
            success: function(response) {
                // payment method selected
                jQuery(document.body).trigger('wc_fragment_refresh');
                jQuery("body").trigger("update_checkout");

                jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
            },
            error: function(error) {
                jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
            }
        });
    }

    jQuery('input[type=radio][name="payment_method"]').click(async function(e) {
        let selectedPayment = jQuery('input[name="payment_method"]:checked');
        const payment_method = jQuery(selectedPayment).val();
        if ("COD" === payment_method) {
            jQuery('#continue-with-cod').show();
            jQuery('.payment-footer-line').hide();
            setPaymentMethodInCart("cod");
        } else {
            jQuery('#continue-with-cod').hide();
            jQuery('.payment-footer-line').show();
            setPaymentMethodInCart(payment_method);
            setTimeout(() => {
                next_action_for_payment_method();
            }, 1000);
        }
    });

    jQuery(document).on('click', '#continue-with-cod', function(e) {
        next_action_for_payment_method();
    });

    function next_action_for_payment_method() {
        let selectedPayment = jQuery('input[name="payment_method"]:checked');
        const payment_method = jQuery(selectedPayment).val();
        if ("COD" === payment_method) {
            let paymentRes = {};
            paymentRes.payment_method = "cod";
            paymentRes.payment_method_display_name = "Cash On Delivery";
            do_checkout_on_payments_complete(paymentRes);
        } else if ("razor_pay" === payment_method) {
            create_razor_pay_order_and_process();
        } else {
            if (typeof window['payment_method_' + payment_method + '_cb'] == 'function') {
                window['payment_method_' + payment_method + '_cb']();
            }
        }
    }

    function create_razor_pay_order_and_process() {
        jQuery("#payment-method-error").text('');
        jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').addClass('show');
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: request_globals.request_url,
            data: {
                action: "skyfinityqc_create_razorpay_order",
                amount: "<?php echo $woocommerce->cart->total; ?>",
                currency: "<?php echo $currency; ?>",
            },
            success: function(response) {
                if (response.success == true) {
                    var options = {
                        "key": "<?php echo $payment_options['rp_stagging_api_key']; ?>", // Enter the Key ID generated from the Dashboard
                        "amount": "<?php echo $woocommerce->cart->total; ?>", // Amount is in currency subunits. Default currency is INR. Hence, 50000 refers to 50000 paise
                        "currency": "<?php echo $currency; ?>",
                        "name": "skyfinityqc Checkout",
                        // "description": "Test Transaction",
                        // "image": "https://example.com/your_logo",
                        "order_id": response?.razorpayOrder?.order_id, // "order_IluGWxBm9U8zJ8", //This is a sample Order ID. Pass the `id` obtained in the response of Step 1
                        "handler": function(response) {
                            let paymentRes = response;
                            paymentRes.payment_method = 'razor_pay';
                            paymentRes.payment_method_display_name = 'Razor Pay';
                            do_checkout_on_payments_complete(paymentRes);
                            // alert(response.razorpay_payment_id);
                            // alert(response.razorpay_order_id);
                            // alert(response.razorpay_signature)
                        },
                        "prefill": {
                            // "name": "",
                            "email": "<?php echo $email; ?>",
                            "contact": "<?php echo $phoneNumber; ?>"
                        },
                        // "notes": {
                        //     "address": "Razorpay Corporate Office"
                        // },
                        "theme": {
                            "color": "<?php echo $active_theme_colors['color-primary']; ?>"
                        }
                    };
                    var rzp1 = new Razorpay(options);
                    rzp1.on('payment.failed', function(response) {
                        console.log(response.error.code);
                        console.log(response.error.description);
                        console.log(response.error.source);
                        console.log(response.error.step);
                        console.log(response.error.reason);
                        console.log(response.error.metadata.order_id);
                        console.log(response.error.metadata.payment_id);
                        jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                    });
                    jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                    rzp1.open();
                } else {
                    console.log('error', response);
                    jQuery('#skyfinityqc_popup_loader, #skyfinityqc_popup_loader_right').removeClass('show');
                    jQuery("#payment-method-error").text(response.Message + ' ' + response.error);
                }
            }
        });
    }
</script>
<?php
