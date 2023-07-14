;(function ($) {
  function wpfm_fca_installation_counter() {
    return $.ajax({
      type: "POST",
      url: ajaxurl,
      data: {
        action: "wpfm_fca_installation_counter", // this is the name of our WP AJAX function that we'll set up next
        product_id: WpfmFcaAdminData.product_id, // change the localization variable.
      },
      dataType: "JSON",
    })
  }

  if (typeof WpfmFcaAdminData.installation != "undefined" && WpfmFcaAdminData.installation != 1) {
    $.when(wpfm_fca_installation_counter()).done(function (response_data) {
      // console.log(response_data)
    })
  }
})(jQuery)
