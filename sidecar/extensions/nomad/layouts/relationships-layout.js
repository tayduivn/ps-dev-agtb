(function(app) {

    var _meta = {
        type: "list",
        components: [
            { view: "list-header-relationships" },
            { view: "list" }
        ]
    };

    app.view.layouts.RelationshipsLayout = app.view.Layout.extend({

        initialize: function(options) {
            this.options.meta = _meta;
            app.view.Layout.prototype.initialize.call(this, options);

            var searchListView = this.getComponent('list-header');

            var listView = this.getComponent('list');
            listView.template =  app.template.get("list-relationships");
            listView.headerHeight = 100;  //TODO: refactor it

            if (searchListView) {
                searchListView.setListView(listView);
            }

        }
    });

})(SUGAR.App);