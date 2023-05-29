<?php

/*
  Plugin Name: FAQ Collector - WooCommerce Product Faq Manager Addon
  Plugin URI: https://www.bluewindlab.net
  Description: Finding a nice way to collect questions from user end for your WooCommerce powered site? FAQ collector addon provide you that way to get user questions directly from product page and make a great list of FAQ for your current and upcoming users.
  Author: Md Mahbub Alam Khan
  Version: 1.1.1
  WP Requires at least: 6.0+
  Author URI: https://www.bluewindlab.net
  Text Domain: bwl-wpfmfc
 */

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

Class BWL_Wpfm_Fc_Addon {

    function __construct() {

        /* ------------------------------ PLUGIN COMMON CONSTANTS --------------------------------- */

        define("BWL_WPFM_FCA_DIR", plugins_url() . '/wpfm-faq-collector-addon/');
        define("BWL_WPFM_FCA_PLUGIN_VERSION", '1.1.1');

        // Call Immediatly Initialized.      
        include_once dirname(__FILE__) . '/includes/bwpfm-fca-check-compatibility.php';

        $wpfm_fca_compatibily_status = wpfm_fca_compatibily_status();

        if ($wpfm_fca_compatibily_status == 1) {

            $this->included_files();
            $this->enqueue_plugin_scripts();
        } else {

            $this->wpfm_fca_compatibily_notice();
        }
    }

    function wpfm_fca_requirement_admin_notices() {
        echo '<div class="updated"><p>You need to download & install both '
        . '<b><a href="http://downloads.wordpress.org/plugin/woocommerce.zip" target="_blank">WooCommerce Plugin</a></b> && '
        . '<b><a href="https://1.envato.market/wpfm-wp" target="_blank">WooCommerce Product Faq Manager Plugin</a></b> '
        . 'to use <b>FAQ Collector Addon</b> ! </p></div>';
    }

    function wpfm_fca_compatibily_notice() {

        add_action('admin_notices', array($this, 'wpfm_fca_requirement_admin_notices'));
    }

    function included_files() {

        include_once dirname(__FILE__) . '/includes/bwpfm-fca-helpers.php';
        include_once dirname(__FILE__) . '/shortcode/wpfm-fca-shortcodes.php';

        $wpfm_fca_display_tab = 1;

        if ($wpfm_fca_display_tab == 1) {

            add_filter('woocommerce_product_tabs', array($this, 'bwpfm_add_custom_product_tab'));
        }

        if (is_admin()) {

            include_once dirname(__FILE__) . '/includes/bwpfm-fca-custom-meta-box.php';
        }
    }

    function enqueue_plugin_scripts() {

        if (!is_admin()) {

            $bwpfm_data = get_option('bwpfm_options');

            wp_register_style('bwl-wpfm-fca-styles', plugins_url('css/wpfm-fac-custom-style.css', __FILE__), array(), BWL_WPFM_FCA_PLUGIN_VERSION);
            wp_register_script('bkb-wpfm-fac-custom-scripts', plugins_url('js/wpfm-fac-custom-scripts.js', __FILE__), array('jquery'), BWL_WPFM_FCA_PLUGIN_VERSION, FALSE);

            //@Load FAQ Collector Form Stylesheet.
            wp_enqueue_style('bwl-wpfm-fca-styles');

            //@Load RTL Stylesheet.
            if (is_rtl()) {
                wp_enqueue_style('bwl-wpfm-fac-rtl-styles', plugins_url('css/wpfm-fac-rtl.css', __FILE__));
            }
        }
    }

    function bwpfm_add_custom_product_tab($tabs) {

        if(! is_product()) {
           return '';
        }
        
        global $product;

        $bwpfm_data = get_option('bwpfm_options');

        $bwpfm_ask_tab_title = esc_html__("Ask A Question", 'bwl-wpfmfc');

        if (isset($bwpfm_data['bwpfm_ask_tab_title']) && $bwpfm_data['bwpfm_ask_tab_title'] != "") {

            $bwpfm_ask_tab_title = trim($bwpfm_data['bwpfm_ask_tab_title']);
        }

        $bwpfm_ask_tab_position = 101; // Set faq tab in last position.

        if (isset($bwpfm_data['bwpfm_ask_tab_position']) && is_numeric($bwpfm_data['bwpfm_ask_tab_position'])) {

            $bwpfm_ask_tab_position = trim($bwpfm_data['bwpfm_ask_tab_position']);
        }


        $wpfm_fc_display_fca_status = get_post_meta($product->get_id(), 'wpfm_fc_display_faq', true); 

        if (isset($wpfm_fc_display_fca_status) && $wpfm_fc_display_fca_status == 1) {

            return $tabs;
        }

        // Added in version 1.0.6

        $fca_container_extra_class = ( isset($bwpfm_data['fca_container_extra_class']) && trim($bwpfm_data['fca_container_extra_class']) != "" ) ? $bwpfm_data['fca_container_extra_class'] : '';
        $title_min_length = ( isset($bwpfm_data['bwpfm_title_min_length']) && trim($bwpfm_data['bwpfm_title_min_length']) != "" && is_numeric($bwpfm_data['bwpfm_title_min_length']) ) ? $bwpfm_data['bwpfm_title_min_length'] : 3;
        $title_max_length = ( isset($bwpfm_data['bwpfm_title_max_length']) && trim($bwpfm_data['bwpfm_title_max_length']) != "" && is_numeric($bwpfm_data['bwpfm_title_max_length']) ) ? $bwpfm_data['bwpfm_title_max_length'] : 100;


        $tabs['bwpfm_fca_tab'] = array(
            'title' => $bwpfm_ask_tab_title,
            'priority' => $bwpfm_ask_tab_position, // Always display at the end of tab :)
            'callback' => array($this, 'bwpfm_fca_tab_panel_content'),
            'content' => do_shortcode('[bwl_fca_form product_id="' . $product->get_id() . '" title_min_length="' . $title_min_length . '" title_max_length="' . $title_max_length . '" fca_container_extra_class="' . $fca_container_extra_class . '" /]') // custom field
        );

        return $tabs;
    }

    function bwpfm_fca_tab_panel_content($key, $tab) {

        // allow shortcodes to function
        $content = str_replace(']]>', ']]&gt;', $tab['content']);
        echo apply_filters('woocommerce_custom_fca_content', $content, $tab);
    }

}

/* ------------------------------ Initialization --------------------------------- */

function init_wpfm_fc_addon() {
    new BWL_Wpfm_Fc_Addon();
}

add_action('init', 'init_wpfm_fc_addon');

load_plugin_textdomain('bwl-wpfmfc', FALSE, dirname(plugin_basename(__FILE__)) . '/lang/');