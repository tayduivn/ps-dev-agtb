(function(app) {

    var _meta = {
        type: "list",
        components: [
            { view:"list-header" },
            { view: "list" }
        ]
    };

    app.view.layouts.ListLayout = app.view.Layout.extend({

        initialize: function(options) {

            // We don't have base metadata for layouts in metadata payload
            // Set it here for now
            this.options.meta = _meta;
            app.view.Layout.prototype.initialize.call(this, options);

            var searchListView = this.getComponent('list-header');

            if (searchListView) {
                searchListView.setListView(this.getComponent('list'));
            }

        }
    });

})(SUGAR.App);