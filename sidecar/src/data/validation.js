/**
 * Validation module.
 *
 * The validation module is used by {@link Data.Bean#validate} method.
 * Each bean field is validated by each of the validators specified in the {@link Data.Validation.validators} hash.
 *
 * The bean is also checked for required fields by {@link Data.Validation#requiredValidator} method.
 *
 * @class Data.Validation
 * @singleton
 * @alias SUGAR.App.validation
 */
(function(app) {

    app.augment("validation", {

        /**
         * A hash of validators. Each validator function must return error definition or nothing otherwise.
         * Error definition could be a primitive value such as max length or an array, e.g. range's lower and upper limits.
         * Validator function accepts field metadata and the value to be validated.
         *
         * @class Data.Validation.validators
         * @singleton
         * @member Data.Validation
         */
        validators: {

            /**
             * Validates the max length of a given value.
             * @param {String} field bean field metadata
             * @param {String} value bean field value
             * @return {Number} max length or noting if the field is valid.
             * @method
             */
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
         *
         * @member Data.Validation
         * @param field field metadata
         * @param {String} fieldName bean field name
         * @param {Data.Bean} model bean instance
         * @param {String} value value to be set
         * @return {Boolean} <code>true</code> if the validation passes, <code>false</code> otherwise
         * @method
         */
        requiredValidator: function(field, fieldName, model, value) {
            if (!_.isUndefined(field.required) && (field.required === true)) {
                var currentValue = model.get(fieldName);
                var currentUndefined = _.isUndefined(currentValue);
                var valueEmpty = _.isNull(value) || value === "";
                if ((currentUndefined && _.isUndefined(value)) || valueEmpty) {
                    return false;
                }
            }

            return true;
        }

    }, false);

})(SUGAR.App);