<?php

/**
 * @Description: Shortcode For Create Login Form
 * @Since: Version 1.0.0
 * @Author: Mahbub
 * @Last Update:11-04-2016
 */

add_shortcode('wpfm_login_form', 'wpfm_login_form');

if (!function_exists('wpfm_login_form')) {

    function wpfm_login_form($atts, $content = null)
    {

        extract(shortcode_atts(array(
            'redirect' => ''
        ), $atts));

        $form = "";

        if (!is_user_logged_in()) {

            if ($redirect) {
                $redirect_url = $redirect;
            } else {
                $redirect_url = get_permalink();
            }

            $args = array(
                'echo' => false,
                'redirect' => $redirect_url,
                'form_id' => 'wpfm_login_form',
                'label_username' => esc_html__('Username', "bwl-wpfmfc"),
                'label_password' => esc_html__('Password', "bwl-wpfmfc"),
                'label_remember' => esc_html__('Remember Me', "bwl-wpfmfc"),
                'label_log_in' => esc_html__('Log In', "bwl-wpfmfc"),
                'id_username' => 'user_login',
                'id_password' => 'user_pass',
                'id_remember' => 'rememberme',
                'id_submit' => 'wp-submit',
                'remember' => true,
                'value_username' => NULL,
                'value_remember' => false
            );

            $form = wp_login_form($args);
        }

        return $form;
    }
}
