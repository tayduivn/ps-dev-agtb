(function(app) {
    /**
     * Layout that places views in columns with each view in a column
     * @class View.Layouts.ColumnsLayout
     * @alias SUGAR.App.layout.ColumnsLayout
     * @extends View.Layout
     */
    app.view.layouts.ForecastsLayout = app.view.Layout.extend({

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

            this.componentsMeta = app.metadata.getLayout("Forecasts").forecasts.meta.components;

            options.context = _.extend(options.context, this.initializeAllModels());

            var defaultSelections = app.defaultSelections;

            // Set initial selected data on the context
            options.context.forecasts.set({
                selectedTimePeriod : defaultSelections.timeperiod_id,
                selectedCategory: defaultSelections.category,
                selectedGroupBy : defaultSelections.group_by,
                selectedDataSet: defaultSelections.dataset,
                selectedUser : defaultSelections.selectedUser,

                /**
                 * boolean to use across components to enable commit button or not
                 */
                commitButtonEnabled : false,

                /**
                 * forecastsCommitButtons triggers this flag to tell forecastsCommitted to call commitForecast()
                 */
                commitForecastFlag : false
            });

            // grab a copy of the init data for forecasts to use
            this.initDataModel = app.initData;

            // then get rid of the data from app
            app.initData = null;

            app.view.Layout.prototype.initialize.call(this, options);

            this.initializeDrawer();
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
        initializeAllModels: function() {
            var self = this,
                componentsMetadata = this.componentsMeta,
                models = {};
            _.each(componentsMetadata, function(component) {
                var name,
                    modelMetadata = component.model,
                    context = component.contextCollection,
                    collectionMetadata = component.collection;
                var module = app.viewModule.toLowerCase();

                if (!models[module]) {
                    var topModel = app.data.createBean(module);
                    models[module] = topModel;
                }

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
         * Initializes the drawer on the left of the forecasts page that contains the filters
         */
        initializeDrawer: function() {
            $('.drawerTrig').on('click', function () {
                // hide and show drawer
                $(this).toggleClass('pull-right').toggleClass('pull-left');
                $('.bordered').toggleClass('hide');

                // toggle icon
                $(this).find('i').toggleClass('icon-chevron-left').toggleClass('icon-chevron-right');

                // widen the rest of the page
                $('#drawer').toggleClass('span2');
                $('#charts').toggleClass('span10').toggleClass('span12');
            });
        },

        /**
         * Add a view (or layout) to this layout.
         * @param {View.Layout/View.View} comp Component to add
         */
        _placeComponent: function(comp) {
            if (!this.$el.children()[0]) {
                this.$el.addClass("complex-layout");
            }

            //add the layout to the div
            $(".view-"+comp.name).append(comp.$el);
        }
    });


    app.view.Field = app.view.Field.extend({
        _render: function() {
            if (this.def.type == 'bool' && (this.name == "forecast" || this.name == 'include_expected')) {
                this.options = this.options || {};
                this.options.viewName = this.view.isMyWorksheet() ? 'edit' : 'detail';
            }
            app.view.Field.__super__._render.call(this);
        }
    });
    
})(SUGAR.App);