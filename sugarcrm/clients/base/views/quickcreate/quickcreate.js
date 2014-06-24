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
 * @class View.Views.Base.QuickcreateView
 * @alias SUGAR.App.view.views.BaseQuickcreateView
 * @extends View.View
 */
({
    plugins: ['Dropdown', 'Tooltip'],

    /**
     * @param {Object} options
     * @inheritDoc
     */
    initialize: function(options) {
        app.events.on("app:sync:complete", this.render, this);
        app.view.View.prototype.initialize.call(this, options);
    },

    /**
     * @inheritDoc
     * @private
     */
    _renderHtml: function() {
        if (!app.api.isAuthenticated() || app.config.appStatus == 'offline') {
            return;
        }
        // loadAdditionalComponents fires render before the private metadata is ready, check for this
        if (app.isSynced) {
            this.createMenuItems = this._getMenuMeta(app.metadata.getModuleNames({filter: 'quick_create', access: 'create'}));
            app.view.View.prototype._renderHtml.call(this);
        }
    },

    /**
     * Retrieve the quickcreate metadata from each module in the list
     * Uses the visible flag on the metadata to determine if admin has elected to hide the module from the list
     *
     * @param {Array} module The module names
     * @return {Array} list of visible menu item metadata
     */
    _getMenuMeta: function(modules) {
        var returnList = [];
        _.each(modules, function(name) {
            var meta = app.metadata.getModule(name);
            if (meta && meta.menu && meta.menu.quickcreate) {
                var menuItem = _.clone(meta.menu.quickcreate.meta);
                if (menuItem.visible === true) {
                    menuItem.module = name;
                    menuItem.type = menuItem.type || 'quickcreate';
                    // apply default icon for compatibility with customizations from previous versions
                    // but leave the possibility to turn icon off by specifying empty value
                    if (!("icon" in menuItem)) {
                        menuItem.icon = "icon-plus";
                    }
                    //TODO: refactor sidecar field hbs helper so it can accept the module name directly
                    menuItem.model = app.data.createBean(name);
                    returnList.push(menuItem);
                }
            }
        }, this);
        return this._sortByOrder(returnList);
    },

    /**
     * Sorts the module list based upon the value of the order attribute.
     *
     * @param {Array} moduleList
     * @returns {Array}
     * @private
     */
    _sortByOrder: function(moduleList) {
        return moduleList.sort(function(a, b) {
            var order = a['order'] - b['order'];
            return (order == 0) ? (a['label'] > b['label']) : order;
        });
    }
})
