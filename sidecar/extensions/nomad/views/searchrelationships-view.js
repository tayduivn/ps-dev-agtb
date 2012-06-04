(function(app) {

    app.view.views.SearchrelationshipsView = app.view.views.SearchlistView.extend({
        initialize: function(options) {
            app.view.views.SearchlistView.prototype.initialize.call(this, options);
            this.template = app.template.get("list.relationships.header"); //todo:remove it later
        },
        onClickMenuCancel:function(e){
            e.preventDefault();
            app.router.goBack();
        }
    });

})(SUGAR.App);
