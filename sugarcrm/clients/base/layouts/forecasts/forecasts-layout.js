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

//        initialize: function(options) {
//            debugger;
//            // Create an empty collection of contacts.
//            var test = new Filters();
//
//            test.fetch({
//                success:function(){
////                    debugger;
//                    var t = test;
//                    console.log(t);
//                }
//            });
//
//
//            var tp = app.data.createBean('TimePeriods', {id: "19d9026d-9312-a310-0868-4fc6477c7ca3"});
//            tp.fetch({fields: ['id', 'name']});
//
//            app.view.Layout.prototype.initialize.call(this, options);
//
//            this.context = _.extend(this.context, {
//                register: app.events.register
//            });
//            console.log("context:");
//            console.log(this.context);
//        },

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