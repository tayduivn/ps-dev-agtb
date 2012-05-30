(function(app) {

    app.view.views.EditView = app.view.View.extend({
        events: {
            "click #saveRecord": "saveRecord",
            "click #backRecord": "cancel"
        },
        initialize: function (options) {
            app.view.View.prototype.initialize.call(this, options);
            this.backupModel();
        },
        saveRecord: function () {
            var model = this.context.get("model"),
                module = model.module;

            model.save(null, {
                success: function (model, resp) {
                    app.alert.show('save_success', {level: 'success', messages: 'Saved successfully.', autoClose: true});
                    app.router.goBack();
                },
                error: function (model, resp, options) {
                    app.alert.show('save_error', {level: 'error', messages: 'Save error!', autoClose: true});
                }
            });
        },
        cancel: function (e) {
            this.restoreModel();
            app.router.goBack();
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