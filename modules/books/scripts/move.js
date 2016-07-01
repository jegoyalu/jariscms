$(document).ready(function() {
    var fixHelper = function(e, ui) {
        ui.children().each(function() {
            $(this).width($(this).width());
        });
        return ui;
    };

    $(".book-pages-list tbody").sortable({
        cursor: 'crosshair',
        helper: fixHelper,
        handle: "a.sort-handle"
    });
});