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
/**
 * @class View.Layouts.Base.DupecheckFilterLayout
 * @alias SUGAR.App.view.layouts.BaseDupecheckFilterLayout
 * @extends View.Layouts.Base.Filter
 */
({
    extendsFrom: 'FilterLayout',
    initialFilter: 'all_records',

    initialize: function(options) {
        this._super('initialize', [options]);
        this.name = 'filter';

        //initialize the last filter to show all duplicates before allowing user to change the filter
        this.setLastFilter(this.module, this.layoutType, this.initialFilter);
    },

    /**
     * {@inheritDoc}
     *
     * Override getting relevant context logic in order to filter on current
     * context.
     */
    getRelevantContextList: function() {
        return [this.context];
    },

    /**
     * {@inheritDoc}
     *
     * Override getting last filter in order to retrieve found duplicates for
     * initial set.
     */
    getLastFilter: function() {
        var lastFilter = this._super('getLastFilter', arguments);
        return (_.isUndefined(lastFilter)) ? this.initialFilter : lastFilter;
    }
})
