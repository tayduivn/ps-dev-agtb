(function(app) {

    app.view.views.ListHeaderRelationshipsView = app.view.views.ListHeaderView.extend({

        onClickMenuCancel: function(e) {
            e.preventDefault();
            app.router.goBack();
        }
    });

})(SUGAR.App);
