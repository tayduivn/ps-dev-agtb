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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({
    plugins: ['Dropdown', 'Tooltip'],
    initialize: function(options) {
        app.events.on("app:sync:complete", this.render, this);
        app.view.View.prototype.initialize.call(this, options);
    },

    _renderHtml: function() {
        if (!app.api.isAuthenticated() || app.config.appStatus == 'offline') {
            return;
        }

        // loadAdditionalComponents fires render before the private metadata is ready, check for this
        if (!(_.isEmpty(app.metadata.getStrings("mod_strings")))) {
            var modules = app.metadata.getModules();
            this.createMenuItems = this._getMenuMeta(modules);
            app.view.View.prototype._renderHtml.call(this);
        }
    },

    /**
     * Retrieve the quickcreate metadata from each module in the list
     * Uses the visible flag on the metadata to determine if admin has elected to hide the module from the list
     *
     * @param {Array} modules
     * @return {Array} list of visible menu item metadata
     */
    _getMenuMeta: function(modules) {
        var menuItem, returnList = [];
        _.each(modules, function(meta, name) {
            // remove any modules for which the user doesn't have create access
            if (meta && meta.menu && meta.menu.quickcreate && app.acl.hasAccess('create', name)) {
                menuItem = meta.menu.quickcreate.meta;
                if (menuItem.visible === true) {
                    menuItem.module = name;
                    menuItem.type = menuItem.type || 'quickcreate';
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
        var sorted = _.sortBy(moduleList, function(menuItem) {
            return menuItem.order;
        });
        return sorted;
    }
})
