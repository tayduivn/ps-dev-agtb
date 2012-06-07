(function(app) {

    app.view.fields.SingleemailField = app.view.Field.extend({
        events: {
            "click .btn": function() {
                app.nomad.sendEmail(this.value);
            }
        }
    });

})(SUGAR.App);