({
    events: {
        'click [name=modal_save_button]': 'saveModel',
        'click [name=full_form_button]': 'getFullForm'
    },
    loadData: function() {
        var popup = this.layout.getPopupComponent();
        if(popup) {
            this.meta = app.metadata.getView(popup.model.module, popup.context.get('name'));
        }
    },
    getFullForm: function(evt) {
        var popup = this.layout.getPopupComponent();
        if(popup) {
            this.app.router.navigate(popup.model.module + "/" + popup.model.get("id") + "/edit", {trigger:true});
        }
    },
    saveModel: function() {
        var popup = this.layout.getPopupComponent();
        if(popup) {
            app.alert.show('save_edit_view', {level: 'process', title: app.lang.getAppString('LBL_PORTAL_SAVING')});
            popup.model.save(null, {
                success: function() {
                    app.alert.dismiss('save_edit_view');
                    popup.trigger("modal:callback", popup.model);
                },
                fieldsToValidate: popup.getFields(popup.module)
            });
        }
    }
})