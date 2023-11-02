<?php /* ?>
<div class="skyfinityqc-step-info">
<h5>
<?php
$site_title = get_bloginfo('name');
echo $site_title !== '' ? $site_title : __('Skyfinity Quick Checkout', 'skyfinity-quick-checkout');
// echo __('Last Few Steps , To Complete Your Order', 'skyfinity-quick-checkout'); ?>
</h5>
<p>
<?php // echo __('To complete your order , kindly fill the neccessary details to help us reach your order successfully.', 'skyfinity-quick-checkout'); ?>
</p>
</div>
<?php */

defined('ABSPATH') || exit;

$cross_sells_ids_in_cart = array();

?>
<div class="skyfinityqc-order-summary" id="skyfinityqc-modal-order-summary-content">
    <?php /* ?>
    <div class="card-white order-summary">
        <div class="d-flex align-item-center justify-center">
            <svg class="sq-icon-image" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M2 2H3.74001C4.82001 2 5.67 2.93 5.58 4L4.75 13.96C4.61 15.59 5.89999 16.99 7.53999 16.99H18.19C19.63 16.99 20.89 15.81 21 14.38L21.54 6.88C21.66 5.22 20.4 3.87 18.73 3.87H5.82001" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M16.25 22C16.9404 22 17.5 21.4404 17.5 20.75C17.5 20.0596 16.9404 19.5 16.25 19.5C15.5596 19.5 15 20.0596 15 20.75C15 21.4404 15.5596 22 16.25 22Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M8.25 22C8.94036 22 9.5 21.4404 9.5 20.75C9.5 20.0596 8.94036 19.5 8.25 19.5C7.55964 19.5 7 20.0596 7 20.75C7 21.4404 7.55964 22 8.25 22Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M9 8H21" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
            <?php echo __('Order Summary', 'skyfinity-quick-checkout'); ?>
        </div>
        <div>
            <?php echo WC()->cart->get_total(); ?>
        </div>
    </div>
    <?php */ ?>
    <h3 class="mobile_order_summary_title"><?php echo __("Order Summary", "skyfinity-quick-checkout"); ?> </h3>

    <div class="product-list">
        <?php
        $cart = WC()->cart->get_cart();
        $count = WC()->cart->get_cart_contents_count();
        if ($count > 0) {
            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $_product = wc_get_product($cart_item['data']->get_id());
                $product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                $cross_sells_ids_in_cart = array_merge( $cart_item['data']->get_cross_sell_ids(), $cross_sells_ids_in_cart );
                $getProductDetail = wc_get_product($cart_item['product_id']);
                ?>
                <div class="card-white product-card">
                    <div class="product-img">
                        <?php
                        echo $getProductDetail->get_image('thumbnail'); // accepts 2 arguments ( size, attr )
                        ?>
                    </div>
                    <div class="product-name">
                        <?php
                        echo '<span class="product-heading">' . $_product->get_title() . '</span>';
                        echo '<span> Quantity : ' . $cart_item['quantity'] . '</span>';
                        ?>
                    </div>
                    <div class="product-total">
                        <?php echo $cart_item['data']->get_price_html(); ?>
                    </div>
                    <div class="skyfinityqc_popup__item_remove">
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
        } else {
            ?>
            <div class="empty-cart">
                <h5> <?php echo __("Your Cart is Empty!", 'skyfinity-quick-checkout'); ?></h5>
                <a class='button ' href="<?php echo esc_attr(get_permalink( wc_get_page_id( 'shop' ) )); ?>">Go to Shop page</a>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
    $cross_sells_ids_in_cart_arr = [];
    foreach ($cross_sells_ids_in_cart as $pid) {
        $found = false;
        foreach( WC()->cart->get_cart() as $c_item ) {
           
            if ( $c_item['variation_id'] != '' && $c_item['product_id'] == $pid) {
                $found = true;
            } else if ( $c_item['product_id'] == $pid )
                $found = true;
        }
        if(!$found) {
            $cross_sells_ids_in_cart_arr[] = $pid;
        }
    }
    if (count($cross_sells_ids_in_cart_arr) > 0) { ?>
        <h3 class="cross_sell_title"><?php echo apply_filters( 'woocommerce_product_cross_sells_products_heading', __( 'You may be interested in&hellip;', 'woocommerce' ) ); ?></h3>
        <div class="product-list cross-sell-products">
            <?php
            foreach ($cross_sells_ids_in_cart_arr as $cross_sells_id) {
                $_product = wc_get_product($cross_sells_id);
                $is_variable_product = $_product->is_type( 'variable' );
                $variable_id = '';
                $product_title = $_product->get_title();
                $product_price = $_product->get_price_html();
                if ($is_variable_product) {
                    $variables = $_product->get_available_variations();
                    if(count($variables) > 0) {
                        $variable_id = $variables[0]['variation_id'];
                        // $product_title = $variables[0]['title'];
                        $product_price = $variables[0]['price_html'];
                    }
                }
                ?>
                <div class="card-white product-card">
                    <div class="product-img">
                        <?php
                        echo $_product->get_image('thumbnail'); // accepts 2 arguments ( size, attr )
                        ?>
                    </div>
                    <div class="product-name">
                        <?php
                        echo '<span class="product-heading">' . $product_title . '</span>';
                        ?>
                        <div> 
                            <?php 
                            
                            echo apply_filters(
                            'woocommerce_loop_add_to_cart_link', // WPCS: XSS ok.
                            sprintf(
                                '<a href="%s" data-product_id="%s" data-variation_id="%s" data-quantity="%s" class="%s" %s>%s</a>',
                                esc_url( $_product->add_to_cart_url() ),
                                esc_attr( $_product->get_id() ),
                                esc_attr( $variable_id ),
                                esc_attr( 1 ),
                                esc_attr( 'button btn-sm ajax_add_to_cart_button' ),
                                '',
                                'add to cart'
                            ),
                            $_product,
                            []
                        );
                        ?></div>
                    </div>
                    <div class="product-total">
                        <?php echo $product_price;  ?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    <?php } ?>

    <div class="totals">
        <div class="card-white totals">
            <div class="total-wrap">
                <div class="total-item sub-total">
                    <div>
                        <?php echo __('Subtotal', 'skyfinity-quick-checkout'); ?>
                    </div>
                    <div>
                        <?php echo WC()->cart->get_cart_subtotal(); ?>
                    </div>
                </div>
                <!-- other costs -->
                <?php

                if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()  && WC()->cart->show_shipping() == 1) {
                    ?>
                    <div class="total-item">
                        <div>
                            <?php echo __('Shipping Charges', 'skyfinity-quick-checkout'); ?>
                        </div>
                        <div>
                            <?php echo wc_price(WC()->cart->get_shipping_total()); ?>
                        </div>
                    </div>
                    <?php
                }
                $fees = WC()->cart->get_fees();
                if (count($fees) >= 1) {
                    foreach ( $fees as $fee ) : ?>
                        <div class="total-item">
                            <div><?php echo $fee->name; ?></div>
                            <div><?php wc_cart_totals_fee_html( $fee ); ?></div>
                        </div>
                        <?php 
                    endforeach;
                }
                $cart_totals = WC()->cart->get_totals();             
                if ($cart_totals['fee_total'] > 0) {
                    ?>
                    <div class="total-item">
                        <div><?php echo esc_html( 'COD Charges' ); ?></div>
                        <div><?php echo wc_price( $cart_totals['fee_total'] ); ?></div>
                    </div>
                    <?php
                }
                if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) {
                    $taxable_address = WC()->customer->get_taxable_address();
                    $estimated_text  = '';

                    if ( WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping() ) {
                        /* translators: %s location. */
                        $estimated_text = sprintf( ' <small>' . esc_html__( '(estimated for %s)', 'woocommerce' ) . '</small>', WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] );
                    }

                    if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
                        foreach ( WC()->cart->get_tax_totals() as $code => $tax ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
                            ?>
                            <div class="total-item">
                                <div><?php echo  $tax->label . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                                <div><?php echo wp_kses_post( $tax->formatted_amount ); ?></div>
                            </div>
                            <?php
                        }
                    } else {
                        ?>
                        <div class="total-item">
                            <div><?php echo WC()->countries->tax_or_vat() . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                            <div><?php wc_cart_totals_taxes_total_html(); ?></div>
                        </div>
                        <?php
                    }
                }
                
                $coupons = WC()->cart->get_applied_coupons();
                // Loop through coupons.
                foreach ($coupons as $coupon) {
                    $coupon = new WC_Coupon($coupon);
                    ?>
                    <div class="total-item discount">
                    <div class="discount-price">
                            <?php echo __('Coupon Discount ', 'skyfinity-quick-checkout');
                            // echo $coupon->get_code(); ?>
                        </div>
                        <div class="discount-price">
                            <?php
                            if ($coupon->get_discount_type() == "percent") {
                                echo $coupon->get_amount() .'%';
                            } else {
                                echo '-' .wc_price($coupon->get_amount());
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <!-- <hr /> -->
            <div class="final-total-wrap">
                <div class="total-item">
                    <div>
                        <?php echo __('Price', 'skyfinity-quick-checkout'); ?>
                    </div>
                    <div>
                        <?php echo WC()->cart->get_total(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>