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
        var view = app.view.createView({
            context: this.context,
            module: this.context.get("module"),
            name: "convert"
        });

       $('.headerpane').parent().before(view.$el);
       view.render();
    }
})
