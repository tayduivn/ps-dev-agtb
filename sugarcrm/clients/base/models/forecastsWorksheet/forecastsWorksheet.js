(function(app) {
    if (!app.Model) {
        app.Model = {};
    }

    app.Model.Worksheet = Backbone.Collection.extend({
        url: app.config.serverUrl + "/Forecasts/worksheet"
    });

})(SUGAR.App);