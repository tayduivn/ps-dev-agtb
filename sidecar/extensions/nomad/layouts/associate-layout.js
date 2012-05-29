(function(app) {

    var _meta = {
        type: "list",
        components: [
            { view:"searchassociate" },
            { view: "associate" }
        ]
    };

    app.view.layouts.AssociateLayout = app.view.Layout.extend({

        initialize: function(options) {
            this.options.meta = _meta;

            app.view.Layout.prototype.initialize.call(this, options);

            var searchListView = this.getComponent('searchassociate');
            var associateView = this.getComponent('associate');

            if(associateView){
                associateView.setPartialsTemplates({'list.item': app.template.get("list.associate.item")});
            }

            if (searchListView) {
                searchListView.setListView(associateView);
            }

        }
    });

})(SUGAR.App);