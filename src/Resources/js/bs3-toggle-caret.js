/**
 * Created by rjurgens on 16/12/2016.
 */
$(document).ready(function () {
    $('.collapse')
        .on('show.bs.collapse', function() {
            $elem = $(this)
                .parent()
                .find(".toggle-icon");
            $toggle = $elem.data('toggleicon').split(',');
            $elem.removeClass($toggle[0]).addClass($toggle[1]);
        })
        .on('hide.bs.collapse', function() {
            $elem = $(this)
                .parent()
                .find(".toggle-icon");
            $toggle = $elem.data('toggleicon').split(',');
            $elem.removeClass($toggle[1]).addClass($toggle[0]);
        });
});

