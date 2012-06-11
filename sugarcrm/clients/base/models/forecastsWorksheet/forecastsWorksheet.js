(function(app) {
    if (!app.Model) {
        app.Model = {};
    }

    var _worksheetModel = Backbone.Model.extend();

    app.Model.Worksheet = Backbone.Collection.extend({
        module:"Forecasts/worksheet"
    });

})(SUGAR.App);