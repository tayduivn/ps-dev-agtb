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
 * Module menu provides a reusable and easy render of a module Menu.
 *
 * This also helps doing customization of the menu per module and provides more
 * metadata driven features.
 *
 * @class View.Views.BaseModuleMenuView
 * @alias SUGAR.App.view.views.BaseModuleMenuView
 */
({
    className: 'btn-group',

    /**
     * The possible actions that this module menu provides.
     *
     * This comes from the metadata files, like:
     *
     * - {custom}/modules/&lt;Module&gt;/clients/base/menus/header/header.php
     */
    actions: [],

    /**
     * Records collection to be easier to apply filters.
     *
     * This will provide allow us to get recently viewed, favorites or other
     * records in the menu that might be needed.
     */
    _recordsCollection: {},

    /**
     * @inheritDoc
     *
     * Adds listener for bootstrap drop down show even (`shown.bs.dropdown`).
     * This will trigger menuOpen method.
     */
    initialize: function(options) {

        options.collection = app.data.createBeanCollection(options.module);

        this._super('initialize', [options]);

        this.events = _.extend({}, this.events, {
            'shown.bs.dropdown': 'populateMenu'
        });
    },

    /**
     * @inheritDoc
     *
     * Retrieves possible menus from the metadata already inSync.
     * Filters all menu actions based on ACLs to prevent user to click them and
     * get a `403` after click.
     */
    _renderHtml: function() {
        var meta = app.metadata.getModule(this.module) || {};

        this.actions = this.filterByAccess(meta.menu && meta.menu.header && meta.menu.header.meta);

        this._super('_renderHtml');
    },

    /**
     * Filters menu actions by ACLs for the current user.
     *
     * @param {Array} meta The menu metadata to check access.
     * @return {Array} Returns only the list of actions the user has access.
     */
    filterByAccess: function(meta) {

        var result = [];

        _.each(meta, function(menuItem) {
            if (app.acl.hasAccess(menuItem.acl_action, menuItem.acl_module)) {
                result.push(menuItem);
            }
        });

        return result;
    },

    /**
     * Method called when a `show.bs.dropdown` event occurs.
     *
     * Populate the favorites and recently viewed records every time we open
     * the menu.
     *
     * @param {Event} event The `shown.bs.dropdown` triggered by Bootstrap
     *   dropdown plugin.
     */
    populateMenu: function(event) {

        var meta = app.metadata.getModule(this.module) || {};

        // FIXME move this to the home override
        if (this.module === 'Home') {
            this.populateDashboards();
            return;
        }

        // FIXME some modules don't have fields therefore we don't have recent
        // and favorites, we should disable them using metadata not with this
        // hack
        if (_.isEmpty(_.omit(meta.fields, '_hash'))) {
            return;
        }

        if (meta.favoritesEnabled) {
            this.populate('favorites', [{
                '$favorite': ''
            }]);
        }

        this.populate('recently-viewed', [{
            '$tracker': '-7 DAY'
        }]);
    },


    /**
     * Return `true` if this menu is open, `false` otherwise.
     */
    isOpen: function() {
        return !!this.$el.hasClass('open');
    },

    /**
     * Populates records templates based on filter given.
     *
     * @param {String} tplName The template to use to populate data.
     * @param {String} filter The filter to be applied.
     */
    populate: function(tplName, filter) {

        var renderPartial = function(data) {
            if (this.disposed || !this.isOpen()) {
                return;
            }

            var tpl = app.template.getView(this.name + '.' + tplName, this.module) ||
                app.template.getView(this.name + '.' + tplName);

            var $placeholder = this.$('[data-container="' + tplName + '"]'),
                $old = $placeholder.nextUntil('.divider');

            $old.remove();
            $placeholder.after(tpl(this.collection));

        };

        this.collection.fetch({
            'fields': ['id', 'name'],
            'filter': filter,
            'limit': 3,
            'success': _.bind(renderPartial, this)
        });

        return;
    }

})
