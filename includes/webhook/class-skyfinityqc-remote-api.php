<?php

namespace SKYFINITYQC;

/**
 * Skyfinity Checkout remote api.
 *
 * @package SkyfinityCheckout
 */

defined('ABSPATH') || exit;

/**
 * Class SKYFINITYQC_REMOTE_API
 */
class SKYFINITYQC_REMOTE_API
{

    /**
     * @var string
     */
    protected $api_register_url = 'https://skyfinity.co.in/register.php';

    /**
     * @var string
     */
    protected $api_de_register_url = 'https://skyfinity.co.in/de_register.php';

    /**
     * @var string
     */
    protected $api_verify_purchase_code = 'https://skyfinity.co.in/api/key';

    /**
     * @var string
     */
    protected $api_update_order_count = 'https://skyfinity.co.in/api/updateOrderCount';

    /**
     * @var string
     */
    protected $api_add_user_after_sms = 'https://skyfinity.co.in/api/user';

    protected $auth_key_token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMGQ2NDdhZGZkMDI5NGVhMjU4OTczOTAxZDA5ZGI2MTM2NmY1ZDg4MjI5NTQ0MGVhMGY1N2ZkYmRmYThlOWQyOWQ4YTNmMGMwYjg1NWU3OTgiLCJpYXQiOjE2ODU5NjM4OTYuNTUxOTUsIm5iZiI6MTY4NTk2Mzg5Ni41NTE5NTIsImV4cCI6MTcxNzU4NjI5Ni41NDkzODIsInN1YiI6IjEiLCJzY29wZXMiOltdfQ.RDFPV5KO8oxhtiL1wcvZMIJsHv_dWxUoHuXYZ-Qpwj-P6iINyInuojjQSYZxnsgqU_wTW5_pIubQaPggk5XCaJxpy4SJNxdx-armQkCzEnV-UGLUAKM8nAVk2dLjN7Q3sPUiSWFKPa_a2SCUSuBLEK6PoYJnkd8UxGd7-jSZPSTGcKpmedNAosP81A8J3aWAvJqxdTJ-tQ3IHrLxBHNR9IL7eykatORdXE1OJaxA47j7_M9KWmsFYZRHTqPjjFlpSDp7rfyybqbu-tlnXVUQ3KjESLXFM95i5i0KChZjP9tlXTefxhgKkPcpI0uqSnah4gLivjGDjzQCQzAbF54fL2xrbiaYJvRvq8S4S1jvCqnl8U7a83Yr2uaCluUTh6zTwspHQYzfw0Es1ZBEdE7NMZWPpxFvsa0YejwGtwSW8cg-1RmfvvlXxxM5-luY0kRTy_mp-qzvmdWWBTOPj2SyUG4RbhRolpbuXWYGT1pmu0-YlgMqWQ9YbAroyyhGFL3aJohaduxSSQxbzf3pxLq3z4h6DXApMy7i6e83U5Rl9nJiAWDmcDzi2MN7yCas3WU2HAO1A0IakEQmD2APNKoXK5Ua_fpZLlqDQKiCuovOiGVpY6MQYNBNNyIOumqTNzFhOgCh6f-qmoQeCqMYW6t1bUvvQrtF5WknEQtjlrJ9ouU';

    /**
     * @var array
     */
    protected $strings = array();

    /**
     * @var string
     */
    protected $code = '';

    private static $instance = false;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new SKYFINITYQC_REMOTE_API();
        }

        return self::$instance;
    }

    /**
     * SKYFINITYQC_REMOTE_API constructor.
     *
     * @param $code
     */
    public function __construct($code = '')
    {
        if ($code !== '') {
            $props = array_keys(get_object_vars($this));
            $props = array_filter($props, function ($prop) {
                return strpos($prop, 'api_') === 0;
            });
            foreach ($props as $prop) {
                $constant = strtoupper("skyfinityqc_remote_{$prop}");
                if (defined($constant) && constant($constant)) {
                    $this->$prop = constant($constant);
                }
            }

            $this->code = $code;
        }
        $this->strings['bad_request'] = __('Bad request.', 'skyfinity-quick-checkout');
        $this->strings['invalid_response'] = __('Invalid response.', 'skyfinity-quick-checkout');
    }

    /**
     * @return array|\WP_Error
     */
    public function register_purchase_code()
    {
        $args = array(
            'timeout' => 30,
            'body' => array(
                'code' => urlencode($this->code),
            ),
        );
        $response = wp_remote_post($this->api_register_url, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ('200' != $response_code) {
            return new \WP_Error('bad_request', $response_code . ': ' . $this->strings['bad_request']);
        }

        $code_check = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($code_check['errors'])) {
            return new \WP_Error('remote_api_error', $code_check['errors']);
        }

        if (empty($code_check['success'])) {
            return new \WP_Error('invalid_response', $this->strings['invalid_response']);
        }

        return $code_check;
    }

    /**
     * @return array|bool|\WP_Error
     */
    public function de_register_purchase_code()
    {
        $args = array(
            'timeout' => 30,
            'body' => array(
                'code' => urlencode($this->code),
            ),
        );
        $response = wp_remote_post($this->api_de_register_url, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        if ('200' != wp_remote_retrieve_response_code($response)) {
            return new \WP_Error('bad_request', $this->strings['bad_request']);
        }

        $code_check = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($code_check['errors'])) {
            return new \WP_Error('remote_api_error', $code_check['errors']);
        }

        if (empty($code_check['success'])) {
            return new \WP_Error('invalid_response', $this->strings['invalid_response']);
        }

        return true;
    }

    /**
     * @return array || bool
     */
    public function verify_code()
    {
        // Call the function to make the cURL request
        // $url = add_query_arg('key', $this->code, $this->api_verify_purchase_code);
        $response = $this->remote_get_json($this->api_verify_purchase_code, 'POST', array('key' => $this->code));

        $current_time = date('Y-m-d H:i:s');
        update_option('skyfinityqc_currentTime', $current_time);
      
        $user_id = false;
        
        if (is_wp_error($response)) {
            update_option('skyfinityqc_userPluginKeyRes', []);
            return ['status' => false, 'message' => 'No Response'];
        } else if (empty($response['data']) || !isset($response['data'][0])) {
            update_option('skyfinityqc_userPluginKeyRes', []);
            return ['status' => false, 'message' => 'No Response'];
        } else {
            $user_id = "";
            if(isset($response['data'][0]['user']) && !empty($response['data'][0]['user']) && isset($response['data'][0]['user'][0]['id'])){
                $user_id = $response['data'][0]['user'][0]['id'];
                
                $order_id = $response['data'][0]['order_id'];
                
                $save_data = $response;
                $save_data['order_id'] = $order_id;
                $save_data['user_id'] = $user_id;
                update_option('skyfinityqc_userPluginKeyRes', $save_data);
                
                $order_limit = $response['data'][0]['order_limit']; // order count is count of order for this user
                // $order_count = $response['data'][0]['order_count']; // order limit is remaining limit of order for this user
                // if ($order_count >= $order_limit) {
                if ($order_limit <= 0) {
                    return array(
                        'status' => false,
                        'expired_plan' => true,
                        'message' => 'Your Skyfinity Quick checkout Plan is expired. Please upgrade your plan.'
                    );
                } else {
                    return ['status' => true, 'user_id' => $user_id, 'message' => 'Success'];
                }
            } else {
                update_option('skyfinityqc_userPluginKeyRes', []);
                return ['status' => false,  'message' => 'Your plugin key is seems invalid. please add valid plugin key and try again.'];
            }            
        }
    }

    /**
     * @return bool
     * 
     * params
     * @user_id : id of the user register while plugin purchase
     */
    public function update_order_count($user_id, $other_data = array())
    {
        $args = array(
            // 'method' => $method,
            // 'timeout' => 30,
            'headers' => array(
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->auth_key_token
            )
        );
        $body = array('flag' => 'Yes', 'user_id' => $user_id);
        if (count($other_data) > 0) {
            $body = array_merge($body, $other_data);
        }
        $args['body'] = $body;

        $response = wp_remote_post($this->api_update_order_count, $args);

        if (is_wp_error($response)) {
            return $response;
        }
        
        if ('200' != wp_remote_retrieve_response_code($response)) {
            return new \WP_Error('bad_request', $this->strings['bad_request']);
        }
        $json = json_decode(wp_remote_retrieve_body($response), true);
        $this->verify_code();

        if (empty($json) || !is_array($json)) {
            return new \WP_Error('invalid_response', $this->strings['invalid_response']);
        }
        return $json;
    }

    /**
     * @param string $url
     *
     * @return array|\WP_Error
     */
    protected function remote_get_json($url, $method = 'GET', $data = false)
    {
        $args = array(
            // 'method' => $method,
            // 'timeout' => 30,
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type: application/json', 
                'Authorization' => 'Bearer ' . $this->auth_key_token
            )
        );
        if ($data) {
            $args['body'] = $data;
        }

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        if ('200' != wp_remote_retrieve_response_code($response)) {
            return new \WP_Error('bad_request', $this->strings['bad_request']);
        }
        $json = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($json) || !is_array($json)) {
            return new \WP_Error('invalid_response', $this->strings['invalid_response']);
        } else {
            $user_id = get_option('skyfinityqc_user_id');
            if (isset($json['data'][0])) {
                $newUserId = $json['data'][0]['id'];
                if (!isset($user_id) || $user_id == '') {
                    // add option skyfinityqc_user_id for current user
                    add_option('skyfinityqc_user_id', $newUserId);
                }
            }
        }
        return $json;
    }

    /**
     * @param array $data
     *
     * @return array|WP_Error
     */
    public function save_customer_after_registration($data)
    {
        $args = array(
            // 'method' => $method,
            // 'timeout' => 30,
            'headers' => array(
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->auth_key_token
            )
        );
        if ($data) {
            $args['body'] = $data;
        }

        $response = wp_remote_post($this->api_add_user_after_sms, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        if ('201' != wp_remote_retrieve_response_code($response)) {
            return new \WP_Error('bad_request', $this->strings['bad_request']);
        }

        $json = json_decode(wp_remote_retrieve_body($response), true);
        return $json;
    }
}