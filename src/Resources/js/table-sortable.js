/**
 * Created by rjurgens on 16/12/2016.
 */
$(document).ready(function() {
    $('.table-sortable')
        .on('change', function(e) {
            $(this).find('tbody[data-sortable="1"]').sortable({
                helper: function (e, tr) {
                    var $originals = tr.children();
                    var $tr = tr.clone();
                    var $tbody = tr.parent().clone().empty();
                    var $table = tr.parent().parent().clone().empty();
                    $tr.children().each(function (index) {
                        // Set helper cell sizes to match the original sizes
                        $(this).width($originals.eq(index).width());
                    });
                    $table.append($tbody.append($tr));
                    return $table;
                },
                cursor: 'move',
                axis: 'y',
                handle: 'td',
                opacity: '0.6',
                start: function (event, ui) {
                    $(ui.item[0]).attr('data-oldindex', ui.item.index());
                },
                stop: function (event, ui) {
                    var $element = $(ui.item[0]);
                    var oldIndex = $element.attr('data-oldindex');
                    $element.removeAttr('data-oldindex');
                    var index = ui.item.index();
                    var move = index - oldIndex;
                    var id = parseInt(ui.item.attr('data-id'));
                    console.log('move ' + id + ' ' + move);
                    if (move !== 0) {
                        var $route = $element.parent().data('ajaxroute');
                        var $field = $element.parent().data('target');
                        var $params = $element.parent().data('ajaxparams');
                        $params[$field] = id;
                        $params['move'] = move;
                        var $url = Routing.generate($route, $params);
                        showSpinner();

                        $.ajax({
                            'url': $url,
                            'success': function (data) {
                                console.log('moved');
                                reorderSortableTable();
                                hideSpinner();
                            },
                            'error': function (xhr) {
                                console.log('error');
                                console.log(xhr);
                                $(ui.sender).sortable('cancel');
                                revertSortableTable();
                                hideSpinner();
                            }
                        });
                    }
                }
            });
        }).trigger('change');

    $('body').on('click', '.ajax-sort', function (e) {
        e.preventDefault();
        var $link = $(e.target).closest('a');

        var $table = $link.closest('.table-sortable');
        if ($table.length === 0)
            $table = $($link.data('target'));

        var $selected = $table.find('tr.selected');
        if ($selected.length > 0)
            $selected = $selected.data('id');
        else
            $selected = null;
        showSpinner();
        console.log('ajax: ' + $link.attr('href'));
        $.ajax({
            'url': $link.attr('href'),
            'method': 'get',
            'success': function (data) {
                var $result = $(data);
                if ($selected) {
                    $result.find('tr[data-id="' + $selected + '"]').addClass('selected');
                }
                $table.html($result.html()).trigger('change');
                hideSpinner();
            },
            'error': function (xhr) {
                console.log(xhr);
            }
        });
    })
});

function reorderSortableTable() {
    var index = 0;
    $('.table-sortable > tbody > tr').each(function() {
        $(this).attr('data-index', index++);
        $(this).find('td.order > a').html('' + index);
    });
}

function revertSortableTable() {
    var $tbody = $('.table-sortable > tbody');
    var $rows = $tbody.children('tr').get();
    $rows.sort(function (a, b) {
        return ($(a).attr('data-index') - $(b).attr('data-index'));
    });
    $.each($rows, function(index, item) {
        $tbody.append(item);
    });
}
