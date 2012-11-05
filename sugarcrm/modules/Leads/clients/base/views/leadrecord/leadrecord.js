/**
 * View that displays edit view on a model
 * @class View.Views.EditView
 * @alias SUGAR.App.layout.EditView
 * @extends View.View
 */
({
    extendsFrom: 'RecordView',
    events: {
       'click .record-convert': 'showConvert'
    },
/*
    render: function() {
        app.view.View.prototype.render.call(this);

        var convert = this.model.get('status');
        if(convert === 'Converted') {
            $('.record-convert').addClass('hide');
        }

    },
*/
    showConvert: function() {
        var view = app.view.createView({
            context: this.context,
            module: this.context.get("module"),
            name: "convert"
        });

       $('.record-button-bar').before(view.$el);
       view.render();

    }
})
