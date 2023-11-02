<?php
namespace SKYFINITYQC;

/**
 * SkyfinityQuickCheckoutPaymentSettings Page.
 *
 * @package SkyfinityQuickCheckoutPaymentSettings Page
 */

defined('ABSPATH') || exit;

class SkyfinityQuickCheckoutPaymentSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

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
            __('Payment Methods Settings', 'skyfinity-quick-checkout'),
            __('Payment Methods', 'skyfinity_quick_checkout'),
            'manage_options',
            'skyfinity-quick-checkout-payment',
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
        $this->options = get_option('skyfinity_quick_payment_setting');

        ?>
        <div class="skyfinity-wrapper">
            <div class="skyfinity-boxes skyfinity-header">
                <div class="skyfinity-box skyfinity-page-title">
                    <h1>
                        <?php echo __('Skyfinity Quick Checkout Payment Methods', 'skyfinity_quick_checkout'); ?>
                    </h1>
                </div>
                <div class="skyfinity-box skyfinity-logo">
                    <!-- https://skyfinity.co.in/storage/Logo_Settings/skyfinity_logo.png -->
                    <a href="https://skyfinity.co.in/" target="_blank"><img
                            src="<?php echo SKYFINITYQC_ASSETS_URL."images/skyfinity_logo.png"; ?>" alt="Skyfinity Logo"></a>
                </div>
            </div>
            <div class="skyfinity-content">
                <form method="post" action="options.php">
                    <nav class="tab">
                        <button type="button" onClick="skyfinityqc_open_setting_tab(event,'cod-settings');"
                            class="tablinks active">COD</button>
                        <button type="button" onClick="skyfinityqc_open_setting_tab(event,'razorpay-settings');"
                            class="tablinks">Razorpay</button>
                        <!-- <button type="button" onClick="skyfinityqc_open_setting_tab(event,'paypal-settings');"
                            class="tablinks">PayPal</button> -->
                        <?php do_action('skyfinityqc/payment_settings/tab_buttons'); ?>

                    </nav>
                    <div class="tab-content">
                        <?php
                        // This prints out all hidden setting fields
                        settings_fields('skyfinity_quick_payment_setting_group');
                        // do_settings_sections('skyfinity_quick_payment_setting');
                        ?>
                        <div class="tabcontent" style="display: block" id="cod-settings">
                            <?php $this->cod_setting_calback(); ?>
                        </div>
                        <div class="tabcontent" id="razorpay-settings">
                            <?php $this->razorpay_setting_callback(); ?>
                        </div>
                        <!-- <div class="tabcontent" id="paypal-settings">
                            <?php $this->paypal_setting_callback(); ?>
                        </div> -->
                        <?php do_action('skyfinityqc/payment_settings/tab_content'); ?>
                    </div>
                    <?php
                    submit_button();
                    ?>
                    <!-- <script type="text/javascript">
                        function skyfinityqc_open_setting_tab(evt, cityName) {
                            // Declare all variables
                            var i, tabcontent, tablinks;

                            // Get all elements with class="tabcontent" and hide them
                            tabcontent = document.getElementsByClassName("tabcontent");
                            for (i = 0; i < tabcontent.length; i++) {
                                tabcontent[i].style.display = "none";
                            }

                            // Get all elements with class="tablinks" and remove the class "active"
                            tablinks = document.getElementsByClassName("tablinks");
                            for (i = 0; i < tablinks.length; i++) {
                                tablinks[i].className = tablinks[i].className.replace(" active", "");
                            }

                            // Show the current tab, and add an "active" class to the button that opened the tab
                            document.getElementById(cityName).style.display = "block";
                            evt.currentTarget.className += " active";
                        }
                    </script> -->
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
        register_setting(
            'skyfinity_quick_payment_setting_group',
            // Option group
            'skyfinity_quick_payment_setting',
            // Option name
            array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
            'payment_methods',
            // ID
            __('Payment Methods', 'skyfinity-quick-checkout'),
            // Title
            array($this, 'print_section_info'),
            // Callback
            'skyfinity_quick_payment_setting' // Page
        );

        add_settings_field(
            'payment_method_cod',
            __('Cash On Delivery (COD)', 'skyfinity-quick-checkout'),
            '',
            // array($this, 'payment_method_cod_settings_key_callback'),
            'skyfinity_quick_payment_setting',
            'payment_methods'
        );

        add_settings_field(
            'payment_method_razor_pay',
            __('Razor Pay', 'skyfinity-quick-checkout'),
            array($this, 'payment_method_razor_pay_settings_key_callback'),
            'skyfinity_quick_payment_setting',
            'payment_methods'
        );
        add_settings_field(
            'payment_method_paypal_pay',
            __('Paypal', 'skyfinity-quick-checkout'),
            array($this, 'payment_method_paypal_pay_settings_key_callback'),
            'skyfinity_quick_payment_setting',
            'payment_methods'
        );

        do_action('skyfinityqc_add_settings_input_fields_payment_method');
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

        if (isset($input['payment_method_cod']))
            $new_input['payment_method_cod'] = sanitize_text_field($input['payment_method_cod']);
        
        if (isset($input['cod_fee']))
            $new_input['cod_fee'] = sanitize_text_field($input['cod_fee']);

        if (isset($input['payment_method_razor_pay']))
            $new_input['payment_method_razor_pay'] = sanitize_text_field($input['payment_method_razor_pay']);

        if (isset($input['rp_stagging_api_key']))
            $new_input['rp_stagging_api_key'] = sanitize_text_field($input['rp_stagging_api_key']);

        if (isset($input['rp_stagging_secret_key']))
            $new_input['rp_stagging_secret_key'] = sanitize_text_field($input['rp_stagging_secret_key']);

        if (isset($input['rp_live_api_key']))
            $new_input['rp_live_api_key'] = sanitize_text_field($input['rp_live_api_key']);

        if (isset($input['rp_live_secret_key']))
            $new_input['rp_live_secret_key'] = sanitize_text_field($input['rp_live_secret_key']);


        if (isset($input['payment_method_razor_pay_test_mode']))
            $new_input['payment_method_razor_pay_test_mode'] = sanitize_text_field($input['payment_method_razor_pay_test_mode']);

        if (isset($input['payment_method_paypal_pay']))
            $new_input['payment_method_paypal_pay'] = sanitize_text_field($input['payment_method_paypal_pay']);

        if (isset($input['pp_stagging_api_key']))
            $new_input['pp_stagging_api_key'] = sanitize_text_field($input['pp_stagging_api_key']);

        if (isset($input['pp_stagging_secret_key']))
            $new_input['pp_stagging_secret_key'] = sanitize_text_field($input['pp_stagging_secret_key']);

        if (isset($input['pp_live_api_key']))
            $new_input['pp_live_api_key'] = sanitize_text_field($input['pp_live_api_key']);

        if (isset($input['pp_live_secret_key']))
            $new_input['pp_live_secret_key'] = sanitize_text_field($input['pp_live_secret_key']);


        if (isset($input['payment_method_paypal_pay_test_mode']))
            $new_input['payment_method_paypal_pay_test_mode'] = sanitize_text_field($input['payment_method_paypal_pay_test_mode']);

        $all_inputs = apply_filters('skyfinityqc_sanitize_input_fields_payment_method', $new_input, $input);

        return $all_inputs;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter skyfinity quick Checkout Payment method below:';
    }

    public function cod_setting_calback()
    {
        ?>
        <h2 class="backend-title">
            <?php echo __('Cash On Delivery', 'skyfinity-quick-checkout'); ?>
        </h2>
        <p class="backend-sub-title">
            <?php echo __('- Have your customers pay with cash (or by other means) upon delivery.', 'skyfinity-quick-checkout'); ?>
        </p>
        <div class="fields-wrapper">
            <label>
                <?php echo __('Enable / Disable', 'skyfinity-quick-checkout'); ?>
            </label>
            <div class="field">
                <?php
                $this->payment_method_cod_settings_key_callback();
                ?>
            </div>
        </div>
        <div class="fields-wrapper">
            <label>COD Fee</label>
            <?php
            printf(
                '<input type="text" class="field" id="cod_fee" name="skyfinity_quick_payment_setting[cod_fee]" value="%s" />',
                isset($this->options['cod_fee']) ? esc_attr($this->options['cod_fee']) : ''
            );
            ?>
        </div>

        <?php
    }

    public function razorpay_setting_callback()
    {
        $this->payment_method_razor_pay_settings_key_callback();
    }
    public function paypal_setting_callback()
    {
        $this->payment_method_paypal_pay_settings_key_callback();
    }

    /** 
     * Get the other settings option array and print one of its values
     */
    public function payment_method_cod_settings_key_callback()
    {
        ?>
        <input type="checkbox" class="skyfinityqc-ui-toggle" id="payment_method_cod"
            name="skyfinity_quick_payment_setting[payment_method_cod]" value="1" <?php echo esc_attr(isset($this->options['payment_method_cod']) && ((int) $this->options['payment_method_cod'] === 1) ? 'checked' : ''); ?> />
        <?php
    }

    /** 
     * Get the other settings option array and print one of its values
     */
    public function payment_method_razor_pay_settings_key_callback()
    {
        ?>
        <h2 class="backend-title">
            <?php echo __('Razorpay Payment Gateway', 'skyfinity-quick-checkout'); ?>
        </h2>
        <p>
            <?php echo __('- Allows payments by Credit/Debit Cards, NetBanking, UPI, and multiple Wallets.', 'skyfinity-quick-checkout'); ?>
        </p>
        <p class="backend-sub-title">
            <?php echo __('- First <a href="https://easy.razorpay.com/onboarding/welcome" class="yellow-link" target="_blank">Signup</a> for a Razorpay account or <a href="https://dashboard.razorpay.com/signin?screen=sign_in" class="yellow-link" target="_blank">Login</a> if you have an existing account.', 'skyfinity-quick-checkout'); ?>
        </p>

        <div class="fields-wrapper">
            <label>Enable / Disable</label>
            <div class="field">
                <input type="checkbox" class="skyfinityqc-ui-toggle" id="payment_method_razor_pay"
                    name="skyfinity_quick_payment_setting[payment_method_razor_pay]" value="1" <?php echo isset($this->options['payment_method_razor_pay']) && ((int) $this->options['payment_method_razor_pay'] === 1) ? 'checked' : ''; ?> />
            </div>
        </div>
        <div class="fields-wrapper">
            <label>Test Mode</label>
            <div class="field">
                <input type="checkbox" class="skyfinityqc-ui-toggle" id="payment_method_razor_pay_test_mode"
                    name="skyfinity_quick_payment_setting[payment_method_razor_pay_test_mode]" value="1" <?php echo isset($this->options['payment_method_razor_pay_test_mode']) && ((int) $this->options['payment_method_razor_pay_test_mode'] === 1) ? 'checked' : ''; ?> />
            </div>
        </div>
        <div class="fields-wrapper">
            <label>Live API Key</label>
            <?php
            printf(
                '<input type="text" class="field" id="rp_live_api_key" name="skyfinity_quick_payment_setting[rp_live_api_key]" value="%s" />',
                isset($this->options['rp_live_api_key']) ? esc_attr($this->options['rp_live_api_key']) : ''
            );
            ?>
        </div>
        <div class="fields-wrapper">
            <label>Live Secret Key</label>
            <?php
            printf(
                '<input type="text" class="field" id="rp_live_secret_key" name="skyfinity_quick_payment_setting[rp_live_secret_key]" value="%s" />',
                isset($this->options['rp_live_secret_key']) ? esc_attr($this->options['rp_live_secret_key']) : ''
            );
            ?>
        </div>
        <div class="fields-wrapper">
            <label>Stagging API Key</label>
            <?php
            printf(
                '<input type="text" class="field" id="rp_stagging_api_key" name="skyfinity_quick_payment_setting[rp_stagging_api_key]" value="%s" />',
                isset($this->options['rp_stagging_api_key']) ? esc_attr($this->options['rp_stagging_api_key']) : ''
            );
            ?>
        </div>
        <div class="fields-wrapper">
            <label>Stagging Secret Key</label>
            <?php
            printf(
                '<input type="text" class="field" id="rp_stagging_secret_key" name="skyfinity_quick_payment_setting[rp_stagging_secret_key]" value="%s" />',
                isset($this->options['rp_stagging_secret_key']) ? esc_attr($this->options['rp_stagging_secret_key']) : ''
            );
            ?>
        </div>
        <?php
    }

    /** 
     * Get the other settings option array and print one of its values
     */
    public function payment_method_paypal_pay_settings_key_callback()
    {
        ?>
        <div class="fields-wrapper">
            <label>Active: </label>
            <div class="field">
                <input type="checkbox" class="skyfinityqc-ui-toggle" id="payment_method_paypal_pay"
                    name="skyfinity_quick_payment_setting[payment_method_paypal_pay]" value="1" <?php echo esc_attr(isset($this->options['payment_method_paypal_pay']) && ((int) $this->options['payment_method_paypal_pay'] === 1) ? 'checked' : ''); ?> />
            </div>
        </div>

        <div class="fields-wrapper">
            <label>PayPal Stagging API KEY </label>
            <?php
            printf(
                '<input type="text" class="field" id="pp_stagging_api_key" name="skyfinity_quick_payment_setting[pp_stagging_api_key]" value="%s" />',
                isset($this->options['pp_stagging_api_key']) ? esc_attr($this->options['pp_stagging_api_key']) : ''
            );
            ?>
        </div>
        <div class="fields-wrapper">
            <label>PayPal Stagging Secret KEY </label>
            <?php
            printf(
                '<input type="text" class="field" id="pp_stagging_secret_key" name="skyfinity_quick_payment_setting[pp_stagging_secret_key]" value="%s" />',
                isset($this->options['pp_stagging_secret_key']) ? esc_attr($this->options['pp_stagging_secret_key']) : ''
            );
            ?>
        </div>
        <div class="fields-wrapper">
            <label>PayPal LIVE API KEY </label>
            <?php
            printf(
                '<input type="text" class="field" id="pp_live_api_key" name="skyfinity_quick_payment_setting[pp_live_api_key]" value="%s" />',
                isset($this->options['pp_live_api_key']) ? esc_attr($this->options['pp_live_api_key']) : ''
            );
            ?>
        </div>
        <div class="fields-wrapper">
            <label>PayPal Pay LIVE Secret KEY </label>
            <?php
            printf(
                '<input type="text" class="field" id="pp_live_secret_key" name="skyfinity_quick_payment_setting[pp_live_secret_key]" value="%s" />',
                isset($this->options['pp_live_secret_key']) ? esc_attr($this->options['pp_live_secret_key']) : ''
            );
            ?>
        </div>
        <div class="fields-wrapper">
            <label>Test Mode</label>
            <div class="field">
                <input type="checkbox" class="skyfinityqc-ui-toggle" id="payment_method_paypal_pay_test_mode"
                    name="skyfinity_quick_payment_setting[payment_method_paypal_pay_test_mode]" value="1" <?php echo isset($this->options['payment_method_paypal_pay_test_mode']) && ((int) $this->options['payment_method_paypal_pay_test_mode'] === 1) ? 'checked' : ''; ?> />
            </div>
        </div>
        <?php
    }
}
if (is_admin())
    $skyfinity_quick_payment_setting_page = new SkyfinityQuickCheckoutPaymentSettingsPage();
