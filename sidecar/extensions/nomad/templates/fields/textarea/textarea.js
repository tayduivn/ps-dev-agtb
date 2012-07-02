(function(app) {

    app.view.fields.TextareaField = app.view.Field.extend({

        fieldTag: "textarea",

        events: {
            'click textarea': 'onClick'
        },

        initialize: function (options) {
            app.view.Field.prototype.initialize.call(this, options);

            this.textareaView = null;                                 //store textarea-edit view
        },

        onClick: function (e) {
            //create textarea-edit view and pass current field name there
            this.textareaView = app.view.createView({
                name: "textarea-edit",
                context: this.context,
                editedFieldName: this.name
            });

            //save data changes and dispose textarea-edit view on edit complete
            var self = this;
            this.textareaView.on("textarea:edit:saved", function(value) {
                self.model.set(self.name, value);
                self.textareaView.dispose();
                self.textareaView = null;
            });

            //render textarea-edit views markup
            this.textareaView.$el.appendTo('#content');
            this.textareaView.render();
            this.textareaView.setValue(this.model.get(this.name));
        },

        _dispose: function() {
            app.view.Field.prototype._dispose.call(this);
            if (this.textareaView) this.textareaView.dispose();
        }

    });

})(SUGAR.App);