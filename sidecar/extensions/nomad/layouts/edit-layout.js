(function(app) {

    var _meta = {
        type: "edit",
        components: [
            { view: "edit" }
        ]
    };

    app.view.layouts.EditLayout = app.view.Layout.extend({

        initialize: function(options) {

            // We don't have base metadata for layouts in metadata payload
            // Set it here for now
            this.options.meta = _meta;
            app.view.Layout.prototype.initialize.call(this, options);
        }
    });

})(SUGAR.App);