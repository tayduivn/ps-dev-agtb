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
 * @class View.Views.BaseHomeModuleMenuView
 * @alias SUGAR.App.view.views.BaseHomeModuleMenuView
 * @extends View.Views.BaseModuleMenuView
 */
({
    extendsFrom: 'ModuleListView',

    events: {
        'click [data-toggle="recently-viewed"]': 'handleToggleRecentlyViewed'
    },

    /**
     * Default settings used when none are supplied through metadata.
     *
     * Supported settings:
     * - {Number} favorites Number of records to show on the favorites
     *   container. Pass 0 if you don't want to support favorites.
     * - {Number} recently_viewed Number of records to show on the recently
     *   viewed container. Pass 0 if you don't want to support recently viewed.
     * - {Number} recently_viewed_toggle Threshold of records to use for
     *   toggling the recently viewed container. Pass 0 if you don't want to
     *   support recently viewed.
     *
     * Example:
     * ```
     * // ...
     * 'settings' => array(
     *     'favorites' => 5,
     *     'recently_viewed' => 9,
     *     'recently_viewed_toggle' => 4,
     *     //...
     * ),
     * //...
     * ```
     *
     * @protected
     */
    _defaultSettings: {
        favorites: 3,
        recently_viewed: 10,
        recently_viewed_toggle: 3
    },

    /**
     * Key for storing the last state of the recently viewed toggle.
     *
     * @const
     * @type {String}
     */
    TOGGLE_RECENTS_KEY: 'more',

    /**
     * The lastState key to use in order to retrieve or save last recently
     * viewed toggle.
     */
    _recentToggleKey: null,

    initialize: function(options) {

        this._super('initialize', [options]);

        // not using `hide_dashboard_bwc` form, because we shouldn't give this
        // feature by default - need confirmation from PMs.
        if (app.config.enableLegacyDashboards && app.config.enableLegacyDashboards === true) {
            this.dashboardBwcLink = app.bwc.buildRoute('Home', null, 'bwc_dashboard');
        }

        this.meta.last_state = { id: 'recent' };
        this._recentToggleKey = app.user.lastState.key(this.TOGGLE_RECENTS_KEY, this);
    },

    /**
     * @inheritDoc
     *
     * Adds the title and the class for the Home module (Sugar cube).
     */
    _renderHtml: function() {
        this._super('_renderHtml');

        this.$el.attr('title', app.lang.get('LBL_TABGROUP_HOME', this.module));
        this.$el.addClass('home btn-group');
    },

    /**
     * @inheritDoc
     *
     * Populates all available dashboards when opening the menu. We override
     * this function without calling the parent one because we don't want to
     * reuse any of it.
     *
     * **Attention** We only populate up to 20 dashboards.
     *
     * TODO We need to keep changing the endpoint until SIDECAR-493 is
     * implemented.
     */
    populateMenu: function() {

        this.collection.fetch({
            'showAlerts': false,
            'success': _.bind(function(data) {

                var pattern = /^(LBL|TPL|NTC|MSG)_(_|[a-zA-Z0-9])*$/;

                _.each(data.models, function(model) {
                    if (pattern.test(model.get('name'))) {
                        model.set('name', app.lang.get(model.get('name'), model.module));
                    }
                });

                this._renderPartial('dashboards');

            }, this),
            'endpoint': function(method, model, options, callbacks) {
                app.api.records(method, 'Dashboards', model.attributes, options.params, callbacks);
            }
        });

        this.populateRecentlyViewed();
    },

    /**
     * Populates recently viewed records with a limit based on last state key.
     *
     * Based on the state it will read 2 different metadata properties:
     *
     * - `recently_viewed_toggle` for the value to start toggling
     * - `recently_viewed` for maximum records to retrieve
     *
     * Defaults to `recently_viewed_toggle` if no state is defined.
     *
     * @param {String} state Populates recently viewed based on the state.
     */
    populateRecentlyViewed: function() {

        var visible = app.user.lastState.get(this._recentToggleKey),
            threshold = this._settings['recently_viewed_toggle'],
            limit = this._settings[visible ? 'recently_viewed' : 'recently_viewed_toggle'];

        if (limit <= 0) {
            return;
        }

        this.collection.fetch({
            'showAlerts': false,
            'fields': ['id', 'name'],
            'date': '-7 DAY',
            'limit': limit,
            'success': _.bind(function(data) {
                this._renderPartial('recently-viewed', {
                    open: !visible,
                    showRecentToggle: data.models.length > threshold || data.next_offset !== -1
                });
            }, this),
            'endpoint': function(method, model, options, callbacks) {
                var url = app.api.buildURL('recent', 'read', options.attributes, options.params);
                app.api.call(method, url, null, callbacks, options.params);
            }
        });

        return;
    },

    /**
     * Renders the data in the partial template given.
     *
     * The partial template can receive more data from the options parameter.
     *
     * @param {String} tplName The template to use to render the partials.
     * @param {Object} options Other optional data to pass to the template.
     * @protected
     */
    _renderPartial: function(tplName, options) {

        if (this.disposed || !this.isOpen()) {
            return;
        }

        var tpl = app.template.getView(this.name + '.' + tplName, this.module) ||
            app.template.getView(this.name + '.' + tplName);

        var $placeholder = this.$('[data-container="' + tplName + '"]'),
            $old = $placeholder.nextUntil('.divider');

        $old.remove();
        $placeholder.after(tpl(_.extend({
            'collection': this.collection
        }, options)));
    },

    /**
     * Handles the toggle of the more recently viewed mixed records.
     *
     * This triggers a refresh on the data to be retrieved based on the amount
     * defined in metadata for the given state. This way we limit the amount of
     * data to be retrieve to the current state and not getting always the
     * maximum.
     *
     * @param {Event} event The click event that triggered the toggle.
     */
    handleToggleRecentlyViewed: function(event) {
        app.user.lastState.set(this._recentToggleKey, Number(!app.user.lastState.get(this._recentToggleKey)));
        this.populateRecentlyViewed();
        event.stopPropagation();
    }
})
