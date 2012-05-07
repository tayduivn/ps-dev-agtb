(function(app) {

    app.view.fields.IntField = app.view.Field.extend({

        // TODO: Override base implementation
        // Check out Mango/sugarcrm/clients/base/fields/int/int.js

        unformat: function(value) {
            // TODO: Implement
            return value;
        },

        format: function(value) {
            // TODO: Implement
            return value;
        }

    });

})(SUGAR.App);