(function(app) {
    /**
     * Layout that places views in columns with each view in a column
     * @class View.Layouts.ColumnsLayout
     * @alias SUGAR.App.layout.ColumnsLayout
     * @extends View.Layout
     */
    app.view.layouts.ForecastsLayout = app.view.Layout.extend({

    initialize: function(options) {

            options.context = _.extend(options.context, this.initializeAllModels());
            options.context = _.extend(options.context, {
                register: app.events.register,
                selectedTimePeriod: {},
                selectedCategory: {},
                selectedUser: {},
                showManagerOpportunities: false
            });

            // Initialize the user on the model
            var selectedUser = {
                'id'            : app.user.get('id'),
                'full_name'     : app.user.get('full_name'),
                // first and last name are not passed through /Forecasts/me
                'first_name'    : '',
                'last_name'     : ''
            };

            //set the current user with the logged in user
            this.options.context.set("selectedUser", selectedUser);

            app.view.Layout.prototype.initialize.call(this, options);

            //If it's a manager, switch to the manager view
            if(app.user.get('isManager'))
            {
                //$('#view-sales-rep').hide();
                //$('#view-manager').show();
            }
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

        fetchAllModels: function() {
            _.each(this.context.forecasts, function(model, key) {
                model.fetch();
            });
        },

        initializeAllModels: function() {

            var self = this,
                componentsMetadata = app.metadata.getLayout("Forecasts").forecasts.meta.components,
                models = {};

            _.each(componentsMetadata, function(component) {
                var module, name,
                    modelMetadata = component.model,
                    collectionMetadata = component.collection;

                if (modelMetadata) {
                    module = modelMetadata.module.toLowerCase();
                    name = modelMetadata.name.toLowerCase();

                    self.namespace(models, module);
                    models[module][name] = self.createModel(modelMetadata);
                }

                if (collectionMetadata) {
                    module = collectionMetadata.module.toLowerCase();
                    name = collectionMetadata.name.toLowerCase();

                    self.namespace(models, module);
                    models[module][name] = self.createCollection(collectionMetadata);
                }
            });

            return models;
        },

        createModel: function(modelMetadata) {
            var Model = Backbone.Model.extend({
                url: app.config.serverUrl + '/' + modelMetadata.module + '/' + modelMetadata.name.toLowerCase()
            });
            return new Model();
        },

        createCollection: function(collectionMetadata) {
            var Collection = Backbone.Collection.extend({
                url: app.config.serverUrl + '/' + collectionMetadata.module + '/' + collectionMetadata.name.toLowerCase()
            });
            return new Collection();
        },

        namespace: function(target, namespace) {
            if (!target[namespace]) {
                target[namespace] = {};
            }
        },

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