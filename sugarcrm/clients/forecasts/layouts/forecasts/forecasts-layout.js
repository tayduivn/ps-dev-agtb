(function(app) {
    /**
     * Layout that places views in columns with each view in a column
     * @class View.Layouts.ColumnsLayout
     * @alias SUGAR.App.layout.ColumnsLayout
     * @extends View.Layout
     */
    app.view.layouts.ForecastsLayout = app.view.Layout.extend({

        componentsMeta: {},

        initialize: function(options) {
            this.componentsMeta = app.metadata.getLayout("Forecasts").forecasts.meta.components;

            options.context = _.extend(options.context, this.initializeAllModels());

            options.context.forecasts.set("selectedTimePeriod", app.defaultSelections.timeperiod_id);
            options.context.forecasts.set("selectedCategory", app.defaultSelections.category);
            options.context.forecasts.set("selectedGroupBy", app.defaultSelections.group_by);
            options.context.forecasts.set("selectedDataSet", app.defaultSelections.dataset);
            options.context.forecasts.set("selectedUser", {
                'id'            : app.user.get('id'),
                'full_name'     : app.user.get('full_name'),
                'isManager'     : app.user.get('isManager'),
                // first and last name are not passed through /Forecasts/me
                'first_name'    : '',
                'last_name'     : ''
            });
            options.context.forecasts.set("showManagerOpportunities", false);

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
                if(component.model){
                    if (component.model.name) {
                        self.context.forecasts[component.model.name.toLocaleLowerCase()].fetch();
                    }
                } else if (component.collection) {
                    if (component.collection.name) {
                        self.context.forecasts[component.collection.name.toLocaleLowerCase()].fetch();
                    }
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
                    collectionMetadata = component.collection;
                var module = app.viewModule.toLowerCase();

                if (!models[module]) {
                    var topModel = Backbone.Model.extend();
                    models[module] = new topModel();
                }

                if (modelMetadata) {
                    name = modelMetadata.name.toLowerCase();

                    self.namespace(models, module);
                    models[module][name] = self.createModel(modelMetadata, app.viewModule);
                }

                if (collectionMetadata) {
                    name = collectionMetadata.name.toLowerCase();

                    self.namespace(models, module);
                    models[module][name] = self.createCollection(collectionMetadata, app.viewModule);
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
                url: app.config.serverUrl + '/' + module + '/' + modelMetadata.name.toLowerCase()
            });
            return new Model();
        },

        /**
         * creates a Backbone collection for a given metadata definition
         * @param collectionMetadata metadata definition of the collection.
         * @param module
         * @return {*} instance of a backbone collection.
         */
        createCollection: function(collectionMetadata, module) {
            var Collection = Backbone.Collection.extend({
                url: app.config.serverUrl + '/' + module + '/' + collectionMetadata.name.toLowerCase()
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

})(SUGAR.App);