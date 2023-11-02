<?php

defined('ABSPATH') || exit;

class SKYFINITYQC_CHECKOUT_STEPS
{
    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        // add_action('skyfinity_quick_checkout_step_summary', array($this, 'skyfinity_quick_checkout_step_summary_cb'));
    }

    public function skyfinity_quick_checkout_step_summary_cb()
    {

    }
}
// $skyfinityqc_checkout_steps = new SKYFINITYQC_CHECKOUT_STEPS();
// $skyfinityqc_checkout_steps->init();