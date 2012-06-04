(function(app) {

    var _meta = {
        type: "list",
        components: [
            { view:"searchrelationships" },
            { view: "list" }
        ]
    };

    app.view.layouts.RelationshipsLayout = app.view.Layout.extend({

        initialize: function(options) {
            this.options.meta = _meta;
            app.view.Layout.prototype.initialize.call(this, options);

            var searchListView = this.getComponent('searchlist');

            if (searchListView) {
                searchListView.setListView(this.getComponent('list'));
            }

        }
    });

})(SUGAR.App);