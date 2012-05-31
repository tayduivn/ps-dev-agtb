(function(app) {

    app.view.fields.DateField = app.view.Field.extend({

        unformat: function(value) {
            return value;
        },

        format: function(value) {
            return value;
        }

    });

})(SUGAR.App);