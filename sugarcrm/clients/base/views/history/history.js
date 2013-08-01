/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
/**
 * History dashlet is composed by a highly configurable set of tabs, each new
 * tab is created based on metadata under a tabs array, where the following
 * properties can be defined:
 *
 * - {Boolean} active If specific tab should be active by default.
 * - {String} filter_applied_to Date field to be used on date
 *    switcher, defaults to date_entered.
 * - {Array} filters Array of filters to be applied.
 * - {Array} labels Array of labels to be applied when
 *   LBL_MODULE_NAME_SINGULAR and LBL_MODULE_NAME aren't available or
 *   there's a need to use custom labels.
 * - {String} link Relationship link to be used while creating new
 *   records who should be associated with the record currently being
 *   viewed.
 * - {String} module Module from which the records are retrieved.
 * - {String} order_by Sort records by field.
 * - {String} record_date Date field to be used to print record
 *   date, defaults to date_entered, though it can be overridden on
 *   metadata.
 *
 * Example:
 * <pre><code>
 * // ...
 * 'tabs' => array(
 *     array(
 *         'filter_applied_to' => 'date_entered',
 *         'filters' => array(
 *             'type' => array('$equals' => 'out'),
 *         ),
 *         'labels' => array(
 *             'singular' => 'LBL_HISTORY_DASHLET_EMAIL_OUTBOUND_SINGULAR',
 *             'plural' => 'LBL_HISTORY_DASHLET_EMAIL_OUTBOUND_PLURAL',
 *         ),
 *         'link' => 'emails',
 *         'module' => 'Emails',
 *         'order_by' => 'date_entered:desc',
 *     ),
 *     //...
 * ),
 * //...
 * </code></pre>
 *
 * @class View.Views.HistoryView
 */
({
    plugins: ['Dashlet', 'timeago'],

    events: {
        'click [data-action=tab-switcher]': 'tabSwitcher',
        'click [data-action=visibility-switcher]': 'visibilitySwitcher',
        'click [data-action=show-more]': 'showMore'
    },

    /**
     * Default options used:
     *
     * - {Integer} filter Default value for filter switcher, supported
     *   values are: '7', '30' and '90' days.
     * - {Integer} limit Default limit imposed to the number of records
     *   retrieved per request.
     * - {String} visibility Default value for visibility switcher,
     *   supported values are: 'user' and 'group'.
     * @protected
     */
    _defaultOptions: {
        filter: 7,
        limit: 5,
        visibility: 'user'
    },

    /**
     * {@inheritDoc}
     */
    initDashlet: function() {
        if (this.meta.config) {
            return;
        }

        this.collection = null;

        this._initOptions();
        this._initTabs();

        this.settings.on('change:filter', this.loadData, this);
    },

    /**
     * Initialize options by storing default options on dashlet settings.
     *
     * @return {View.Views.HistoryView} Instance of this view.
     * @protected
     */
    _initOptions: function() {
        var settings = _.extend({}, this._defaultOptions, this.settings.attributes);
        this.settings.set(settings);
        return this;
    },

    /**
     * Initialize tabs.
     *
     * @return {View.Views.HistoryView} Instance of this view.
     * @protected
     */
    _initTabs: function() {
        this.tabs = [];

        _.each(this.dashletConfig.tabs, function(tab, index) {
            if (tab.active) {
                this.settings.set('activeTab', index);
            }

            var collection = this._createCollection(tab);
            if (_.isNull(collection)) {
                return;
            }

            this.tabs[index] = tab;
            this.tabs[index].collection = collection;
        }, this);
    },

    /**
     * Create collection based on tab properties and current context,
     * furthermore if supplied tab has a valid 'link' property a related
     * collection will be created instead.
     *
     * @param {Object} tab Tab properties.
     * @return {Data.BeanCollection} A new instance of bean collection or null
     *   if we cannot access module metadata.
     * @protected
     */
    _createCollection: function(tab) {
        var collection,
            meta = app.metadata.getModule(this.module);

        if (_.isUndefined(meta)) {
            return null;
        }

        if (meta.fields[tab.link] && meta.fields[tab.link].type === 'link') {
            collection = app.data.createRelatedCollection(this.model, tab.link);
        } else {
            collection = app.data.createBeanCollection(tab.module);
        }

        return collection;
    },

    /**
     * Retrieves collection options for a specific tab.
     *
     * @param {Integer} index Tab index.
     * @return {Object} Collection options.
     * @protected
     */
    _getCollectionOptions: function(index) {
        var tab = this.tabs[index],
            options = {
                limit: this.settings.get('limit'),
                myItems: this.settings.get('visibility') === 'user',
                offset: 0,
                params: {
                    order_by: tab.order_by || null
                }
            };

        return options;
    },

    /**
     * Retrieves collection filters for a specific tab.
     *
     * @param {Integer} index Tab index.
     * @return {Array} Collection filters.
     * @protected
     */
    _getCollectionFilters: function(index) {
        var tab = this.tabs[index],
            filters = [];

        _.each(tab.filters, function(condition, field) {
            var filter = {};
            filter[field] = condition;

            filters.push(filter);
        });

        return filters;
    },

    /**
     * Retrieves dashlet filters.
     *
     * @param {Integer} index Tab index.
     * @return {Array} Dashlet filters.
     * @protected
     */
    _getFilters: function(index) {
        var tab = this.tabs[index],
            filter = {},
            filterDate = new Date(),
            filters = [];

        filterDate.setDate(filterDate.getDate() - this.settings.get('filter'));

        filter[tab.filter_applied_to || 'date_entered'] = {
            $gte: app.date.format(filterDate, 'Y-m-d H:i:s')
        };

        filters.push(filter);

        return filters;
    },

    /**
     * Fetch data for view tabs based on selected options and filters.
     *
     * @param {Object} options Options that are passed to collection/model's
     *   fetch method.
     */
    loadData: function(options) {
        options = options || {};

        if (this.disposed || this.meta.config) {
            return;
        }

        var self = this,
            totalFetches = 0;

        _.each(this.tabs, function(tab, index) {
            tab.collection.options = this._getCollectionOptions(index);
            tab.collection.filterDef = _.union(
                this._getCollectionFilters(index),
                this._getFilters(index)
            );

            tab.collection.fetch({
                complete: function() {
                    totalFetches++;

                    if (self.disposed || totalFetches < _.size(self.tabs)) {
                        return;
                    }

                    self.dataFetched = true;

                    self.collection = self.tabs[self.settings.get('activeTab')].collection;
                    self.render();

                    if (_.isFunction(options.complete)) {
                        options.complete.call(this);
                    }
                }
            });
        }, this);
    },

    /**
     * Event handler for visibility switcher.
     *
     * @param {Event} event Click event.
     */
    visibilitySwitcher: function(event) {
        var visibility = this.$(event.currentTarget).val();
        if (visibility === this.settings.get('visibility')) {
            return;
        }

        this.settings.set('visibility', visibility);
        this.layout.loadData();
    },

    /**
     * Show more records for current collection.
     */
    showMore: function() {
        var self = this;

        this.collection.paginate({
            limit: this.settings.get('limit'),
            add: true,
            success: function() {
                if (!self.disposed) {
                    self.render();
                }
            }
        });
    },

    /**
     * Event handler for tab switcher.
     *
     * @param {Event} event Click event.
     */
    tabSwitcher: function(event) {
        var index = this.$(event.currentTarget).data('index');
        if (index === this.settings.get('activeTab')) {
            return;
        }

        this.settings.set('activeTab', index);
        this.collection = this.tabs[index].collection;
        this.render();
    },

    /**
     * Create new record.
     *
     * @param {String} module Module name.
     * @param {Object} options Options used for new record creation:
     *
     * - {String} link Relationship link.
     * - {String} layout Layout name.
     *
     * @protected
     */
    _createRecord: function(module, options) {
        options = options || {};

        // FIXME: At the moment there are modules marked as bwc enabled though
        // they have sidecar support already, so they're treated as exceptions
        // and drawers are used instead.
        var bwcExceptions = ['Emails'],
            meta = app.metadata.getModule(module) || {};

        if (meta.isBwcEnabled && !_.contains(bwcExceptions, module)) {
            this._createBwcRecord(module, options.link);
            return;
        }

        this._openCreateDrawer(module, options.layout);
    },

    /**
     * Create new record.
     *
     * If we're on Homepage an orphan record is created, otherwise, the link
     * parameter is used and the new record is associated with the record
     * currently being viewed.
     *
     * @param {String} module Module name.
     * @param {String} link Relationship link.
     * @protected
     */
    _createBwcRecord: function(module, link) {
        if (this.module !== 'Home') {
            app.bwc.createRelatedRecord(module, this.model, link);
            return;
        }

        var params = {
            return_module: this.module,
            return_id: this.model.id
        };

        var route = app.bwc.buildRoute(module, null, 'EditView', params);

        app.router.navigate(route, {trigger: true});
    },

    /**
     * Opens create record drawer.
     *
     * @param {String} module Module name.
     * @param {String} layout Layout name, defaults to 'create-actions' if none
     *   supplied.
     * @protected
     */
    _openCreateDrawer: function(module, layout) {
        layout = layout || 'create-actions'
        app.drawer.open({
            layout: layout,
            context: {
                create: true,
                module: module,
                prepopulate: {
                    related: this.model
                }
            }
        });
    },

    /**
     * Create new meeting.
     */
    createMeeting: function() {
        this._createRecord('Meetings', {link: 'meetings'});
    },

    /**
     * Create new email.
     */
    createEmail: function() {
        this._createRecord('Emails', {link: 'emails', layout: 'compose'});
    },

    /**
     * Create new call.
     */
    createCall: function() {
        this._createRecord('Calls', {link: 'calls'});
    },

    /**
     * {@inheritDoc}
     *
     * If default collection is available, new model related properties are
     * injected into each model.
     *
     * - {String} picture_url Picture url for model's assigned user.
     * - {String} record_date Date field to be used to print record
     *   date, defaults to date_entered, though it can be overridden on
     *   metadata.
     */
    _renderHtml: function() {
        if (!_.isObject(this.collection) || _.isUndefined(this.tabs)) {
            app.view.View.prototype._renderHtml.call(this);
            return;
        }

        var index = this.settings.get('activeTab'),
            recordDate = this.tabs[index].record_date || 'date_entered';

        _.each(this.collection.models, function(model) {
            var pictureUrl = app.api.buildFileURL({
                module: 'Users',
                id: model.get('assigned_user_id'),
                field: 'picture'
            });

            model.set('picture_url', pictureUrl);
            model.set('record_date', model.get(recordDate));
        }, this);

        app.view.View.prototype._renderHtml.call(this);
    },

    /**
     * {@inheritDoc}
     */
    _dispose: function() {
        if (this.collection) {
            this.collection.off(null, null, this);
        }

        _.each(this.tabs, function(tab) {
            tab.collection.off(null, null, this);
        });

        this.$('.select2').select2('destroy');

        app.view.View.prototype._dispose.call(this);
    }
})
