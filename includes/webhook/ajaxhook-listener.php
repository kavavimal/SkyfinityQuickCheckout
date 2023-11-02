<?php

defined('ABSPATH') || exit;

/**
 * Apply given coupon to checkout
 */
function skyfinityqc_apply_coupon_cb()
{
    $code = sanitize_text_field($_POST['code']);
    if ($code) {
        $coupon_id = wc_get_coupon_id_by_code($code);

        if (!$coupon_id) {
            $result = array(
                'succeed' => false,
                'Message' => __("No coupon code was found", "skyfinity_quick_checkout"),
            );
        } else if (WC()->cart->has_discount($code)) {
            $result = array(
                'success' => false,
                'Message' => __("Discount is already applied.", "skyfinity_quick_checkout"),
            );
        } else {
            WC()->cart->apply_coupon($code);
            $result = array(
                'success' => true,
                'Message' => __("Coupon applied successfully.", "skyfinity_quick_checkout"),
            );
        }
    } else {
        $result = array(
            'success' => false,
            'Message' => "coupon not applied.",
        );
    }
    echo json_encode($result);
    die();
}

add_action("wp_ajax_skyfinityqc_apply_coupon", "skyfinityqc_apply_coupon_cb");
add_action("wp_ajax_nopriv_skyfinityqc_apply_coupon", "skyfinityqc_apply_coupon_cb");
function skyfinityqc_remove_coupon_cb()
{
    $code = sanitize_text_field($_POST['code']);
    if ($code) {
        $coupon_id = wc_get_coupon_id_by_code($code);

        if (!$coupon_id) {
            $result = array(
                'succeed' => false,
                'Message' => __("No coupon code was found", "skyfinity_quick_checkout"),
            );
        } else if (WC()->cart->has_discount($code)) {
            WC()->cart->remove_coupon($code);
            WC()->cart->calculate_totals();
            $result = array(
                'success' => true,
                'Message' => __("Coupon Removed successfully from your cart.", "skyfinity_quick_checkout"),
            );
        } else {
            $result = array(
                'success' => false,
                'Message' => __("Coupon Not applied to your cart.", "skyfinity_quick_checkout"),
            );
        }
    } else {
        $result = array(
            'success' => false,
            'Message' => "coupon not applied to your cart.",
        );
    }
    echo json_encode($result);
    die();
}

add_action("wp_ajax_skyfinityqc_remove_coupon", "skyfinityqc_remove_coupon_cb");
add_action("wp_ajax_nopriv_skyfinityqc_remove_coupon", "skyfinityqc_remove_coupon_cb");

function skyfinityqc_get_coupons_list_content_cb()
{
    ob_start();
    include(SKYFINITYQC_PATH . 'includes/views/coupons-list.php');
    $html_content = ob_get_clean();

    echo json_encode(array('success' => true, 'html_content' => $html_content));
    die();
}
add_action("wp_ajax_skyfinityqc_get_coupons_list_content", "skyfinityqc_get_coupons_list_content_cb");
add_action("wp_ajax_nopriv_skyfinityqc_get_coupons_list_content", "skyfinityqc_get_coupons_list_content_cb");

function skyfinityqc_get_cart_contents_cb()
{
    ob_start();
    include(SKYFINITYQC_PATH . 'includes/views/cart-summary.php');
    $html_content = ob_get_clean();

    echo json_encode(array('success' => true, 'html_content' => $html_content));
    die();
}

add_action("wp_ajax_skyfinityqc_get_cart_contents", "skyfinityqc_get_cart_contents_cb");
add_action("wp_ajax_nopriv_skyfinityqc_get_cart_contents", "skyfinityqc_get_cart_contents_cb");


function skyfinityqc_get_payment_list_cb()
{
    ob_start();
    include(SKYFINITYQC_PATH . 'includes/views/payment-list.php');
    $html_content = ob_get_clean();

    echo json_encode(array('success' => true, 'html_content' => $html_content));
    die();
}

add_action("wp_ajax_skyfinityqc_get_payment_list", "skyfinityqc_get_payment_list_cb");
add_action("wp_ajax_nopriv_skyfinityqc_get_payment_list", "skyfinityqc_get_payment_list_cb");



function skyfinityqc_get_summary_page_content_cb()
{
    ob_start();
    include(SKYFINITYQC_PATH . 'includes/views/skyfinity-quick-popup-summary.php');
    $html_content = ob_get_clean();

    echo json_encode(array('success' => true, 'html_content' => $html_content));
    die();
}

add_action("wp_ajax_skyfinityqc_get_summary_page_content", "skyfinityqc_get_summary_page_content_cb");
add_action("wp_ajax_nopriv_skyfinityqc_get_summary_page_content", "skyfinityqc_get_summary_page_content_cb");

add_action('wp_ajax_skyfinityqc_wc_ajax_add_to_cart', 'skyfinityqc_wc_ajax_add_to_cart'); 
add_action('wp_ajax_nopriv_skyfinityqc_wc_ajax_add_to_cart', 'skyfinityqc_wc_ajax_add_to_cart');          
function skyfinityqc_wc_ajax_add_to_cart() {  
    $product_id = absint(sanitize_text_field($_POST['product_id']));
    $quantity = empty(sanitize_text_field($_POST['quantity'])) ? 1 : wc_stock_amount(sanitize_text_field($_POST['quantity']));
    $variation_id = absint(sanitize_text_field($_POST['variation_id']));
    $product_status = get_post_status($product_id); 
    if ('publish' === $product_status && WC()->cart->add_to_cart($product_id, $quantity, $variation_id)) { 
        WC_AJAX::get_refreshed_fragments(); 
    } else { 
        $data = array( 'error' => true);
        echo wp_send_json($data);
    }
    wp_die();
}