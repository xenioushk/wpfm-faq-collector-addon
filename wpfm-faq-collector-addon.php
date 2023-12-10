<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Plugin Name: FAQ Collector - WooCommerce Product Faq Manager Addon
 * Plugin URI: https://bluewindlab.net/portfolio/faq-tab-for-woocommerce-advanced-faq-addon/
 * Description: Finding an excellent way to collect questions from the user end for your WooCommerce-powered site? FAQ collector addon provides a way to get user questions directly from the product page and make a great list of FAQs for your current and upcoming users.
 * Author: Mahbub Alam Khan
 * Version: 1.1.7
 * WP Requires at least: 6.0+
 * Author URI:     https://codecanyon.net/user/xenioushk
 * Text Domain: bwl-wpfmfc
 * Domain Path: /lang/
 * 
 * 
 * @package FAQ Collector - WooCommerce Product Faq Manager Addon
 * @author Mahbub Alam Khan
 * @license GPL-2.0+
 * @link https://codecanyon.net/user/xenioushk
 * @copyright 2023 BlueWindLab
 * 
 */


class BWL_Wpfm_Fc_Addon
{

    function __construct()
    {

        define("BWL_WPFM_FCA_PLUGIN_VERSION", "1.1.7");
        define("BWL_WPFM_FCA_DIR", plugins_url() . "/wpfm-faq-collector-addon/");
        define("BWL_WPFM_FCA_PLUGIN_UPDATER_SLUG", plugin_basename(__FILE__)); // change plugin current version in here.

        define("BWL_WPFM_FCA_CC_ID", "9992576");
        define('BWL_WPFM_FCA_INSTALLATION_TAG', 'wpfm_fca_installation_' . str_replace('.', '_', BWL_WPFM_FCA_PLUGIN_VERSION));

        // Call Immediatly Initialized.      
        include_once dirname(__FILE__) . '/includes/bwpfm-fca-check-compatibility.php';

        $wpfm_fca_compatibily_status = wpfm_fca_compatibily_status();

        if ($wpfm_fca_compatibily_status == 1) {

            $this->includeFiles();
            add_action('wp_enqueue_scripts', array($this, 'enqueueFrontendScripts'));
            add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'));
            add_action('plugins_loaded', [$this, 'loadTranslationFile']);
        } else {

            $this->wpfm_fca_compatibily_notice();
        }
    }

    function wpfm_fca_requirement_admin_notices()
    {
        echo '<div class="notice notice-error"><p>You need to download & install both '
            . '<b><a href="https://downloads.wordpress.org/plugin/woocommerce.zip" target="_blank">WooCommerce Plugin</a></b> && '
            . '<b><a href="https://1.envato.market/wpfm-wp" target="_blank">WooCommerce Product Faq Manager Plugin</a></b> '
            . 'to use <b>FAQ Collector Addon</b>!</p></div>';
    }

    function wpfm_fca_compatibily_notice()
    {

        add_action('admin_notices', array($this, 'wpfm_fca_requirement_admin_notices'));
    }

    function includeFiles()
    {

        include_once dirname(__FILE__) . '/includes/bwpfm-fca-helpers.php';
        include_once dirname(__FILE__) . '/includes/shortcode/wpfm-fca-login-form.php';
        include_once dirname(__FILE__) . '/includes/shortcode/wpfm-fca-ask-question-form.php';

        $wpfm_fca_display_tab = 1;

        if ($wpfm_fca_display_tab == 1) {

            add_filter('woocommerce_product_tabs', array($this, 'bwpfm_add_custom_product_tab'));
        }

        if (is_admin()) {

            include_once dirname(__FILE__) . '/includes/bwpfm-fca-custom-meta-box.php';

            // Integrate Plugin Update Notifier.
            require_once(__DIR__ . '/includes/autoupdater/WpAutoUpdater.php');
            require_once(__DIR__ . '/includes/autoupdater/installer.php');
            require_once(__DIR__ . '/includes/autoupdater/updater.php');
        }
    }

    function enqueueFrontendScripts()
    {

        wp_enqueue_style('wpfm-fca-frontend', plugins_url('assets/styles/frontend.css', __FILE__), [], BWL_WPFM_FCA_PLUGIN_VERSION);
        wp_register_script('wpfm-fac-frontend', plugins_url('assets/scripts/frontend.js', __FILE__), ['jquery'], BWL_WPFM_FCA_PLUGIN_VERSION, FALSE);

        //@Load FAQ Collector Form Stylesheet.
        // wp_enqueue_style('bwl-wpfm-fca-styles');

        //@Load RTL Stylesheet.
        if (is_rtl()) {
            wp_enqueue_style('wpfm-fac-frontend-rtl', plugins_url('assets/styles/frontend_rtl.css', __FILE__));
        }
    }

    function enqueueAdminScripts()
    {

        wp_localize_script(
            'jquery',
            'WpfmFcaAdminData',
            [
                'product_id' => BWL_WPFM_FCA_CC_ID,
                'installation' => get_option(BWL_WPFM_FCA_INSTALLATION_TAG)
            ]
        );
    }

    function bwpfm_add_custom_product_tab($tabs)
    {

        if (!is_product()) {
            return '';
        }

        global $product;

        $bwpfm_data = get_option('bwpfm_options');

        $bwpfm_ask_tab_title = esc_html__("Ask A Question", "bwl-wpfmfc");

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

        $fca_container_extra_class = (isset($bwpfm_data['fca_container_extra_class']) && trim($bwpfm_data['fca_container_extra_class']) != "") ? $bwpfm_data['fca_container_extra_class'] : '';
        $title_min_length = (isset($bwpfm_data['bwpfm_title_min_length']) && trim($bwpfm_data['bwpfm_title_min_length']) != "" && is_numeric($bwpfm_data['bwpfm_title_min_length'])) ? $bwpfm_data['bwpfm_title_min_length'] : 3;
        $title_max_length = (isset($bwpfm_data['bwpfm_title_max_length']) && trim($bwpfm_data['bwpfm_title_max_length']) != "" && is_numeric($bwpfm_data['bwpfm_title_max_length'])) ? $bwpfm_data['bwpfm_title_max_length'] : 100;


        $tabs['bwpfm_fca_tab'] = array(
            'title' => $bwpfm_ask_tab_title,
            'priority' => $bwpfm_ask_tab_position, // Always display at the end of tab :)
            'callback' => array($this, 'bwpfm_fca_tab_panel_content'),
            'content' => do_shortcode('[bwl_fca_form product_id="' . $product->get_id() . '" title_min_length="' . $title_min_length . '" title_max_length="' . $title_max_length . '" fca_container_extra_class="' . $fca_container_extra_class . '" /]') // custom field
        );

        return $tabs;
    }

    function bwpfm_fca_tab_panel_content($key, $tab)
    {

        // allow shortcodes to function
        $content = str_replace(']]>', ']]&gt;', $tab['content']);
        echo apply_filters('woocommerce_custom_fca_content', $content, $tab);
    }


    public function loadTranslationFile()
    {
        load_plugin_textdomain("bwl-wpfmfc", FALSE, dirname(plugin_basename(__FILE__)) . "/lang/");
    }
}

/* ------------------------------ Initialization --------------------------------- */

function init_wpfm_fc_addon()
{
    new BWL_Wpfm_Fc_Addon();
}

add_action('init', 'init_wpfm_fc_addon');
