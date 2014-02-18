({
    className: 'container-fluid',

    // forms switch
    _renderHtml: function () {
        this._super('_renderHtml');

        this.$('#mySwitch').on('switch-change', function (e, data) {
            var $el = $(data.el),
                value = data.value;
        });
    },

    _dispose: function() {
        this.$('#mySwitch').off('switch-change');

        this._super('_dispose');
    }
})
