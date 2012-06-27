(function(app) {

    var _meta = {
        type: "list",
        components: [
            { view: "list-header-associate" },
            { view: "list-associate" }
        ]
    };

    app.view.layouts.AssociateLayout = app.view.Layout.extend({

        initialize: function(options) {
            this.options.meta = _meta;

            app.view.Layout.prototype.initialize.call(this, options);

            var searchListView = this.getComponent('list-header-associate');
            var associateView = this.getComponent('list-associate');

            if(associateView){
                associateView.setTemplateOption("partials", {'list-item': app.template.get("list-item-associate")});
            }

            if (searchListView) {
                searchListView.setListView(associateView);
            }

        }
    });

})(SUGAR.App);