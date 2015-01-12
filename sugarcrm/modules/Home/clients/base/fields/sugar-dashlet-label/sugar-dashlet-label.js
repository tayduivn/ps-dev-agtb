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
 * @class View.Fields.Base.Home.SugarDashletLabelField
 * @alias SUGAR.App.view.fields.BaseHomeSugarDashletLabelField
 * @extends View.Field
 *
 * Label for trademarked `Sugar Dashlet&reg;` term.
 */
({
    /**
     * @override
     *
     * No-op function because this field is static.
     */
    bindDataChange: function() {},

    /**
     * @override
     *
     * No-op function because this field is static.
     */
    bindDomChange: function() {},

    /**
     * @override
     *
     * No-op function because this field is static.
     */
    unbindDom: function() {},

    /**
     * @inheritDoc
     *
     * Translates `this.def.label` and expands `{{sugardashlet}}` to include
     * the trademarked text.
     *
     * @return {Handlebars.SafeString} The trademarked text.
     */
    format: function() {
        return new Handlebars.SafeString(app.lang.get(this.def.label, 'Home', {
            sugardashlet: new Handlebars.SafeString('Sugar Dashlet<sup>&reg;</sup>')
        }));
    }
});
