//FILE SUGARCRM flav=ent ONLY
/**
 * Add support for changing application language when the Portal user's preferred language changes
 * @class View.PortalContactsRecordView
 * @alias SUGAR.App.view.views.PortalContactsRecordView
 * @extends View.RecordView
 */
({
    extendsFrom: 'RecordView',
    /**
     * @override
     */
    bindDataChange: function(){
        app.view.invokeParent(this, {
            type: 'view',
            name: 'record',
            platform: 'base',
            method: 'bindDataChange'});
        this.context.on("button:save_button:click", this._setPreferredLanguage, this);
    },
    /**
     * Update application language based on Portal user's preferred language
     * @private
     */
    _setPreferredLanguage: function(){
        var newLang = this.model.get("preferred_language");
        if(_.isString(newLang) && newLang !== app.lang.getLanguage()){
            app.alert.show('language', {level: 'warning', title: 'LBL_LOADING_LANGUAGE', autoclose: false});
            app.lang.setLanguage(newLang, function(){
                app.alert.dismiss('language');
            });

        }
    }
})
