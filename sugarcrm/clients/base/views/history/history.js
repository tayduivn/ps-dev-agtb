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
/**
 * History dashlet takes advantage of the tabbed dashlet abstraction by using
 * its metadata driven capabilities to configure its tabs in order to display
 * historic information about specific modules.
 *
 * @class View.Views.BaseHistoryView
 * @alias SUGAR.App.view.views.BaseHistoryView
 * @extends View.Views.BaseTabbedDashletView
 */
({
    extendsFrom: 'TabbedDashletView',

    /**
     * {@inheritDoc}
     *
     * @property {Number} _defaultSettings.filter Number of past days against
     *   which retrieved records will be filtered, supported values are '7',
     *   '30' and '90' days, defaults to '7'.
     * @property {Number} _defaultSettings.limit Maximum number of records to
     *   load per request, defaults to '10'.
     * @property {String} _defaultSettings.visibility Records visibility
     *   regarding current user, supported values are 'user' and 'group',
     *   defaults to 'user'.
     */
    _defaultSettings: {
        filter: 7,
        limit: 10,
        visibility: 'user'
    },

    /**
     * {@inheritDoc}
     */
    initialize: function(options) {
        options.meta = options.meta || {};
        options.meta.template = 'tabbed-dashlet';

        this._super('initialize', [options]);
    },

    /**
     * Retrieves custom filters.
     *
     * @param {Integer} index Tab index.
     * @return {Array} Custom filters.
     * @protected
     */
    _getFilters: function(index) {
        var filterDate = new Date();
        filterDate.setDate(filterDate.getDate() - this.settings.get('filter'));
        var filterStr = app.date.format(filterDate, 'Y-m-d');

        var tab = this.tabs[index],
            filter = {},
            filters = [];

        filter[tab.filter_applied_to] = {$gte: filterStr};

        filters.push(filter);

        return filters;
    },

    /**
     * {@inheritDoc}
     *
     * New model related properties are injected into each model:
     *
     * - {String} picture_url Picture url for model's assigned user.
     */
    _renderHtml: function() {
        if (this.meta.config) {
            app.view.View.prototype._renderHtml.call(this);
            return;
        }

        _.each(this.collection.models, function(model) {
            var pictureUrl = app.api.buildFileURL({
                module: 'Users',
                id: model.get('assigned_user_id'),
                field: 'picture'
            });

            model.set('picture_url', pictureUrl);
        }, this);

        this._super("_renderHtml");
    },

    /**
     * {@inheritDoc}
     */
    _dispose: function() {
        this.$('.select2').select2('destroy');

        this._super("_dispose");
    },

    archiveEmail: function(event, params) {
        var self = this;
        app.drawer.open({
            layout: 'archive-email',
            context: {
                create: true,
                module: 'Emails',
                prepopulate: {
                    related: this.model
                }
            }
        }, function(model) {
            if (model) {
                self.context.trigger('panel-top:refresh', 'emails');
            }
        });
    }
})
