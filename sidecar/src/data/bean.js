(function(app) {

    /**
     * Represents a base class for all bean model classes.
     * Bean has the following properties:
     * - module: module name
     * - beanType: bean type
     * - validations: validation hash where keys are field names and values are arrays of validators
     */
    app.augment("Bean", Backbone.Model.extend({

        /**
        * See Backbone.Model.validate documentation for details.
        * @param attrs
        */
        validate: function(attrs) {
            var errors = [], result, validators, self = this;

            _.each(self.required, function(field) {
                result = app.validation.requiredValidator(field, self, attrs[field]);
                _addValidationError(errors, result, field);
            });

            if (!_.isEmpty(self.validations)) {
                _.each(_.keys(attrs), function(attribute) {
                    validators = self.validations[attribute];

                    _.each(validators, function(validator) {
                        result = validator(self, attrs[attribute]);
                        _addValidationError(errors, result, attribute);
                    });
                });
            }

            // "validate" method should not return anything in case there are not validation errors
            if (errors.length > 0) return errors;
        }

    }), false);

    function _addValidationError(errors, result, attribute) {
        if (result) {
            result.attribute = attribute;
            errors.push(result);
        }
    }

})(SUGAR.App);