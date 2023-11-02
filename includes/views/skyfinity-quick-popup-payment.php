<?php

defined('ABSPATH') || exit;

?>
<!-- <div class="step_title_wrap">
    <h4 class="">03</h4>
    <h5 class="step-title">
        <?php // echo __("Payment", "skyfinity-quick-checkout"); 
        ?>
    </h5>
</div> -->
<div class="payment-method-list-wrap">
    <div class="payment-method-list">
    </div>
    <span id="payment-method-error" class=" error-message"></span>
</div>
<div class="mt-1 d-flex card-primary m-bottom-fixed">
    <?php /* ?><!-- <div id="skyfinityqc-modal-total-content-payment">
        <?php echo WC()->cart->get_total(); ?>
    </div>
    <div>
        <button class="button btn-navigate-form-step" type="button" step_number="2">
            <?php echo __("Back", "skyfinity-quick-checkout"); ?>
        </button>
        <button class="button submit-btn  btn-navigate-form-step" type="button" data-next="true">Checkout</button>
    </div> -->
    <?php */ ?>
    <p class="payment-footer-line">Select a payment gateway to continue</p>
    <button class="button" id="continue-with-cod" type="button" style="display: none">
        <?php echo __("Continue", "skyfinity-quick-checkout"); ?>
    </button>
</div>