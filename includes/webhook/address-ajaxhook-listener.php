<?php

defined('ABSPATH') || exit;

function skyfinityqc_add_address_cb()
{
    global $wpdb;
    try {
        $result = array();
        $full_name = sanitize_text_field($_POST['full_name']);
        $country_code = sanitize_text_field($_POST['country_code']);
        $phone_number = sanitize_text_field($_POST['phone_number']);
        $pincode = sanitize_text_field($_POST['pincode']);
        $address_1 = sanitize_text_field($_POST['address_1']);
        $state = sanitize_text_field($_POST['state']);
        $billing_city = sanitize_text_field($_POST['city']);
        $address_2 = sanitize_text_field($_POST['address_2']);
        $country = sanitize_text_field($_POST['country']);
        $address_type = sanitize_text_field($_POST['address_type']);
        $landmark = sanitize_text_field($_POST['landmark']);

        $address_id = sanitize_text_field($_POST['address_id']);

        $current_user = wp_get_current_user();
        $table_name = $wpdb->prefix . 'sqckt_address';
        $newAddressId = '';
        if ($address_id == '') {
            $wpdb->insert(
                $table_name,
                array(
                    'userId' => isset($current_user) ? $current_user->ID : '',
                    'fullname' => isset($full_name) ? $full_name : '',
                    'country_code' => isset($country_code) ? $country_code : '',
                    'phone_number' => isset($phone_number) ? $phone_number : '',
                    'pincode' => isset($pincode) ? $pincode : '',
                    'address_1' => isset($address_1) ? $address_1 : '',
                    'address_2' => isset($address_2) ? $address_2 : '',
                    'state' => isset($state) ? $state : '',
                    'city' => isset($billing_city) ? $billing_city : '',
                    'country' => isset($country) ? $country : '',
                    'address_type' => isset($address_type) ? $address_type : '',
                    'landmark' => isset($landmark) ? $landmark : '',
                )
            );
            $newAddressId = $wpdb->insert_id;
        } else {
            $wpdb->update(
                $table_name,
                array(
                    'userId' => isset($current_user) ? $current_user->ID : '',
                    'fullname' => isset($full_name) ? $full_name : '',
                    'country_code' => isset($country_code) ? $country_code : '',
                    'phone_number' => isset($phone_number) ? $phone_number : '',
                    'pincode' => isset($pincode) ? $pincode : '',
                    'address_1' => isset($address_1) ? $address_1 : '',
                    'address_2' => isset($address_2) ? $address_2 : '',
                    'state' => isset($state) ? $state : '',
                    'city' => isset($billing_city) ? $billing_city : '',
                    'country' => isset($country) ? $country : '',
                    'address_type' => isset($address_type) ? $address_type : '',
                    'landmark' => isset($landmark) ? $landmark : '',
                ),
                array('id' => $address_id),
            );
        }

        $result = array(
            'success' => true,
            'Message' => "address added successfully.",
            "newAddressId" => $newAddressId,
        );

        echo json_encode($result);
    } catch (Exception $error) {
        $result = array(
            'success' => false,
            'error' => $error,
            'Message' => "Something went wrong",
        );
        echo json_encode($result);
    }
    die();
}

add_action("wp_ajax_skyfinityqc_add_address", "skyfinityqc_add_address_cb");

function skyfinityqc_get_address_list_cb()
{
    global $wpdb;
    $current_user = '';
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
    }
    try {
        if ($current_user !== '') {
            $table_name = $wpdb->prefix . 'sqckt_address';
            $address_lists = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE userId=$current_user->ID"));
            $address_list_html = '';
            ob_start();
            if (!empty($address_lists)){
                foreach ($address_lists as $value) {
                    ?>
                    <div class="address-item">
                        <label for="shipping_address_<?php echo esc_attr($value->id); ?>">
                            <input type="radio" class="shipping_address_selection" name="shipping_address"
                                id="shipping_address_<?php echo esc_attr($value->id); ?>" value="<?php echo esc_attr($value->id); ?>" />
                            <p class="mb-0 d-flex justify-start">
                                <?php echo esc_html($value->fullname ? $value->fullname : ''); ?>
                                <span class="address-type-badge">
                                    <?php echo esc_html($value->address_type ? $value->address_type : ''); ?>
                                </span>
                            </p>
                            <?php
                            // echo $value->country_code ? '<p class="mb-0">Country Code : ' . $value->country_code . '</p>' : '';
                            $addressString = '<p class="mb-0">';
                            $addressString .= $value->address_1 ? $value->address_1 . ', ' : '';
                            $addressString .= $value->address_2 ? $value->address_2 . ', ' : '';
                            $addressString .= '</p>';
                            $addressString .= '<p class="mb-0">';
                            $addressString .= $value->city ? $value->city . ', ' : '';
                            $addressString .= $value->state ? $value->state . ', ' : '';
                            $addressString .= $value->country ? $value->country . ', ' : '';
                            $addressString .= $value->pincode ? $value->pincode . ', ' : '';
                            $addressString .= $value->landmark ? $value->landmark : '';
                            $addressString .= '</p>';
                            echo $addressString;
                            // echo $value->phone_number ? '<p class="mb-0">Phone Number : ' . $value->phone_number . '</p>' : '';
                            // echo $value->address_1 ? '<p class="mb-0">House Number : ' . $value->address_1 . '</p>' : '';
                            // echo $value->address_2 ? '<p class="mb-0">Area : ' . $value->address_2 . '</p>' : '';
                            // echo $value->city ? '<p class="mb-0">city : ' . $value->city . '</p>' : '';
                            // echo $value->state ? '<p class="mb-0">State : ' . $value->state . '</p>' : '';
                            // echo $value->pincode ? '<p class="mb-0">Pincode : ' . $value->pincode . '</p>' : '';
                            // echo $value->landmark ? '<p class="mb-0">Landmark : ' . $value->landmark . '</p>' : '';
                            ?>
                        </label>
                        <div class="address-actions">
                            <button type="button" class="address-action-icon"></button>
                            <div class="address-action-menu">
                                <button type='button' class='skyfinityqc_edit_address' data-full_name="<?php echo esc_attr($value->fullname); ?>"
                                    data-country_code="<?php echo esc_attr($value->country_code); ?>"
                                    data-phone_number="<?php echo esc_attr($value->phone_number); ?>"
                                    data-address_1="<?php echo esc_attr($value->address_1); ?>" data-address_2="<?php echo esc_attr($value->address_2); ?>"
                                    data-city="<?php echo esc_attr($value->city); ?>" data-state="<?php echo esc_attr($value->state); ?>"
                                    data-pincode="<?php echo esc_attr($value->pincode); ?>" data-country="<?php echo esc_attr($value->country); ?>" data-address_type="<?php echo esc_attr($value->address_type); ?>"
                                    data-landmark="<?php echo esc_attr($value->landmark); ?>" data-id='<?php echo esc_attr($value->id); ?>'>Edit</button>
                                <button type='button' class='skyfinityqc_remove_address' data-id='<?php echo esc_attr($value->id); ?>'>Delete</button>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
            $address_list_html = ob_get_contents();
            ob_clean();
            $result = array(
                'success' => true,
                'Message' => "address Get successfully.",
                'address_list' => $address_lists,
                'address_list_html' => $address_list_html,
            );
        } else {
            $result = array(
                'success' => true,
                'Message' => "address Get successfully.",
                'address_list' => [],
                'address_list_html' => '',
            );
        }
        echo json_encode($result);
    } catch (Exception $error) {
        $result = array(
            'success' => false,
            'Message' => "address Not found.",
            'address_list' => [],
            'address_list_html' => '',
        );
        echo json_encode($result);
    }
    die();
}

add_action("wp_ajax_skyfinityqc_get_address_list", "skyfinityqc_get_address_list_cb");


function skyfinityqc_set_address_to_checkout_cb()
{
    global $wpdb;
    $current_user = wp_get_current_user();
    try {
        $args = array();
        $result = array();
        if (isset($_POST['id']) && $_POST['id'] != '') {
            $id = sanitize_text_field($_POST['id']);
            $table_name = $wpdb->prefix . 'sqckt_address';
            $row = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $table_name . ' WHERE `id`=' . $id));

            $args = array(
                'billing_email' => $current_user->user_email,
                // 'billing_phone' => $row->country_code . $row->phone_number,
                'billing_first_name' => $row->fullname,
                'billing_last_name' => '',
                'billing_address_1' => $row->address_1,
                'billing_address_2' => $row->address_2,
                'billing_postcode' => $row->pincode,
                'billing_city' => $row->city,
                'billing_state' => $row->state,
                'billing_country' => $row->country,

                'shipping_email' => $current_user->user_email,
                'shipping_first_name' => $row->fullname,
                'shipping_last_name' => '',
                'shipping_address_1' => $row->address_1,
                'shipping_address_2' => $row->address_2,
                'shipping_postcode' => $row->pincode,
                'shipping_city' => $row->city,
                'shipping_state' => $row->state,
                'shipping_country' => $row->country,

            );
        } else if(isset($_POST['pincode']) && $_POST['pincode'] != '') {
            $args =  array(
                'billing_email' => $current_user->user_email,
                // 'billing_phone' => $_POST['country_code'] . $_POST['phone_number'],
                'billing_first_name' => sanitize_text_field($_POST['full_name']),
                'billing_last_name' => '',
                'billing_address_1' => sanitize_text_field($_POST['address_1']),
                'billing_address_2' => sanitize_text_field($_POST['address_2']),
                'billing_postcode' => sanitize_text_field($_POST['pincode']),
                'billing_city' => sanitize_text_field($_POST['city']),
                'billing_state' => sanitize_text_field($_POST['state']),
                'billing_country' => sanitize_text_field($_POST['country']),

                'shipping_email' => $current_user->user_email,
                'shipping_first_name' => sanitize_text_field($_POST['full_name']),
                'shipping_last_name' => '',
                'shipping_address_1' => sanitize_text_field($_POST['address_1']),
                'shipping_address_2' => sanitize_text_field($_POST['address_2']),
                'shipping_postcode' => sanitize_text_field($_POST['pincode']),
                'shipping_city' => sanitize_text_field($_POST['city']),
                'shipping_state' => sanitize_text_field($_POST['state']),
                'shipping_country' => sanitize_text_field($_POST['country']),

            );
        }

        skyfinityqc_update_wc_user_fields($current_user->ID, $args);

        $result = array(
            'success' => true,
            'Message' => "address Set to Checkout order successfully.",
        );
        echo json_encode($result);
    } catch (Exception $error) {
        $result = array(
            'success' => false,
            'Message' => "something went wrong",
        );
        echo json_encode($result);
    }
    die();
}

add_action("wp_ajax_skyfinityqc_set_address_to_checkout", "skyfinityqc_set_address_to_checkout_cb");

function skyfinityqc_remove_address_cb()
{
    global $wpdb;
    $result = array();
    $id = sanitize_text_field($_POST['id']);
    $table_name = $wpdb->prefix . 'sqckt_address';
    $wpdb->delete(
        $table_name,
        array('id' => $id)
    );

    $result = array(
        'success' => true,
        'Message' => "address Removed successfully.",
    );

    echo json_encode($result);
    die();
}
add_action("wp_ajax_skyfinityqc_remove_address", "skyfinityqc_remove_address_cb");

function skyfinityqc_get_address_by_postcode_cb()
{
    try {
        $pincode = sanitize_text_field($_POST['pincode']);
        $pluginSettings = get_option('skyfinity_quick_checkout_setting');
        
        if(isset($pluginSettings['google_map_api_key'])) {

            $geocode = wp_remote_get("https://maps.google.com/maps/api/geocode/json?key=" . $pluginSettings['google_map_api_key'] . "&address=$pincode");
            $json = json_decode(wp_remote_retrieve_body($geocode));
            if (isset($json->results[0])) {
                $latitude = $json->results[0]->geometry->location->lat;
                $longitude = $json->results[0]->geometry->location->lng;
                $address = $json->results[0]->formatted_address;
                $address_components = $json->results[0]->address_components;
                $city = '';
                $state = '';
                $country = '';
                if (count($address_components) > 0) {
                    for ($i = 0; $i < count($address_components); $i++) {
                        for ($j = 0; $j < count($address_components[$i]->types); $j++) {
                            switch ($address_components[$i]->types[$j]) {
                                case 'locality':
                                    $city = $address_components[$i]->long_name;
                                    break;
                                case 'administrative_area_level_1':
                                    $state = $address_components[$i]->long_name;
                                    break;
                                case 'country':
                                    $country = $address_components[$i]->long_name;
                                    break;
                                // case 'postal_code':
                                //     $postal_code = $address_components[$i]->long_name;
                                //     break;
                                default:
                                    break;
                            }
                        }
                    }
                    $result = array(
                        'success' => true,
                        'address' => array('city' => $city, 'state' => $state, 'country' => $country),
                        'json' => $json->results[0],
                        'Message' => "address fetch successfully.",
                    );
                } else {
                    $result = array(
                        'success' => false,
                        'Message' => "address Not found.",
                    );
                }
            } else {
                $result = array(
                    'success' => false,
                    'Message' => "address Not found.",
                );
            }
        } else {
            $result = array(
                'success' => false,
                'Message' => "key Not found.",
            );
        }
    } catch (Exception $error) {
        $result = array(
            'success' => false,
            'error' => $error,
            'Message' => "Something went wrong",
        );
    }

    echo json_encode($result);
    die();
}
add_action("wp_ajax_skyfinityqc_get_address_by_postcode", "skyfinityqc_get_address_by_postcode_cb");
add_action("wp_ajax_nopriv_skyfinityqc_get_address_by_postcode", "skyfinityqc_get_address_by_postcode_cb");