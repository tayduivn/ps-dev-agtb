({
    className: 'container-fluid',

    // forms range
    _renderHtml: function () {
        this._super('_renderHtml');

        var fieldSettings = {
            view: this,
            def: {
                name: 'include',
                type: 'range',
                view: 'edit',
                sliderType: 'connected',
                minRange: 0,
                maxRange: 100,
                'default': true,
                enabled: true
            },
            viewName: 'edit',
            context: this.context,
            module: this.module,
            model: this.model,
            meta: app.metadata.getField('range')
        },
        rangeField = app.view.createField(fieldSettings);

        this.$('#test_slider').append(rangeField.el);

        rangeField.render();

        rangeField.sliderDoneDelegate = function(minField, maxField) {
            return function(value) {
                minField.val(value.min);
                maxField.val(value.max);
            };
        }(this.$('#test_slider_min'), this.$('#test_slider_max'));
    }
})
