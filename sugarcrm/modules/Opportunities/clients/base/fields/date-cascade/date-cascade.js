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
 * @class View.Fields.Base.Opportunities.DateCascadeField
 * @alias SUGAR.App.view.fields.BaseOpportunitiesDateCascadeField
 * @extends View.Fields.Base.DateField
 */
({
    extendsFrom: 'DateField',

    initialize: function(options) {
        this.plugins = _.union(this.plugins || [], ['Cascade']);
        this._super('initialize', [options]);
        this.def.lblString = app.lang.get('LBL_UPDATE_OPPORTUNITIES_RLIS', 'Opportunities') +
            ' ' +
            app.lang.getModuleName('RevenueLineItems', {plural: true});
    }
})
