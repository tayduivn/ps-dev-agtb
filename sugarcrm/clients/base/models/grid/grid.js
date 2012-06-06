(function(app) {
    if (!app.Model) {
        app.Model = {};
    }

    var _gridModel = Backbone.Model.extend();

    app.Model.Grid = Backbone.Collection.extend({
        module:"Forecasts/grid"
    });

})(SUGAR.App);