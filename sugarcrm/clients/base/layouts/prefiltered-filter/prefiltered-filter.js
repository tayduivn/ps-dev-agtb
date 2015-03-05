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
 * @class View.Layouts.Base.PrefilteredFilterLayout
 * @alias SUGAR.App.view.layouts.BasePrefilteredFilterLayout
 * @extends View.Layout.Base.FilterLayout
 */
({
    /**
     * {@inheritDoc}
     */
    extendsFrom: 'FilterLayout',

    /**
     * Predefined filter definition.
     */
    filterDef: null,

    /**
     * {@inheritDoc}
     */
    initialize: function(opts) {
        var filter = app.data.getBeanClass('Filters').prototype;
        this._super('initialize', [opts]);
        this.filterDef = filter.combineFilterDefinitions(
            this.context.get('collection').origFilterDef,
            this.context.get('filterDef')
        );
    },

    /**
     * {@inheritDoc}
     */
    applyFilter: function(query, dynamicFilterDef) {
        this.context.get('collection').origFilterDef =
            _.union([], this.filterDef,  this.context.get('collection').origFilterDef);
        this._super('applyFilter', [query, dynamicFilterDef]);
    }
})
