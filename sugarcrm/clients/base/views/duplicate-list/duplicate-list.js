/**
 * View that displays edit view on a model
 * @class View.Views.EditView
 * @alias SUGAR.App.layout.EditView
 * @extends View.View
 */
({
    extendsFrom: 'ListView',
    initialize: function(options) {

        app.view.View.prototype.initialize.call(this, options);

        var singleSelect = [{
            'type' : 'selection',
            'name' : this.module + '_duplicate_select',
            'sortable' : false,
            'label' : 'Select'
        }];

        if(!_.isUndefined(this.meta)) {
            this.meta.panels[0].fields = singleSelect.concat(this.meta.panels[0].fields);
        }
    }
})
