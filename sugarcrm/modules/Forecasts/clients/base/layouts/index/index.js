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
({
    /**
     * Layout that places views in columns with each view in a column
     * @class View.Layouts.ForecastsLayout
     * @alias SUGAR.App.layout.ForecastsLayout
     * @extends View.Layout
     */

        /**
         * Holds the metadata for each of the components used in forecasts
         */
        componentsMeta: {},

        /**
         * Stores the initial data models coming from view.sidecar.php
         * todo: use this to populate models that we already have data for; currently only holds filters, chartoptions, & user
         *
         */
        initDataModel: {},

        initialize: function(options) {

            this.componentsMeta = options.meta.components;

            options.context = _.extend(options.context, this.initializeAllModels());

            // Initialize the config model
            var ConfigModel = Backbone.Model.extend({
                url: app.api.buildURL("Forecasts", "config"),
                sync: function(method, model, options) {
                    var url = _.isFunction(model.url) ? model.url() : model.url;
                    return app.api.call(method, url, model, options);
                },
                // include metadata from config into the config model by default
                defaults: app.metadata.getModule('Forecasts').config
            });
            options.context.forecasts.config = new ConfigModel();

            var defaultSelections = app.defaultSelections;

            // Set initial selected data on the context
            options.context.forecasts.set({
                selectedTimePeriod : defaultSelections.timeperiod_id,
                selectedRanges: defaultSelections.ranges,
                selectedGroupBy : defaultSelections.group_by,
                selectedDataSet: defaultSelections.dataset,

                /**
                 * Initially set to the currently logged-in user, selectedUser is different from currentUser
                 * because selectedUser is used by other components and is changeable by most components
                 * (e.g. selecting a different user via the hierarchy tree or clicking in the worksheet)
                 */
                selectedUser : defaultSelections.selectedUser,

                /**
                 * boolean to reload the active worksheet
                 */
                reloadWorksheetFlag: false,
                
                /**
                 * The active worksheet
                 */
                currentWorksheet: "",
                

                /**
                 * used across Forecasts to contain sales rep worksheet totals
                 */
                updatedTotals : {},

                /**
                 * used across Forecasts to contain manager worksheet totals
                 */
                updatedManagerTotals : {},

                /**
                 * todo-sfa keep track of changes to modal.js and when they have proper events being passed
                 * we can do away with this
                 *
                 * set by forecastsConfigTabbedButtons.js when the saved button is clicked so that it's callback
                 * can check this variable to know which button was clicked
                 */
                saveClicked : false,

                // todo: the following three booleans need to be refactored out and made into events, not flags/booleans
                /**
                 * boolean to use across components to enable commit button or not
                 */
                reloadCommitButton : false,       

                /**
                 * forecastsCommitButtons triggers this flag to tell forecastsCommitted to call commitForecast()
                 */
                commitForecastFlag : false,

                /**
                 * hiddenSidebar: Is the sidebar hidden or not.
                 */
                hiddenSidebar : false

            });

            // grab a copy of the init data for forecasts to use
            this.initDataModel = app.initData;

            // then get rid of the data from app
            app.initData = null;

            app.view.Layout.prototype.initialize.call(this, options);
        },

        /**
         * Fetches data for layout's model or collection.
         *
         * The default implementation first calls the {@link Core.Context#loadData} method for the layout's context
         * and then iterates through the components and calls their {@link View.Component#loadData} method.
         * This method sets context's `fields` property beforehand.
         *
         * Override this method to provide custom fetch algorithm.
         */
        loadData: function() {
            this.fetchAllModels();
        },

        /**
         * Iterates through all the loaded models & collections as defined in metadata and does a "fetch" on it
         */
        fetchAllModels: function() {
            var self = this;
            _.each(this.componentsMeta, function(component) {

                if(component.model && component.model.name){
                    self.context.forecasts[component.model.name.toLowerCase()].fetch();
                }

                if(component.contextCollection && component.contextCollection.name) {
                    self.context.forecasts[component.contextCollection.name.toLowerCase()].fetch();
                }

                if(component.collection && component.collection.name) {
                    self.context.forecasts[component.collection.name.toLowerCase()].fetch();
                }

            });
        },

        /**
         * Iterates through metadata to define and initialize each model and collection as defined therein.
         * @return {Object} new instance of the main model, which contains instances of the sub-models for each view
         * as defined in metadata.
         */
        initializeAllModels: function(existingModel) {
            var self = this,
                componentsMetadata = this.componentsMeta,
                module = app.viewModule.toLowerCase(),
                models = {},
                existingModel = existingModel || {};

            // creates the context.forecasts topmost model, if it's not already set on the models
            if(_.isUndefined(existingModel[module])) {
                models[module] = app.data.createBean(module);
                // creates the config model as a special case
                self.namespace(models, module);
            } else {
                models[module] = existingModel[module];
            }
            // Loops through components from the metadata, and creates their models/collections, as defined
            _.each(componentsMetadata, function(component) {
                var name,
                    modelMetadata = component.model,
                    context = component.contextCollection,
                    collectionMetadata = component.collection;

                if (modelMetadata) {
                    name = modelMetadata.name.toLowerCase();
                    self.namespace(models, module);
                    models[module][name] = self.createModel(modelMetadata, app.viewModule);
                }

                if(context) {
                    var name = context.name.toLowerCase();
                    var moduleContext = context.module;
                    self.namespace(models, module);

                    models[module][name] = self.createCollection();
                }

                if (collectionMetadata) {
                    name = collectionMetadata.name.toLowerCase();
                    self.namespace(models, module);
                    models[module][name] = self.createCollection();
                    models[module][name].url = app.config.serverUrl + '/' + app.viewModule + '/' + name;
                }
            });

            return models;
        },

        /**
         * creates a Backbone model for a given metadata definition
         * @param modelMetadata metadata definiton of the model.
         * @param module
         * @return {*} instance of a backbone model.
         */
        createModel: function(modelMetadata, module) {

            var Model = Backbone.Model.extend({
                sync: function(method, model, options) {
                    myURL = app.api.buildURL(module, modelMetadata.name.toLowerCase());
                    return app.api.call(method, myURL, null, options);
                }
            });

            return new Model();
        },

        /**
         * creates a Backbone collection for a given metadata definition
         * @param collectionMetadata metadata definition of the collection.
         * @param module
         * @return {*} instance of a backbone collection.
         */
        createCollection: function() {
            var Collection = Backbone.Collection.extend({
                model : Backbone.Model.extend({
                    sync : function(method, model, options) {
                        var url = _.isFunction(model.url) ? model.url() : model.url;
                        return app.api.call(method, url, model, options);
                    }
                }),
                /**
                 * Custom sync to use the app api to call the url (o-auth headers are inserted here)
                 *
                 * @param method
                 * @param model
                 * @param options
                 * @return {*}
                 */
                sync: function(method, model, options) {
                    var url = _.isFunction(model.url) ? model.url() : model.url;
                    return app.api.call(method, url, null, options);
                }

            });
            return new Collection();
        },

        namespace: function(target, namespace) {
            if (!target[namespace]) {
                target[namespace] = {};
            }
        },

        /**
         * Add a view (or layout) to this layout.
         * @param {View.Layout/View.View} comp Component to add
         */
        _placeComponent: function(comp) {
            var compName = comp.name || comp.meta.name,
                divName = ".view-" + compName;

            // Certain views in forecasts are controlled by other views
            // If there is a sub-view (eg: a view creates another view and manually renders it in)
            // then we can set placeInLayout => false and we create all the models and such
            // from the rest of metadata, but we just dont place it into the html of the layout
            // as another view will be handling that
            if(_.has(comp, 'meta') && !_.isUndefined(comp.meta) &&
                _.has(comp.meta, 'placeInLayout') && comp.meta.placeInLayout == false) {
                return;
            }

            if (!this.$el.children()[0]) {
                this.$el.addClass("complex-layout");
            }

            //add the components to the div
            if (compName && this.$el.find(divName)[0]) {
                this.$el.find(divName).append(comp.$el);
            } else {
                this.$el.append(comp.$el);
            }
        },

    /**
     * Override render so we can init the alerts for the page to use.
     *
     * @return {*}
     * @private
     */
    _render : function() {
        app.view.Layout.prototype._render.call(this);

        // init the alerts
        app.alert.init();
        $('div.tab-pane.active').height($(document).height());
        return this;
    }

})
