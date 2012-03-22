/**
 * Base bean class. Use {@link DataManager} to create instances of beans.
 *
 * @class Bean
 * @extends Backbone.Model
 * @alias SUGAR.App.Bean
 */
(function(app) {

    app.augment("Bean", Backbone.Model.extend({

        /**
         * Bean initialization
         * @method
         * @private
         */
        initialize: function() {
            _.each(this.fields , function(def){
                if (def.calculated){
                    //TODO: SugarLogic Code here
                }
            }, this);
        },

        /*
         * Updates a property of type 'relate'.
         * @param {String} attribute field name
         * @param {Bean} bean related bean
         */
        setRelated: function(attribute, bean) {
            // TODO: Implement once the metadata is spec'ed out
            // This will be a convinience method.
            // We may decide to drop it and resort to setting related bean ID into the corresponding field
        },

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
         *    first_name: { maxLength: 20, someOtherValidator: { some complex error definition... } },
         *    last_name: { required: true }
         *  }
         *  </code></pre>
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

                // First, check if the field is required
                result = app.validation.requiredValidator(field, fieldName, self, value);
                _addValidationError(errors, !result, fieldName, "required");

                // Next, run all validations for the value only if "required" validation passed
                if (result && value) {
                    _.each(_.keys(app.validation.validators), function(validatorName) {
                        validator = app.validation.validators[validatorName];
                        result = validator(field, value);
                        _addValidationError(errors, result, fieldName, validatorName);
                    });
                }
            });

            //{
            // first_name: { maxLength: 20 },
            // last_name: { required: true }
            // }

            // "validate" method should not return anything in case there are no validation errors
            if (!_.isEmpty(errors)) return errors;
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
     * @param errors
     * @param result
     * @param fieldName
     * @param validatorName
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