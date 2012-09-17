 /**
 * View that displays edit view on a model
 * @class View.Views.EditView
 * @alias SUGAR.App.layout.EditView
 * @extends View.View
 */
({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
//        this.model.on("error:validation", this.handleValidationError, this);
        this.sendModel = this.initializeSendEmailModel();
    },

    initializeSendEmailModel: function() {
        var view = this;
        var SaveModel = Backbone.Model.extend({
            sync: function (method, model, options) {
                this.hydrateFromEditModel();
                var myURL = app.api.buildURL('Emails');
                return app.api.call(method, myURL, model, options);
            },
            
            hydrateFromEditModel: function() {
                this.set(_.extend({}, view.model.attributes, {
                    status: "ready",
                    to_addresses: [ {
                        email: view.model.get('to_addresses')
                    }]
                }));
            }
        });
        return new SaveModel;
    },
    
    saveModel: function() {
        // TODO we need to dismiss this in global error handler
        app.alert.show('save_edit_view', {level: 'process', title: app.lang.getAppString('LBL_PORTAL_SAVING')});
        this.sendModel.save(null, {
            success: function(data, textStatus, jqXHR) {
                app.alert.show('save_edit_view', {level: 'process', title: app.lang.getAppString('LBL_EMAIL_SEND_SUCCESS')});
                console.info("Email sent!", arguments);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                app.alert.show('save_edit_view', {level: 'process', title: app.lang.getAppString('LBL_EMAIL_SEND_FAILURE')});
                console.error("Email not sent!", arguments);
            },
            complete: function() {
                setTimeout(function() {
                    app.alert.dismiss('save_edit_view');
                }, 2000);
            },
            
            fieldsToValidate: this.getFields(this.module)
        });
    }
})
