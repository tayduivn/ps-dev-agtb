(function(app) {

    app.view.fields.DatetimecomboField = app.view.Field.extend({

        // TODO: Implement i18n

        unformat: function(value) {
            return value;
        },

        format: function(value) {
            return value;
        }

    });

})(SUGAR.App);