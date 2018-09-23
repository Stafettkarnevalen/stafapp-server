(function ($, window) {
    $.fn.contextMenu = function (settings) {
        var $menuOriginalPosition = {};
        return this.each(function () {

            // Open context menu
            $(this).on("contextmenu", function (e) {

                var $id = $(this).closest('.body').attr('id');

                console.log('ctx menu: ' + $id);

                // return native menu if pressing control
                if (e.ctrlKey) return;

                var $menu = $(settings.menuSelector).data("invokedOn", $(e.target));

                if (!($id in $menuOriginalPosition)){
                    console.log('set orig: ' + $id);
                    $menuOriginalPosition[$id] = $menu.offset();
                }

                console.log($menuOriginalPosition);

                settings.menuActivated.call(this, e);

                //open menu
                $menu
                    .show()
                    .css({
                        position: "absolute",
                        left: getMenuPosition(e.clientX, $menuOriginalPosition[$id].left, 'width', 'scrollLeft', $(e.target)),
                        top: getMenuPosition(e.clientY, $menuOriginalPosition[$id].top, 'height', 'scrollTop', $(e.target))
                    })
                    .off('click')
                    .on('click', 'a', function (e) {
                        if (!$(e.target).parent().hasClass('disabled')) {
                            $menu.dropdown('toggle').hide();
                            settings.menuSelected.call(this, $menu.data("invokedOn"), e);
                        }
                        e.preventDefault();
                        return false;
                    })
                    .dropdown('toggle');

                console.log('ctx done');
                $('body').on('scroll mousewheel touchmove', function(e) {
                    //if ($menu && $menu.is(':visible')) {
                    //    e.preventDefault();
                    //    e.stopPropagation();
                    //    return false;
                    //}
                    return true;
                });
                return false;
            });

            //make sure menu closes on any click
            $('body').click(function () {
                $(settings.menuSelector).hide();
            });
        });

        function getMenuPosition(mouse, orig, direction, scrollDir, elem) {
            var win = $(window)[direction](),
                scroll = $(window)[scrollDir](),
                menu = $(settings.menuSelector)[direction](),
                position = mouse + scroll - orig;

            if (elem.closest('.modal-dialog').length && scrollDir === 'scrollTop') {
                console.log('account for modal scrolling');
                console.log(elem.closest('.modal-content-body').scrollTop());
                scroll = elem.closest('.modal-content-body').scrollTop() - 102;
                if (elem.closest('.modal-content-body.affix-search').length)
                    scroll -= 50;
                if (elem.closest('.modal-content-body.no-toolbar').length)
                    scroll += 50;
                position = mouse + scroll - orig;
            }

            // opening menu would pass the side of the page
            if (mouse + menu > win && menu < mouse)
                position -= menu;

            // console.log(position);

            return position;
        }
    };
})(jQuery, window);
