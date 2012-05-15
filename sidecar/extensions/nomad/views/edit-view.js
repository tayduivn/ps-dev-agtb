(function(app) {

    app.view.views.EditView = app.view.View.extend({
        events: {
            "click #saveRecord": function () {
                var model = this.context.get("model");
                model.save(null, {
                    success: function () {
                        console.log('---saved successfully');
                        app.router.navigate("", {trigger: true});
                    },
                    error: function () {
                        console.log('---save error');
                    }
                });
            },
            "click #backRecord": function () {
                app.router.navigate("", {trigger: true});
            }
        }
    });

})(SUGAR.App);