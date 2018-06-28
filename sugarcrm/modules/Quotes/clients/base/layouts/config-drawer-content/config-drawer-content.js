/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Layouts.Base.Quotes.ConfigDrawerContentLayout
 * @alias SUGAR.App.view.layouts.BaseQuotesConfigDrawerContentLayout
 * @extends View.Layouts.Base.ConfigDrawerContentLayout
 */
({
    /**
     * @inheritdoc
     */
    extendsFrom: 'BaseConfigDrawerContentLayout',

    /**
     * @inheritdoc
     */
    _initHowTo: function() {
        var helpUrl = {
            more_info_url: '<a href="' + app.help.getMoreInfoHelpURL('config', 'QuotesConfig') + '" target="_blank">',
            more_info_url_close: '</a>',
        };
        var viewQuotesObj = app.help.get('Quotes', 'config_opps', helpUrl);

        this.viewQuotesObjTpl = app.template.getLayout(this.name + '.help', this.module)(viewQuotesObj);
    },

    /**
     * @inheritdoc
     */
    _switchHowToData: function(helpId) {
        switch (helpId) {
            case 'config-columns':
            case 'config-summary':
            case 'config-summation':
            case 'config-total':
                this.currentHowToData.title = app.lang.get('LBL_CONFIG_FIELD_SELECTOR', this.module, {
                    moduleName: app.lang.get('LBL_MODULE_NAME', this.module),
                });
                this.currentHowToData.text = this.viewQuotesObjTpl;
                break;
        }
    }
})
