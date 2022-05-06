<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Check if WooCommerce is active
 * Added Multisite Support.
 **/

function wpfm_fca_compatibily_status() {
    
    if( class_exists( 'BWL_Woo_Faq_Manager' ) && class_exists( 'WooCommerce' ) ) {
        
        return 1;
        
    } else {

        return 0;
        
    }
    
//     if ( defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE== TRUE 
//             && is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) 
//             && is_plugin_active_for_network( 'woocommerce-product-faq-manager/woocommerce-product-faq-manager.php' ) 
//             && class_exists('BWL_Woo_Faq_Manager') 
//             ) {
//        
//        return 1;
//        
//    } else if ( ( ! defined('WP_ALLOW_MULTISITE') && in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) ) &&
//        ( in_array('woocommerce-product-faq-manager/woocommerce-product-faq-manager.php', apply_filters('active_plugins', get_option('active_plugins')))  && class_exists('BWL_Woo_Faq_Manager') )) {
// 
//        return 1;
//        
//    } else {
//
//        return 0;
//        
//    }

    
}