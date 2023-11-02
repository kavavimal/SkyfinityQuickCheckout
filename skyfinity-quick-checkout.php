<?php
/**
 * 
 * Plugin Name: Skyfinity Quick Checkout
 * Plugin URI: https://skyfinity.co.in/pricing
 * Description: Skyfinity Quick Checkout is a solution designed to enhance conversions, minimize Return to Order (RTO) rates, and faster business growth.
 * Version: 0.0.1
 * Author: Skyfinity
 * Author URI: https://skyfinity.co.in/
 * Text Domain: skyfinity-quick-checkout
 * 
 **/

defined('ABSPATH') || exit;

/**
 * Class SKYFINITY_CHECKOUT
 */
class SKYFINITY_CHECKOUT
{
    /**
     *  constructor.
     */
    public function __construct()
    {
        $this->define_constants();
        add_action('plugins_loaded', array($this, 'skyfinityqc_load_plugin_textdomain'));
        add_filter('plugin_action_links_skyfinity-quick-checkout/skyfinity-quick-checkout.php', array($this, 'skyfinityqc_settings_link'));

    }

    /**
     * Define required constants
     */
    public function define_constants()
    {
        define('SKYFINITYQC__FILE__', __FILE__);
        define('SKYFINITYQC_PLUGIN_BASE', plugin_basename(SKYFINITYQC__FILE__));
        define('SKYFINITYQC_PATH', plugin_dir_path(SKYFINITYQC__FILE__));

        define('SKYFINITYQC_URL', plugins_url('/', SKYFINITYQC__FILE__));

        define('SKYFINITYQC_ASSETS_PATH', SKYFINITYQC_PATH . 'assets/');
        define('SKYFINITYQC_ASSETS_URL', SKYFINITYQC_URL . 'assets/');
        define('SKYFINITYQC_ENC_KEY', 'skyfinityqc_enc_secrets');
    }

    /**
     * Load Skyfinity Quick Checkout textdomain.
     *
     * Load gettext translate for Skyfinity Quick Checkout text domain.
     * @since 1.0.0
     *
     * @return void
     */
    public function skyfinityqc_load_plugin_textdomain()
    {
        load_plugin_textdomain('skyfinity-quick-checkout');
    }


    /**
     * Add setting page link into plugin page
     */
    public function skyfinityqc_settings_link($links)
    {
        $url = esc_url(
            add_query_arg(
                'page',
                'skyfinity-quick-checkout',
                get_admin_url() . 'admin.php'
            )
        );

        $settings_link = "<a href='$url'>" . __('Settings') . '</a>';

        array_push(
            $links,
            $settings_link
        );
        return $links;
    }

}

$plugin = new SKYFINITY_CHECKOUT();
require_once SKYFINITYQC_PATH . 'includes/plugin.php';
$plugin_init = new SKYFINITYQC\SKYFINITYQC_PLUGIN(true);
