<?php

defined('ABSPATH') || exit;

use Razorpay\Api\Api;

/**
 * Create order to razorpay payment
 */
function skyfinityqc_create_razorpay_order_cb()
{
    try {
        $amount = sanitize_text_field($_POST['amount']);
        $currency = sanitize_text_field($_POST['currency']);

        if ($amount) {
            $orderData = [
                'receipt' => 'skyfinityqcid_'.time(),
                'amount' => $amount * 100,
                'currency' => $currency
            ];
            $paymentOptions = get_option('skyfinity_quick_payment_setting');

            $api_key = '';
            $secret_key = '';
            if(isset($paymentOptions['payment_method_razor_pay_test_mode']) && $paymentOptions['payment_method_razor_pay_test_mode'] == '1') {
                $api_key = $paymentOptions['rp_stagging_api_key'];
                $secret_key = $paymentOptions['rp_stagging_secret_key'];
            } else {
                $api_key = $paymentOptions['rp_live_api_key'];
                $secret_key = $paymentOptions['rp_live_secret_key'];
            }
            

            $api = new Api($api_key, $secret_key);

            $razorpayOrder = $api->order->create($orderData);
            $orderData = [
                'order_id' => $razorpayOrder->id,
                'amount' => $razorpayOrder->amount,
                'amount_paid' => $razorpayOrder->amount_paid,
                'currency' => $razorpayOrder->currency,
                'status' => $razorpayOrder->status,
                'notes' => $razorpayOrder->notes,
            ];
            $result = array(
                'success' => true,
                'Message' => "Order Created successfully.",
                'razorpayOrder' => $orderData,
            );
        } else {
            $result = array(
                'success' => false,
                'Message' => "amount not provided.",
            );
        }
    } catch (Exception $e) {
        $result = array(
            'success' => false,
            'Message' => "Error in processing payment.",
            'error' => $e->getMessage()
        );
    }
    echo json_encode($result);
    die();
}

add_action("wp_ajax_skyfinityqc_create_razorpay_order", "skyfinityqc_create_razorpay_order_cb");

/**
 * process payment and save order to woocommerce after payment callback  
 */
function skyfinityqc_create_order_after_razorpay_order_cb()
{
    $current_user = wp_get_current_user();
    try {
        $paymentResponse = sanitize_text_field($_POST['paymentResponse']);
        
        if ($paymentResponse) {
            $cart = WC()->cart;
            $checkout = WC()->checkout();
            $order_id = $checkout->create_order(array());
            $order = wc_get_order($order_id);
            $address = array(
                'first_name' => get_user_meta($current_user->ID, 'billing_first_name', true),
                'last_name' => get_user_meta($current_user->ID, 'billing_last_name', true),
                'company' => get_user_meta($current_user->ID, 'billing_company', true),
                'email' => get_user_meta($current_user->ID, 'billing_email', true),
                'phone' => get_user_meta($current_user->ID, 'billing_phone', true),
                'address_1' => get_user_meta($current_user->ID, 'billing_address_1', true),
                'address_2' => get_user_meta($current_user->ID, 'billing_address_2', true),
                'city' => get_user_meta($current_user->ID, 'billing_city', true),
                'state' => get_user_meta($current_user->ID, 'billing_state', true),
                'postcode' => get_user_meta($current_user->ID, 'billing_postcode', true),
                'country' => get_user_meta($current_user->ID, 'billing_country', true),
            );
            $shipping_address = array(
                'first_name' => get_user_meta($current_user->ID, 'shipping_first_name', true),
                'last_name' => get_user_meta($current_user->ID, 'shipping_last_name', true),
                'company' => get_user_meta($current_user->ID, 'shipping_company', true),
                'email' => get_user_meta($current_user->ID, 'shipping_email', true),
                'phone' => get_user_meta($current_user->ID, 'shipping_phone', true),
                'address_1' => get_user_meta($current_user->ID, 'shipping_address_1', true),
                'address_2' => get_user_meta($current_user->ID, 'shipping_address_2', true),
                'city' => get_user_meta($current_user->ID, 'shipping_city', true),
                'state' => get_user_meta($current_user->ID, 'shipping_state', true),
                'postcode' => get_user_meta($current_user->ID, 'shipping_postcode', true),
                'country' => get_user_meta($current_user->ID, 'shipping_country', true),
            );
            $order->set_address($address, 'billing');
            $order->set_address($shipping_address, 'shipping');           
            
            $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');
            if ($chosen_shipping_methods) {
                $shipping = new WC_Order_Item_Shipping();
                if (count($chosen_shipping_methods) > 0 && isset($chosen_shipping_methods[0])) {
                    $shipping->set_method_id($chosen_shipping_methods[0]); // set an existing Shipping method ID
                    $order->add_item($shipping);
                }
            }
            update_post_meta($order_id, '_customer_user', get_current_user_id());
            if(isset($_POST['instruction']) && $_POST['instruction'] != '') {
                update_post_meta($order_id, 'instruction', sanitize_text_field($_POST['instruction']));
            }
            $order->set_payment_method($paymentResponse['payment_method']);
            $order->set_payment_method_title($paymentResponse['payment_method_display_name']);
            if (isset($paymentResponse['transaction_id']) && $paymentResponse['transaction_id'] !== '') {
                $order->set_transaction_id($paymentResponse['transaction_id']);
            } else if ($paymentResponse['payment_method'] == 'razor_pay') {
                $order->set_transaction_id($paymentResponse['razorpay_payment_id']);
                // alert(response.razorpay_payment_id);
                // alert(response.razorpay_order_id);
                // alert(response.razorpay_signature);
            } else if($paymentResponse['payment_method'] == 'cashfree') {
                $order->set_transaction_id($paymentResponse['transaction']['transactionId']);
            } else if($paymentResponse['payment_method'] == 'paypal') {
                $order->set_transaction_id($paymentResponse['id']);
            } else if($paymentResponse['payment_method'] == 'cod') {
                $payment_options = get_option('skyfinity_quick_payment_setting');
                if (isset($payment_options['cod_fee']) && $payment_options['cod_fee'] != '') {
                    // create Fee object
                    $fee = new WC_Order_Item_Fee();
                    $fee->set_name( 'COD fee' );
                    $fee->set_amount( $payment_options['cod_fee'] );
                    $fee->set_total( $payment_options['cod_fee'] );
                    
                    $order->add_item( $fee );
                    $order->calculate_totals();
                }
            }
            // $order->calculate_totals();
            $order->payment_complete();
            $order->save();

            $settings = get_option('skyfinity_quick_checkout_setting');
            do_action('skyfinityqc_on_order_save',$order, $address, $shipping_address);
            $cart->empty_cart();
            
            // Get the thank-you page URL
            $thankyou_url = $order->get_checkout_order_received_url();

            $result = array(
                'success' => true,
                'Message' => "Order Created successfully.",
                'thankyou_url' => $thankyou_url,
            );
        } else {
            $result = array(
                'success' => false,
                'Message' => "paymentResponse Not received.",
            );
        }
    } catch (Exception $e) {
        $result = array(
            'success' => false,
            'Message' => "Error in creating Order.",
            'error' => $e->getMessage()
        );
    }
    echo json_encode($result);
    die();
}

add_action("wp_ajax_skyfinityqc_create_order_after_razorpay_order", "skyfinityqc_create_order_after_razorpay_order_cb");
function skyfinityqc_change_payment_method_selected_cb() {
    $targetPaymentMethod = sanitize_text_field($_POST['payment_method']);
    try {
        // Set the chosen payment method for the cart
        WC()->session->set('chosen_payment_method', $targetPaymentMethod);
        // Recalculate cart totals to reflect the new payment method
        do_action("woocommerce_cart_calculate_fees");
        WC()->cart->calculate_totals();

        $result = array(
            'success' => true,
            'Message' => "payment method selected.",
            'cart_fee' => WC()->cart->get_fees(),
        );
    } catch (Exception $e) {
        $result = array(
            'success' => false,
            'Message' => "Error in selecting payment method.",
            'error' => $e->getMessage()
        );
    }
    echo json_encode($result);
    die();
}
add_action("wp_ajax_skyfinityqc_change_payment_method_selected", "skyfinityqc_change_payment_method_selected_cb");

add_action( 'woocommerce_cart_calculate_fees', 'skyfinityqc_wc_add_cart_fees_by_payment_gateway' );
if ( ! function_exists( 'skyfinityqc_wc_add_cart_fees_by_payment_gateway' ) ) {
    /**
     * skyfinityqc_wc_add_cart_fees_by_payment_gateway.
     */
    function skyfinityqc_wc_add_cart_fees_by_payment_gateway( $cart ) {
        $payment_options = get_option('skyfinity_quick_payment_setting');
        // You need to enter your fees here, in `payment gateway` => `fee amount` format
        $fees = array(
            'cod'    => $payment_options['cod_fee'],
        );
        // Getting current chosen payment gateway
        $chosen_payment_method = false;
        $available_gateways    = WC()->payment_gateways->get_available_payment_gateways();
        if ( isset( WC()->session->chosen_payment_method ) ) {
            $chosen_payment_method = WC()->session->chosen_payment_method;
        } elseif ( ! empty( sanitize_text_field($_REQUEST['payment_method']) ) ) {
            $chosen_payment_method = sanitize_key( sanitize_text_field($_REQUEST['payment_method']) );
        } elseif ( '' != ( $default_gateway = get_option( 'woocommerce_default_gateway' ) ) ) {
            $chosen_payment_method = $default_gateway;
        } elseif ( ! empty( $available_gateways ) ) {
            $chosen_payment_method = current( array_keys( $available_gateways ) );
        }
        // if ( ! isset( $available_gateways[ $chosen_payment_method ] ) ) {
        //     $chosen_payment_method = false;
        // }
        // Applying fee (maybe)
        if ( $chosen_payment_method && ! empty( $fees[ $chosen_payment_method ] ) ) {
            
            $name      = 'COD Charges (' . $chosen_payment_method . ')';
            $amount    = $fees[ $chosen_payment_method ];
            $taxable   = true;
            $tax_class = '';
            WC()->cart->add_fee( $name, $amount, $taxable, $tax_class );
        } else {
            $fees = WC()->cart->get_fees();
            foreach ($fees as $key => $fee) {
                if($fees[$key]->name === 'COD Charges (' . $chosen_payment_method . ')') {
                    unset($fees[$key]);
                }
            }
            WC()->cart->fees_api()->set_fees($fees);
        }

    }
}