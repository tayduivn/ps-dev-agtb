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
 *
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
     * Populates recently created dashboards on open menu. We override this
     * function without calling the parent one because we don't can't reuse any
     * of it.
     *
     * TODO We need to create the custom Bean and Collection until SIDECAR-493
     * is ready and merged.
     */
    populateMenu: function() {
        var sync, Dashboard, DashboardCollection, dashCollection;

        sync = function(method, model, options) {
            options = app.data.parseOptionsForSync(method, model, options);
            var callbacks = app.data.getSyncCallbacks(method, model, options);
            app.api.records(method, this.apiModule, model.attributes, options.params, callbacks);
        };

        Dashboard = app.Bean.extend({
            sync: sync,
            apiModule: 'Dashboards',
            module: 'Home'
        }),
        DashboardCollection = app.BeanCollection.extend({
            sync: sync,
            apiModule: 'Dashboards',
            module: 'Home',
            model: Dashboard
        });

        dashCollection = new DashboardCollection();
        dashCollection.fetch({
            //Don't show alerts for this request
            showAlerts: false,
            success: _.bind(function(data) {

                var pattern = /^(LBL|TPL|NTC|MSG)_(_|[a-zA-Z0-9])*$/;

                _.each(dashCollection.models, function(model) {
                    if (pattern.test(model.get('name'))) {
                        model.set('name', app.lang.get(model.get('name'), dashCollection.module));
                    }
                });

                var tpl = app.template.getView(this.name + '.dashboards', this.module);
                var $placeholder = this.$('[data-container="dashboards"]'),
                    $old = $placeholder.nextUntil('.divider');

                $old.remove();
                $placeholder.after(tpl(dashCollection));

            }, this)
        });
    }
})
