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
    });

}(jQuery, window, window.document));
