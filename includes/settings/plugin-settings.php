<?php
namespace SKYFINITYQC;

/**
 * PluginSettingsPage.
 *
 * @package PluginSettingsPage
 */

defined('ABSPATH') || exit;

class PluginSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    private $plugin;

    private $order_limit;

    private $user_plugin_key_res = [];

    /**
     * Start up
     */
    public function __construct()
    {
        $this->updateorder_limit();
        $this->options = get_option('skyfinity_quick_checkout_plugin_setting');
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        $this->plugin = SKYFINITYQC_PLUGIN::$instance;
    }

    public function updateorder_limit() {
        $user_plugin_key_res = get_option('skyfinityqc_userPluginKeyRes');
        
        $this->user_plugin_key_res = $user_plugin_key_res;
        if (isset($this->user_plugin_key_res) && isset($this->user_plugin_key_res['data']) && isset($this->user_plugin_key_res['data'][0])) {
            // $order_count = $this->user_plugin_key_res['data'][0]['order_count']; // order count is count of order for this user
            $this->order_limit = $this->user_plugin_key_res['data'][0]['order_limit']; // order limit is remaining limit of order for this user
        }
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        //create new top-level menu
        add_menu_page(
            __('Skyfinity Quick Checkout Plugin', 'skyfinity-quick-checkout'),
            __('Skyfinity Quick Checkout', 'skyfinity_quick_checkout'),
            'manage_options',
            'skyfinity-quick-checkout',
            array($this, 'create_admin_page'),
            'dashicons-schedule',
            // plugins_url('/images/icon.png', __FILE__),
            50
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        global $wpdb;
        
        $key = "";
        if(isset($this->options['skyfinity_quick_activation_key'])){
            $key = $this->options['skyfinity_quick_activation_key'];
        }
        $isActive = false;
        if ($key != '' && $key != null && $this->order_limit > 0) {
            $isActive = true;
        }
        ?>
        <div class="skyfinity-wrapper">
            <div class="skyfinity-boxes skyfinity-header">
                <div class="skyfinity-box skyfinity-page-title">
                    <h1>
                        <?php echo __('Skyfinity Quick Checkout', 'skyfinity_quick_checkout'); ?>
                    </h1>
                </div>
                <div class="skyfinity-box skyfinity-logo">
                    <a href="https://skyfinity.co.in/" target="_blank"><img
                            src="<?php echo SKYFINITYQC_ASSETS_URL."images/skyfinity_logo.png"; ?>" alt="Skyfinity Logo"></a>
                </div>
            </div>
            <div class="skyfinity-content">
                <form method="post" action="options.php">
                    <div class="activation-form">
                        <h2 class="backend-title">
                            <?php echo __('Skyfinity Quick Checkout Plugin', 'skyfinity-quick-checkout'); ?>
                        </h2>
                        <p class="backend-sub-title">
                            <?php echo __('- Have your customers pay with cash (or by other means) upon delivery.', 'skyfinity-quick-checkout'); ?>
                        </p>
                        <p>
                            <?php
                            printf(
                                __('Please fill in the plugin activation key here. If you do not have an activation key yet, you can find it after %s'),
                                '<a target="_blank" class="yellow-link" href="https://skyfinity.co.in/">Logging in here</a>'
                            );
                            ?>
                        </p>
                        <?php
                        settings_fields('skyfinity_quick_checkout_plugin_setting_group');
                        // do_settings_sections('skyfinity_quick_checkout_plugin_setting');
                        $this->skyfinity_quick_key_callback();
                        ?>

                    </div>
                    <?php
                    $attributes = array('data-style' => $isActive ? 'plugin-activated' : 'plugin-not-activated');

                    submit_button($isActive ? __('Activated', 'skyfinity_quick_checkout') : __('Activate Plugin', 'skyfinity_quick_checkout'), 'primary', 'submit', true, $attributes);
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
        $this->plugin = SKYFINITYQC_PLUGIN::$instance;
        $this->updateorder_limit();
        register_setting(
            'skyfinity_quick_checkout_plugin_setting_group',
            // Option group
            'skyfinity_quick_checkout_plugin_setting',
            // Option name
            array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
            'skyfinity_quick_settings',
            // ID
            '',
            // __('Skyfinity Quick Settings', 'skyfinity-quick-checkout'),
            // Title
            array($this, 'print_section_info'),
            // Callback
            'skyfinity_quick_checkout_plugin_setting' // Page
        );

        add_settings_field(
            'skyfinity_quick_activation_key',
            // ID
            __('Activation Key', 'skyfinity-quick-checkout'),
            // Title 
            array($this, 'skyfinity_quick_key_callback'),
            // Callback
            'skyfinity_quick_checkout_plugin_setting',
            // Page
            'skyfinity_quick_settings' // Section           
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input)
    {
        $new_input = array();
        if (isset($input['skyfinity_quick_activation_key']) && $input['skyfinity_quick_activation_key'] != ""){
            $plugin_key = sanitize_text_field($input['skyfinity_quick_activation_key']);
            $skyfinityqc_remote_api = new SKYFINITYQC_REMOTE_API($plugin_key);
            $skyfinityqc_remote_api->verify_code();
            $this->updateorder_limit();
            $encrypted_key = openssl_encrypt($plugin_key, 'AES-256-CBC', SKYFINITYQC_ENC_KEY);
            $new_input['skyfinity_quick_activation_key'] = $encrypted_key;
        }
        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        // print 'Enter skyfinity quick Checkout Settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function skyfinity_quick_key_callback()
    {
        ?>
        <div class="fields-wrapper">

            <?php
            printf(
                '<input type="password" class="field" id="skyfinity_quick_activation_key" name="skyfinity_quick_checkout_plugin_setting[skyfinity_quick_activation_key]" value="%s" />',
                isset($this->options['skyfinity_quick_activation_key']) ? esc_attr($this->options['skyfinity_quick_activation_key']) : ''
            );

            ?>
        </div>
        <div class="message">
            <?php
                // $remained_order = $order_limit - $order_count;
                // new logic will be on order limit 
                $key = "";
                if(isset($this->options['skyfinity_quick_activation_key'])){
                    $key = $this->options['skyfinity_quick_activation_key'];
                }
                $message = '';
                
                if ($key == '' || (!isset($this->user_plugin_key_res) || !isset($this->user_plugin_key_res['data']))) {
                    $message = 'Invalid Key. please add valid plugin activation key';
                } else if ($key != '' && $this->order_limit <= 0) {
                    $message = 'You dont have any order(s) left. Please upgrade your plan.';
                } else if ($key != '' && $this->order_limit <= 5) {
                    $message = "You have only ".$this->order_limit." order(s) left. Please upgrade your plan.";
                }
                
                if ($message !== '') {
                    echo '<h4 style="color: #f47171;">';
                    echo $message;
                    echo '</h4>';
                }

            ?>
        </div>
        <?php
    }
}
if (is_admin())
    $skyfinity_quick_plugin_setting_page = new PluginSettingsPage();
