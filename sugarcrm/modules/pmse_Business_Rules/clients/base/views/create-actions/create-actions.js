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
    extendsFrom: 'CreateView',

    saveOpenBusinessRulesName: 'save_open_businessrules',

    SAVEACTIONS: {
        SAVE_OPEN_BUSINESRULES: 'saveOpenBusinessRules'
    },

    initialize: function (options) {
        app.view.invokeParent(this, {type: 'view', name: 'create-actions', method: 'initialize', args:[options]});

        var createViewEvents = {};
        createViewEvents['click a[name=' + this.saveOpenBusinessRulesName + ']:not(.disabled)'] = 'saveOpenBusinessRules';
        this.events = _.extend({}, this.events, createViewEvents);
    },

    saveOpenBusinessRules: function() {
        this.context.lastSaveAction = this.SAVEACTIONS.SAVE_OPEN_BUSINESRULES;
        this.initiateSave(_.bind(function () {
            app.navigate(this.context, this.model, 'layout/businessrules');
        }, this));
    }
})
