(function(app) {

    /**
     * Represents a base class for all bean model classes.
     * Bean has the following properties:
     * - module: module name
     * - beanType: bean type
     * - fields: fields metadata
     * - relationships: relationships metadata
     */
    app.augment("Bean", Backbone.Model.extend({

        fetchRelated: function(link, options) {
            var relations = app.Relationships.buildCollection(link, this);
            relations.fetch(options);
            return relations;
        },

        addRelated: function(link, bean, options, data) {
            var relation = app.Relationships.buildRelation(link, this, bean, data);
            relation.save(options);
            return relation;
        },

        removeRelated: function(link, bean, options) {
            var relation = app.Relationships.buildRelation(link, this, bean);
            relation.destroy(options);
            return relation;
        },

        setRelated: function(attribute, bean, options) {
            // TODO: Deal with fields of type relate
        },

        /**
        * See Backbone.Model.validate documentation for details.
        * @param attrs
        * @return errors hash if the bean is invalid or nothing otherwise.
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


            // Errors hash structure:
            // keys: field names, values: arrays of errors
            // each element of the array is a hash of error definition which is an error type.
            // For example:
            // { first_name: [ { len: 20 } ], last_name: [ { required: true } ]

            // "validate" method should not return anything in case there are no validation errors
            if (!_.isEmpty(errors)) return errors;
        },

        toString: function() {
            return this.module + "/" + this.beanType + "-" + this.id;
        }

    }), false);

    function _addValidationError(errors, result, fieldName, validatorName) {
        if (result) {
            if (_.isUndefined(errors[fieldName])) {
                errors[fieldName] = [];
            }
            var error = {};
            error[validatorName] = result;
            errors[fieldName].push(error);
        }
    }

})(SUGAR.App);