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
             * @return {Number} max length or nothing if the field is valid.
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
            },

            /**
             * Validates the min length of a given value.
             * @param {String} field bean field metadata
             * @param {String} value bean field value
             * @return {Number} min length or nothing if the field is valid.
             * @method
             */
            minLength: function(field, value) {
                var minLength;

                if (_.isNumber(field.minlen)) { // TODO: Not sure what the proper property is if there is one
                    minLength = field.minlen;
                    value = value || "";
                    value = value.toString();

                    if (value.length < minLength) {
                        return minLength;
                    }
                }
            },

            /**
             * Validates that a given value is a valid URL.
             * NOTE: Should be noted we can't do full validation of urls w/ javascript as that is impossible.
             * @param {String} field bean field metadata
             * @param {String} value bean field value
             * @return {Boolean} Should return true if not valid
             */
            url: function(field, value) {
                if (field.type == "url") {
                    return (/^(https?|http):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/.test(value))
                }
            },

            /**
             * Validates that a given value is a valid email address.
             * NOTE: Should be noted that we can't do full email validation w/ javascript.
             * @param {String} field bean field metadata
             * @param {String} value bean field value
             * @return {Boolean} Should return true if not valid
             */
            email: function(field, value) {
                var results = [];
                if (field.type == "email") {
                    if (value.length && value.length > 0) {
                        _.each(value, function(fieldProperties) {
                            var isValid = app.utils.isValidEmailAddress(fieldProperties.email_address);
                            if (!isValid) {
                                results.push(fieldProperties.email_address);
                            }
                        });
                    }
                    if (results.length > 0) {
                        return results;
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
                    return true;
                }
            }

            return false;
        }

    }, false);

})(SUGAR.App);