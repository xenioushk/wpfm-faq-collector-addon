<?php

/*-----------------------------AJAX Settings ----------------------------------*/

// @Description: Send JS required variables in to header.
// @Since: Version 1.0.0

if (!function_exists('bwl_fca_set_ajax_url')) {

    function bwl_fca_set_ajax_url()
    {

?>
<script type="text/javascript">
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>',
  err_bwl_fca_captcha = '<?php esc_html_e(' Incorrect Captcha Value!', "bwl-wpfmfc"); ?>',
  err_bwl_fca_question = '<?php esc_html_e(' Write your question. Min length 3 characters !', "bwl-wpfmfc"); ?>',
  err_bwl_fca_email = '<?php esc_html_e(' Valid Email address required!', "bwl-wpfmfc"); ?>',
  err_bwl_fca_success_msg = '<?php esc_html_e(' FAQ successfully added for review!', "bwl-wpfmfc"); ?>',
  err_bwl_fca_error_msg = '<?php esc_html_e(' Unable to add faq. Please try again!', "bwl-wpfmfc"); ?>';
</script>

<?php

    }

    add_action('wp_head', 'bwl_fca_set_ajax_url');
}

//@Description: Save user submitted question and send email to administrator.
//@Since: Version 1.0.0


function bwl_fca_ques_save_post_data()
{

    if (empty($_REQUEST) || !wp_verify_nonce($_REQUEST['wpfm_fca_nonce_field'], 'wpfm_fca_nonce_action')) {

        $status = array(
            'bwl_fca_add_status' => 0
        );
    } else {

        $bwpfm_data = get_option('bwpfm_options');

        // User Submitted Information.

        $title = trim(esc_attr($_REQUEST['title']));
        $post_type = trim(esc_attr($_REQUEST['post_type']));

        // Initialize Sender Name.
        $sender_name = "";

        if (isset($_REQUEST['sender_name'])) {
            $sender_name = trim(esc_attr($_REQUEST['sender_name'])); // Sender Name.
        }

        $sender_email = trim(sanitize_email($_REQUEST['email'])); // Sender Email.
        $product_id = trim(esc_attr($_REQUEST['product_id'])); // Get Product ID

        if ($product_id == "") {
            $status = array(
                'bwl_fca_add_status' => 0
            );
        }

        //@Description: Get product title and product URL. We are going to send those information to admin via email address.
        //@Since: Version 1.0.2

        $product_title = esc_attr(get_the_title($product_id));
        $product_url = esc_url(get_the_permalink($product_id));

        // @Description: Added data in to GLOBAL FAQ section.
        // @Since: Version 1.0.1

        $post = array(
            'post_title'            => $title,
            'post_status'        => 'pending', // Choose: publish, preview, future, etc.
            'post_type'          => $post_type  // Use a custom post type if you want to
        );

        $post_id = wp_insert_post($post);  // Insert user submitted faq in to database and we will pick the post ID.

        // Update Product FAQ Section.

        //        $wpfm_faqs = get_post_meta( $product_id, 'wpfm_contents' );

        // Built an array to insert data in to FAQ meta.
        $wpfm_faqs =  array(
            'wpfm_faq_type' => 1, // Add As a Global FAQ.
            'faq_title' => $title,
            'faq_desc' => '',
            'wpfm_global_faq_id' => $post_id
        );


        // Issue fixed in here ver-1.0.3
        // Fix issue in here.

        $wpfm_meta_title = apply_filters('filter_wpfm_content_meta', ''); // filter the title.

        $all_wpfm_faqs = get_post_meta($product_id, $wpfm_meta_title, true); // get previously inserted faqs.

        // if all_wpfm_faqs is empty then we are going to intialize the array.

        if (empty($all_wpfm_faqs)) {
            $all_wpfm_faqs = array();
        }

        // Append newly inserted faq in to old lists.

        $all_wpfm_faqs[] = $wpfm_faqs;

        // update the meta filed value.
        update_post_meta($product_id, $wpfm_meta_title, $all_wpfm_faqs);

        // Store Product ID for future reference.

        add_post_meta($post_id, 'bwpfm_fc_product_id',  $product_id);

        // Add Post Meta For sender name.
        // @Since: version 1.0.2

        if ($sender_name != "") :
            add_post_meta($post_id, 'bwpfm_fc_sender_name', $sender_name);
        endif;

        // Add Post Meta For user email.

        add_post_meta($post_id, 'bwpfm_fc_user_email', $sender_email);

        // Add external FAQ submission date time.

        add_post_meta($post_id, 'bwpfm_fc_date_time',  date_i18n('Y-m-d H:i:s', false, true));

        //Send Email to administrator.

        $bwpfm_email_status = TRUE; // Initally We send email when user post a new FAQ.

        if (isset($bwpfm_data['bwpfm_email_status']) && $bwpfm_data['bwpfm_email_status'] == 1) {

            $bwpfm_email_status = FALSE;
        }

        if ($bwpfm_email_status == TRUE) {

            $to =  get_bloginfo('admin_email');

            if (isset($bwpfm_data['bwpfm_admin_email']) && $bwpfm_data['bwpfm_admin_email'] != "") {

                $to =  sanitize_email($bwpfm_data['bwpfm_admin_email']);
            }

            $subject = esc_html__('New Product FAQ Question!', "bwl-wpfmfc");
            $edit_bwl_fca_url =  get_admin_url() . "post.php?post&#61;$post_id&#38;action&#61;edit";

            $body = "<p>" . esc_html__("Hello Administrator", "bwl-wpfmfc") . ",<br>" . esc_html__("A new FAQ question has been submitted for following product.", "bwl-wpfmfc") . "</p>";
            $body .= "<h3>" . esc_html__("Product:", "bwl-wpfmfc") . $product_title . "</h3><br />";
            $body .= "<p><strong>" . esc_html__("Product URL", "bwl-wpfmfc") . ":</strong> <a href='" . $product_url . "'>" . $product_url . "</a></p><hr />";
            $body .= "<h3>" . esc_html__("Question Information", "bwl-wpfmfc") . "</h3><hr />";
            $body .= "<p><strong>" . esc_html__("Question Title", "bwl-wpfmfc") . ":</strong><br />" . $title . "</p>";

            if ($sender_name != "") :
                $body .= "<p><strong>" . esc_html__("Submitted By", "bwl-wpfmfc") . ":</strong><br />" . $sender_name . "</p>";
            endif;
            $body .= "<p><strong>" . esc_html__("FAQ Status", "bwl-wpfmfc") . ":</strong> " . esc_html__("Pending", "bwl-wpfmfc") . "</p>";
            $body .= "<p><strong>" . esc_html__("Review", "bwl-wpfmfc") . ":</strong> " . $edit_bwl_fca_url . "</p>";
            $body .= "<p>" . esc_html__("Thank You!", "bwl-wpfmfc") . "</p>";

            $headers[] = "From: New Question Submitted <$sender_email>";

            add_filter('wp_mail_content_type', 'bwl_fca_email_html_content_type');

            wp_mail($to, $subject, $body, $headers);

            remove_filter('wp_mail_content_type', 'bwl_fca_email_html_content_type');
        }

        // Finally send confirmation to front end.
        // @since: version 1.0.0

        $status = array(
            'bwl_fca_add_status' => 1
        );
    }

    echo json_encode($status);

    die();
}

/**
 * @Description: Add A filter for sending HTML email.
 * @Created At: 08-04-2013
 * @Last Edited AT: 30-06-2013
 * @Created By: Mahbub
 **/

function bwl_fca_email_html_content_type()
{
    return 'text/html';
}

add_action('wp_ajax_bwl_fca_ques_save_post_data', 'bwl_fca_ques_save_post_data');
add_action('wp_ajax_nopriv_bwl_fca_ques_save_post_data', 'bwl_fca_ques_save_post_data');