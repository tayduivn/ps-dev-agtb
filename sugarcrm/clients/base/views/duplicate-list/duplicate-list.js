/**
 * View that displays edit view on a model
 * @class View.Views.EditView
 * @alias SUGAR.App.layout.EditView
 * @extends View.View
 */
({
    extendsFrom: 'ListView',
    initialize: function(options) {
        options.meta = $.extend(true, {}, app.metadata.getView(options.module, "list"), options.meta);

        app.view.views.ListView.prototype.initialize.call(this, options);
    },

    _render: function() {
        app.view.views.ListView.prototype._render.call(this);
        this.$('table.table-striped').addClass('duplicates highlight');
    }
})
