(function(app) {
    if (!app.Model) {
        app.Model = {};
    }

    app.Model.Worksheet = Backbone.Collection.extend({
        url:"Forecasts/worksheet"
    });

})(SUGAR.App);