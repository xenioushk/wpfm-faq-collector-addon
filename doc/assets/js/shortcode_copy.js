/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


(function ($) {

    $(function () {

        $('#documenter_sidebar').prepend('<div class="sc_msg">Copied!</p>');

        var $sc_msg = $('.sc_msg');

        function copyHandler($this) {

            $this.find('input').val("")
                    .val($this.find('i').data('content'))
                    .focus()
                    .select();
            document.execCommand('copy');

        }

        $('.sc').each(function () {

            var $this = $(this);

            var $tooltip_text = $this.html();

            $this.append("<input type='text' class='tmp_field' value=''/>").append("<i class='sc_tooltip' data-content='" + $tooltip_text + "'>Copy</i>");

            $this.find('.sc_tooltip').on("click", function () {

                copyHandler($this);
                $sc_msg.slideDown('slow');
                setTimeout(function () {
                    $sc_msg.slideUp('slow');
                }, 1500);

            });

        });
        
        // VENOBOX VIDEO.

        $(document).ready(function () {
            $('.venobox').venobox();
        });  

    });

})(jQuery);