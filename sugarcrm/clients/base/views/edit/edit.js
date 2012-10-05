/**
 * View that displays edit view on a model
 * @class View.Views.EditView
 * @alias SUGAR.App.layout.EditView
 * @extends View.View
 */
({
    extendsFrom: 'BaseeditView',
    events: {
        'click [name=save_button]': 'saveModel'
    },
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.context.off("subnav:save", null, this);
        this.context.on("subnav:save", this.saveModel, this);
        this.model.on("error:validation", this.handleValidationError, this);
    },
    saveModel: function() {
        var self = this;

        // TODO we need to dismiss this in global error handler
        app.alert.show('save_edit_view', {level: 'process', title: app.lang.getAppString('LBL_PORTAL_SAVING')});
        this.model.save(null, {
            success:function () {

                self.checkFileFieldsAndProcessUpload(self.model, {
                    success: function () {
                        app.alert.dismiss('save_edit_view');
                        self.app.navigate(self.context, self.model, 'detail');
                    }
                });

            },
            fieldsToValidate: this.getFields(this.module)
        });
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
                model.uploadFile(fileField, $file, {
                    field: fileField,
                    success: function() {
                        filesToUpload--;
                        if (filesToUpload===0) {
                            app.alert.dismiss('upload');
                            if (callbacks.success) callbacks.success();
                        }
                    },
                    error: function(error) {
                        filesToUpload--;
                        if (filesToUpload===0) {
                            app.alert.dismiss('upload');
                        }
                        var errors = {};
                        errors[error.responseText] = {};
                        model.trigger('error:validation:' + this.field, errors);
                        model.trigger('error:validation');
                    }
                });
            }
        }
        else {
            if (callbacks.success) callbacks.success();
        }
    },
    _renderHtml: function() {
        app.view.View.prototype._renderHtml.call(this);
        // workaround because we use the same view for edit and create
        if (!this.model.id) {
            this.context.trigger('subnav:set:title', app.lang.get('LBL_NEW_FORM_TITLE', this.module));
        }
    }
})
