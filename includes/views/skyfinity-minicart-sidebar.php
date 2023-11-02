<?php

defined('ABSPATH') || exit;

// skyfinity_quick_checkout_setting[minicart_sidebar_status]
class SKYFINITYQC_MINI_CART
{
    private $plugin_options;
    public function __construct()
    {
        $this->plugin_options = get_option('skyfinity_quick_checkout_setting');
        $this->init();
    }

    public function init()
    {
        if (isset($this->plugin_options['minicart_sidebar_status']) && $this->plugin_options['minicart_sidebar_status'] == 1) {
            add_shortcode('skyfinityqc_minicart_sidebar_open_button', array($this, 'skyfinityqc_minicart_sidebar_open_button_cb'));
            // Hooked: The mini cart count and the cart content
            add_action( 'wp_footer', array($this,'skyfinityqc_wc_mini_cart'));
            // Ajax refreshing mini cart count and content
            add_filter( 'woocommerce_add_to_cart_fragments', array($this, 'skyfinityqc_sidebar_add_to_cart_fragment'));
            add_filter( 'woocommerce_add_to_cart_fragments', array($this, 'skyfinityqc_button_add_to_cart_fragment'));

            add_action('wp_ajax_skyfinityqc_cart_qty_update', array($this, 'skyfinityqc_cart_qty_update_cb'));
            add_action('wp_ajax_nopriv_skyfinityqc_cart_qty_update', array($this, 'skyfinityqc_cart_qty_update_cb'));

            if (isset($this->plugin_options['minicart_sidebar_add_to_menu']) && $this->plugin_options['minicart_sidebar_add_to_menu'] == 1) {
                add_action('wp_nav_menu_items', array($this,'skyfinityqc_add_minicart_icon_to_primary_menu'), 10, 2);
            }
        }
    }

    public function skyfinityqc_minicart_sidebar_open_button_cb() {
        $output = '';
        ob_start();
        ?>
        <div id="skyfinityqc-show-mini-cart" class="">
            <?php $this->skyfinityqc_wc_mini_cart_count_content(); ?>
        <div>
        <?php 
        $output = ob_get_clean();
        return $output;
    }

    public function skyfinityqc_add_minicart_icon_to_primary_menu($items, $args){
        if($args->theme_location == 'primary'){
            $items = $items.'<li class="menu-item menu-item-type-post_type menu-item-object-page">'.do_shortcode('[skyfinityqc_minicart_sidebar_open_button]').'</li>';
        }
        return $items;
    }

    public function skyfinityqc_button_add_to_cart_fragment( $fragments ) {
        ob_start();
        ?>
        <div id="skyfinityqc-show-mini-cart" class="">
            <?php $this->skyfinityqc_wc_mini_cart_count_content(); ?>
        <div>
        <?php

        $fragments['#skyfinityqc-show-mini-cart'] = ob_get_clean();
        return $fragments;
    }

    public function skyfinityqc_wc_mini_cart_count_content() {
        $count = WC()->cart->get_cart_contents_count();
        ?>
         <span class="skyfinityqc-show-mini-cart-button">
            <span class="skyfinityqc-cart-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart3" viewBox="0 0 16 16"> <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/> </svg><small class="skyfinityqc-qty"><?php echo esc_html($count); ?></small></span>
        </span>
        <?php
    }

    public function skyfinityqc_sidebar_add_to_cart_fragment( $fragments ) {
        ob_start();
        ?>
        <div id="skyfinityqc-mini-cart-content" class="">
            <?php $this->skyfinityqc_wc_mini_cart_content(); ?>
        <div>
        <?php
        $fragments['#skyfinityqc-mini-cart-content'] = ob_get_clean();
        return $fragments;
    }
    public function skyfinityqc_wc_mini_cart() {
        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            
            ?>
            <div id="skyfinityqc-mini-cart-sidebar" class="">
                <div id="skyfinityqc_mini_cart_loader">
                    <svg version="1.1" id="L2" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100" enable-background="new 0 0 100 100" xml:space="preserve">
                        <circle fill="currntColor" stroke="#fff" stroke-width="4" stroke-miterlimit="10" cx="50" cy="50" r="48" />
                        <line fill="none" stroke-linecap="round" stroke="#fff" stroke-width="4" stroke-miterlimit="10" x1="50" y1="50" x2="85" y2="50.5">
                            <animateTransform attributeName="transform" dur="2s" type="rotate" from="0 50 50" to="360 50 50" repeatCount="indefinite" />
                        </line>
                        <line fill="none" stroke-linecap="round" stroke="#fff" stroke-width="4" stroke-miterlimit="10" x1="50" y1="50" x2="49.5" y2="74">
                            <animateTransform attributeName="transform" dur="15s" type="rotate" from="0 50 50" to="360 50 50" repeatCount="indefinite" />
                        </line>
                    </svg>
                </div>
                <div class="skyfinityqc-minicart-sidebar-inner">
                    <span id="skyfinityqc-close-mini-cart">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"> <g> <path fill="none" d="M0 0h24v24H0z"/> <path d="M12 10.586l4.95-4.95 1.414 1.414-4.95 4.95 4.95 4.95-1.414 1.414-4.95-4.95-4.95 4.95-1.414-1.414 4.95-4.95-4.95-4.95L7.05 5.636z"/> </g> </svg>
                    </span>
                    <div id="skyfinityqc-mini-cart-content" class="">
                        <?php $this->skyfinityqc_wc_mini_cart_content(); ?>
                    </div>
                </div>
            </div>
            <script type="text/javascript">
                jQuery(document).ready(function(){
                    const body = document.querySelector("body");
                    jQuery( document.body ).on( 'added_to_cart', function(){
                        jQuery('#skyfinityqc-mini-cart-sidebar').addClass('show');
                        body.style.overflow = "hidden";
                    });

                    jQuery(document).on('click', '.skyfinityqc-show-mini-cart-button', function() {
                        // Check if the cart item count is 0
                        var cart_item = <?php print WC()->cart->get_cart_contents_count(); ?>;
                        if (cart_item === 0) {
                            window.location.href = "<?php echo get_permalink( wc_get_page_id( 'shop' ) ); ?>"
                        } else {
                            jQuery('#skyfinityqc-mini-cart-sidebar').addClass('show');
                            document.body.style.overflow = "hidden";
                        }
                    });
                        
                    jQuery(document).on('click','#skyfinityqc-close-mini-cart', function() {
                        jQuery('#skyfinityqc-mini-cart-sidebar').removeClass('show');
                        body.style.overflow = "auto";
                    });

                     jQuery( document ).on( 'blur', '.skyfinityqc-mini-cart-products input.qty', function() {
                        var $thisbutton = jQuery(this);
                        var item_hash = jQuery( this ).attr( 'name' ).replace(/cart\[([\w]+)\]\[qty\]/g, "$1");
                        var item_quantity = jQuery( this ).val();
                        var currentVal = parseFloat(item_quantity);
                        jQuery('#skyfinityqc_mini_cart_loader').addClass('show');
                        jQuery.ajax({
                            type: 'POST',
                            url: '<?php echo admin_url("admin-ajax.php"); ?>',
                            data: {
                                action: 'skyfinityqc_cart_qty_update',
                                hash: item_hash,
                                quantity: currentVal
                            },
                            success: function(response) {
                                jQuery(document.body).trigger('wc_fragment_refresh');
                                jQuery('#skyfinityqc_mini_cart_loader').removeClass("show");
                            },
                            error: function(error) {
                                jQuery('#skyfinityqc_mini_cart_loader').removeClass("show");
                            }
                        });  
                    });
                });
            </script>
            <?php
        }
    }

    public function skyfinityqc_cart_qty_update_cb() {

        // Set item key as the hash found in input.qty's name
        $cart_item_key = sanitize_text_field($_POST['hash']);

        // Get the array of values owned by the product we're updating
        $threeball_product_values = WC()->cart->get_cart_item( $cart_item_key );

        // Get the quantity of the item in the cart
        $threeball_product_quantity = apply_filters( 'woocommerce_stock_amount_cart_item', apply_filters( 'woocommerce_stock_amount', preg_replace( "/[^0-9\.]/", '', filter_var(sanitize_text_field($_POST['quantity']), FILTER_SANITIZE_NUMBER_INT)) ), $cart_item_key );

        // Update cart validation
        $passed_validation  = apply_filters( 'woocommerce_update_cart_validation', true, $cart_item_key, $threeball_product_values, $threeball_product_quantity );

        // Update the quantity of the item in the cart
        if ( $passed_validation ) {
            WC()->cart->set_quantity( $cart_item_key, $threeball_product_quantity, true );
        }

        // Refresh the page
        echo do_shortcode( '[woocommerce_cart]' );

        die();

    }


    // Utility function that outputs the mini cart content
    public function skyfinityqc_wc_mini_cart_content() {
        $cart = WC()->cart->get_cart();
        $count = WC()->cart->get_cart_contents_count();
        ?>
        <div class="skyfinityqc-mini-cart-header">
            <span class="skyfinityqc-cart-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart3" viewBox="0 0 16 16"> <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/> </svg><small class="skyfinityqc-qty"><?php echo esc_html($count); ?></small></span>
                <h3>Shopping Cart</h3>
        </div>
        <div class="skyfinityqc-mini-cart-products">
            <?php
            foreach ( $cart as $cart_item_key => $cart_item  ) {
                $_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                $product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                    $product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
                    $thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                    $product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
                    ?>
                    <div class="media mini-cart__item woocommerce-mini-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>">
                        <div class="product-img">
                            <?php echo esc_html($thumbnail); ?> 
                        </div>
                        <div class="media-body mini-cart__item_body">
                            <div class="mini-cart__item__heading mt-0"><?php echo esc_html($product_name); ?></div>

                            <div class="product_item_qty_wrap">
                                <?php if ($_product->is_sold_individually()) {
                                     $product_quantity = sprintf('<input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key);
                                 } else {
                                    $input_args = array(
                                        'input_name' => "cart[{$cart_item_key}][qty]",
                                        'input_value' => $cart_item['quantity'],
                                        'max_value' => $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(),
                                        'min_value' => '0'
                                    );
                                                                  
                                    $product_quantity = woocommerce_quantity_input($input_args, $_product, false);
                                }
                                echo sprintf( '%s &times; %s', apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item), $product_price );
                                ?>
                            </div>
                            <?php
                            echo apply_filters( 'woocommerce_widget_cart_item_quantity', '<div class="cart__item__price">' .
                            sprintf( '%s &times; %s', $cart_item['quantity'], $product_price ) .
                            '</div>', $cart_item, $cart_item_key );

                            if( isset($size_name) ) { ?>
                                <div class="mini-cart__item__size"><?php echo esc_html($size_name); ?></div>
                            <?php } ?>
                        </div>

                        <div class="mini-cart__item_remove ">
                            <?php
                            echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
                                '<a href="%s" class="remove remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s">&times;</a>',
                                esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                                __( 'Remove this item', 'woocommerce' ),
                                esc_attr( $product_id ),
                                esc_attr( $cart_item_key ),
                                esc_attr( $_product->get_sku() )
                            ), $cart_item_key );
                            ?>
                        </div>
                    </div>
                    <?php
                }
            } ?>
        </div>
        <div class="skyfinityqc-mini-cart-footer">
            <?php if ($count > 0) { ?>
                <div class="skyfinityqc_minicart_totals">
                    <strong>Subtotal:</strong> <span><?php echo WC()->cart->get_total(); ?></span>
                </div>
                <a href="<?php echo esc_attr(get_permalink( wc_get_page_id( 'cart' ) )); ?>" class="skyfinityqc_button"><span class="btn__text"><?php _e('View Cart', 'skyfinity_quick_checkout'); ?></span></a>
                <button id="skyfinityqc_checkout_button" type="button" class=" skyfinityqc_button ">Quick Checkout</button>
            <?php } else { ?>
                <div class="empty-cart">
                    <h5> <?php echo __("Your Cart is Empty!", 'skyfinity-quick-checkout'); ?></h5>
                    <a class='skyfinityqc_button ' href="<?php echo esc_attr(get_permalink( wc_get_page_id( 'shop' ) )); ?>">Go to Shop page</a>
                </div>
            <?php } ?>
        </div>
        <?php
    }
}
$skyfinityqc_mini_cart = new SKYFINITYQC_MINI_CART();
