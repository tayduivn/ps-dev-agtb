(function(app) {

    app.view.fields.IntField = app.view.Field.extend({

        unformat: function(value) {
            return this.app.utils.formatNumber(value, 0, 0, "", ".");
        },

        format: function(value) {
            value = this.app.utils.formatNumber(value, 0, 0, this.def.number_group_seperator, ".");
            return isNaN(value) ? "" : value;
        }

    });

})(SUGAR.App);