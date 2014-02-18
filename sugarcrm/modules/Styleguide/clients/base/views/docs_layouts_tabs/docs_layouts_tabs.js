({
    className: 'container-fluid',

    // layouts tabs
    _renderHtml: function () {
        this._super('_renderHtml');

        this.$('#nav-tabs-pills')
            .find('ul.nav-tabs > li > a, ul.nav-list > li > a, ul.nav-pills > li > a')
            .on('click.styleguide', function(e){
                e.preventDefault();
                e.stopPropagation();
                $(this).tab('show');
            });
    },

    _dispose: function() {
        this.$('#nav-tabs-pills')
            .find('ul.nav-tabs > li > a, ul.nav-list > li > a, ul.nav-pills > li > a')
            .off('click.styleguide');

        this._super('_dispose');
    }
})
