/**
 * Base bean class. Use {@link Data.DataManager} to create instances of beans.
 *
 * **CRUD on beans**
 *
 * Use standard Backbone's <code>fetch</code>, <code>save</code>, and <code>destroy</code>
 * methods to perform CRUD operations on beans. See {@link Data.DataManager} class for details.
 *
 * @class Data.Bean
 * @extends Backbone.Model
 * @alias SUGAR.App.Bean
 */
(function(app) {

    app.augment("Bean", Backbone.Model.extend({

        /**
         * Validates a bean.
         *
         * <code>validate</code> is called before <code>set</code> and <code>save</code>,
         * and is passed the attributes that are about to be updated.
         * <code>set</code> and <code>save</code> will not continue if validate returns an error.
         * Failed validations trigger an <code>"error"</code> event.
         *
         * <code>validate</code> returns an errors hash of the following structure:
         *
         * - keys: field names, values: errors hash
         * - errors hash is a collection of error definitions
         * - error definition can be a primitive type or an object. It depends on validator.
         *
         *  Example:
         *  <pre><code>
         *  {
         *    first_name: { maxLength: 20, someOtherValidator: { some complex error definition... } }
         *  }
         *  </code></pre>
         *
         *  This method does not check for required fields.
         *  TODO: Add convinience method that checks for required fields
         *
         * @param attrs attributes hash that is about to be set on this bean
         * @return {Object} errors hash if the bean is invalid or nothing otherwise.
         */
        validate: function(attrs) {
            var errors = {}, self = this;
            var field, value, result, validator;

            _.each(_.keys(self.fields), function(fieldName) {
                field = self.fields[fieldName];
                value = attrs[fieldName];

                if (value) {
                    _.each(_.keys(app.validation.validators), function(validatorName) {
                        validator = app.validation.validators[validatorName];
                        result = validator(field, value);
                        _addValidationError(errors, result, fieldName, validatorName);
                    });
                }
            });

            // {
            // first_name: { maxLength: 20 },
            // }

            // "validate" method should not return anything in case there are no validation errors
            if (!_.isEmpty(errors)) {
                app.error.handleValidationError(this, errors);
                return errors;
            }
        },

        /**
         * Returns string representation useful for debugging:
         * <code>bean:[module-name]/[id]</code>
         * @return {String} string representation of this bean
         */
        toString: function() {
            return "bean:" + this.module + "/" + (this.id ? this.id : "<no-id>");
        }

    }), false);

    /**
     * Adds validation error to the passed in error object.
     * @param {Object} errors
     * @param result
     * @param {String} fieldName
     * @param {String} validatorName
     * @private
     */
    function _addValidationError(errors, result, fieldName, validatorName) {
        if (result) {
            if (_.isUndefined(errors[fieldName])) {
                errors[fieldName] = {};
            }
            errors[fieldName][validatorName] = result;
        }
    }
})(SUGAR.App);