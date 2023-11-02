<?php
namespace SKYFINITYQC;

/**
 * SkyfinityQuickCheckoutSettingsPage.
 *
 * @package SkyfinityQuickCheckoutSettingsPage
 */

defined('ABSPATH') || exit;

class SkyfinityQuickCheckoutSettingsPage
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
            __('Settings', 'skyfinity-quick-checkout'),
            __('Settings', 'skyfinity_quick_checkout'),
            'manage_options',
            'skyfinity-quick-checkout-settings',
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
        $this->options = get_option('skyfinity_quick_checkout_setting');

        ?>
        <div class="skyfinity-wrapper">
            <div class="skyfinity-boxes skyfinity-header">
                <div class="skyfinity-box skyfinity-page-title">
                    <h1>
                        <?php echo __('Skyfinity Quick Checkout Settings', 'skyfinity_quick_checkout'); ?>
                    </h1>
                </div>
                <div class="skyfinity-box skyfinity-logo">
                    <a href="https://skyfinity.co.in/" target="_blank"><img
                            src="<?php echo SKYFINITYQC_ASSETS_URL."images/skyfinity_logo.png"; ?>" alt="Skyfinity Logo"></a>
                </div>
            </div>
            <div class="skyfinity-content">
                <form method="post" action="options.php">
                    <nav class="tab">
                        <button type="button" onClick="skyfinityqc_open_setting_tab(event,'trucaller-settings');"
                            class="tablinks active">Trucaller</button>

                        <button type="button" onClick="skyfinityqc_open_setting_tab(event,'mini-cart-sidebar');"
                            class="tablinks">Mini Cart Sidebar</button>

                        <button type="button" onClick="skyfinityqc_open_setting_tab(event,'theme-settings');"
                            class="tablinks">Theme</button>

                        <?php
                        $isCouponEnabledWoo = wc_coupons_enabled();
                        if ($isCouponEnabledWoo) { ?>
                        <button type="button" onClick="skyfinityqc_open_setting_tab(event,'coupon-settings');"
                            class="tablinks">Coupons</button>
                        <?php } ?>

                        <button type="button" onClick="skyfinityqc_open_setting_tab(event,'other-settings');"
                            class="tablinks">Other</button>
                    </nav>

                    <div class="tab-content">
                        <?php
                        // This prints out all hidden setting fields
                        settings_fields('skyfinity_quick_checkout_setting_group');
                        // do_settings_sections('skyfinity_quick_checkout_setting');
                        ?>
                        <div class="tabcontent" style="display: block" id="trucaller-settings">
                            <?php $this->truecaller_callback(); ?>
                        </div>
                        <div class="tabcontent" id="mini-cart-sidebar">
                            <?php $this->minicart_sidebar_callback(); ?>
                        </div>
                        <div class="tabcontent" id="theme-settings">
                            <?php $this->view_themes_callback(); ?>
                        </div>
                        <?php
                        $isCouponEnabledWoo = wc_coupons_enabled();
                        if ($isCouponEnabledWoo) { ?>
                        <div class="tabcontent" id="coupon-settings">
                            <?php $this->view_coupon_callback(); ?>
                        </div>
                        <?php } ?>
                        <div class="tabcontent" id="other-settings">
                            <?php $this->view_other_settings_callback(); ?>
                        </div>
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
            'skyfinity_quick_checkout_setting_group',
            // Option group
            'skyfinity_quick_checkout_setting',
            // Option name
            array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
            'skyfinity_quick_settings',
            // ID
            __('Skyfinity Quick Settings', 'skyfinity-quick-checkout'),
            // Title
            array($this, 'print_section_info'),
            // Callback
            'skyfinity_quick_checkout_setting' // Page
        );


        // section for truecaller settings
        add_settings_section(
            'true_caller_settings',
            // ID
            __('True Caller', 'skyfinity-quick-checkout'),
            // Title
            array($this, 'print_section_info_truecaller'),
            // Callback
            'skyfinity_quick_checkout_setting' // Page
        );

        add_settings_field(
            'login_with_truecaller',
            __('Login With Truecaller', 'skyfinity-quick-checkout'),
            array($this, 'login_with_truecaller_callback'),
            'skyfinity_quick_checkout_setting',
            'true_caller_settings'
        );
        add_settings_field(
            'true_caller_key',
            __('API Key', 'skyfinity-quick-checkout'),
            array($this, 'truecaller_key_callback'),
            'skyfinity_quick_checkout_setting',
            'true_caller_settings'
        );
        add_settings_field(
            'true_caller_appname',
            __('App Name', 'skyfinity-quick-checkout'),
            array($this, 'truecaller_appname_callback'),
            'skyfinity_quick_checkout_setting',
            'true_caller_settings'
        );
        add_settings_field(
            'google_map_api_key',
            __('API Key', 'skyfinity-quick-checkout'),
            array($this, 'google_map_api_key_callback'),
            'skyfinity_quick_checkout_setting',
            'google_map_api_key_settings'
        );

        add_settings_field(
            'MSG91_authkey',
            __('MSG91 Auth Key', 'skyfinity-quick-checkout'),
            array($this, 'MSG91_authkey_callback'),
            'skyfinity_quick_checkout_setting',
            'true_caller_settings'
        );

        // section for Other settings
        add_settings_section(
            'other_settings',
            // ID
            __('Other Settings', 'skyfinity-quick-checkout'),
            // Title
            array($this, 'print_section_info_other_setting'),
            // Callback
            'skyfinity_quick_checkout_setting' // Page
        );
        add_settings_field(
            'autofill',
            __('AutoFill', 'skyfinity-quick-checkout'),
            array($this, 'autofill_settings_key_callback'),
            'skyfinity_quick_checkout_setting',
            'other_settings'
        );
        add_settings_field(
            'fetch_coupon',
            __('Fetch Coupon from woocommerce', 'skyfinity-quick-checkout'),
            array($this, 'fetch_coupon_settings_key_callback'),
            'skyfinity_quick_checkout_setting',
            'other_settings'
        );
        add_settings_field(
            'remove_data_on_uninstall',
            __('Remove all data on uninstall plugin', 'skyfinity-quick-checkout'),
            array($this, 'remove_data_settings_key_callback'),
            'skyfinity_quick_checkout_setting',
            'other_settings'
        );

        // section for Theme settings
        add_settings_section(
            'theme_setting',
            // ID
            __('Theme Settings', 'skyfinity-quick-checkout'),
            // Title
            array($this, 'print_section_info_other_setting'),
            // Callback
            'skyfinity_quick_checkout_setting' // Page
        );
        add_settings_field(
            'view_theme',
            __('Themes', 'skyfinity-quick-checkout'),
            array($this, 'view_themes_callback'),
            'skyfinity_quick_checkout_setting',
            'theme_setting'
        );
        add_settings_section(
            'minicart_sidebar_setting',
            __('Mini cart Sidebar Settings', 'skyfinity-quick-checkout'),
            array($this, 'print_section_info_other_setting'),
            'skyfinity_quick_checkout_setting' // Page
        );
        add_settings_field(
            'minicart_sidebar_status',
            __('Enable/Disable', 'skyfinity-quick-checkout'),
            array($this, 'minicart_sidebar_status_callback'),
            'skyfinity_quick_checkout_setting',
            'minicart_sidebar_setting'
        );
        add_settings_field(
            'minicart_sidebar_add_to_menu',
            __('Enable/Disable', 'skyfinity-quick-checkout'),
            array($this, 'minicart_sidebar_add_to_menu_callback'),
            'skyfinity_quick_checkout_setting',
            'minicart_sidebar_setting'
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

        if (isset($input['login_with_truecaller']))
            $new_input['login_with_truecaller'] = sanitize_text_field($input['login_with_truecaller']);
            
            if (isset($input['true_caller_key']))
            $new_input['true_caller_key'] = sanitize_text_field($input['true_caller_key']);

        if (isset($input['true_caller_appname']))
            $new_input['true_caller_appname'] = sanitize_text_field($input['true_caller_appname']);
        if (isset($input['MSG91_authkey']))
            $new_input['MSG91_authkey'] = sanitize_text_field($input['MSG91_authkey']);
        if (isset($input['google_map_api_key']))
            $new_input['google_map_api_key'] = sanitize_text_field($input['google_map_api_key']);
        if (isset($input['MSG91_tempelate_id']))
            $new_input['MSG91_tempelate_id'] = sanitize_text_field($input['MSG91_tempelate_id']);

        if (isset($input['autofill']))
            $new_input['autofill'] = sanitize_text_field($input['autofill']);

        if (isset($input['fetch_coupon']))
            $new_input['fetch_coupon'] = sanitize_text_field($input['fetch_coupon']);

        if (isset($input['selected_coupons'])) {
            foreach ($input['selected_coupons'] as &$tag) {
                $tag = esc_attr($tag);
            }
            $new_input['selected_coupons'] = $input['selected_coupons'];
        }
        
        if (isset($input['remove_data_on_uninstall']))
            $new_input['remove_data_on_uninstall'] = sanitize_text_field($input['remove_data_on_uninstall']);

        if (isset($input['view_theme']))
            $new_input['view_theme'] = sanitize_text_field($input['view_theme']);

        if (isset($input['custom_theme'])) {
            foreach ($input['custom_theme'] as &$tag) {
                $tag = esc_attr($tag);
            }
            $new_input['custom_theme'] = $input['custom_theme'];
        }

        if (isset($input['minicart_sidebar_status']))
            $new_input['minicart_sidebar_status'] = sanitize_text_field($input['minicart_sidebar_status']);

        if (isset($input['minicart_sidebar_add_to_menu']))
            $new_input['minicart_sidebar_add_to_menu'] = sanitize_text_field($input['minicart_sidebar_add_to_menu']);
        
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
     * Print the Section information for trucaller settings
     */
    public function print_section_info_truecaller()
    {
        print 'You can find your trucaller app key in your true caller account admin page';
    }

    /** 
     * Print the Section information for other settings
     */
    public function print_section_info_other_setting()
    {
        // print '';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function truecaller_callback()
    {
        ?>
        <h2 class="backend-title">
        <?php echo __('Login Using Truecaller', 'skyfinity-quick-checkout'); ?>
        </h2>
        <p>
        <?php echo __('- Enable instant login using trucaller for your Customer journey.', 'skyfinity-quick-checkout'); ?>
        </p>
        <p class="backend-sub-title">
        </p>
        <div class="fields-wrapper">
        <label>
            <?php echo __('Enable / Disable', 'skyfinity-quick-checkout'); ?>
        </label>
        <?php
        $this->login_with_truecaller_callback();
        ?>
        </div>
        <h2 class="backend-title">
            <?php echo __('Truecaller', 'skyfinity-quick-checkout'); ?>
        </h2>
        <p>
            <?php echo __('- Enable instant and seamless verification across your Customer journey.', 'skyfinity-quick-checkout'); ?>
        </p>
        <p class="backend-sub-title">
            <?php echo __('- First <a href="https://verification-sdk-console.truecaller.com/sign-up" class="yellow-link" target="_blank">Signup</a> for a Truecaller account or <a href="https://verification-sdk-console.truecaller.com/login" class="yellow-link" target="_blank">Login</a> if you have an existing account.', 'skyfinity-quick-checkout'); ?>
        </p>

        <div class="fields-wrapper">
            <label>
                <?php echo __('API Key', 'skyfinity-quick-checkout'); ?>
            </label>
            <?php
            $this->truecaller_key_callback();
            ?>
        </div>
        <div class="fields-wrapper">
            <label>
                <?php echo __('App Name', 'skyfinity-quick-checkout'); ?>
            </label>
            <?php
            $this->truecaller_appname_callback(); ?>
        </div>
        <?php
    }

    /**
     * View other settings tab content
     */
    public function view_other_settings_callback()
    {
        $this->MSG91_authkey_callback(); ?>

        <h2 class="backend-title">
            <?php echo __('Google Map API key', 'skyfinity-quick-checkout'); ?>
        </h2>
        <div class="fields-wrapper">
            <label>
                <?php echo __('Google Map API Key', 'skyfinity-quick-checkout'); ?>
            </label>
            <?php
                $this->google_map_api_key_callback();
            ?>
        </div>

            <h2 class="backend-title">
                <?php echo __('Remove Data on Uninstall Plugin', 'skyfinity-quick-checkout'); ?>
            </h2>
            <p class="backend-sub-title">
                <?php echo __('- If Enabled, On Uninstall plugin it will remove all saved data in custom tables. plugin settings, user addresses etc...', 'skyfinity-quick-checkout'); ?>
            </p>
            <div class="fields-wrapper">
                <label>
                    <?php echo __('Enable / Disable', 'skyfinity-quick-checkout'); ?>
                </label>
                <?php $this->remove_data_settings_key_callback(); ?>
            </div>
    <?php
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function login_with_truecaller_callback()
    {
        ?>
        <div class="field">
            <input type="checkbox" class="skyfinityqc-ui-toggle" id="login_with_truecaller"
                name="skyfinity_quick_checkout_setting[login_with_truecaller]" value="1" <?php echo isset($this->options['login_with_truecaller']) && ((int) $this->options['login_with_truecaller'] === 1) ? 'checked' : ''; ?> />
        </div>
        <?php
    }
    /** 
     * Get the settings option array and print one of its values
     */
    public function truecaller_key_callback()
    {
        printf(
            '<input type="text" id="true_caller_key" name="skyfinity_quick_checkout_setting[true_caller_key]" class="field" value="%s" />',
            isset($this->options['true_caller_key']) ? esc_attr($this->options['true_caller_key']) : ''
        );
    }

    public function truecaller_appname_callback()
    {
        printf(
            '<input type="text" id="true_caller_appname" name="skyfinity_quick_checkout_setting[true_caller_appname]"  class="field" value="%s" />',
            isset($this->options['true_caller_appname']) ? esc_attr($this->options['true_caller_appname']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function google_map_api_key_callback()
    {

        printf(

            '<input type="text" id="google_map_api_key" name="skyfinity_quick_checkout_setting[google_map_api_key]" class="field" value="%s" />',

            isset($this->options['google_map_api_key']) ? esc_attr($this->options['google_map_api_key']) : ''

        );

    }

    public function MSG91_authkey_callback()
    {
        printf(
            '<input type="hidden" id="MSG91_authkey" name="skyfinity_quick_checkout_setting[MSG91_authkey]" value="%s" />',
            isset($this->options['MSG91_authkey']) ? esc_attr($this->options['MSG91_authkey']) : '367472ASGUQxDdBL6453ac17P1'
        );
        printf(
            '<input type="hidden" id="MSG91_tempelate_id" name="skyfinity_quick_checkout_setting[MSG91_tempelate_id]" value="%s" />',
            isset($this->options['MSG91_tempelate_id']) ? esc_attr($this->options['MSG91_tempelate_id']) : '64532f56d6fc05412a39b1b3'
        );
    }

    /** 
     * Get the other settings option array and print one of its values
     */
    public function autofill_settings_key_callback()
    {
        ?>
        <div class="field">
            <input type="checkbox" class="skyfinityqc-ui-toggle" id="autofill" name="skyfinity_quick_checkout_setting[autofill]"
                value="1" <?php echo isset($this->options['autofill']) && ((int) $this->options['autofill'] === 1) ? 'checked' : ''; ?> />
        </div>
        <?php
    }

    /** 
     * Get the other settings option array and print one of its values
     */
    public function fetch_coupon_settings_key_callback()
    {

        ?>
        <div class="field">
            <input type="checkbox" class="skyfinityqc-ui-toggle" id="fetch_coupon"
                name="skyfinity_quick_checkout_setting[fetch_coupon]" value="1" <?php echo isset($this->options['fetch_coupon']) && ((int) $this->options['fetch_coupon'] === 1) ? 'checked' : ''; ?> />
        </div>
        <?php
    }
    
    /** 
     * Get the other settings option array and print one of its values
     */
    public function remove_data_settings_key_callback()
    {
        ?>
        <div class="field">
            <input type="checkbox" class="skyfinityqc-ui-toggle" id="remove_data_on_uninstall"
                name="skyfinity_quick_checkout_setting[remove_data_on_uninstall]" value="1" <?php echo isset($this->options['remove_data_on_uninstall']) && ((int) $this->options['remove_data_on_uninstall'] === 1) ? 'checked' : ''; ?> />
        </div>
        <?php
    }

    /** 
     * Get the other settings option array and print one of its values
     */
    public function view_themes_callback()
    {
        $theme_colors = skyfinityqc_get_theme_colors();
        ?>
        <h2 class="backend-title">
            <?php echo __('Themes', 'skyfinity-quick-checkout'); ?>
        </h2>
        <p class="backend-sub-title">
            <?php echo __('- Choose from two color themes or customize your own based on your requirements using the custom theme option.', 'skyfinity-quick-checkout'); ?>
        </p>
        <ul class="theme-settings field skyfinityqc_custom-radios">
            <?php
            foreach ($theme_colors as $key => $theme) {
                ?>
                <li class="theme-item">
                    <input type="radio" class="skyfinityqc-ui-toggle" id="view_theme_<?php echo esc_attr($key); ?>"
                        name="skyfinity_quick_checkout_setting[view_theme]" value="<?php echo esc_attr($key); ?>" <?php
                           echo isset($this->options['view_theme']) && $this->options['view_theme'] === $key ? 'checked="true"' : '';
                           ?> />
                    <label for="view_theme_<?php echo esc_attr($key); ?>">
                        <span>
                            <img src="<?php echo esc_attr(SKYFINITYQC_ASSETS_URL); ?>images/check-icn.svg" alt="Checked" />
                        </span>
                        Theme
                        <?php echo esc_attr($key); ?>
                    </label>
                    <div class="colors">
                        <?php foreach ($theme as $color_name => $color_code) { ?>
                            <span class="color-item" style="background-color: <?php echo esc_attr($color_code); ?>"
                                title="<?php echo esc_attr($color_name); ?>"></span>
                        <?php } ?>
                    </div>
                </li>
                <?php
            }
            ?>
            <li>
                <input type="radio" class="skyfinityqc-ui-toggle" id="view_theme_custom"
                    name="skyfinity_quick_checkout_setting[view_theme]" value="custom" <?php echo isset($this->options['view_theme']) && ($this->options['view_theme'] === 'custom') ? 'checked' : ''; ?> />
                <label for="view_theme_custom">
                    <span>
                        <img src="<?php echo esc_attr(SKYFINITYQC_ASSETS_URL); ?>images/check-icn.svg" alt="Checked" />
                    </span>
                    Custom
                </label>
                <ul class="custom-colors">
                    <?php
                    $fields_color = ['color-primary', 'color-primary-light', 'color-secondary'];
                    foreach ($fields_color as $key => $color) {
                        ?>
                        <li>
                            <label for="custom_color_<?php echo esc_attr($color); ?>"><?php echo esc_attr($color); ?></label>
                            <input type="text" name="skyfinity_quick_checkout_setting[custom_theme][<?php echo esc_attr($color); ?>]"
                                value="<?php echo esc_attr(isset($this->options['custom_theme'][$color]) ? $this->options['custom_theme'][$color] : ''); ?>"
                                id="custom_color_<?php echo esc_attr($color); ?>" data-default-color="#444" class="skyfinityqc-color-field" />
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </li>
        </ul>
        <?php
    }

    /** 
     * Get the coupon settings option array and print one of its values
     */
    public function view_coupon_callback()
    {
        $isCouponEnabledWoo = wc_coupons_enabled();
        if ($isCouponEnabledWoo) { 
            $selected_coupons = isset($this->options['selected_coupons']) && $this->options['selected_coupons'] != '' ? $this->options['selected_coupons'] : [];
            $coupon_posts = get_posts(
                array(
                    'posts_per_page' => -1,
                    'orderby' => 'name',
                    'order' => 'asc',
                    'post_type' => 'shop_coupon',
                    'post_status' => 'publish',
                )
            );
            
            ?>
            <h2 class="backend-title">
                <?php echo __('Coupon', 'skyfinity-quick-checkout'); ?>
            </h2>
            <p class="backend-sub-title">
                <?php echo __('- Fetch coupon list from WooCommerce coupons.', 'skyfinity-quick-checkout'); ?>
            </p>
            <div class="fields-wrapper">
                <label>
                    <?php echo __('Enable / Disable', 'skyfinity-quick-checkout'); ?>
                </label>
                <?php $this->fetch_coupon_settings_key_callback(); ?>
            </div>
            
            <div class="fields-wrapper">
                <label>
                    <?php echo __('Select Coupons', 'skyfinity-quick-checkout'); ?>
                </label>
                <select class="skyfinityqc-control select2" id="skyfinityqc_coupons" name="skyfinity_quick_checkout_setting[selected_coupons][]" multiple="true">
                    <?php 
                    foreach ($coupon_posts as $key => $coupon_post) {
                        ?>
                        <option value="<?php echo esc_attr($coupon_post->post_title); ?>" <?php echo in_array($coupon_post->post_title, $selected_coupons) ? "selected" : ''; ?>>
                            <?php echo esc_html($coupon_post->post_title); ?>
                        </option>
                        <?php
                    }
                    ?>
                </select>
            </div>
            <script type="text/javascript">
                 jQuery(document).ready(function() {
                    jQuery('#skyfinityqc_coupons').select2({multiple: true, width: '300px', dropdownCssClass: "bigdrop"});
                });
            </script>
        <?php }
    }

    /** 
     * Get the other settings option array and print one of its values
     */
    public function minicart_sidebar_callback()
    {
        $theme_colors = skyfinityqc_get_theme_colors();
        ?>
        <h2 class="backend-title">
            <?php echo __('Minicart Sidebar', 'skyfinity-quick-checkout'); ?>
        </h2>
        <p class="backend-sub-title">
            <?php echo __('- allow you customer to view mini cart sidebar in any page and checkout from any page.', 'skyfinity-quick-checkout'); ?>
        </p>
        
        <div class="fields-wrapper">
            <label>
                <?php echo __('Enable / Disable', 'skyfinity-quick-checkout'); ?>
            </label>
            <?php $this->minicart_sidebar_status_callback(); ?>
        </div>


        <h2 class="backend-title">
            <?php echo __('Add Minicart Icon to Primary Menu', 'skyfinity-quick-checkout'); ?>
        </h2>
        <p class="backend-sub-title">
            <?php echo __('- add sidebar open navigation to your primary menu, only work if your menu has set as `primary` navigation menu', 'skyfinity-quick-checkout'); ?>
        </p>
        <div class="fields-wrapper">
            <label>
                <?php echo __('Enable / Disable', 'skyfinity-quick-checkout'); ?>
            </label>
            <?php $this->minicart_sidebar_add_to_menu_callback(); ?>
        </div>
         <p class="backend-sub-title">
            <?php 
             printf('- If you wish to add mini cart icon button to any other place in your site you can use this shortcode %s ','<code>[skyfinityqc_minicart_sidebar_open_button]</code>'); 
            ?>
        </p>
        <?php
    }

    /** 
     * Get the other settings option array and print one of its values
     */
    public function minicart_sidebar_status_callback()
    {
        ?>
        <div class="field">
            <input type="checkbox" class="skyfinityqc-ui-toggle" id="minicart_sidebar_status"
                name="skyfinity_quick_checkout_setting[minicart_sidebar_status]" value="1" <?php echo isset($this->options['minicart_sidebar_status']) && ((int) $this->options['minicart_sidebar_status'] === 1) ? 'checked' : ''; ?> />
        </div>
        <?php
    }

    /** 
     * Get the other settings option array and print one of its values
     */
    public function minicart_sidebar_add_to_menu_callback()
    {
        ?>
        <div class="field">
            <input type="checkbox" class="skyfinityqc-ui-toggle" id="minicart_sidebar_add_to_menu"
                name="skyfinity_quick_checkout_setting[minicart_sidebar_add_to_menu]" value="1" <?php echo isset($this->options['minicart_sidebar_add_to_menu']) && ((int) $this->options['minicart_sidebar_add_to_menu'] === 1) ? 'checked' : ''; ?> />
        </div>
        <?php
    }
}
if (is_admin())
    $skyfinity_quick_setting_page = new SkyfinityQuickCheckoutSettingsPage();
