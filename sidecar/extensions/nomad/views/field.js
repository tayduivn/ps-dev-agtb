(function(app) {

    app.view.Field = app.view.Field.extend({
        /**
         * Handles how validation errors are appended to the fields dom element
         * @param {Object} errors hash of validation errors
         */
        handleValidationError: function(errors) {
            var template,
                errMessages = [];

            this.$('.control-group').addClass("error");

            //create data array for template
            _.each(errors, function(errorContext, errorName) {
                errMessages.push(app.error.getErrorString(errorName, errorContext));
            });

            //get template and output the result
            template = app.template.get('field.messages');
            this.$('.controls').append(template(errMessages));
            
            app.alert.show('field_validation_error', {level:'error', messages:'Validation error!'});
        }
    });

})(SUGAR.App);