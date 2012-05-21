(function(app) {

    /**
     * View that displays edit view on a model
     * @class View.Views.EditView
     * @alias SUGAR.App.layout.EditView
     * @extends View.View
     */
    app.view.views.EditView = app.view.View.extend({
        events: {
            'click [name=save_button]': 'saveModel'
        },
        initialize: function(options) {
            app.view.View.prototype.initialize.call(this, options);
        },
        saveModel: function() {
            var self = this;

            // TODO we need to dismiss this in global error handler
            app.alert.show('save_edit_view', {level:'process', title:'Saving'});
            this.model.save(null, {
                success: function() {
                    app.alert.dismiss('save_edit_view');
                    self.app.navigate(self.context, self.model, 'detail');
                },
                fieldsToValidate: this.getFields(this.model.module)
            });
        },
        bindDataChange: function() {
            if (this.model) {
                this.model.on("change", function() {
                        if (this.app.additionalComponents.subnav) {
                            this.app.additionalComponents.subnav.model = app.controller.context.attributes.model;
                            this.app.additionalComponents.subnav.meta = this.meta;
                        }
                    }, this
                );
            }
        }
    });
})(SUGAR.App);
