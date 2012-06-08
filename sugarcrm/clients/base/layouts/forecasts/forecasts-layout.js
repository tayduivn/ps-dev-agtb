(function(app) {
    /**
     * Layout that places views in columns with each view in a column
     * @class View.Layouts.ColumnsLayout
     * @alias SUGAR.App.layout.ColumnsLayout
     * @extends View.Layout
     */
    app.view.layouts.ForecastsLayout = app.view.Layout.extend({

        _models: {},

        initialize: function(options) {
            this.initializeAllModels();

            app.view.Layout.prototype.initialize.call(this, options);

            this.context = _.extend(this.context, {
                register: app.events.register,
                // keep a record of the currently selected user on context
                selectedUser: {}
            });

            this.fetchAllModels();
        },

        fetchAllModels: function() {
            _.each(this._models, function(model, key) {
                model.fetch();
            });
        },

        getModel: function(name) {
            return this._models[name];
        },

        initializeAllModels: function() {
            var self = this,
                modelMetadata = app.metadata.getLayout("Forecasts").forecasts.meta.models;

            _.each(modelMetadata, function(data) {
                self.createModels(data);
            });

            this._models.grid = new app.Model.Grid();
        },

        createModels: function(data) {
            var Model = Backbone.Model.extend({
                module: data.module + '/' + data.name.toLowerCase(),

                initialize: function() {
                    this.setModelBindings()
                },

                setModelBindings: function() {
                    var self = this;
                    this.on('change', function() {
                        _.each(this.attributes, function(data, key) {
                            if (self.hasChanged(key)) {
                                self[key].set(self.get(key));
                            }
                        });
                    });
                }
            });

            var model = this._models[data.name.toLowerCase()] = new Model();
            this.createNestedModels(model, data.models);
        },

        createNestedModels: function(model, modelsMetadata) {
            _.each(modelsMetadata, function(name) {
                model[name] = new Backbone.Model();
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