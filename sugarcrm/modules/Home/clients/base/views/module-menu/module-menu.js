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
     * The collection used to list dashboards on the dropdown.
     *
     * This is initialized on {@link #_initCollections}.
     *
     * @property
     * @type {Data.BeanCollection}
     */
    dashboards: null,

    /**
     * The collection used to list the recently viewed on the dropdown,
     * since it needs to use a {@link Data.MixedBeanCollection}
     *
     * This is initialized on {@link #_initCollections}.
     *
     * @property
     * @type {Data.MixedBeanCollection}
     */
    recentlyViewed: null,

    /**
     * Default settings used when none are supplied through metadata.
     *
     * Supported settings:
     * - {Number} dashboards Number of dashboards to show on the dashboards
     *   container. Pass 0 if you don't want to support dashboards listed here.
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
     *     'dashboards' => 10,
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
        dashboards: 20,
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

    /**
     * @inheritDoc
     *
     * Initializes the collections that will be used when the dropdown is
     * opened.
     *
     * Initializes Legacy dashboards.
     *
     * Sets the recently viewed toggle key to be ready to use when the dropdown
     * is opened.
     */
    initialize: function(options) {

        this._super('initialize', [options]);

        this._initCollections();
        this._initLegacyDashboards();

        this.meta.last_state = { id: 'recent' };
        this._recentToggleKey = app.user.lastState.key(this.TOGGLE_RECENTS_KEY, this);
    },

    /**
     * Creates the collections needed for list of dashboards and recently
     * viewed.
     *
     * The views' collection is pointing to the Home module and we might need
     * to use that later for something that could be populated from that
     * module. Therefore, we create other collections to be used for extra
     * information that exists on the Home dropdown menu.
     *
     * @chainable
     * @private
     */
    _initCollections: function() {

        this.dashboards = app.data.createBeanCollection('Dashboards');
        this.recentlyViewed = app.data.createMixedBeanCollection();

        return this;
    },

    /**
     * Sets the legacy dashboards link if it is configured to be enabled.
     *
     * We are not using the `hide_dashboard_bwc` form, because we don't provide
     * this feature by default and it is enabled only on upgrades from 6.x..
     * This will be removed in the future, when all dashlets are available in
     * 7.x..
     *
     * @chainable
     * @private
     */
    _initLegacyDashboards: function() {
        if (app.config.enableLegacyDashboards && app.config.enableLegacyDashboards === true) {
            this.dashboardBwcLink = app.bwc.buildRoute(this.module, null, 'bwc_dashboard');
        }
        return this;
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
     * @override
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

        this.dashboards.fetch({
            'limit': this._settings['dashboards'],
            'showAlerts': false,
            'success': _.bind(function(data) {

                var pattern = /^(LBL|TPL|NTC|MSG)_(_|[a-zA-Z0-9])*$/;

                _.each(data.models, function(model) {
                    if (pattern.test(model.get('name'))) {
                        model.set('name', app.lang.get(model.get('name'), model.module));
                    }
                    // hardcode the module to `Home` due to different link that
                    // we support
                    model.module = 'Home';
                });

                this._renderPartial('dashboards', {
                    collection: this.dashboards
                });

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

        var modules = this._getModulesForRecentlyViewed();
        if (_.isEmpty(modules)) {
            return;
        }

        this.recentlyViewed.fetch({
            'showAlerts': false,
            'fields': ['id', 'name'],
            'date': '-7 DAY',
            'limit': limit,
            'module_list': modules,
            'success': _.bind(function(data) {
                this._renderPartial('recently-viewed', {
                    collection: this.recentlyViewed,
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
     * Retrieve a list of modules where support for recently viewed records is
     * enabled and current user has access to list their records.
     *
     * Dashboards is discarded since it is already populated by default on the
     * drop down list {@link #populateMenu}.
     *
     * To disable recently viewed items for a specific module, please set the
     * `recently_viewed => 0` on:
     *
     * - `{custom/,}modules/{module}/clients/{platform}/view/module-menu/module-menu.php`
     *
     * @return {Array} List of supported modules names.
     * @private
     */
    _getModulesForRecentlyViewed: function() {
        // FIXME: we should find a better option instead of relying on visible
        // modules
        var modules = app.metadata.getModuleNames({filter: 'visible', access: 'list'});

        modules = _.filter(modules, function(module) {
            var view = app.metadata.getView(module, 'module-menu');
            return !view || !view.settings || view.settings.recently_viewed > 0;
        });

        return modules;
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

        options = options || {};

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
