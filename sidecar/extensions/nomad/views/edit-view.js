(function(app) {

    app.view.views.EditView = app.view.View.extend({
        events: {
            "click #saveRecord": function () {
                var model = this.context.get("model"),
                    module = model.module;

                model.save(null, {
                    success: function (model, resp) {
                        console.log("---saved successfully");

                        //navigate to record details
                        if (this.name == 'edit') {
                            app.router.goBack();
                        } else {
                            app.router.navigate(module + '/' + resp.id, {trigger: true});
                        }
                    },
                    error: function () {
                        console.log("---save error");
                    }
                });
            },
            "click #backRecord": function () {
                app.router.goBack();
            }/*,
            "focusin input": function (e) {
                debugger;
                //$(e.srcElement).parent(".control-group").removeClass('error');
            }*/
        }
    });

})(SUGAR.App);