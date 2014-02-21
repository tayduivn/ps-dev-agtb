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

    initialize: function(options) {

        this._super('initialize', [options]);

        // not using `hide_dashboard_bwc` form, because we shouldn't give this
        // feature by default - need confirmation from PMs.
        if (app.config.enableLegacyDashboards && app.config.enableLegacyDashboards === true) {
            this.dashboardBwcLink = app.bwc.buildRoute('Home', null, 'bwc_dashboard');
        }
    },

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

        this.populateRecentlyViewed(this._settings.recently_viewed);
    },

    /**
     * Populates all recently viewed records.
     *
     * @param {Number} limit The number of records to populate. Needs to be an
     *   integer `> 0`.
     */
    populateRecentlyViewed: function(limit) {

        if (limit <= 0) {
            return;
        }

        this.collection.fetch({
            'showAlerts': false,
            'fields': ['id', 'name'],
            'date': '-7 DAY',
            'limit': limit,
            'success': _.bind(this._renderPartial, this, 'recently-viewed'),
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
     * @param {String} tplName The template to use to render the partials.
     * @protected
     */
    _renderPartial: function(tplName) {

        if (this.disposed || !this.isOpen()) {
            return;
        }

        var tpl = app.template.getView(this.name + '.' + tplName, this.module) ||
            app.template.getView(this.name + '.' + tplName);

        var $placeholder = this.$('[data-container="' + tplName + '"]'),
            $old = $placeholder.nextUntil('.divider');

        $old.remove();
        $placeholder.after(tpl(this.collection));
    }
})
