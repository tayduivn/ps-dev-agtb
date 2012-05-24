(function(app) {

    /**
     * View that displays a modal to add a activity in the activity stream.
     * @class View.Views.EditmodalView
     * @alias SUGAR.App.layout.EditmodalView
     * @extends View.View
     */
    app.view.views.EditmodalView = app.view.View.extend({
        events: {
            'click [name=save_button]': 'saveButton',
            'click [name=cancel_button]': 'cancelButton'
        },
        initialize: function(options) {
            app.view.View.prototype.initialize.call(this, options);
            this.fallbackFieldTemplate = "edit";

            if (this.layout) {
                this.layout.on("app:view:activity:editmodal", function() {
                    this.context.set('createModel',
                        app.data.createRelatedBean(app.controller.context.get('model'), null, "notes", {})
                    );
                    this.render();
                    this.$('.modal').modal('show');
                }, this);
            }
        },

        // Delegate events
        saveButton: function() {
            var self = this;
            this.context.get('createModel').set('portal_flag', true);
            this.context.get('createModel').save(null, {
                relate: true,
                success: function(data) {
                    self.$el.find('[name=save_button]').button();
                    //reset the form
                    self.$('.modal').modal('hide').find('form').get(0).reset();
                    // adds the model to the collection
                    self.context.get('collection').add(self.context.get('createModel'));
                },
                error: function(data) {
                    self.$el.find('[name=save_button]').button();
                }
            });
        },
        cancelButton: function() {
            this.$('.modal').modal('hide').find('form').get(0).reset();
            this.context.get('createModel').clear();
        }
    });

})(SUGAR.App);
