(function($, window, document) {
    'use strict';

    $(document).ready(function () {
        $('[data-toggle="offcanvas"]').click(function () {
            var target = $(this).attr('data-target');
            if (target === 'left'){
                $('.offcanvas__container')
                    .removeClass('offcanvas__container--open-right')
                    .toggleClass('offcanvas__container--open-left');
            }

            if (target === 'right') {
                $('.offcanvas__container')
                    .removeClass('offcanvas__container--open-left')
                    .toggleClass('offcanvas__container--open-right');
            }

            if (target === 'exit') {
                $('.offcanvas__container').removeClass('offcanvas__container--open-left offcanvas__container--open-right');
            }
        });
        $('ul.dropdown-menu [data-toggle=dropdown]').on('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            $(this).parent().siblings().removeClass('open');
            $(this).parent().toggleClass('open');
        });
    });

}(jQuery, window, window.document));
