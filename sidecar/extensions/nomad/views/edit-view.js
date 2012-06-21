(function(app) {

    app.view.views.EditView = app.view.View.extend({

        events: {
            "click #saveRecord": "saveRecord",
            "click #backRecord": "cancel"
        },

        initialize: function (options) {
            app.view.View.prototype.initialize.call(this, options);
            this.backupModel();

            _.each(this.meta.panels, function (panel, panelIndex) {
                _.each(panel.fields, function (field, fieldIndex) {
                    if (field.name.indexOf("email") == 0) field.type = "singleemail";
                });
            });

            var link = this.context.get("link");
            if (link) {
                // Pre-populate relate field
                var parentModule = this.model.link.bean.module;
                var parentId = this.model.link.bean.id;
                var relateField = app.data.getRelateField(parentModule, link);
                if (relateField) {
                    this.relateField = relateField.name;
                    this.model.set(relateField.id_name, parentId);
                }
            }
       },

        saveRecord: function () {
            var self = this;
            app.alert.show('save_process', {level: 'general', messages: 'Saving...', autoClose: true});

            this.model.save(null, {
                relate: !!this.context.get('link'),
                fieldsToValidate: this.getFields(),
                success: function (model, resp) {
                    app.alert.dismiss('save_process');
                    app.alert.show('save_success', {level: 'success', messages: 'Saved successfully.', autoClose: true});
                    var depth = parseInt(self.context.get("depth")) || 1;
                    app.router.go(-depth);
                },
                error: function (model, resp, options) {
                    app.alert.dismiss('save_process');
                    app.alert.show('save_error', {level: 'error', messages: 'Save error!', autoClose: true});
                }
            });
        },

        cancel: function (e) {
            this.restoreModel();
            var depth = parseInt(this.context.get("depth")) || 1;
            app.router.go(-depth);
        },

        backupModel: function () {
            var serializedModel = JSON.stringify(this.model.attributes);
            this._modelBackup = JSON.parse(serializedModel);
        },

        restoreModel: function () {
            this.model.set(this._modelBackup);
        }
    });

})(SUGAR.App);