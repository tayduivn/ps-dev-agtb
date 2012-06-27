(function(app) {

    app.view.fields.UrlField = app.view.Field.extend({
        events: {
            "click .link": function () {
                app.nomad.openUrl(this.value);
            }
        }

    });

})(SUGAR.App);