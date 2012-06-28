(function(app) {

    app.view.Field = app.view.Field.extend({

        events: {
            "focusout input, textarea, select": 'resetErrorDispay'
        },

        /**
         * Handles how validation errors are appended to the fields dom element
         * @param {Object} errors hash of validation errors
         */
        handleValidationError: function (errors) {
            var template,
                errMessages = [];

            this.$('.control-group').addClass("error");

            //create data array for template
            _.each(errors, function(errorContext, errorName) {
                errMessages.push(app.error.getErrorString(errorName, errorContext));
            });

            //get template and output the result
            template = app.template.get('edit-field-error');
            this.$('.controls').append(template(errMessages));
        },

        /**
         * Resets displaying field validation error style & messages.
         */
        resetErrorDispay: function () {
            var fieldContainer = this.$('.control-group');
            if (fieldContainer.hasClass('error')) {
                this.$('.control-group').removeClass('error');
                this.$('.controls').children().not(this.fieldTag).remove();
            }
        }
    });

})(SUGAR.App);
