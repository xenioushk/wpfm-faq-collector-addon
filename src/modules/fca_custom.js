/*****************************************************
 *@Description: FAQ Collector Addon
 *@Created By: Mahbub
 *@Created At: 09-01-15
 *@Last Edited: 05-03-2018
 *****************************************************/

;(function ($) {
  $(function () {
    function bwl_fca_randomNum(maxNum) {
      return Math.floor(Math.random() * maxNum + 1) //return a number between 1 - 10
    }

    function checkRegexp(o, regexp) {
      if (!regexp.test(o.val())) {
        return false
      } else {
        return true
      }
    }

    /*------------------------------FAQ ASK FORM---------------------------------*/

    if ($(".bwl_fca_ques_form").length) {
      var $bwl_fca_ques_form = $(".bwl_fca_ques_form")

      var $bwl_fca_msg_box = $bwl_fca_ques_form.find(".bwl-fca-message-box"),
        $bwl_fca_title = $bwl_fca_ques_form.find("#title"),
        $bwl_fca_sender_status = $bwl_fca_ques_form.find("#sender_status"),
        $bwl_fca_sender_name = $bwl_fca_ques_form.find("#sender_name"),
        $bwl_fca_email = $bwl_fca_ques_form.find("#email"),
        $bwl_fca_captcha = $bwl_fca_ques_form.find("#captcha"),
        $bwl_fca_post_type = $bwl_fca_ques_form.find("#post_type"),
        $bwl_fca_product_id = $bwl_fca_ques_form.find("#product_id"),
        $bwl_fca_captcha_status = $bwl_fca_ques_form.find("#captcha_status")

      var $bwl_fca_ques_form_fields = $([]).add($bwl_fca_title).add($bwl_fca_sender_name).add($bwl_fca_email).add($bwl_fca_captcha)

      if ($bwl_fca_captcha.length === 1) {
        var num1 = $bwl_fca_ques_form.find("#num1")
        var num2 = $bwl_fca_ques_form.find("#num2")

        $bwl_fca_ques_form_fields = $([]).add($bwl_fca_title).add($bwl_fca_sender_name).add($bwl_fca_email).add($bwl_fca_captcha)
      } else {
        $bwl_fca_ques_form_fields = $([]).add($bwl_fca_title).add($bwl_fca_sender_name).add($bwl_fca_email)
      }

      function fca_init_filed_data() {
        var $bwl_fca_sender_name_val = $bwl_fca_sender_name.val()
        var $bwl_fca_email_val = $bwl_fca_email.val()

        $bwl_fca_ques_form_fields.val("").removeAttr("disabled").removeClass("bwl_fca_ques_disabled_field")

        if ($bwl_fca_sender_status.length == 1 && $bwl_fca_sender_status.val() == 1) {
          // If user logged in we will fill the data.

          $bwl_fca_sender_name.val($bwl_fca_sender_name_val)
          $bwl_fca_email.val($bwl_fca_email_val)
        }

        $bwl_fca_title.focus()
      }

      // Initialize All Form Fields.
      fca_init_filed_data()

      $bwl_fca_ques_form.find("input[type=submit]").click(function () {
        var $fca_submit_btn = $(this),
          emailRegex = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/

        // Fetch Form Messages.

        var bwl_fca_bValid = true,
          required_field_msg = "",
          ok_border = "border: 1px solid #EEEEEE",
          error_border = "border: 1px solid #E63F37"

        // Question Title Field Validation.

        if ($.trim($bwl_fca_title.val()).length < $bwl_fca_title.data("min_length") || $.trim($bwl_fca_title.val()).length > $bwl_fca_title.data("max_length")) {
          bwl_fca_title_bValid = false
          $bwl_fca_title.attr("style", error_border)
          required_field_msg += " " + $bwl_fca_title.data("error_msg") + "<br />"
        } else {
          bwl_fca_title_bValid = true
          $bwl_fca_title.attr("style", ok_border)
          required_field_msg += ""
        }

        bwl_fca_bValid = bwl_fca_bValid && bwl_fca_title_bValid

        // User Email Validation.

        if ($.trim($bwl_fca_email.val()).length == 0 || checkRegexp($bwl_fca_email, emailRegex) == false) {
          bwl_fca_email_bValid = false
          $bwl_fca_email.attr("style", error_border)
          required_field_msg += " " + $bwl_fca_email.data("error_msg") + "<br />"
        } else {
          bwl_fca_email_bValid = true
          $bwl_fca_email.attr("style", ok_border)
          required_field_msg += ""
        }

        bwl_fca_bValid = bwl_fca_bValid && bwl_fca_email_bValid

        // Captcha Validation.

        if ($bwl_fca_captcha.length == 1) {
          if (parseInt($.trim(num1.val())) + parseInt($.trim(num2.val())) != parseInt($.trim($bwl_fca_captcha.val()))) {
            bwl_fca_captcha_bValid = false
            $bwl_fca_captcha.attr("style", error_border)
            required_field_msg += " " + $bwl_fca_captcha.data("error_msg")
          } else {
            bwl_fca_captcha_bValid = true
            $bwl_fca_captcha.attr("style", ok_border)
            required_field_msg += ""
          }

          bwl_fca_bValid = bwl_fca_bValid && bwl_fca_captcha_bValid
        }

        //Alert Message Box For Required Fields.

        if (bwl_fca_bValid == false) {
          $bwl_fca_msg_box.html("").addClass("bwl-fca-ques-form-error-box").html(required_field_msg).slideDown("slow")
        }

        if (bwl_fca_bValid == true) {
          $bwl_fca_ques_form_fields.attr("style", ok_border)
          $bwl_fca_ques_form_fields.addClass("bwl_fca_ques_disabled_field").attr("disabled", "disabled")
          $fca_submit_btn.addClass("bwl_fca_ques_disabled_field").attr("disabled", "disabled")
          $bwl_fca_msg_box.html("").removeClass("bwl-fca-ques-form-error-box").addClass("bwl-fca-ques-form-wait-box").html("Please Wait .....").slideDown("slow")

          $.ajax({
            url: ajaxurl,
            type: "POST",
            dataType: "JSON",
            data: {
              action: "bwl_fca_ques_save_post_data", // action will be the function name,
              title: $bwl_fca_title.val(),
              sender_name: $bwl_fca_sender_name.val(),
              email: $bwl_fca_email.val(),
              post_type: $bwl_fca_post_type.val(),
              product_id: $bwl_fca_product_id.val(),
              wpfm_fca_nonce_field: $bwl_fca_ques_form.find("#wpfm_fca_nonce_field").val(),
            },
            success: function (data) {
              if (data.bwl_fca_add_status == 1) {
                //Reload For New Number.

                if ($bwl_fca_captcha_status.val() == 1) {
                  num1.val(bwl_fca_randomNum(15))
                  num2.val(bwl_fca_randomNum(20))
                }

                $bwl_fca_msg_box.removeClass("bwl-fca-ques-form-wait-box").html("").html($fca_submit_btn.data("success_msg")).addClass("bkb-ques-form-success-box").delay(3000).slideUp("slow")
                fca_init_filed_data()
                $fca_submit_btn.removeAttr("disabled").removeClass("bwl_fca_ques_disabled_field")
              } else {
                $bwl_fca_msg_box.removeClass("bwl-fca-ques-form-wait-box").html("").html($fca_submit_btn.data("error_msg")).addClass("bwl-fca-ques-form-error-box").delay(3000).slideUp("slow")
                fca_init_filed_data()
                $fca_submit_btn.removeAttr("disabled").removeClass("bwl_fca_ques_disabled_field")
              }
            },
            error: function (xhr, textStatus, e) {
              $bwl_fca_msg_box.removeClass("bwl-fca-ques-form-wait-box").html("").html($fca_submit_btn.data("error_msg")).addClass("bwl-fca-ques-form-error-box").delay(3000).slideUp("slow")
              $bwl_fca_ques_form_fields.removeAttr("disabled").removeClass("bwl_fca_ques_disabled_field")
              $fca_submit_btn.removeAttr("disabled").removeClass("bwl_fca_ques_disabled_field")
              return
            },
          })
        }

        return false
      })
    }
  })
})(jQuery) // jQuery Wrapper!
