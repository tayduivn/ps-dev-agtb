// layouts drawer
function _render_content(view, app) {
    view.$('#sg_open_drawer').on('click.styleguide', function(){
        app.drawer.open({
            layout: 'create',
            context: {
                create: true,
                model: app.data.createBean('Styleguide')
            }
        });
    });
}

function _dispose_content(view) {
    view.$('#sg_open_drawer').off('click.styleguide');
}
