<?php

defined('ABSPATH') || exit;

/** 
 * Ajax callback function for check requested user authentication.
 * */
function skyfinityqc_send_sms_otp_cb()
{
    $result = array();
    $mobile = sanitize_text_field($_POST['mobile']);
    $email = sanitize_text_field($_POST['email']);
    $country_code = sanitize_text_field($_POST['country_code']);
    $international_phone = $country_code . $mobile;
    // return error if mobile not entered
    if (!isset($mobile) || $mobile === '') {
        echo json_encode(
            array(
                'error' => true,
                'message' => 'Mobile Number is required',
            )
        );
        die();
    }
    // check if user phone is exist and mobile number is metch with account
    $is_mobile_registered = get_users(['meta_key' => 'billing_phone', 'meta_value' => $international_phone, 'fields' => 'ID']);
    $user_id = false;
    if (count($is_mobile_registered) != 0) {
        $user_id = $is_mobile_registered[0];
        $user_info = get_userdata($user_id);
        $username = $user_info->user_login;
        if ($username !== $email) {
            echo json_encode(
                array(
                    'error' => true,
                    'message' => 'Mobile Number and/or email Not match with your account',
                )
            );
            die();
        }
    } else {
        // check if user email is exist and mobile number is metch with account
        $user_id = username_exists($email);
        if ($user_id) {
            $phone = get_user_meta($user_id, 'billing_phone', true);
            if ($phone !== '' && $phone !== $international_phone) {
                echo json_encode(
                    array(
                        'error' => true,
                        'message' => 'Email and/or Mobile Number Not match with your account',
                    )
                );
                die();
            }
        }
    }
    // send opt to users phone number
    $optionsValues = get_option('skyfinity_quick_checkout_setting');
    // $message = rawurlencode('Your verification code is ##OTP##.');
    // $api_url = 'https://control.msg91.com/api/sendotp.php';
    $api_url = "https://control.msg91.com/api/v5/otp";
    // $url     = sprintf( '%s?authkey=%s&message=%s&sender=%s&mobile=%s&otp_expiry=300', $api_url, $optionsValues['MSG91_authkey'], $message, 'MSGSND', $international_phone );
    $url = sprintf($api_url . '?template_id=%s&mobile=%s', $optionsValues['MSG91_tempelate_id'], $international_phone);
    $response = wp_remote_post(
        $url,
        array(
            'method' => 'POST',
            'timeout' => 30,
            'redirection' => 10,
            'httpversion' => '1.1',
            'blocking' => true,
            'headers' => array(
                'authkey' => $optionsValues['MSG91_authkey'],
            ),
            'body' => array(),
            'cookies' => array(),
        )
    );
    $user_message = '';
    $dev_message = array();
    $res_param = array();
    if (is_wp_error($response)) {
        $dev_message = $response->get_error_message();
        $success = false;
    } else {
        $decoded_response = (array) json_decode($response['body']);
        $type = isset($decoded_response['type']) ? $decoded_response['type'] : '';
        if ('success' === $type) {
            $success = true;
        } else {
            $success = false;
            $user_message = __('Api error', 'orion-login');
        }
    }
    $result = array(
        'success' => $success,
        'userMessage' => $user_message,
        'devMessage' => $dev_message,
        'resParam' => $res_param,
        'url' => $url,
        'decoded_response' => $decoded_response,
    );
    echo json_encode($result);
    die();
}
add_action("wp_ajax_skyfinityqc_send_sms_otp", "skyfinityqc_send_sms_otp_cb");
add_action("wp_ajax_nopriv_skyfinityqc_send_sms_otp", "skyfinityqc_send_sms_otp_cb");
/** 
 * Ajax callback function for check requested user authentication.
 * */
function skyfinityqc_resend_otp_cb()
{
    $result = array();
    $mobile = sanitize_text_field($_POST['mobile']);
    $country_code = sanitize_text_field($_POST['country_code']);
    if (isset($mobile) && $mobile !== '') {
        $optionsValues = get_option('skyfinity_quick_checkout_setting');
        $international_phone = $country_code . $mobile;
        $api_url = "https://control.msg91.com/api/v5/otp/retry";
        $url = sprintf($api_url . '?authkey=%s&retrytype=text&mobile=%s', $optionsValues['MSG91_authkey'], $international_phone);
        $response = wp_remote_post(
            $url,
            array(
                'method' => 'GET',
                'timeout' => 30,
                'redirection' => 10,
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array(
                    'authkey' => $optionsValues['MSG91_authkey'],
                ),
                'body' => array(),
                'cookies' => array(),
            )
        );
        $user_message = '';
        $dev_message = array();
        $res_param = array();
        if (is_wp_error($response)) {
            $dev_message = $response->get_error_message();
            $success = false;
        } else {
            $decoded_response = (array) json_decode($response['body']);
            $type = isset($decoded_response['type']) ? $decoded_response['type'] : '';
            if ('success' === $type) {
                $success = true;
            } else {
                $success = false;
                $user_message = __('Api error', 'orion-login');
            }
        }
        $result = array(
            'success' => $success,
            'userMessage' => $user_message,
            'devMessage' => $dev_message,
            'resParam' => $res_param,
            'decoded_response' => $decoded_response,
        );
    } else {
        $result = array(
            'error' => true,
            'message' => 'Mobile Number is required',
        );
    }
    echo json_encode($result);
    die();
}
add_action("wp_ajax_skyfinityqc_resend_otp", "skyfinityqc_resend_otp_cb");
add_action("wp_ajax_nopriv_skyfinityqc_resend_otp", "skyfinityqc_resend_otp_cb");
/** 
 * Ajax callback function for Verify OTP.
 * */
function skyfinityqc_verify_sms_otp_cb()
{
    $result = array();
    $mobile = sanitize_text_field($_POST['mobile']);
    $country_code = sanitize_text_field($_POST['country_code']);
    $verify_otp = sanitize_text_field($_POST['verify_otp']);
    if (isset($mobile) && $mobile !== '' && $verify_otp !== '') {
        $optionsValues = get_option('skyfinity_quick_checkout_setting');
        $international_phone = $country_code . $mobile;
        $api_url = 'https://control.msg91.com/api/v5/otp/verify';
        $url = sprintf($api_url . '?mobile=%s&otp=%s', $international_phone, $verify_otp);
        $response = wp_remote_post(
            $url,
            array(
                'method' => 'GET',
                'timeout' => 30,
                'redirection' => 10,
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array(
                    'authkey' => $optionsValues['MSG91_authkey'],
                ),
                'body' => array(),
                'cookies' => array(),
            )
        );
        $user_message = '';
        $dev_message = array();
        $res_param = array();
        if (is_wp_error($response)) {
            $dev_message = $response->get_error_message();
            $success = false;
        } else {
            $email = sanitize_text_field($_POST['email']);
            if (isset($mobile) && $mobile !== '' && isset($email) && $email !== '') {
                // create user account if email is present
                $user_name = $email;
                $user_id = username_exists($user_name);
                if (is_user_logged_in()) {
                    $user_id = get_current_user_id();
                }
                if (!$user_id && false == email_exists($user_name)) {
                    $random_password = wp_generate_password(12, false);
                    $other_meta = array('billing_phone' => $international_phone, 'billing_phone_country_code' => $country_code);
                    $user_id = wc_create_new_customer($user_name, $user_name, $random_password, array('meta_input' => $other_meta));
                    $email_html = "<html><body><div>
                    <h3>Thanks for Registration.</h3>
                    <p>Your account is created. you can login to your account with your email as user name and below password</p>
                    <p><b>Password</b>: " . $random_password . "</p>
                </div></body></html>";
                    // ob_start();
                    // echo $email_html;
                    // $email_content = ob_get_contents();
                    // ob_end_clean();
                    $headers = array('Content-Type: text/html; charset=UTF-8');
                    $mailres = wp_mail($user_name, "Welcome!", $email_html, $headers);
                    // wp_mail($user_name, 'Welcome!', 'Your password is: ' . $random_password);
                } else {
                    $random_password = __('User already exists.  Password inherited.', 'skyfinity-quick-checkout');
                }
            
                if ($user_id !== 0 ) {
                    update_user_meta($user_id, 'billing_phone', $international_phone);
                    update_user_meta($user_id, 'billing_phone_country_code', $country_code);
                    wp_set_current_user($user_id);
                    wp_set_auth_cookie($user_id);
                }
            } else {
                $result = array(
                    'error' => true,
                    'message' => __('Mobile number and Email Fields are required', 'skyfinity_quick_checkout'),
                );
            }
            $decoded_response = (array) json_decode($response['body']);
            $type = isset($decoded_response['type']) ? $decoded_response['type'] : '';
            if ('success' === $type) {
                $success = true;
            } else {
                $success = false;
                $user_message = $decoded_response['message'] ? $decoded_response['message'] : __('Api error', 'orion-login');
            }
        }
        $result = array(
            'success' => $success,
            'userMessage' => $user_message,
            'devMessage' => $dev_message,
            'resParam' => $res_param,
            'url' => $url,
            'decoded_response' => $decoded_response,
            'user_id' => $user_id,
        );
    } else {
        $result = array(
            'error' => true,
            'message' => 'Mobile Number is required',
        );
    }
    echo json_encode($result);
    die();
}
add_action("wp_ajax_skyfinityqc_verify_sms_otp", "skyfinityqc_verify_sms_otp_cb");
add_action("wp_ajax_nopriv_skyfinityqc_verify_sms_otp", "skyfinityqc_verify_sms_otp_cb");