(function(app) {

    var _meta = {
        type: "list",
        components: [
            { view: "associate" }
        ]
    };

    app.view.layouts.AssociateLayout = app.view.Layout.extend({

        initialize: function(options) {
            this.options.meta = _meta;
            app.view.Layout.prototype.initialize.call(this, options);
        }
    });

})(SUGAR.App);