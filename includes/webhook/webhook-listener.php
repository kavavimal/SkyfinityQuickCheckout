<?php

defined('ABSPATH') || exit;

/*
Webhook Listener
*/
function skyfinityqc_handle_webhook_callback($request)
{
    $body = json_decode($request->get_body());
    // Process the webhook data here
    // ...
    global $wpdb;
    $table_name = $wpdb->prefix . 'sqckt_trueauth';
    $access_token = $body->accessToken;
    $api_url = $body->endpoint; // 'https://api4.truecaller.com/v1/keytoneusers/me';

    // method1
    // Set the cURL options
    // $ch = curl_init();
    // curl_setopt($ch, CURLOPT_URL, $api_url);
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    //     'Authorization: Bearer ' . $access_token,
    // )
    // );
    // // Send the request and get the response
    // $profileData = curl_exec($ch);
    // curl_close($ch);

    // method2
    $profileData = wp_remote_get($api_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
        ),
    ));
    
    // add record to request table
    $wpdb->insert(
        $table_name,
        array(
            'requestid' => isset($body->requestId) ? $body->requestId : '',
            'accessToken' => isset($body) ? json_encode($body) : '',
            'profile' => isset($profileData) ? $profileData : '',
        ),
        array('%s', '%s')
    );
    return new WP_REST_Response("Webhook Received", 200);
}
function skyfinityqc_register_webhook_listener_endpoint()
{
    register_rest_route('webhook/v1', '/access-token', array(
        'methods' => 'POST',
        'callback' => 'skyfinityqc_handle_webhook_callback',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'skyfinityqc_register_webhook_listener_endpoint');