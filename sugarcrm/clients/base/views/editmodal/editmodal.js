({
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
                this.context.get('createModel').on("error:validation", function() {
                    this.resetButton();
                }, this);
            }, this);
        }
    },
    // Delegate events
    saveButton: function() {
        var self = this,
            createModel = this.context.get('createModel');

        self.$('[name=save_button]').button('loading');

        // portal_flag is a required field for Notes
        createModel.set('portal_flag', true);

        // saves the related bean
        createModel.save(null, {
            relate: true,
            fieldsToValidate: this.getFields(this.module),
            success: function() {
                self.checkFileFieldsAndProcessUpload(createModel, {
                    success: function() { self.saveComplete(); }
                });
            },
            error: function() {
                self.resetButton();
            }

        });
    },
    cancelButton: function() {
        this.$('.modal').modal('hide').find('form').get(0).reset();
        this.context.get('createModel').clear();
    },
    saveComplete: function() {
        //reset the form
        this.$('.modal').modal('hide').find('form').get(0).reset();
        //reset the `Save` button
        this.resetButton();
        //add the new model to the collection
        this.collection.fetch({relate:true});
    },
    resetButton: function() {
        this.$('[name=save_button]').button('reset');
    }
})