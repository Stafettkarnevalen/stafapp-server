$(document).ready(function() {
    $('body')
        .on('click', '.set-edit-mode', function(e) {
            e.preventDefault();
            showSpinner();
            let $url = Routing.generate('nav.authuser_merge_profile_data', {
                '_locale': '{{ app.request.locale }}',
                'path': 'modules.tools',
                'value': $(this).data('mode')
            });
            let $elem = $(this);
            $.ajax({
                'url': $url,
                'method': 'get',
                'success': function (data) {
                    if ($elem.parent().is('li')) {
                        let $profile = $('#profile');
                        $elem.parents('ul.dropdown-menu:first').find('.edit-mode-status').removeClass('fa-check');
                        $elem.parent().find('.edit-mode-status').addClass('fa-check');

                        $profile.find('.edit-mode-status').removeClass('fa-check-square').removeClass('text-success').addClass('fa-square-o').addClass('text-normal');
                        $profile.find('.set-edit-mode[data-mode=' + $elem.data('mode') + '] .edit-mode-status').addClass('fa-check-square').addClass('text-success').removeClass('fa-square-o').removeClass('text-normal');
                    } else {
                        let $nav = $('#main_nav');

                        $elem.parents('.container-fluid').find('.edit-mode-status').removeClass('fa-check-square').removeClass('text-success').addClass('fa-square-o').addClass('text-normal');
                        $elem.parents('.form-group').find('.edit-mode-status').addClass('fa-check-square').addClass('text-success').removeClass('fa-square-o').removeClass('text-normal');

                        $nav.find('.edit-mode-status').removeClass('fa-check');
                        $nav.find('.set-edit-mode[data-mode=' + $elem.data('mode') + '] .edit-mode-status').addClass('fa-check');
                    }

                    let $mode = parseInt($elem.data('mode'));
                    $elems = $('.mod_hide');
                    if ($mode === 1)
                        $elems.removeClass('hidden');
                    else
                        $elems.addClass('hidden');

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