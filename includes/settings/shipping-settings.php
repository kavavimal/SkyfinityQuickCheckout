<?php
namespace SKYFINITYQC;

/**
 * SkyfinityQuickCheckoutShippingSettings Page.
 *
 * @package SkyfinityQuickCheckoutShippingSettings Page
 */

defined('ABSPATH') || exit;

class SkyfinityQuickCheckoutShippingSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Holds tab menus to show on settings page
     */
    private $settings_tab_menus = array();

    /**
     * Start up
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_submenu_page(
            'skyfinity-quick-checkout',
            // parent page
            __('Shipping Settings', 'skyfinity-quick-checkout'),
            __('Shipping', 'skyfinity_quick_checkout'),
            'manage_options',
            'skyfinity-quick-checkout-shipping',
            array($this, 'create_admin_page')
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        global $wpdb;
        // Set class property
        $this->options = get_option('skyfinity_quick_checkout_shipping_setting');

        ?>
        <div class="skyfinity-wrapper">
            <div class="skyfinity-boxes skyfinity-header">
                <div class="skyfinity-box skyfinity-page-title">
                    <h1>
                        <?php echo __('Skyfinity Quick Checkout Shipping Settings', 'skyfinity_quick_checkout'); ?>
                    </h1>
                </div>
                <div class="skyfinity-box skyfinity-logo">
                    <a href="https://skyfinity.co.in/" target="_blank"><img
                            src="https://skyfinity.co.in/storage/Logo_Settings/skyfinity_logo.png" alt="Skyfinity Logo"></a>
                </div>
            </div>
            <div class="skyfinity-content">
                <form method="post" action="options.php">
                    <?php if(!empty($this->settings_tab_menus)) { ?>
                        <nav class="tab">
                            <?php foreach($this->settings_tab_menus as $key => $tab_item) { ?>
                                <button 
                                    type="button" 
                                    onClick="skyfinityqc_open_setting_tab(event,'<?php echo esc_attr($tab_item['slug']); ?>-settings');" 
                                    class="tablinks <?php echo esc_attr(($key == 0) ? "active" : ""); ?>">
                                    <?php echo esc_html($tab_item['title']); ?>
                                </button>
                            <?php } ?>
                        </nav>
                        <div class="tab-content">
                            <?php
                            // This prints out all hidden setting fields
                            settings_fields('skyfinity_quick_checkout_shipping_setting_group');
                            // do_settings_sections('skyfinity_quick_checkout_shipping_setting');
                            ?>
                            <?php foreach($this->settings_tab_menus as $key => $tab_item) { ?>
                                <div class="tabcontent" id="<?php echo esc_attr($tab_item['slug']); ?>-settings" style="<?php echo ($key == 0) ? "display: block" : ""; ?>">
                                    <?php do_action('skyfinityqc/shipping_settings/'.$tab_item['slug']); ?>
                                </div>
                            <?php } ?>
                        </div>
                        <?php
                        submit_button();
                    } else {
                        echo '<p>'.__('You need to buy Addon to Use Shipping methods Like "Shiprocket"', 'skyfinity_quick_checkout').'</p>';
                    }
                    ?>
                </form>
                <div class="skyfinity-footer">
                    <div class="skyfinity-box skyfinity-page-title">
                        <h3>
                            <?php echo __('Skyfinity Quick Checkout', 'skyfinity_quick_checkout'); ?>
                        </h3>
                    </div>
                    <div class="skyfinity-box skyfinity-logo">
                        <label>Design & Developed By - <a href="https://www.infinitysoftech.co/" class="footer-link"
                                target="_blank">Infinity Softech</a> & <a href="https://www.skygoaltech.com/"
                                class="footer-link" target="_blank">Skygoal Tech</a></label>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        $this->settings_tab_menus = apply_filters('skyfinity_quick_checkout_shipping_settings_array', array());
        register_setting(
            'skyfinity_quick_checkout_shipping_setting_group',
            // Option group
            'skyfinity_quick_checkout_shipping_setting',
            // Option name
            array($this, 'sanitize') // Sanitize
        );
        do_action('skyfinityqc_add_settings_input_fields_shipping_settings');
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input)
    {
        $new_input = array();
        $pluginSetting = get_option('skyfinity_quick_checkout_plugin_setting');
        $license_key = $pluginSetting['skyfinity_quick_activation_key'];
        $key = openssl_decrypt($license_key, 'AES-256-CBC', SKYFINITYQC_ENC_KEY);
        $skyfinityqc_remote_api = new SKYFINITYQC_REMOTE_API($key);
    
        $skyfinityqc_remote_api->verify_code();

        $all_inputs = apply_filters('skyfinityqc_sanitize_input_fields_shipping_settings', $new_input, $input);
        return $all_inputs;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter skyfinity quick Checkout Shippings below:';
    }

}
if (is_admin())
    $skyfinity_quick_shipping_setting_page = new SkyfinityQuickCheckoutShippingSettingsPage();
