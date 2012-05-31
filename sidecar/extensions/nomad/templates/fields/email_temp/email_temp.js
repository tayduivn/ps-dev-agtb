(function(app) {

    app.view.fields.Email_tempField = app.view.Field.extend({
        events: {
            "click .btn": function () {
                app.nomad.sendEmail([this.value]);
            }
        },

        unformat: function(value) {
            return value;
        },

        format: function(value) {
            return value;
        }
    });

})(SUGAR.App);