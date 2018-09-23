$(document).ready(function() {
    $('body')
        .on('click', '.clear_search', function() {
            $(this).addClass('disabled').siblings('.search').val('');
            var $target = $($(this).parent().data('target'));
            $target.find('tbody tr').each(function() {
                $(this).css('display', '');
            });
            $url = Routing.generate('nav.authuser_merge_profile_data', {
                '_locale': $('html').attr('lang'),
                'path': 'search.' + $(this).data('domain') + '.filter',
                'value': null
            });
            $.ajax({
                'url': $url
            });
        })
        .on('click', '.searchbar .dropdown-menu a input', function(e) {
            e.stopPropagation();
            var $target = $(e.currentTarget);
            if ($target.closest('.input-group').find('.search').val().length > 0)
                $target.closest('.input-group').find('.search').trigger('input');

            $url = Routing.generate('nav.authuser_merge_profile_data', {
                '_locale': $('html').attr('lang'),
                'path': 'search.' + $target.data('domain') + '.field.' + $target.attr('name'),
                'value': $target.is(':checked') ? 1 : 0
            });
            $.ajax({
                'url': $url
            });
        })
        .on('click', '.searchbar .dropdown-menu a', function(e) {
            var $target = $(e.currentTarget);
            var $inp = $target.find('input');
            $inp.trigger('click');
            return false;
        })
        .on('input', '.search', function() {
            if ($(this).val().length > 0) {
                $(this).siblings('.clear_search').removeClass('disabled');
                var $filter = $(this).val();
                var $target = $($(this).parent().data('target'));
                var $parent = $(this).parent();
                var $fields = $parent.find('.dropdown-menu a input:checked');

                if ($fields.length > 0) {
                    var checked = [];
                    $.each($fields, function() {
                       checked.push('td.' + $(this).attr('name'));
                    });
                    $fields = checked.join();
                } else {
                    $fields = 'td';
                }

                $url = Routing.generate('nav.authuser_merge_profile_data', {
                    '_locale': $('html').attr('lang'),
                    'path': 'search.' + $(this).data('domain') + '.filter',
                    'value': $filter
                });
                $.ajax({
                    'url': $url
                });

                $target.find('tbody tr').each(function() {
                    $(this).css('display', $(this).find($fields).is(':icontains(' + $filter + ')') ? '' : 'none');
                });
            } else {
                $(this).siblings('.clear_search').trigger('click');
            }
        });
});

$.expr[":"].icontains = $.expr.createPseudo(function(arg) {
    return function(elem) {
        return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
    };
});