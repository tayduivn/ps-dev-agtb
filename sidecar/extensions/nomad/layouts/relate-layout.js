(function(app) {

    app.view.layouts.RelateLayout = app.view.Layout.extend({

        initialize: function(options) {
            app.view.Layout.prototype.initialize.call(this, options);
        }
    });

})(SUGAR.App);