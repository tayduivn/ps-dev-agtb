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
 * Planned Activities dashlet takes advantage of the tabbed dashlet abstraction
 * by using its metadata driven capabilities to configure its tabs in order to
 * display planned activities of specific modules.
 *
 * @class View.Views.BasePlannedActivitiesView
 * @alias SUGAR.App.view.views.BasePlannedActivitiesView
 * @extends View.Views.BaseHistoryView
 * @inheritdoc
 */
({
    extendsFrom: 'HistoryView',

    /**
     * {@inheritDoc}
     */
    _initEvents: function() {
        this.events = _.extend(this.events, {
            'click [data-action=date-switcher]': 'dateSwitcher'
        });

        return this;
    },

    /**
     * {@inheritDoc}
     */
    _getRecordsTemplate: function(module) {
        this._recordsTpl = this._recordsTpl || {};

        if (!this._recordsTpl[module]) {
            this._recordsTpl[module] = app.template.getView(this.name + '.records', module) ||
                app.template.getView(this.name + '.records', this.module) ||
                app.template.getView(this.name + '.records') ||
                app.template.getView('history.records', this.module) ||
                app.template.getView('history.records') ||
                app.template.getView('tabbed-dashlet.records', this.module) ||
                app.template.getView('tabbed-dashlet.records');
        }

        return this._recordsTpl[module];
    },

    /**
     * {@inheritDoc}
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

        filter[tab.filter_applied_to] = defaultFilters[this.settings.get('date')];

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
     * New model related properties are injected into each model:
     *
     * - {Boolean} overdue True if record is prior to now.
     */
    _renderHtml: function() {
        if (this.meta.config) {
            app.view.View.prototype._renderHtml.call(this);
            return;
        };

        var tab = this.tabs[this.settings.get('activeTab')],
            now = new Date();

        _.each(this.collection.models, function(model) {
            var date = new Date(model.get(tab.record_date));

            model.set('overdue', date < now);
        }, this);

        app.view.invokeParent(this, {
            type: 'view',
            name: 'history',
            method: '_renderHtml',
            platform: 'base'
        });

    }
})
