({
    className: 'container-fluid',

    // layouts drawer
    _renderHtml: function () {
        this._super('_renderHtml');

        this.$('#sg_open_drawer').on('click.styleguide', function(){
            app.drawer.open({
                layout: 'create',
                context: {
                    create: true,
                    model: app.data.createBean('Styleguide')
                }
            });
        });
    },

    _dispose: function() {
        this.$('#sg_open_drawer').off('click.styleguide');

        this._super('_dispose');
    }
})
