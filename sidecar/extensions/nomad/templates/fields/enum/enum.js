(function(app) {

    app.view.fields.EnumField = app.view.Field.extend({

        // TODO: Override base implementation
        // Check out Mango/sugarcrm/clients/base/fields/int/int.js

        fieldTag: "select"
    });

})(SUGAR.App);