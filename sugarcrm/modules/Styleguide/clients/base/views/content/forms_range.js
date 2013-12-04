// forms range
function _render_content(view, app) {
    var fieldSettings = {
        view: view,
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
        context: view.context,
        module: view.module,
        model: view.model,
        meta: app.metadata.getField('range')
    },
    rangeField = app.view.createField(fieldSettings);

    view.$('#test_slider').append(rangeField.el);

    rangeField.render();

    rangeField.sliderDoneDelegate = function(minField, maxField) {
        return function(value) {
            minField.val(value.min);
            maxField.val(value.max);
        };
    }(view.$('#test_slider_min'), view.$('#test_slider_max'));
}
