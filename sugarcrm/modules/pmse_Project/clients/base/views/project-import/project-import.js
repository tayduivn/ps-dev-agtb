({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.context.off("project:import:finish", null, this);
        this.context.on("project:import:finish", this.importProject, this);
    },

    /**
     * {@inheritdocs}
     *
     * Sets up the file field to edit mode
     *
     * @param {View.Field} field
     * @private
     */
    _renderField: function(field) {
        app.view.View.prototype._renderField.call(this, field);
        if (field.name === 'project_import') {
            field.setMode('edit');
        }
    },

    importProject: function() {
//        console.log('importProject');
        var self = this,
            projectFile = $('[name=project_import]');
//            console.log(projectFile );
        if (_.isEmpty(projectFile.val())) {
            app.alert.show('error_validation_process', {
                level:'error',
                messages: app.lang.get('LBL_PMSE_PROCESS_DEFINITION_EMPTY_WARNING', self.module),
                autoClose: false
            });
        } else {
            app.file.checkFileFieldsAndProcessUpload(self, {
                    success: function (data) {
                        var route = app.router.buildRoute(self.module, data.project_import);
                        route = route + '/layout/designer';
                        app.router.navigate(route, {trigger: true});
                        app.alert.show('process-import-saved', {
                            level: 'success',
                            messages: app.lang.get('LBL_PMSE_PROCESS_DEFINITION_IMPORT_SUCCESS', self.module),
                            autoClose: true
                        });
                    },
                    error: function (data) {
                        app.alert.show('process-import-saved', {
                            level: 'error',
                            messages: app.lang.get(data.responseText, self.module),
                            autoClose: false
                        });
                    }
                },
                {deleteIfFails: true, htmlJsonFormat: true}
            );
        }
    },
    checkFileFieldsAndProcessUpload : function(model, callbacks) {

        callbacks = callbacks || {};

        //check if there are attachments
        var $files = _.filter($(":file"), function(file) {
            var $file = $(file);
            return ($file.val() && $file.attr("name") && $file.attr("name") !== "") ? $file.val() !== "" : false;
        });
        var filesToUpload = $files.length;

        //process attachment uploads
        if (filesToUpload > 0) {
            app.alert.show('upload', {level: 'process', title: 'LBL_UPLOADING', autoclose: false});

            //field by field
            for (var file in $files) {
                var $file = $($files[file]),
                    fileField = $file.attr("name");
                if (callbacks.success) callbacks.success();

//                model.uploadFile(fileField, $file, {
//                    field: fileField,
//                    success: function() {
//                        filesToUpload--;
//                        if (filesToUpload===0) {
//                            app.alert.dismiss('upload');
//                            if (callbacks.success) callbacks.success();
//                        }
//                    },
//                    error: function(error) {
//                        filesToUpload--;
//                        if (filesToUpload===0) {
//                            app.alert.dismiss('upload');
//                        }
//                        var errors = {};
//                        errors[error.responseText] = {};
//                        model.trigger('error:validation:' + this.field, errors);
//                        model.trigger('error:validation');
//                    }
//                });
            }
        }
        else {
            if (callbacks.success) callbacks.success();
        }
    }
})
