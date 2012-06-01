(function(app) {

    app.view.fields.PhoneField = app.view.Field.extend({
        events: {
            "click .call": function () {
                app.nomad.callPhone([this.value]);
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