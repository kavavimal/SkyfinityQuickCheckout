<?php
namespace SKYFINITYQC;

/**
 * Skyfinity Checkout Plugin.
 *
 * @package SkyfinityCheckout
 */
defined('ABSPATH') || exit;
/**
 * Class SKYFINITYQC_PLUGIN
 */
class SKYFINITYQC_PLUGIN
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    private $plugin_options;
    private $is_verified;
    private $chk_plan_expire;

    /**
     * Instance.
     *
     * Holds the plugin instance.
     *
     * @since 1.0.0
     * @access public
     * @static
     *
     * @var SKYFINITYQC_Plugin
     */
    public static $instance = null;

    /**
     * Instance.
     *
     * Ensures only one instance of the plugin class is loaded or can be loaded.
     *
     * @since 1.0.0
     * @access public
     * @static
     *
     * @return SKYFINITYQC_PLUGIN An instance of the class.
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self(true);

            /**
             * SKYFINITYQC Plugin loaded.
             *
             * Fires when skyfinity quick checkout was fully loaded and instantiated.
             *
             * @since 1.0.0
             */
            
        }

        return self::$instance;
    }

    /**
     * SKYFINITYQC_PLUGIN constructor.
     *
     */
    public function __construct()
    {
        if ($this->is_compatible()) {
            register_activation_hook(SKYFINITYQC__FILE__, array($this, 'skyfinityqc_on_plugin_install'));
            register_deactivation_hook(SKYFINITYQC__FILE__, array($this, 'on_skyfinityqc_deactivate'));
            
            $this->options = get_option('skyfinity_quick_checkout_setting');
            $this->plugin_options = get_option('skyfinity_quick_checkout_plugin_setting');
            $this->register_autoloader();
            add_action('init', [$this, 'init'], 0);
            do_action('skyfinityqc/loaded');
        }
    }

    
	/**
	 * Compatibility Checks
	 *
	 * Checks whether the site meets the addon requirement.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function is_compatible()
	{
		// Check if Woocommerce installed and activated
		if(!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))){
			add_action('admin_notices', [$this, 'skyfinityqc_admin_notice_missing_main_plugin']);
			return false;
		}

		return true;
	}

    /**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Woocommerce installed or activated.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function skyfinityqc_admin_notice_missing_main_plugin()
	{

		if (isset($_GET['activate']))
			unset($_GET['activate']);

		$message = sprintf(
			/* translators: 1: Plugin name 2: Skyfinity Quick Checkout */
			esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'skyfinity-quick-checkout'),
			'<strong>' . esc_html__('Skyfinity Quick Chekcout', 'skyfinity-quick-checkout-cashfree') . '</strong>',
			'<strong>' . esc_html__('Woocommerce', 'skyfinity-quick-checkout-cashfree') . '</strong>'
		);

		printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);

	}

    /**
     * Initializes the Plugin files
     */
    public function init()
    {
        add_filter('plugin_row_meta', array($this, 'skyfinityqc_plugin_row_meta'), 10, 2);

        add_action('admin_init', array($this, 'skyfinityqc_check_license'));
        add_action('admin_enqueue_scripts', array($this, 'skyfinityqc_enqueue_admin_scripts'));
        
        if (isset($this->chk_plan_expire['status']) && $this->chk_plan_expire['status'] == true && isset($this->chk_plan_expire['user_id']) && $this->chk_plan_expire['user_id'] != false) {
            add_filter('woocommerce_locate_template', array($this, 'skyfinityqc_woo_adon_plugin_template'), 1, 3);
            add_filter('body_class', array($this, 'skyfinityqc_add_body_class'));
            add_action('init', array($this, 'skyfinityqc_initiate_checkout_button'));
            add_action('wp_enqueue_scripts', array($this, 'skyfinityqc_enqueue_frontend'));
            add_action('woocommerce_created_customer', array($this, 'skyfinityqc_on_user_register'), 1, 3);
            add_action('woocommerce_new_order', array($this, 'skyfinityqc_create_wc_order_cb'), 1, 2);
            
            /**
             * SKYFINITYQC init.
             *
             * Fires when Skyfinity quick checkout are initialized.
             *
             * @since 1.0.0
             */
            do_action('skyfinityqc/init');
        }
    }

    public function register_autoloader()
    {
        //Includes files for use functionalities
        require_once SKYFINITYQC_PATH . 'includes/utils.php';
        if (!class_exists('SKYFINITYQC_REMOTE_API', false)) {
            require_once SKYFINITYQC_PATH . 'includes/webhook/class-skyfinityqc-remote-api.php';
        }
        $this->chk_plan_expire = $this->is_license_key_valid();
        // Admin Settings
        require_once SKYFINITYQC_PATH . 'includes/settings/plugin-settings.php';
        
        if (isset($this->chk_plan_expire['status']) && $this->chk_plan_expire['status'] == true && isset($this->chk_plan_expire['user_id']) && $this->chk_plan_expire['user_id'] != false) {
            require_once SKYFINITYQC_PATH . 'includes/settings/skyfinity-quick-settings.php';
            require_once SKYFINITYQC_PATH . 'includes/settings/payment-methods.php';
            require_once SKYFINITYQC_PATH . 'includes/settings/shipping-settings.php';
            
            // hooks
            require_once SKYFINITYQC_PATH . 'includes/skyfinity-quick-checkout-hooks.php';
            require_once SKYFINITYQC_PATH . 'includes/views/skyfinity-minicart-sidebar.php';
            
            $paymentOptions = get_option('skyfinity_quick_payment_setting');
            if (isset($paymentOptions['payment_method_razor_pay']) && $paymentOptions['payment_method_razor_pay'] == true) {
                require_once SKYFINITYQC_PATH . 'includes/razorpay-php/Razorpay.php';
            }
            // Extra Hooks
            require_once SKYFINITYQC_PATH . 'includes/webhook/webhook-listener.php';
            require_once SKYFINITYQC_PATH . 'includes/webhook/ajaxhook-listener.php';
            require_once SKYFINITYQC_PATH . 'includes/webhook/sms-ajaxhook-listener.php';
            require_once SKYFINITYQC_PATH . 'includes/webhook/address-ajaxhook-listener.php';
            require_once SKYFINITYQC_PATH . 'includes/webhook/payment_api_ajax_hooks.php';
        }
    }

    public function skyfinityqc_add_body_class(array $classes)
    {
        $classes[] = 'skyfinityqc-quick-checkout';
        return $classes;
    }

    public function skyfinityqc_on_plugin_install()
    {
        if (!is_plugin_active('woocommerce/woocommerce.php') and current_user_can('activate_plugins')) {
            // Stop activation redirect and show error for woocommerce dependancy
            wp_die('This plugin requires the Woocommerce Plugin to be installed and active. <br><a href="' . esc_url(admin_url('plugins.php')) . '">&laquo; Return to Plugins</a>');
        } else {
            $this->skyfinityqc_on_install_create_table();
            $this->skyfinityqc_add_default_option_values();
        }
    }


    /***
     * Create Database Table for store truecaller api callback data to process further
     * */
    public function skyfinityqc_on_install_create_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sqckt_trueauth';
        $create_table_query = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `requestid` text NOT NULL,
            `accessToken` text NOT NULL,
            `profile` text NOT NULL,
            PRIMARY KEY  (id)
            ) CHARSET=utf8;
        ";

        $address_table_name = $wpdb->prefix . 'sqckt_address';
        $create_address_table_query = "CREATE TABLE IF NOT EXISTS " . $address_table_name . " (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `userId` bigint(20) UNSIGNED NOT NULL,
                `fullname` text NOT NULL,
                `country_code` text NOT NULL,
                `phone_number` text NOT NULL,
                `pincode` text NOT NULL,
                `address_1` text NOT NULL,
                `address_2` text NOT NULL,
                `state` text NOT NULL,
                `city` text NOT NULL,
                `country` text NOT NULL,
                `address_type` text NOT NULL,
                `landmark` text NOT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY  (id)
            ) CHARSET=utf8;
        ";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($create_table_query);
        dbDelta($create_address_table_query);
    }

    /**
     * Add options that are used by default values in option table.
     */
    public function skyfinityqc_add_default_option_values()
    {
        $plugin_options = array(
            'MSG91_authkey' => '367472ASGUQxDdBL6453ac17P1',
            'MSG91_tempelate_id' => '64532f56d6fc05412a39b1b3',
            'view_theme' => 'T1',
            'login_with_truecaller' => '1',
            'fetch_coupon' => '1'
        );
        $get_plugin_options = get_option('skyfinity_quick_checkout_setting');

        if ($get_plugin_options === '') {
            update_option('skyfinity_quick_checkout_setting', $plugin_options);
        } else if ($get_plugin_options !== false) {
            // The option already exists, so update it.
            $get_plugin_options['MSG91_authkey'] = $plugin_options['MSG91_authkey'];
            $get_plugin_options['MSG91_tempelate_id'] = $plugin_options['MSG91_tempelate_id'];
            update_option('skyfinity_quick_checkout_setting', $get_plugin_options);

        } else {
            // The option hasn't been created yet, so add it with $autoload set to 'no'.
            $deprecated = null;
            $autoload = 'no';
            add_option('skyfinity_quick_checkout_setting', $plugin_options);
        }
    }

    /***
     * Delete Database Table on uninstall
     * */
    public function skyfinityqc_on_uninstall_delete_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sqckt_trueauth';
        $sql = "DROP TABLE IF EXISTS " . $table_name . ";";
        $wpdb->query($wpdb->prepare($sql));
        $address_table_name = $wpdb->prefix . 'sqckt_address';
        $address_table_sql = "DROP TABLE IF EXISTS " . $address_table_name . ";";
        $wpdb->query($wpdb->prepare($address_table_sql));
    }

    /**
     * Deactivate Skyfinity Quick Checkout plugin
     */
    function on_skyfinityqc_deactivate()
    {
        if (function_exists('register_uninstall_hook'))
            register_uninstall_hook(__FILE__, 'skyfinity_quick_checkout_uninstall');
        if (isset($this->options['remove_data_on_uninstall'])) {
            $removeData = $this->options['remove_data_on_uninstall'];
            if ($removeData) {
                $this->skyfinityqc_on_uninstall_delete_table();
                delete_option("skyfinity_quick_checkout_setting");
                delete_option("skyfinity_quick");

                delete_option("skyfinity_quick_checkout_plugin_setting");
                delete_option("skyfinity_quick_payment_setting");
                delete_option("skyfinityqc_user_id");
            }
        }
        delete_option("skyfinity_quick_checkout_db_version");
    }

    /**
     *  include required admin scripts
     * */
    public function skyfinityqc_enqueue_admin_scripts()
    {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('skyfinityqc-fonts-style', SKYFINITYQC_ASSETS_URL . 'css/skyfinityqc_fonts_style.css');
        wp_enqueue_style('skyfinityqc-admin-style', SKYFINITYQC_ASSETS_URL . 'css/admin-style.css');
        wp_enqueue_script('skyfinityqc-admin-script', SKYFINITYQC_ASSETS_URL . 'js/admin-script.js', array('wp-color-picker'), false, true);
        wp_register_style( 'select2css',  SKYFINITYQC_ASSETS_URL . 'select2/select2.min.css', false, '1.0', 'all' );
        wp_register_script( 'select2', SKYFINITYQC_ASSETS_URL . 'select2/select2.full.min.js', array( 'jquery' ), '1.0', true );
        wp_enqueue_style( 'select2css' );
        wp_enqueue_script( 'select2' );
        wp_localize_script('skyfinityqc-admin-script', 'request_globals', array('request_url' => admin_url('admin-ajax.php')));
    }

    /**
     * Initialize the settings pages
     *
     * @since    1.0.0
     */
    public function skyfinityqc_init_setting_pages()
    {
        // Includes files for use functionalities
        require_once SKYFINITYQC_PATH . 'includes/utils.php';

        // Admin Settings
        require_once SKYFINITYQC_PATH . 'includes/settings/plugin-settings.php';

    }

    function skyfinityqc_loader_frontend()
    {
        add_action('init', array($this, 'skyfinityqc_initiate_checkout_button'));
        require_once SKYFINITYQC_PATH . 'includes/skyfinity-quick-checkout-hooks.php';
        require_once SKYFINITYQC_PATH . 'includes/views/skyfinity-minicart-sidebar.php';

        require_once SKYFINITYQC_PATH . 'includes/settings/skyfinity-quick-settings.php';
        require_once SKYFINITYQC_PATH . 'includes/settings/payment-methods.php';
        $paymentOptions = get_option('skyfinity_quick_payment_setting');
        if (isset($paymentOptions['payment_method_razor_pay']) && $paymentOptions['payment_method_razor_pay'] == true) {
            require_once SKYFINITYQC_PATH . 'includes/razorpay-php/Razorpay.php';
        }

        // Extra Hooks
        require_once SKYFINITYQC_PATH . 'includes/webhook/webhook-listener.php';
        require_once SKYFINITYQC_PATH . 'includes/webhook/ajaxhook-listener.php';
        require_once SKYFINITYQC_PATH . 'includes/webhook/sms-ajaxhook-listener.php';
        require_once SKYFINITYQC_PATH . 'includes/webhook/address-ajaxhook-listener.php';
        require_once SKYFINITYQC_PATH . 'includes/webhook/payment_api_ajax_hooks.php';
    }

    /**
     *  include required Frontend scripts
     * */
    public function skyfinityqc_enqueue_frontend()
    {
        wp_enqueue_style('intl-tel-input-style', SKYFINITYQC_ASSETS_URL . 'intl-tel-input/css/intlTelInput.css');
        wp_enqueue_style('skyfinityqc-fonts-style', SKYFINITYQC_ASSETS_URL . 'css/skyfinityqc_fonts_style.css');
        wp_enqueue_style('skyfinityqc-minicart-style', SKYFINITYQC_ASSETS_URL . 'css/skyfinityqc_minicart.css');
        wp_enqueue_style('skyfinityqc-style', SKYFINITYQC_ASSETS_URL . 'css/skyfinityqc_style.css');

        wp_enqueue_script('intl-tel-input-script', SKYFINITYQC_ASSETS_URL . 'intl-tel-input/js/intlTelInput.min.js', array('jquery'), '', false);
        wp_enqueue_script('skyfinityqc-script', SKYFINITYQC_ASSETS_URL . 'js/skyfinityqc_script.js', array('jquery'), '', true);
        wp_localize_script('skyfinityqc-script', 'request_globals', array('request_url' => admin_url('admin-ajax.php')));

        // Check if 'wc-cart-fragments' script is already enqueued or registered
        if ( ! wp_script_is( 'wc-cart-fragments', 'enqueued' ) && wp_script_is( 'wc-cart-fragments', 'registered' ) ) {
            // Enqueue the 'wc-cart-fragments' script
            wp_enqueue_script( 'wc-cart-fragments' );
        }
    }

    public function skyfinityqc_initiate_checkout_button()
    {
        $optionsValues = get_option('skyfinity_quick_checkout_setting');
        // Check if the license key is valid
        $chk_plan_expire = $this->is_license_key_valid();
        if (isset($chk_plan_expire['status']) && $chk_plan_expire['status'] == true && $chk_plan_expire['user_id'] != false) {
            remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);
            add_action('woocommerce_proceed_to_checkout', array($this, 'skyfinityqc_add_content_after_addtocart'), 5);
        }
        if (isset($optionsValues['login_with_truecaller']) && $optionsValues['login_with_truecaller'] == '1' && (
        (!isset($optionsValues['true_caller_key']) || $optionsValues['true_caller_key'] == '') ||
        (!isset($optionsValues['true_caller_appname']) || $optionsValues['true_caller_appname'] == ''))) {
            add_action('admin_notices', array($this, 'skyfinityqc_plugin_truecaller_key_required_notice'));
        }
    }
    public function skyfinityqc_plugin_truecaller_key_required_notice() {
        echo '<div class="error"><b>Important Notice:</b><p>Please add Truecaller API Key and App Name to make Skyfinity Quick checkout working.</p></div>';
    }

    /** 
     * Add custom button on checkout page
     * */
    public function skyfinityqc_add_content_after_addtocart()
    {
        echo '<button id="skyfinityqc_checkout_button" type="button" class=" button ">Quick Checkout</button>';
    }
    

    public function is_license_key_valid()
    {
        $user_plugin_currTime_res = get_option('skyfinityqc_currentTime');
        $current_time = date('Y-m-d H:i:s');
        $time_difference = abs(strtotime($user_plugin_currTime_res) - strtotime($current_time));
        $data = get_option('skyfinityqc_userPluginKeyRes');
        
        $license_key = isset($this->plugin_options['skyfinity_quick_activation_key'])?$this->plugin_options['skyfinity_quick_activation_key']:"";
        
        if (isset($license_key) && $license_key !="" && !empty($data) && isset($data['data'][0])) {
            $check_verify = false;
            
            if ($data['data'][0]['order_limit'] <= 5) {
                $check_verify = true;
            } else if($time_difference > 3600) {
                $check_verify = true;
            }
            if ($check_verify) {
                $key = openssl_decrypt($license_key, 'AES-256-CBC', SKYFINITYQC_ENC_KEY);
                $skyfinityqc_remote_api = new SKYFINITYQC_REMOTE_API($key);
                $newData =$skyfinityqc_remote_api->verify_code();

                return $newData;
            } else if ($data['data'][0]['order_limit'] > 0) {
                return ['status' => true, 'user_id' => $data['user_id'], 'message' => 'Success'];   
            }
        } else if (isset($license_key) && empty($data)) {
            $key = openssl_decrypt($license_key, 'AES-256-CBC', SKYFINITYQC_ENC_KEY);
            $skyfinityqc_remote_api = new SKYFINITYQC_REMOTE_API($key);
            $newData =$skyfinityqc_remote_api->verify_code();

            return $newData;
        } else {
            return false;
        }
    }

    public function skyfinityqc_check_license()
    {
        $license_key = isset($this->plugin_options['skyfinity_quick_activation_key']) ? $this->plugin_options['skyfinity_quick_activation_key'] : null;
        // Check if the license key is valid
        $chk_plan_expire = $this->is_license_key_valid();
        
        if (isset($chk_plan_expire['status']) && $chk_plan_expire['status'] != true && isset($chk_plan_expire['user_id']) && $chk_plan_expire['user_id'] == false) {
            if (isset($chk_plan_expire['expired_plan']) && $chk_plan_expire['expired_plan'] == true) {
                add_action('admin_notices', array($this, 'skyfinityqc_plan_expire_notice'));
                $this->is_verified = false;
            } else if (!$license_key || $license_key == '') {
                add_action('admin_notices', array($this, 'skyfinityqc_invalid_license_notice'));
                // Disable or restrict plugin functionality here
                $this->is_verified = false;
            } else {
                $this->is_verified = false;
            }
        } else {
            do_action('skyfinityqc/admin/init');
            $this->is_verified = true;
        }
    }

    /**
     * Add documentation link into plugin page
     */
    public function skyfinityqc_plugin_row_meta($links, $file)
    {
        if ('skyfinity-quick-checkout/skyfinity-quick-checkout.php' == $file) {
            $row_meta = array(
                'docs' => '<a href="' . esc_url('https://skyfinity.co.in/documentation') . '" target="_blank" aria-label="' . esc_attr__('Plugin Additional Links', 'skyfinity-quick-checkout') . '" style="color:green;">' . esc_html__('Documentation', 'skyfinity-quick-checkout') . '</a>'
            );

            return array_merge($links, $row_meta);
        }
        return (array) $links;
    }

    public function skyfinityqc_on_user_register($customer_id, $new_customer_data, $password_generated)
    {
        $data = get_option('skyfinityqc_userPluginKeyRes');
        
        $plugin_user_id = $data['data'][0]['user'][0]['id'];
        $user_info = get_userdata($customer_id);
        $billing_phone = get_user_meta($customer_id, 'billing_phone', true);
        $saveuser_data = array(
            'contact_number' => $billing_phone,
            'email' => $user_info->user_login,
            'register_under_user_id' => $plugin_user_id,
            'site_url' => get_site_url(),
        );
        $skyfinityqc_remote_api = SKYFINITYQC_REMOTE_API::getInstance();
        $save_user = $skyfinityqc_remote_api->save_customer_after_registration($saveuser_data);
    }


    /***
     * update template locate for overide default one
     * */
    public function skyfinityqc_woo_adon_plugin_template($template, $template_name, $template_path)
    {
        global $woocommerce;
        $_template = $template;
        if (!$template_path)
            $template_path = $woocommerce->template_url;

        $plugin_path = untrailingslashit(SKYFINITYQC_PATH) . '/woocommerce/';

        // Look within passed path within the theme - this is priority
        $template = locate_template(
            array(
                $template_path . $template_name,
                $template_name
            )
        );

        if (!$template && file_exists($plugin_path . $template_name))
            $template = $plugin_path . $template_name;

        if (!$template)
            $template = $_template;

        return $template;
    }


    public function skyfinityqc_invalid_license_notice()
    {
        echo '<div class="error"><p>Your Skyfinity Quick checkout license is invalid. Please enter a valid license key in the plugin settings.</p></div>';
    }

    public function skyfinityqc_plan_expire_notice()
    {
        echo '<div class="error"><p>Your Skyfinity Quick checkout Plan is expired. Please upgrade your plan.</p></div>';
    }

    /**
     * calback function will be called when new order is created
     */
    public function skyfinityqc_create_wc_order_cb($order_id, $order)
    {
        $user_plugin_key_res = get_option('skyfinityqc_userPluginKeyRes');

        $user_data = $user_plugin_key_res;
        $plugin_user_id = $user_data['user_id'];

        // Get order details 
        $order = wc_get_order($order_id);

        $other_data = array(
            'woocommerce_order_id' => $order_id,
            'woocommerce_order_total' => wc_format_decimal($order->get_total(), 2),
            'woocommerce_order_date' => date('Y-m-d H:i:s', strtotime(get_post($order->get_id())->post_date)),
            'woocommerce_order_url' => get_site_url(),
        );

        $skyfinityqc_remote_api = SKYFINITYQC_REMOTE_API::getInstance();
        $res = $skyfinityqc_remote_api->update_order_count($plugin_user_id, $other_data);
        
        return $res;
    }
}

// SKYFINITYQC_PLUGIN::instance();