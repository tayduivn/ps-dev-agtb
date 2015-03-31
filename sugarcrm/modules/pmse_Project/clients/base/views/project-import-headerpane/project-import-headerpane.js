/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
({
    extendsFrom: 'HeaderpaneView',
    events:{
        'click [name=project_finish_button]': 'initiateFinish',
        'click [name=project_cancel_button]': 'initiateCancel'
    },

    initiateFinish: function() {
        var that = this;
        App.alert.show('project-import-confirmation',  {
            level: 'confirmation',
            messages: translate('LBL_PMSE_IMPORT_CONFIRMATION'),
            onConfirm: function () {
                that.context.trigger('project:import:finish');
            },
            onCancel: function () {
                app.router.goBack();
            }
        });
    },

    initiateCancel : function() {
        //app.router.navigate(app.router.buildRoute('Home'), {trigger: true});
        app.router.goBack();
    }
})
