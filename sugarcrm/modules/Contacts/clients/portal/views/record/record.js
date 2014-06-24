/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

//FILE SUGARCRM flav=ent ONLY
/**
 * Add support for changing application language when the Portal user's
 * preferred language changes.
 *
 * @class View.Views.Portal.Contacts.RecordView
 * @alias SUGAR.App.view.views.PortalContactsRecordView
 * @extends View.Views.Base.RecordView
 */
({
    extendsFrom: 'RecordView',
    /**
     * @override
     */
    bindDataChange: function(){
        this._super("bindDataChange");
        var model = this.context.get('model');
        model.on('sync', this._setPreferredLanguage, this);
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
