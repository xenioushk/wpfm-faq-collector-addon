<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Check if WooCommerce is active
 * Added Multisite Support.
 **/

function wpfm_fca_compatibily_status()
{
    if (class_exists('BWL_Woo_Faq_Manager') && class_exists('WooCommerce')) {

        return 1;
    } else {

        return 0;
    }
}
