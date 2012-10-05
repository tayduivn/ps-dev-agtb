 /**
 * View that displays edit view on a model
 * @class View.Views.EditView
 * @alias SUGAR.App.layout.EditView
 * @extends View.View
 */
({
    events: {
        'click [name=save_draft_button]': 'saveAsDraft',
        'click [name=save_button]': 'send'
    },

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
                var myURL = app.api.buildURL('Mail');
                return app.api.call(method, myURL, model, options);
            },
            
            hydrateFromEditModel: function() {
                this.set(_.extend({}, view.model.attributes, {
                    to_addresses: [ {
                        email: view.model.get('to_addresses')
                    }],
                    cc_addresses: [ {
                        email: view.model.get('cc_addresses')
                    }],
                    bcc_addresses: [ {
                        email: view.model.get('bcc_addresses')
                    }]
                }));
            }
        });
        return new SaveModel;
    },

    saveAsDraft: function(){
        debugger;
        this.saveModel('draft', app.lang.getAppString('LBL_EMAIL_SEND_SUCCESS'));
    },

    send: function() {
        debugger;
        this.saveModel('ready', app.lang.getAppString('LBL_EMAIL_SEND_SUCCESS'));
    },

    saveModel: function(status, successMessage) {
        var self = this;

        app.alert.show('save_edit_view', {level: 'process', title: app.lang.getAppString('LBL_PORTAL_SAVING')});

        this.sendModel.set('status', status);
        this.sendModel.save(null, {
            success: function(data, textStatus, jqXHR) {
                app.alert.dismiss('save_edit_view');
                app.alert.show('save_edit_view', {autoclose: true, level: 'success', title: successMessage});
                console.info("Email sent!", arguments);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                app.alert.dismiss('save_edit_view');
                var msg = {autoclose: false, level: 'error', title: app.lang.getAppString('LBL_EMAIL_SEND_FAILURE')};

                if(_.isString(textStatus.description)) {
                    msg.messages = [textStatus.description];
                }

                app.alert.show('save_edit_view', msg);
                console.error("Email not sent!", arguments);
            },
            complete: function() {
                /*setTimeout(function() {
                    app.alert.dismiss('save_edit_view');
                }, 2000);*/
            },
            
            fieldsToValidate: this.getFields(this.module)
        });
    }


})
