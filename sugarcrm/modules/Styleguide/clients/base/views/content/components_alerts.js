// components dropdowns
function _render_content(view, app) {
    this.$('[data-alert]').on('click', function() {
        var level = $(this).data('alert'),
            state = $(this).text();

        if (state !== 'Fire') {
            app.alert.dismiss('core_meltdown_' + level);
            $(this).text('Fire');
        } else {
            app.alert.show('core_meltdown_' + level, {
                level: level,
                messages: 'The core is in meltdown!!',
                autoClose: false
            });
            $(this).text('Dismiss');
        }
    });
}

function _dispose_content(view) {
    app.alert.dismiss('core_meltdown');
}
