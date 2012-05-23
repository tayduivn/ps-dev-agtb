(function(app) {

    app.view.fields.IntField = app.view.Field.extend({

        // TODO: Override base implementation
        // Check out Mango/sugarcrm/clients/base/fields/int/int.js

        unformat: function(value) {
            value = this.app.utils.formatNumber(value, 0, 0, "", ".");
            return value;
        },

        format: function(value) {
            value = this.app.utils.formatNumber(value, 0, 0, this.def.number_group_seperator, ".");
            return value;
        }

    });

})(SUGAR.App);