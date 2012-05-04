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
            this.saveEnabled = true;
            app.view.View.prototype.initialize.call(this, options);
            if (this.model) {
                this.model.on('model.validation.disableSave', this.disableSave, this);
                this.model.on('model.validation.enableSave', this.enableSave, this);
            }

        },
        saveModel: function() {
            var self = this;
            if (this.saveEnabled) {
                this.model.save(null, {
                    success: function() {
                        self.app.navigate(self.context, self.model, 'detail');
                    }
                });
            }
        },
        disableSave: function() {
            this.$('[name=save_button]').attr('disabled', true);
            this.saveEnabled = false;
        },
        enableSave: function() {
            this.$('[name=save_button]').attr('disabled', false);
            this.saveEnabled = true;
        }
    });

})(SUGAR.App);