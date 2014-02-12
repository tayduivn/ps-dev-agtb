/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/**
 * @class BaseDashboardLayout
 * @extends app.view.Layout
 *
 * The outer layout of the dashboard.
 * This layout contains the header view and wraps the daslet-main layout.
 * The layouts for each dashboard are stored in the dashboard endpoint (rest/v10/Dashboards/{id})
 *
 */
({
    toggled: false,
    className: 'row-fluid',
    dashboardLayouts: {
        'record': 'record-dashboard',
        'records': 'list-dashboard'
    },
    events: {
        'click [data-action=create]': 'createClicked'
    },
    error: {
        //Dashboard is a special case where a 404 here shouldn't break the page,
        //it should just send us back to the default homepage
        handleNotFoundError : function(error) {
            app.router.redirect("#Home");
            //Prevent the default error handler
            return false;
        },
        handleValidationError : function(error) {
            return false;
        }
    },

    /**
     * {@inheritdoc}
     */
    initialize: function (options) {
        var context = options.context,
            module = context.parent && context.parent.get('module') || context.get("module");

        if (options.meta && options.meta.method && options.meta.method === 'record' && !context.get("modelId")) {
            context.set("create", true);
        }

        var model = this._getNewDashboardObject('model', context);
        if (context.get("modelId")) {
            model.set("id", context.get("modelId"), {silent: true});
        }
        context.set({
            model: model,
            collection: this._getNewDashboardObject('collection', context)
        });

        this._super('initialize', [options]);

        this.model.on("setMode", function (mode) {
            if (mode === "edit" || mode === "create") {
                this.$(".dashboard").addClass("edit");
            } else {
                this.$(".dashboard").removeClass("edit");
            }
        }, this);

        if (module === 'Home') {
            this.on("render", this.toggleSidebar, this);
            if (context.get('modelId')) {
                // save it as last visit
                var lastVisitedStateKey = this.getLastStateKey();
                app.user.lastState.set(lastVisitedStateKey, context.get('modelId'));
            }
        }
    },

    /**
     * {@inheritdoc}
     */
    loadData: function (options, setFields) {
        if (this.context.parent && !this.context.parent.isDataFetched()) {
            var parent = this.context.parent.get("modelId") ? this.context.parent.get("model") : this.context.parent.get("collection");

            parent.once("sync", function () {
                this._super('loadData', [options, setFields]);
            }, this);
        } else {
            this._super('loadData', [options, setFields]);
        }
    },

    /**
     * Checks if the sidebar is toggled, if not this function toggles it
     */
    toggleSidebar: function () {
        if (!this.toggled) {
            app.controller.context.trigger('toggleSidebar');
            this.toggled = true;
        }
    },

    /**
     * Navigate to the create layout when create button is clicked.
     *
     * @param {Event} evt Mouse event.
     */
    createClicked: function(evt) {
        if (this.model.dashboardModule === 'Home') {
            var route = app.router.buildRoute(this.module, null, 'create');
            app.router.navigate(route, {trigger: true});
        } else {
            this.navigateLayout('create');
        }
    },

    /**
     * Places only components that include the Dashlet plugin and places them in the "main-pane" div of
     * the dashlet layout.
     * @param component {app.view.Component}
     * @private
     */
    _placeComponent: function (component) {
        var dashboardEl = this.$("[data-dashboard]"),
            css = this.context.get("create") ? " edit" : "";
        if (dashboardEl.length === 0) {
            dashboardEl = $("<div></div>").attr({
                'class': 'cols row-fluid'
            });
            this.$el.append(
                $("<div></div>")
                    .addClass('dashboard' + css)
                    .attr({'data-dashboard': 'true'})
                    .append(dashboardEl)
            );
        } else {
            dashboardEl = dashboardEl.children(".row-fluid");
        }
        dashboardEl.append(component.el);
    },

    /**
     * If current context doesn't contain dashboard model id,
     * it will trigger set default dashboard to create default metadata
     */
    bindDataChange: function () {
        var modelId = this.context.get("modelId");
        if (!(modelId && this.context.get("create")) && this.collection) {
            this.collection.on("reset", this.setDefaultDashboard, this);
        }
    },

    /**
     * Build the default dashboard metadata only if dashboards are empty.
     *
     * Default dashboard metadata are stored in the following layout metadata
     * <pre>
     * listview - list-dashboard
     * recordview - record-dashboard
     * </pre>
     * If the default dashboard is not assigned,
     * the layout will render dashboard-empty template.
     */
    setDefaultDashboard: function() {
        if (this.disposed) {
            return;
        }
        var lastVisitedStateKey = this.getLastStateKey(),
            lastViewed = app.user.lastState.get(lastVisitedStateKey),
            hasHelpOnly = (this.collection.models.length == 1
                && _.first(this.collection.models).get('dashboard_type') == 'help-dashboard'),
            helpLastShown = (hasHelpOnly && lastViewed === _.first(this.collection.models).get('id'));

        if(hasHelpOnly && !helpLastShown) {
            // If the collection contains exactly one model that is a help dashboard,
            // and the user saw the help dashboard last and chose to hide it, show the empty template
            this._renderEmptyTemplate();
        } else if (this.collection.models.length > 0) {
            var model = _.first(this.collection.models),
                lastVisitedStateKey = this.getLastStateKey(),
                lastViewed = app.user.lastState.get(lastVisitedStateKey),
                currentModule = this.context.get('module');
            if (lastViewed) {
                var lastVisitedModel = this.collection.get(lastViewed);
                //if last visited dashboard not in the fetching list,
                //it should navigate to the first searched dashboard.
                //And it should clean out the previous last visited dashboard,
                //since it is no longer existed.
                if (!_.isEmpty(lastVisitedModel)) {
                    app.user.lastState.set(lastVisitedStateKey, '');
                    model = lastVisitedModel;
                }
            }
            if (this.context.parent) {
                //For other modules
                this.navigateLayout(model.id);
            } else {
                //SC-748: Should dispose the dashboard to release the warning listener
                this.dispose();
                // check to see if the legacy dashboard was the last one visited if we are on the Home module
                if (currentModule == 'Home' && _.isString(lastViewed) && lastViewed.indexOf('bwc_dashboard') !== -1) {
                    app.router.navigate(lastViewed, {trigger: true});
                } else {
                    app.navigate(this.context, model);
                }
            }
        } else {
            var _initDashboard = this._getInitialDashboardMetadata();

            _.each(_initDashboard, function(dash) {
                var model = this._getNewDashboardObject('model', this.context);
                model.set(dash);
                if (this.context.get("modelId")) {
                    model.set("id", this.context.get("modelId"), {silent: true});
                }
                model.save({}, this._getDashboardModelSaveParams());
            }, this);
        }
    },

    /**
     * Gets initial dashboard metadata and adds help dashboard if it doesnt exist
     *
     * @return {Array} an array of dashboard metadata
     * @private
     */
    _getInitialDashboardMetadata: function() {
        var layoutName = this.dashboardLayouts[this.context.parent && this.context.parent.get('layout') || 'record'],
            initDash = app.metadata.getLayout(this.model.dashboardModule, layoutName) || {};

        // check to make sure this module has initial dashboards assigned for this view
        if (!_.isEmpty(initDash)) {
            // make sure there's a dashboard_type of "dashboard" by default
            // unless there's a specific custom dashboard_type already defined
            initDash.dashboard_type = initDash.dashboard_type || 'dashboard';
        }

        return this.addHelpDashboardMetadata(initDash);
    },

    /**
     * Adds the help-dashboard metadata to a metadata Object
     *
     * @param {Object} _initDashboard The default dashboard for a module
     */
    addHelpDashboardMetadata: function(_initDashboard) {
        var _helpDB = app.metadata.getLayout(this.model.dashboardModule, 'help-dashboard');
        if(!_.isEmpty(_initDashboard)) {
            _initDashboard = [_helpDB, _initDashboard];
        } else {
            _initDashboard = [_helpDB];
        }
        return _initDashboard;
    },

    /**
     * Build the cache key for last visited dashboard
     * Combine parent module and view name to build the unique id
     *
     * @return {String} hash key.
     */
    getLastStateKey: function() {
        if (this._lastStateKey) {
            return this._lastStateKey;
        }
        var model = this.context.get('model'),
            view = model.get('view_name'),
            module = model.dashboardModule,
            key = module + '.' + view;
        this._lastStateKey = app.user.lastState.key(key, this);
        return this._lastStateKey;
    },

    /**
     * For the RHS dashboard, this method loads entire dashboard component and adds the
     * <pre><code>dashboard_type</code></pre> member to the context of the dashboard.
     * <pre><code>dashboard_type</code></pre> gets used in dashletselect to filter dashlets
     *
     * @param id {String} - dashboard id
     */
    navigateLayout:function (id) {
        var layout = this.layout,
            lastVisitedStateKey = this.getLastStateKey();
        this.dispose();

        //if dashboard layout navigates to the different dashboard,
        //it should store last visited dashboard id.
        if (!_.contains(['create', 'list'], id)) {
            app.user.lastState.set(lastVisitedStateKey, id);
        }

        // add dashboard type to context variables,
        // can only create dashboards with dashboard_type of 'dashboard'
        var ctxVars = { dashboard_type: 'dashboard' };
        if (id === 'create') {
            ctxVars.create = true;
        } else if (id !== 'list') {
            ctxVars.modelId = id;
        }

        layout._addComponentsFromDef([
            {
                layout: {
                    type: 'dashboard',
                    components: (id === 'list') ? [] : [
                        {
                            view: 'dashboard-headerpane'
                        },
                        {
                            layout: 'dashlet-main'
                        }
                    ],
                    last_state: {
                        id: 'last-visit'
                    }
                },
                context: _.extend({
                    module: 'Home',
                    forceNew: true
                }, ctxVars)
            }
        ]);
        layout.removeComponent(0);
        layout.loadData({}, false);
        layout.render();
    },

    /**
     * {@inheritdoc}
     */
    unbindData: function() {
        var model, collection;
        this.off("render", this.toggleSidebar, this);
        if (this.collection) {
            this.collection.off("reset", this.setDefaultDashboard, this);
        }
        if (this.context.parent) {
            model = this.context.parent.get("model");
            collection = this.context.parent.get("collection");

            if (model) {
                model.off("sync", null, this);
            }
            if (collection) {
                collection.off("sync", null, this);
            }
        }

        this._super('unbindData');
    },

    /**
     * Returns a Dashboard Model or Dashboard Collection based on modelOrCollection
     *
     * @param {String} modelOrCollection The return type, 'model' or 'collection'
     * @param context
     * @return {Bean|BeanCollection}
     * @private
     */
    _getNewDashboardObject: function(modelOrCollection, context) {
        var obj,
            ctx = context && context.parent || context,
            module = ctx.get("module") || context.get("module"),
            layoutName = ctx.get("layout") || '',
            sync = function (method, model, options) {
                options = app.data.parseOptionsForSync(method, model, options);
                var callbacks = app.data.getSyncCallbacks(method, model, options),
                    path = (this.dashboardModule === 'Home' || model.id) ? this.apiModule : this.apiModule + '/' + this.dashboardModule;
                if (method === 'read') {
                    options.params.view_name = layoutName;
                }
                app.api.records(method, path, model.attributes, options.params, callbacks);
            };

        if (module === 'Home') {
            layoutName = '';
        }
        switch(modelOrCollection) {
            case 'model':
                obj = this._getNewDashboardModel(module, layoutName, sync);
                break;

            case 'collection':
                obj = this._getNewDashboardCollection(module, layoutName, sync);
                break;
        }

        return obj;
    },

    /**
     * Returns a new Dashboard Bean with proper view_name and sync function set
     *
     * @param {String} module The name of the module we're in
     * @param {String} layoutName The name of the layout
     * @param {Function} syncFn The sync function to use
     * @param {Boolean} getNew If you want a new instance or just the the Dashboard definition (optional)
     * @return {Dashboard} a new Dashboard Bean
     * @private
     */
    _getNewDashboardModel: function(module, layoutName, syncFn, getNew) {
        getNew = (_.isUndefined(getNew)) ? true : getNew;
        var Dashboard = app.Bean.extend({
            sync: syncFn,
            apiModule: 'Dashboards',
            module: 'Home',
            dashboardModule: module,
            maxColumns: (module === 'Home') ? 3 : 1,
            minColumnSpanSize: (module === 'Home') ? 4 : 12,
            defaults: {
                view_name: layoutName
            }
        });
        return (getNew) ? new Dashboard() : Dashboard;
    },

    /**
     * Returns a new DashboardCollection with proper view_name and sync function set
     *
     * @param {String} module The name of the module we're in
     * @param {String} layoutName The name of the layout
     * @param {Function} syncFn The sync function to use
     * @param {Boolean} getNew If you want a new instance or just the the DashboardCollection definition (optional)
     * @return {DashboardCollection} A new Dashboard BeanCollection
     * @private
     */
    _getNewDashboardCollection: function(module, layoutName, syncFn, getNew) {
        getNew = (_.isUndefined(getNew)) ? true : getNew;
        var Dashboard = this._getNewDashboardModel(module, layoutName, syncFn, false),
            DashboardCollection = app.BeanCollection.extend({
                sync: syncFn,
                apiModule: 'Dashboards',
                module: 'Home',
                dashboardModule: module,
                model: Dashboard
            });
        return (getNew) ? new DashboardCollection() : DashboardCollection;
    },

    /**
     * Collects params for Dashboard model save
     *
     * @return {Object} The dashboard model params to pass to its save function
     * @private
     */
    _getDashboardModelSaveParams: function() {
        var params = {
            silent: true,
            //Don't show alerts for this request
            showAlerts: false
        };

        params.error = _.bind(this._renderEmptyTemplate, this);

        if (this.context.parent) {
            params.success = _.bind(function(model) {
                if (!this.disposed && model.get('dashboard_type') === 'help-dashboard') {
                    this.navigateLayout(model.id);
                }
            }, this);
        } else {
            params.success = _.bind(function(model) {
                if (!this.disposed && model.get('dashboard_type') === 'help-dashboard') {
                    app.navigate(this.context, model);
                }
            }, this);
        }
        return params;
    },

    /**
     * Gets the empty dashboard layout template
     * and renders it to <pre><code>this.$el</code></pre>
     *
     * @private
     */
    _renderEmptyTemplate: function() {
        var template = app.template.getLayout(this.type + '.dashboard-empty');
        this.$el.html(template(this));
    },

    /**
     * {@inheritdoc}
     */
    _dispose: function () {
        this.dashboardLayouts = null;
        this._super('_dispose');
    }
})
