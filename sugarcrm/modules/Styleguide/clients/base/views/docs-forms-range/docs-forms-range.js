/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
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
