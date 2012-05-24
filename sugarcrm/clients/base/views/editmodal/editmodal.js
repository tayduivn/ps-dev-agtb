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

        // We don't need the filename attribute, the upload is done later
        createModel.unset('filename', {silent: true});

        // saves the related bean
        createModel.save(null, {
            relate: true,
            fieldsToValidate: this.getFields(this.module),
            success: function() {
                //check if there are attachments
                var $files = this.$(":file[name=filename]");
                $files = _.filter($files, function(file) {
                    return $(file).val() ? $(file).val() !== "" : false;
                });

                if ($files.length == 0) {
                    self.saveComplete();
                } else {
                    app.alert.show('upload', {level: 'process', title: 'Uploading', autoclose: false});

                    createModel.uploadAttachment(self.module, "filename", $files, {
                        success: function() {
                            app.alert.dismiss('upload');
                            self.saveComplete();
                        },
                        error: function(xhr, responseText) {
                            app.alert.show('upload', {level: 'error', title: 'File upload error', messages: [responseText], autoclose: false});
                        }
                    });
                }
            },
            error: function() {
                self.$el.find('[name=save_button]').button();
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
        this.$('[name=save_button]').button();
        //add the new model to the collection
        this.collection.fetch({relate:true});
    }
})