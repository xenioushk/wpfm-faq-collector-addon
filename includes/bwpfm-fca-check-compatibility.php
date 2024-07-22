<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Check the addon compatibility and parent purchase status.
 * First we check both dependent plugins status.
 * If plugins are activated, we will check the purchase status.
 * @since: 1.0.0
 * @author: Mahbub Alam Khan
 * @created: 12.12.2017
 * @updated: 22.07.2024
 * @return bool
 */

function wpfm_fca_compatibily_status()
{

    if (class_exists('BWL_Woo_Faq_Manager') && class_exists('WooCommerce')) {
        $status = 1;
        if (BWL_WPFM_PARENT_PLUGIN_PURCHASE_STATUS == 0) {
            $status = 2;
        }
    } else {
        $status = 0;
    }

    return $status;
}