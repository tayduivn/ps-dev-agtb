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
                selectedTimePeriod: {},
                selectedSalesStage: {},
                selectedUser: {}
            });

            app.view.Layout.prototype.initialize.call(this, options);

            this.fetchAllModels();
            this.initializeDrawer();
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
            console.log(comp);
        }
    });

})(SUGAR.App);