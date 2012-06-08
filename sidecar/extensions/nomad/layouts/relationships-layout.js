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

            var listView = this.getComponent('list');
            listView.template =  app.template.get("list.relationships");

            if (searchListView) {
                searchListView.setListView(listView);
            }

        }
    });

})(SUGAR.App);