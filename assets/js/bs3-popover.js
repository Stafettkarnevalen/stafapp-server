$(document).ready(function() {

    $('body')
        .on('mouseenter', '[data-helpmode]', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (parseInt($(this).data('helpmode')) === 2 || ($(document).data('helpmode-default') === 2 && $(this).data('helpmode') === '')) {
                if (!$(this).data('content'))
                    $(this).data('content', $(this).data('content-default'));
                if (!$(this).data('placement'))
                    $(this).data('placement', 'bottom');
                $(this).popover({'content': $(this).data('content'), 'title': $(this).attr('title'), 'html': true, 'placement': $(this).data('placement'), 'container': $(this).parent()}).popover('show');
            } else if (parseInt($(this).data('helpmode')) === 1 || ($(document).data('helpmode-default') === 1 && $(this).data('helpmode') === '')) {
                if (!$(this).data('content'))
                    $(this).data('content', $(this).data('content-default'));
                if (!$(this).data('placement'))
                    $(this).data('placement', 'bottom');
                $(this).tooltip({'content': $(this).data('content'), 'title': $(this).attr('title'), 'html': true, 'placement': $(this).data('placement')}).tooltip('show');
            } else {
                $(this).popover('destroy').tooltip('destroy');
            }
        })
        .on('mouseleave', '[data-helpmode]', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if ($(document).data('helpmode-default') === 2 || parseInt($(this).data('helpmode')) === 2) {
                $(this).popover('destroy');
            } else if ($(document).data('helpmode-default') === 1 || parseInt($(this).data('helpmode')) === 1) {
                $(this).tooltip('destroy');
            } else {
                $(this).popover('destroy').tooltip('destroy');
            }
        })
        .on('click', '.set-tooltip-mode', function(e) {
            e.preventDefault();
            showSpinner();
            let $url = Routing.generate('nav.authuser_merge_profile_data', {
                '_locale': '{{ app.request.locale }}',
                'path': 'tooltips.mode',
                'value': $(this).data('mode')
            });
            let $elem = $(this);
            $.ajax({
                'url': $url,
                'method': 'get',
                'success': function (data) {
                    if ($elem.parent().is('li')) {
                        let $profile = $('#profile');

                        $elem.parents('ul.dropdown-menu:first').find('.help-mode-status').removeClass('fa-check');
                        $elem.parent().find('.help-mode-status').addClass('fa-check');

                        $profile.find('.help-mode-status').removeClass('fa-check-square').removeClass('text-success').addClass('fa-square-o').addClass('text-normal');
                        $profile.find('.set-tooltip-mode[data-mode=' + $elem.data('mode') + '] .help-mode-status').addClass('fa-check-square').addClass('text-success').removeClass('fa-square-o').removeClass('text-normal');
                    } else {
                        let $nav = $('#main_nav');

                        $elem.parents('.container-fluid').find('.help-mode-status').removeClass('fa-check-square').removeClass('text-success').addClass('fa-square-o').addClass('text-normal');
                        $elem.parents('.form-group').find('.help-mode-status').addClass('fa-check-square').addClass('text-success').removeClass('fa-square-o').removeClass('text-normal');

                        $nav.find('.help-mode-status').removeClass('fa-check');
                        $nav.find('.set-help-mode[data-mode=' + $elem.data('mode') + '] .help-mode-status').addClass('fa-check');
                    }


                    $(document).data('helpmode-default', parseInt($elem.data('mode')));
                    hideSpinner();
                },
                'error': function (xhr) {
                    console.log(xhr);
                }
            });
        })
    ;
});

function hideSpinner() {
    console.log('spinner.hide');
    $('#modal_loader').css('z-index', 0).css('display', 'none');
}
function showSpinner() {
    console.log('spinner.show');
    let $spinner = $('#modal_loader');
    if ($spinner.length === 0) {
        $spinner = $('<div id="modal_loader" class="loader"><div class="fa fa-spinner fa-4x fa-pulse"></div></div>');
        $('body').append($spinner);
    }
    $spinner.css('z-index', 1000000).css('display', '');
}