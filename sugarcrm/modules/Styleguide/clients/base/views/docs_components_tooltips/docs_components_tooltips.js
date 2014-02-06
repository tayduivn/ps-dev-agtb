({
    className: 'container-fluid',

    //components tooltips
    _renderHtml: function () {
        this._super('_renderHtml');

        this.$('#tooltips').tooltip({
            selector: '[rel=tooltip]'
        });
    }
})
