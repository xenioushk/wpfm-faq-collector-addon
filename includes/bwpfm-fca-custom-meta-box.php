<?php

//@Description:Custom Meta Box Display In Product Details Page.

function bwpfm_fc_custom_meta_init() {

    global $post;

    $bwpfm_data = get_option('bwpfm_options');

    $bwpfm_fc_user_email = get_bloginfo('admin_email');

    if (isset($bwpfm_data['bwpfm_admin_email']) && $bwpfm_data['bwpfm_admin_email'] != "") {

        $bwpfm_fc_user_email = sanitize_email( $bwpfm_data['bwpfm_admin_email'] );
        
    }

    $cmb_bwpfm_fca_fields = array(
        'meta_box_id' => 'cmb_bwpfm_fca', // Unique id of meta box.
        'meta_box_heading' => esc_html__('BWL Woo Product FAQ Settings', 'bwl-wpfmfc'), // That text will be show in meta box head section.
        'post_type' => 'bwl-woo-faq-manager', // define post type. go to register_post_type method to view post_type name.        
        'context' => 'normal',
        'priority' => 'high',
        'fields' => array(
            'bwpfm_fc_email_send_status' => array(
                'title' => esc_html__('Send email to user ', 'bwl-wpfmfc'),
                'id' => 'bwpfm_fc_email_send_status',
                'name' => 'bwpfm_fc_email_send_status',
                'type' => 'select',
                'value' => array(
                    '1' => esc_html__('Yes', 'bwl-wpfmfc'),
                    '0' => esc_html__('No', 'bwl-wpfmfc')
                ),
                'default_value' => 0,
                'class' => 'widefat',
                'desc' => ' ' . esc_html__('This data will not store in to database. Select Yes to send a notification message to user about the question.', 'bwl-wpfmfc')
            ),
            'bwpfm_fc_sender_name' => array(
                'title' => esc_html__('Sender Name ', 'bwl-wpfmfc'),
                'id' => 'bwpfm_fc_sender_name',
                'name' => 'bwpfm_fc_sender_name',
                'type' => 'text',
                'value' => '',
                'default_value' => '-',
                'class' => 'medium',
                'desc' => ' ' . esc_html__('You can add/edit sender name in here.', 'bwl-wpfmfc')
            ),
            'bwpfm_fc_user_email' => array(
                'title' => esc_html__('Sender Email ', 'bwl-wpfmfc'),
                'id' => 'bwpfm_fc_user_email',
                'name' => 'bwpfm_fc_user_email',
                'type' => 'text',
                'value' => '',
                'default_value' => '',
                'class' => 'medium',
                'desc' => ' ' . esc_html__('You can add/edit sender email in here.', 'bwl-wpfmfc')
            ),
            'bwpfm_fc_date_time' => array(
                'title' => esc_html__('Date & Time', 'bwl-wpfmfc'),
                'id' => 'bwpfm_fc_date_time',
                'name' => 'bwpfm_fc_date_time',
                'type' => 'info',
                'value' => '',
                'default_value' => 'N/A',
                'class' => 'medium',
                'desc' => ''
            )
        )
    );


    new WPFM_Meta_Box($cmb_bwpfm_fca_fields);


    // Another Custom Meta Box For WooCommerce Product Page.
    // @Removed it from Parent Plugin and include it in here.
    // @Since Version 1.0.2

    $cmb_wpfm_fc_display_faq_fields = array(
        'meta_box_id' => 'cmb_wpfm_fc_display_faq', // Unique id of meta box.
        'meta_box_heading' => esc_html__('Ask A Question Settings', 'bwl-wpfmfc'), // That text will be show in meta box head section.
        'post_type' => 'product', // define post type. go to register_post_type method to view post_type name.        
        'context' => 'side',
        'priority' => 'low',
        'fields' => array('wpfm_fc_display_faq' => array(
                'title' => esc_html__('Hide Ask A Question Tab? ', 'bwl-wpfmfc'),
                'id' => 'wpfm_fc_display_faq',
                'name' => 'wpfm_fc_display_faq',
                'type' => 'select',
                'value' => array(
                    '1' => esc_html__('Yes', 'bwl-wpfmfc'),
                    '0' => esc_html__('No', 'bwl-wpfmfc')
                ),
                'default_value' => '',
                'class' => 'widefat'
            )
        )
    );


    new WPFM_Meta_Box($cmb_wpfm_fc_display_faq_fields);
}

// META BOX START EXECUTION FROM HERE.

add_action('admin_init', 'bwpfm_fc_custom_meta_init');



/* ------------------------------ After Save Post We are going to send email to user --------------------------------- */

function bwpfm_fc_updated_send_email($post_id) {

    // If this is just a revision, don't send the email.

    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id) || get_post_type($post_id) != 'bwl-woo-faq-manager')
        return;


    if (get_post_status($post_id) == 'publish' && get_post_type($post_id) == 'bwl-woo-faq-manager' &&
            isset($_POST['bwpfm_fc_email_send_status']) && $_POST['bwpfm_fc_email_send_status'] == 1 &&
            isset($_POST['bwpfm_fc_user_email']) && $_POST['bwpfm_fc_user_email'] != get_bloginfo('admin_email')) {

        $bwpfm_fc_user_email = sanitize_email( $_POST['bwpfm_fc_user_email'] );

        // Add sender name to reply message.
        //@Since: Version 1.0.2
        $bwpfm_fc_sender_name = "";

        if (isset($_POST['bwpfm_fc_sender_name']) && $_POST['bwpfm_fc_sender_name'] != "") {
            $bwpfm_fc_sender_name = ' ' . esc_attr ( $_POST['bwpfm_fc_sender_name'] );
        }

        // Get Product info by product ID.

        $bwpfm_product_link_info = "";

        if (get_post_meta($post_id, 'bwpfm_fc_product_id', true) != "") {

            $bwpfm_fc_product_id = get_post_meta($post_id, 'bwpfm_fc_product_id', true);
            $product_title = get_the_title($bwpfm_fc_product_id);
            $product_url = get_the_permalink($bwpfm_fc_product_id);

            $bwpfm_product_link_info .= '<p><br /><strong>Check product page:</strong> <a href="' . $product_url . '" title="' . $product_title . '" target="_blank">' . $product_title . '</a></p>';
        }

        //Get FAQ info.

        $faq_title = get_the_title($post_id);
        $faq_content = stripslashes($_POST['content']);

        $subject = esc_html__('Product FAQ has been updated!', 'bwl-wpfmfc');
        $sender_email = sanitize_email( get_bloginfo('admin_email') ); // Email send from blog admin.

        $message = "<p>Hello" . $bwpfm_fc_sender_name . ", <br />Your submitted product FAQ question has been updated on our website.</p>";
        $message .= '<hr><p><strong>Question:</strong> ' . $faq_title . '</p>';
        $message .= '<p><strong>Answer:</strong> <br />' . $faq_content . '</p>';
        $message .= $bwpfm_product_link_info;
        $message .= "<p><br />Thanks.</p>";

        $headers[] = "From: FAQ Question <$sender_email>";

        add_filter('wp_mail_content_type', 'bwl_fca_email_html_content_type');

        // Send email to admin.
        wp_mail($bwpfm_fc_user_email, $subject, $message, $headers);

        remove_filter('wp_mail_content_type', 'bwl_fca_email_html_content_type');
    }
}

add_action('save_post', 'bwpfm_fc_updated_send_email');
