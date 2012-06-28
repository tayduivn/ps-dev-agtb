(function(app) {

    app.view.views.ListHeaderAssociateView = app.view.views.ListHeaderView.extend({
        initialize: function(options) {
            app.view.views.ListHeaderView.prototype.initialize.call(this, options);
            this.template = app.template.get("list-header-associate"); //todo:remove it later
        },
        onClickMenuSave:function(e){
            e.preventDefault();
            this.listView.save();
        },
        onClickMenuCancel:function(e){
            e.preventDefault();
            app.router.goBack();
        }
    });

})(SUGAR.App);
