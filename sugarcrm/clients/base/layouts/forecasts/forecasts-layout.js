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
                register: app.events.register
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
            this._models.filters = new app.Model.Filters();
            this._models.chartoptions = new app.Model.ChartOptions();
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