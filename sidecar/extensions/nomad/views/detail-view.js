(function(app) {

    app.view.views.DetailView = app.view.View.extend({
        events: {
            "click #backRecord": function () {
                app.router.navigate("", {trigger: true});
            }
        }
    });

})(SUGAR.App);