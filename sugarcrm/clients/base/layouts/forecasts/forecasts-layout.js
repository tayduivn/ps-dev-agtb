(function(app) {

//    var Filter = Backbone.Model.extend();
//
//    var Filters = Backbone.Collection.extend({
//        model: Filter,
//        module: "Forecasts/filters"
//    });
//
//    var Forecasts = Backbone.Model.extend({
//        initialize: function() {
////            debugger;
//            this.filters = new Filters();
//            this.filters.fetch();
//
////            this.forecastLine = new ForecastLine();
////            this.chart = new Chart();
//        }
//    });


    /**
     * Layout that places views in columns with each view in a column
     * @class View.Layouts.ColumnsLayout
     * @alias SUGAR.App.layout.ColumnsLayout
     * @extends View.Layout
     */
    app.view.layouts.ForecastsLayout = app.view.Layout.extend({

        initialize: function(options) {
            app.view.Layout.prototype.initialize.call(this, options);

            this.context = _.extend(this.context, {
                register: app.events.register
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