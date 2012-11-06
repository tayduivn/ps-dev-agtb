/**
 * View that displays edit view on a model
 * @class View.Views.EditView
 * @alias SUGAR.App.layout.EditView
 * @extends View.View
 */
({
    extendsFrom: 'RecordView',
    events: {
       'click .lead-convert': 'showConvert'
    },

    showConvert: function() {
        var layout = app.view.createLayout({
            context: this.context,
            module: this.context.get("module"),
            name: "convert",
            layout: this.layout
        });

        $('.headerpane').parent().before(layout.$el);
        layout.render();
    }
})
