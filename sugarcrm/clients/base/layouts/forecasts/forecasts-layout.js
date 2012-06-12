(function(app) {
    /**
     * Layout that places views in columns with each view in a column
     * @class View.Layouts.ColumnsLayout
     * @alias SUGAR.App.layout.ColumnsLayout
     * @extends View.Layout
     */
    app.view.layouts.ForecastsLayout = app.view.Layout.extend({

        initialize: function(options) {
            var models = this.initializeAllModels();

            options.context = _.extend(options.context, {
                model: models,
                register: app.events.register,
                selectedTimePeriod: '',
                // keep a record of the currently selected user on context
                selectedUser: {},
                getGridFilters: function(){
                    return {
                        "assigned_user_id": this.attributes.selectedUser.id
                    };
                }
            });

            app.view.Layout.prototype.initialize.call(this, options);

            this.fetchAllModels();
        },

        fetchAllModels: function() {
            _.each(this.context.model.forecasts, function(model, key) {
                model.fetch();
            });
        },

        initializeAllModels: function() {
            var self = this,
                componentsMetadata = app.metadata.getLayout("Forecasts").forecasts.meta.components,
                models = {};

            _.each(componentsMetadata, function(component) {
                var model,
                    modelMetadata = component.model;

                if (modelMetadata) {
                    if (!models[modelMetadata.module.toLowerCase()]) {
                        models[modelMetadata.module.toLowerCase()] = {};
                    }

                    model = self.createModel(modelMetadata.module, modelMetadata.name);
                    models[modelMetadata.module.toLowerCase()][modelMetadata.name.toLowerCase()] = model;
                }
            });

            models.forecasts.worksheet = new app.Model.Worksheet(); //TODO: create model using the metadata
            return models;
        },

        createModel: function(module, name) {
            var Model = Backbone.Model.extend({
                url: app.config.serverUrl + '/' + module + '/' + name.toLowerCase()
            });
            return new Model();
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
            console.log(comp);
        }
    });

})(SUGAR.App);