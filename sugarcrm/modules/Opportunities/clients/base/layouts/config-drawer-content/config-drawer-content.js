// FILE SUGARCRM flav=ent ONLY
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
/**
 * @class View.Layouts.Base.OpportunitiesConfigDrawerContentLayout
 * @alias SUGAR.App.view.layouts.BaseOpportunitiesConfigDrawerContentLayout
 * @extends View.Layouts.Base.ConfigDrawerContentLayout
 */
({
    extendsFrom: 'ConfigDrawerContentLayout',

    viewOppsByTitle: undefined,
    viewOppsByText: undefined,

    /**
     * @inheritdoc
     * @override
     */
    _initHowTo: function() {
        this.viewOppsByTitle = app.lang.get('LBL_OPPS_CONFIG_VIEW_BY_LABEL', 'Opportunities');
        this.viewOppsByText = app.lang.get('LBL_OPPS_CONFIG_HELP_VIEW_BY_TEXT', 'Opportunities');
    },

    /**
     * @inheritdoc
     * @override
     */
    _switchHowToData: function(helpId) {
        switch(helpId) {
            case 'config-opps-view-by':
                this.currentHowToData.title = this.viewOppsByTitle;
                this.currentHowToData.text = this.viewOppsByText;
        }

        this._super('_switchHowToData');
    }
})
