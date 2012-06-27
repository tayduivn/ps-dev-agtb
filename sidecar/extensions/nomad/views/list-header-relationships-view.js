(function(app) {

    app.view.views.ListHeaderRelationshipsView = app.view.views.ListHeaderView.extend({
        initialize: function(options) {
            app.view.views.ListHeaderView.prototype.initialize.call(this, options);
        },
        onClickMenuCancel:function(e){
            e.preventDefault();
            app.router.goBack();
        }
    });

})(SUGAR.App);
