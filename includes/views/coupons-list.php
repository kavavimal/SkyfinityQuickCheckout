<?php

defined('ABSPATH') || exit;

$plugin_options = get_option('skyfinity_quick_checkout_setting');
$isCouponEnabledWoo = wc_coupons_enabled();
if ($plugin_options['fetch_coupon'] == 1 && $isCouponEnabledWoo) {
    $selected_coupons = isset($plugin_options['selected_coupons']) && $plugin_options['selected_coupons'] != '' ? $plugin_options['selected_coupons'] : [];
    $coupon_posts = get_posts(
        array(
            'posts_per_page' => -1,
            'orderby' => 'name',
            'order' => 'asc',
            'post_type' => 'shop_coupon',
            'post_status' => 'publish',
        )
    );
    $selected_coupons_list = [];
    foreach ($selected_coupons as $key => $coupon) {
        $found_key = array_search($coupon, array_column($coupon_posts, 'post_title'));
        if ($found_key !== false) {
            array_push($selected_coupons_list, $coupon_posts[$found_key]);
        }
    }
    $applied_coupons = WC()->cart->get_applied_coupons();
    foreach ($selected_coupons_list as $key => $coupon_post) {
        $is_applied = in_array(strtolower($coupon_post->post_title), $applied_coupons); ?>
        <div class="coupon-card <?php echo $is_applied ? 'applied' : ''; ?>">
            <div class="coupon-row">
                <span id="cpnCode">
                    <?php echo esc_html($coupon_post->post_title); ?>
                </span>
                <?php if ($is_applied) { ?>
                    <div>
                        <!-- <span class="button btn-sm">Applied</span> -->
                        <button type="button" id="cpnRemoveBtn<?php echo esc_attr($key); ?>" class="button btn-sm skyfinityqc_remove_coupon_btn"
                            data-coupon-code="<?php echo esc_attr($coupon_post->post_title); ?>">Remove</button>
                    </div>
                <?php } else { ?>
                    <button type="button" id="cpnBtn<?php echo esc_attr($key); ?>" class="button btn-sm skyfinityqc_cpnBtn"
                        data-coupon-code="<?php echo esc_attr($coupon_post->post_title); ?>">Apply</button>
                <?php } ?>
            </div>
            <p class="coupon-desc">
                <?php echo esc_html($coupon_post->post_excerpt); ?>
            </p>
            <div class="circle1"></div>
            <div class="circle2"></div>
        </div>
    <?php }
} ?>