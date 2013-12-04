// forms switch
function _render_content(view, app) {
    view.$('#mySwitch').on('switch-change', function (e, data) {
        var $el = $(data.el),
            value = data.value;
    });
}

function _dispose_content(view) {
    view.$('#mySwitch').off('switch-change');
}
