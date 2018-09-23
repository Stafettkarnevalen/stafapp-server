/**
 * Created by rjurgens on 25/09/2017.
 */

var $modals = [];

$(document).ready(function() {

    /**
     * Automatically close alert messages after 5 seconds.
     */
    $(".alert-dismissible").fadeTo(5000, 500).slideUp(500, function() {
        $(this).alert('close');
    });

    $('body')
    /**
     * When a modal is loaded:
     * 1) display a backdrop and a spinner while the modal loads.
     * 2) place the modal in a stack
     * 3) display hte title of the modal like a breadcrumb of all the modals in the stack
     * 4) freeze scrolling on top page
     */
    .on('click', 'a[data-toggle~=modal]', function(e) {
        //console.log('modal.show');
        showSpinner();
        var $modal = $('<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">' +
            '<div class="modal-dialog" role="document"><div class="modal-content"></div></div></div>');
        // title, breadcrumb
        var $title = $(this).attr('data-title');
        if ($modals.length) {
            $title = $modals[$modals.length - 1].attr('data-title') + ' - ' + $title;
            $modal.css('z-index', 10000 * $modals.length);
        } else {
            $modal.css('z-index', 10000);
        }
        $modal.attr('data-title', $title);

        $modal.find('.modal-content').load($(this).attr('href'), function() {
            hideSpinner();
            $modal.find('span.form-title').html($title);
        });
        $modal.reload = !!$(this).attr('data-reload');
        $modal.url = $(this).attr('href');

        $modal.on('hidden.bs.modal', function () {
            console.log($(this));
            //$modal.empty();
            //$modal.detach();
            $(this).remove();
        });

        // show and push into the stack, freeze scrolling on top page
        $modal.modal('show');
        if ($modals.length === 0)
            $('body').css('overflow', 'hidden');
        $modals.push($modal);

        // prevent default in event chain and return false
        e.preventDefault();
        return false;
    })

    /**
     * When a modal is loaded:
     * 1) display a backdrop and a spinner while the modal loads.
     * 2) place the modal in a stack
     * 3) display hte title of the modal like a breadcrumb of all the modals in the stack
     * 4) freeze scrolling on top page
     */
    .on('click', 'button[data-toggle~=modal]', function(e) {
        //console.log('modal.show');
        showSpinner();
        var $modal = $('<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">' +
            '<div class="modal-dialog" role="document"><div class="modal-content"></div></div></div>');

        // title, breadcrumb
        var $title = $(this).attr('data-title');
        if ($modals.length) {
            $title = $modals[$modals.length - 1].attr('data-title') + ' - ' + $title;
            $modal.css('z-index', 10000 * $modals.length);
        } else {
            $modal.css('z-index', 10000);
        }
        $modal.attr('data-title', $title);
        $modal.find('.modal-content').load($(this).attr('value'), function() {
            hideSpinner();
            $modal.find('span.form-title').html($title);
        });
        $modal.reload = !!$(this).attr('data-reload');
        $modal.url = $(this).attr('value');

        // show and push into the stack, freeze scrolling on top page
        $modal.modal('show');
        if ($modals.length === 0)
            $('body').css('overflow', 'hidden');
        $modals.push($modal);

        // prevent default in event chain and return false
        e.preventDefault();
        return false;
    })

    /**
     * When a modal is loaded:
     * 1) display a backdrop and a spinner while the modal loads.
     * 2) place the modal in a stack
     * 3) display hte title of the modal like a breadcrumb of all the modals in the stack
     * 4) freeze scrolling on top page
     */
    .on('click', 'a[data-toggle~=confirm]', function(e) {
        //console.log('confirm.show');
        showSpinner();
        var $modal = $('<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">' +
            '<div class="modal-dialog" role="document"><div class="modal-content"></div></div></div>');
        // title, breadcrumb
        var $title = $(this).attr('data-title');
        if ($modals.length) {
            $title = $modals[$modals.length - 1].attr('data-title') + ' - ' + $title;
            $modal.css('z-index', 10000 * $modals.length);
        } else {
            $modal.css('z-index', 10000);
        }
        $modal.attr('data-title', $title);
        $modal.find('.modal-content').load($(this).attr('href'), function() {
            hideSpinner();
            $modal.find('span.form-title').html($title);
        });
        $modal.reload = !!$(this).attr('data-reload');
        $modal.url = $(this).attr('href');

        // show and push into the stack, freeze scrolling on top page
        $modal.modal('show');
        if ($modals.length === 0)
            $('body').css('overflow', 'hidden');
        $modals.push($modal);

        // prevent default in event chain and return false
        e.preventDefault();
        return false;
    })

    /**
     * When a modal is loaded:
     * 1) display a backdrop and a spinner while the modal loads.
     * 2) place the modal in a stack
     * 3) display hte title of the modal like a breadcrumb of all the modals in the stack
     * 4) freeze scrolling on top page
     */
    .on('click', 'button[data-toggle~=confirm]', function(e) {
        //console.log('confirm.show');
        showSpinner();
        var $modal = $('<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">' +
            '<div class="modal-dialog" role="document"><div class="modal-content"></div></div></div>');
        // title, breadcrumb
        var $title = $(this).attr('data-title');
        if ($modals.length) {
            $title = $modals[$modals.length - 1].attr('data-title') + ' - ' + $title;
            $modal.css('z-index', 10000 * $modals.length);
        } else {
            $modal.css('z-index', 10000);
        }
        $modal.attr('data-title', $title);
        $modal.find('.modal-content').load($(this).attr('value'), function() {
            hideSpinner();
            $modal.find('span.form-title').html($title);
        });
        $modal.reload = !!$(this).attr('data-reload');
        $modal.url = $(this).attr('value');

        // show and push into the stack, freeze scrolling on top page
        $modal.modal('show');
        if ($modals.length === 0)
            $('body').css('overflow', 'hidden');
        $modals.push($modal);

        // prevent default in event chain and return false
        e.preventDefault();
        return false;
    })

    /**
     *
     */
    .on('click', 'a.ajax', function(e) {

        if ($(this).closest('.modal').length === 0)
            return true;

        //console.log('ajax load');
        e.preventDefault();
        showSpinner();
        var $modal = $modals[$modals.length - 1];
        var $title = $modal.attr('data-title');
        $modal.loading = true;



        $modal.find('.modal-content').load($(this).attr('href'), function() {
            $modal.find('span.form-title').html($title);


            $modal.loading = false;
            hideSpinner();
        });
    })

    /**
     *
     */
    .on('submit', 'form', function(evt) {
        // if not in a modal, do nothing
        if ($(this).closest('.modal').length === 0) {
            //console.log('non-modal');
            return true;
        }
        //console.log('form.submit');
        var form = $(this);
        var btn = $(document.activeElement);

        if (!form[0].checkValidity())
            return true;

        evt.preventDefault();

        // show spinner before posting
        showSpinner();

        // this is required in the form when using modals
        var target = form.attr('action');

        var formData = new FormData(form[0]);
        formData.append(btn.attr('name'), '');
        console.log(Array.from(formData));

        // post
        $.ajax({
            'url': target,
            'method': 'post',
            'data': formData,
            'contentType': false,
            'processData': false,
            'success': function(data) {
                console.log('sucess');
                // close modal
                var $modal = $modals[$modals.length - 1];
                $modal.find("[data-dismiss=modal]").trigger({ type: "click" });

                // reload parent without the modal
                console.log(data);

                if (data.reloadPage === 1) {
                    //while ($modals.length > 0) {
                        // $modal = $modals.pop();
                        //$modal.trigger({type: 'hide.bs.modal'});//find("[data-dismiss=modal]").trigger({ type: "click" });
                        //$modal = $modals.pop();
                    //    $modal.modal('hide');
                    //    $modal = $modals[$modals.length - 1];
                    //}
                    document.location.reload();
                } else if ($modals.length === 1 && $modal.reload) {
                    document.location.reload();
                } else if ($modals.length > 1 && $modal.reload) {
                    // reload ajax modal
                    var $parent = $modals[$modals.length - 2];
                    console.log('reload:' + $parent.url);
                    showSpinner();
                    $parent.loading = true;
                    var $title = $parent.attr('data-title');
                    $parent.find('.modal-content').load($parent.url, function() {
                        $parent.find('span.form-title').html($title);
                        $parent.loading = false;
                        hideSpinner();
                    });
                }
            },
            'error': function(xhr) {
                console.log('error');

                var $modal = $modals[$modals.length - 1];
                var $title = $modal.attr('data-title');
                try {
                    // parse the response for a json answer
                    var json = JSON.parse(xhr.responseText);
                    // if a redirect was given
                    if (json.redirect) {
                        console.log('json.redirect');
                        $modal.find('.modal-content').load(json.redirect, function() {
                            hideSpinner();
                            $modal.find('span.form-title').html($title);
                        });
                    }
                    // if an error was sent
                    else if (json.error) {
                        console.log('json.error');
                        // hide spinner
                        hideSpinner();
                        // display page
                        $('.modal-content').html(json.responseText);
                    }
                } catch (e) {
                    console.log('non.json');
                    if (xhr.responseText) {
                        // hide spinner
                        hideSpinner();
                        // response was plain text, show it
                        $('.modal-content').html(xhr.responseText);
                    } else {
                        // response was empty, should not happen, hide dialog
                        $modal = $modals[$modals.length - 1];
                        $modal.find("[data-dismiss=modal]").trigger({ type: "click" });

                        // reload parent without the modal
                        if ($modals.length === 1 && $modal.reload) {
                            document.location.reload();
                        } else if ($modals.length > 1 && $modal.reload) {
                            // reload ajax modal

                        }
                    }
                }
            }
        });
    });

    $(document)

    /**
     * When a modal is shown:
     * 3) hide spinner
     */
    .on('shown.bs.modal', function() {

    })


    /**
     * When a modal is hidden:
     * 1) pop the modal from the stack
     * 2) remove the modal, remove all parts from the dom
     * 3) unfreeze scrolling on top page if the modal stack is empty
     * 4) hide spinner
     */
    .on('hidden.bs.modal', function() {
        console.log('modal.destroy');

        // pop the modal
        let $modal = $modals.pop();

        // remove the modal from the dom
        $modal.off().remove();

        // unfreeze scrolling on top page
        if ($modals.length === 0)
            $('body').css('overflow', 'auto');

        // hide spinner
        if ($modals.length === 0 || !$modals[$modals.length - 1].loading)
            hideSpinner();
    });


});

function hideSpinner() {
    console.log('spinner.hide');
    $('#modal_loader').css('z-index', 0).css('display', 'none');
}
function showSpinner() {
    console.log('spinner.show');
    var $spinner = $('#modal_loader');
    if ($spinner.length === 0) {
        $spinner = $('<div id="modal_loader" class="loader"><div class="fa fa-spinner fa-4x fa-pulse"></div></div>');
        $('body').append($spinner);
    }
    $spinner.css('z-index', 1000000).css('display', '');
}