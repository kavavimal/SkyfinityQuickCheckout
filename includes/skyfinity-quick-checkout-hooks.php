<?php
defined('ABSPATH') || exit;
use Razorpay\Api\Api;

/** 
 * Ajax callback function for check requested user authentication.
 * */
function skyfinityqc_truecaller_auth_check_cb()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'sqckt_trueauth';
    $requestId = sanitize_text_field($_REQUEST['requestId']);
    $results = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE requestId=" . $requestId));
    if (isset($results) && !empty($results)) {
        $foundRes = json_decode($results->accessToken);
        $profile = json_decode($results->profile);
        $user_id = 0;

        // create user account if email is present
        if (isset($profile->onlineIdentities->email) && $profile->onlineIdentities->email != '') {
            $user_name = $profile->onlineIdentities->email;
            $user_id = username_exists($user_name);

            if (!$user_id && false == email_exists($user_name)) {
                $first_name = $profile->name->first ? $profile->name->first : '';
                $last_name = $profile->name->last ? $profile->name->last : '';
                $random_password = wp_generate_password(12, false);
                $user_id = wc_create_new_customer(
                    $user_name,
                    $user_name,
                    $random_password,
                    array(
                        'meta_input' => array(
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'display_name' => $first_name . ' ' . $last_name,
                            'role ' => 'customer',
                            'billing_phone' => isset($profile->phoneNumbers[0]) ? $profile->phoneNumbers[0] : '',
                        )
                    )
                );
                wp_mail($user_name, 'Welcome!', 'Your password is: ' . $random_password);
                function skyfinityqc_update_wc_user_fields($id, $args = array())
                {
                    if (!empty($args)) {
                        foreach ($args as $key => $value) {
                            update_user_meta($id, $key, $value);
                        }
                    }
                }
                $address = $profile->addresses[0];
                $args = array(
                    'billing_address_1' => isset($address->street) ? $address->street : '',
                    // 'billing_address_2' => '',
                    'billing_city' => isset($address->city) ? $address->city : '',
                    // 'billing_company' => '',
                    'billing_country' => isset($address->countryCode) ? $address->countryCode : '',
                    'billing_email' => $user_name,
                    'billing_first_name' => $first_name,
                    'billing_last_name' => $last_name,
                    'billing_phone' => isset($profile->phoneNumbers[0]) ? $profile->phoneNumbers[0] : '',
                    'billing_postcode' => isset($address->zipcode) ? $address->zipcode : '',
                    // 'billing_state' => '',

                    'shipping_address_1' => isset($address->street) ? $address->street : '',
                    // 'shipping_address_2' => '',
                    'shipping_city' => isset($address->city) ? $address->city : '',
                    // 'shipping_company' => '',
                    'shipping_country' => isset($address->countryCode) ? $address->countryCode : '',
                    'shipping_email' => $user_name,
                    'shipping_first_name' => $first_name,
                    'shipping_last_name' => $last_name,
                    'shipping_postcode' => isset($address->zipcode) ? $address->zipcode : '',
                    // 'shipping_state' => '',
                );

                skyfinityqc_update_wc_user_fields($user_id, $args);

                // $user_id = wp_create_user( $user_name, $random_password, $user_name );
            } else {
                $random_password = __('User already exists.  Password inherited.', 'textdomain');
            }
        }
        if ($user_id !== 0) {
            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id);
        }
        $result = json_encode(
            array(
                'success' => true,
                'message' => 'get data success',
                'accessToken' => $foundRes,
                'profile' => $profile,
                'user_id' => $user_id
            )
        );
        echo $result;
        die();
    } else {
        $result = json_encode(array('error' => true, 'message' => 'Not Found'));
        echo $result;
        die();
    }

}
add_action("wp_ajax_skyfinityqc_truecaller_auth_check", "skyfinityqc_truecaller_auth_check_cb");
add_action("wp_ajax_nopriv_skyfinityqc_truecaller_auth_check", "skyfinityqc_truecaller_auth_check_cb");

function skyfinityqc_update_wc_user_fields($id, $args = array())
{
    if (!empty($args)) {
        foreach ($args as $key => $value) {
            update_user_meta($id, $key, $value);
        }
    }
}

/** 
 * Popup content container function
 * */
function skyfinityqc_skyfinity_quick_checkout_popup()
{
    global $wp;
    $optionsValues = get_option('skyfinity_quick_checkout_setting');
    $current_url = home_url($wp->request);
    $uniqid = time();
    $redirect_current_url = add_query_arg(array('true_auth' => true, 'requestId' => $uniqid), $current_url);
    include(SKYFINITYQC_PATH . 'includes/views/skyfinity-quick-popup-content.php');
    
    include(SKYFINITYQC_PATH . 'includes/views/skyfinity-quick-popup-script.php');
    // Register your script (replace 'skyfinityqc_scripts' with a unique handle).
    // wp_register_script('skyfinityqc_scripts', SKYFINITYQC_PATH . 'includes/views/skyfinity-quick-popup-script.php', array(), null, true);
    // Enqueue the registered script.
    // wp_enqueue_script('skyfinityqc_scripts');

    include(SKYFINITYQC_PATH . 'includes/views/skyfinity-intl-phone-number-script.php');
}
add_action('wp_footer', 'skyfinityqc_skyfinity_quick_checkout_popup');

/**
 * Get Steps List
 */
function skyfinityqc_get_checkout_steps()
{
    $skyfinity_quick_checkout_steps = array(
        array('step_no' => '1', 'slug', 'summary', 'step_name' => 'Summary'),
        array('step_no' => '2', 'slug', 'address', 'step_name' => 'Address'),
        array('step_no' => '3', 'slug', 'payment', 'step_name' => 'Payment')
    );

    return apply_filters('skyfinityqc_checkout_steps', $skyfinity_quick_checkout_steps);
}

/**
 * Add Theme variables as per set on settings page
 */
function skyfinityqc_get_active_theme_colors()
{
    $optionsValues = get_option('skyfinity_quick_checkout_setting');
    $theme_colors = skyfinityqc_get_theme_colors();
    if (isset($theme_colors) && count($theme_colors) > 0 && isset($optionsValues['view_theme']) && isset($theme_colors[$optionsValues['view_theme']])) {
        $active_theme_colors = $theme_colors[$optionsValues['view_theme']];
    } else if (isset($optionsValues['view_theme']) && $optionsValues['view_theme'] == 'custom') {
        $active_theme_colors = $optionsValues['custom_theme'];
    } else {
        $active_theme_colors = array(
            'color-primary' => '#d43f59',
            'color-secondary' => '#2a2742'
        );
    }
    return $active_theme_colors;
}
function skyfinityqc_add_preset_style_variables()
{
    $optionsValues = get_option('skyfinity_quick_checkout_setting');
    $active_theme_colors = skyfinityqc_get_active_theme_colors();
    ?>
    <style id="skyfinityqc_theme_variables">
        :root {
            --skyfinityqc-theme:
                <?php echo $optionsValues['view_theme']; ?>
            ;
            <?php
            foreach ($active_theme_colors as $color_name => $color_code) {
                echo '--skyfinityqc-' . $color_name . ': ' . $color_code . ';';
            }
            ?>
        }
    </style>
    <?php
}
add_action('wp_head', 'skyfinityqc_add_preset_style_variables');

function skyfinityqc_modal_total_content_summary_fragment( $fragments ) {
    ob_start();
    ?>
    <div id="skyfinityqc-modal-total-content-summary">
        <?php echo WC()->cart->get_total(); ?>
    </div>
    <?php

    $fragments['#skyfinityqc-modal-total-content-summary'] = ob_get_clean();
    return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'skyfinityqc_modal_total_content_summary_fragment');

function skyfinityqc_modal_total_content_address_fragment( $fragments ) {
    ob_start();
    ?>
    <div id="skyfinityqc-modal-total-content-address">
        <?php echo WC()->cart->get_total(); ?>
    </div>
    <?php

    $fragments['#skyfinityqc-modal-total-content-address'] = ob_get_clean();
    return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'skyfinityqc_modal_total_content_address_fragment');

function skyfinityqc_modal_total_content_payment_fragment( $fragments ) {
    ob_start();
    ?>
    <div id="skyfinityqc-modal-total-content-payment">
        <?php echo WC()->cart->get_total(); ?>
    </div>
    <?php

    $fragments['#skyfinityqc-modal-total-content-payment'] = ob_get_clean();
    return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'skyfinityqc_modal_total_content_payment_fragment');

function skyfinityqc_modal_order_summary_content_fragment( $fragments ) {
    ob_start();
    include(SKYFINITYQC_PATH . 'includes/views/cart-summary.php');
    $fragments['#skyfinityqc-modal-order-summary-content'] = ob_get_clean();
    return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'skyfinityqc_modal_order_summary_content_fragment');

function get_skyfinityqc_step_content($step)
{
    $current_user = '';
    $phoneNumber = '';
    $email = '';
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $phoneNumber = get_user_meta($current_user->ID, 'billing_phone', true);
        $email = $current_user->user_email;
    }

    switch ($step) {
        case '1':
            include(SKYFINITYQC_PATH . 'includes/views/skyfinity-quick-popup-summary.php');
            break;
        case '2':
            include(SKYFINITYQC_PATH . 'includes/views/skyfinity-quick-popup-address.php');
            break;
        case '3':
            include(SKYFINITYQC_PATH . 'includes/views/skyfinity-quick-popup-payment.php');

            break;
        default:
            echo '<div class="default-content">contents here</div>';
    }
}
