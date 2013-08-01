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
 * Planned Activities dashlet is composed by a highly configurable set of tabs.
 *
 * @class View.Views.PlannedActivitiesView
 * @extends View.Views.HistoryView
 * @inheritdoc
 */
({
    extendsFrom: 'HistoryView',

    events: {
        'click [data-action=date-switcher]': 'dateSwitcher',
        'click [data-action=tab-switcher]': 'tabSwitcher',
        'click [data-action=visibility-switcher]': 'visibilitySwitcher',
        'click [data-action=show-more]': 'showMore'
    },

    /**
     * Default options used:
     *
     * - {String} date Default value for date switcher, supported
     *   values are: 'today' and 'future'.
     * - {Integer} limit Default limit imposed to the number of records
     *   retrieved per request.
     * - {String} visibility Default value for visibility switcher,
     *   supported values are: 'user' and 'group'.
     * @protected
     */
    _defaultOptions: {
        date: 'today',
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
            today = app.date.format(new Date(), 'Y-m-d'),
            filter = {},
            filters = [],
            defaultFilters = {
                today: {$lte: today + ' 23:59:59'},
                future: {$gt: today + ' 23:59:59'}
            };

        filter[tab.filter_applied_to || 'date_entered'] = defaultFilters[this.settings.get('date')];

        filters.push(filter);

        return filters;
    },

    /**
     * Event handler for date switcher.
     *
     * @param {Event} event Click event.
     */
    dateSwitcher: function(event) {
        var date = this.$(event.currentTarget).val();
        if (date === this.settings.get('date')) {
            return;
        }

        this.settings.set('date', date);
        this.layout.loadData();
    },

    /**
     * {@inheritDoc}
     *
     * If default collection is available, new model related properties are
     * injected into each model:
     *
     * - {Boolean} overdue True if record is prior to now.
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
            recordDate = this.tabs[index].record_date || 'date_entered',
            now = new Date();

        _.each(this.collection.models, function(model) {
            var date = new Date(model.get(recordDate)),
                pictureUrl = app.api.buildFileURL({
                    module: 'Employees',
                    id: model.get('assigned_user_id'),
                    field: 'picture'
                });

            model.set('picture_url', pictureUrl);
            model.set('record_date', date);
            model.set('overdue', date < now);
        }, this);

        app.view.View.prototype._renderHtml.call(this);
    }
})
