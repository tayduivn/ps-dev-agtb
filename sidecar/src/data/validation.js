(function(app) {

    app.augment("validation", {

        // A hash of validators. Each validator function must return an error definition for invalid values or nothing otherwise.
        // Error definition could be a primitive value such as max length or an array, e.g. range's lower and upper limits.
        // Validator function accepts field metadata and the value to be validated.
        validators: {

            maxLength: function(field, value) {
                if (_.isNumber(field.len)) {
                    var maxLength = field.len;
                    value = value || "";
                    value = value.toString();
                    if (value.length > maxLength) {
                        return maxLength;
                    }
                }
            }

            // TODO: More validators will be added as we need them

            // EMAIL, URL, whatever

            // TODO: Do we need "type" validators, e.g. "int", "currency", etc?

        },

        /**
         * Validates if the required field is set on a bean or about to be set.
         * @param field Field metadata
         * @param fieldName Bean's field name
         * @param model Bean instance
         * @param value A value to be set
         * @returns true if the validation passes
         */
        requiredValidator: function(field, fieldName, model, value) {
            if (!_.isUndefined(field.required) && (field.required === true)) {
                var currentValue = model.get(fieldName);
                var currentUndefined = _.isUndefined(currentValue);
                // TODO: How about trimming string value? Check out underscore.string lib. Or is it done by form binding component?
                var valueEmpty = _.isNull(value) || value === "";
                if ((currentUndefined && _.isUndefined(value)) || valueEmpty) {
                    return false;
                }
            }

            return true;
        }

    }, false);

})(SUGAR.App);