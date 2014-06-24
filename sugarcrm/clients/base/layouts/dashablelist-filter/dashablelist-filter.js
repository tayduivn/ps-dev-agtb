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
 * @class View.Layouts.Base.DashablelistFilterLayout
 * @alias SUGAR.App.view.layouts.BaseDashablelistFilterLayout
 * @extends View.Layout
 */
({
    className: 'dashablelist-filter',

    /**
     * {@inheritDoc}
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        // We need to initialize the filterpanel with the filter and module
        // saved on the dashlet.
        var filterPanelLayout = this.getComponent('filterpanel');
        if (filterPanelLayout) {
            filterPanelLayout.before('render', this._reinitializeFilterPanel, null, this);
            this.listenTo(this.layout, 'dashlet:filter:reinitialize', filterPanelLayout.render);
        }
    },

    /**
     * This function sets the `currentModule` on the filterpanel layout, and
     * the `currentFilterId` on its context. It is invoked before
     * `filter:reinitialize` is triggered from `_render` on the filterpanel
     * layout.
     *
     * @private
     */
    _reinitializeFilterPanel: function() {
        var filterPanelLayout = this.getComponent('filterpanel');
        if (!filterPanelLayout) {
            return;
        }

        var moduleName = this.model.get('module'),
            id = this.model.get('filter_id');

        filterPanelLayout.currentModule = moduleName;
        this.context.set('currentFilterId', id);
    }
})
