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

        /**
         * Fetches relationships.
         * @param link Link name
         * @param options Options hash (see Backbone.Collection.fetch method documentation for details).
         */
        fetchRelated: function(link, options) {
            var relations = app.Relationships.buildCollection(link, this);
            relations.fetch(options);
            return relations;
        },

        /**
         * Adds a relationship.
         * @param link Link name
         * @param beanOrId related bean instance or its ID.
         * @param options Options hash (success and error callbacks, etc.)
         * @param data Additional data to attach to the new relationship.
         */
        addRelated: function(link, beanOrId, options, data) {
            var relation = app.Relationships.buildRelation(link, this, beanOrId, data);
            relation.save(options);
            return relation;
        },

        /**
         * Removes relationship.
         * @param link Link name
         * @param beanOrId Related bean instance or its ID.
         * @param options Options hash (success and error callbacks, etc.)
         */
        removeRelated: function(link, beanOrId, options) {
            var relation = app.Relationships.buildRelation(link, this, beanOrId);
            relation.destroy(options);
            return relation;
        },

        /**
         * Updates a property of type 'relate'.
         * @param attribute Property name
         * @param bean Related bean
         * @param options Options hash (success and error callbacks, etc.)
         */
        setRelated: function(attribute, bean, options) {
            options || (options = {});
            var origError = options.error;
            var origSuccess = options.success;

            var self = this;
            var field = this.fields[attribute];
            var link = field.link;
            var rname = field.rname;
            var idFieldName = field.id_name;

            var oldValue = this.get(attribute);
            var oldId = this.get(idFieldName);

            var values = {};
            values[attribute] = bean.get(rname);
            values[idFieldName] = bean.id;
            this.set(values);

            options.error = function(model, resp) {
                values[attribute] = oldValue;
                values[idFieldName] = oldId;
                self.set(values);

                if (origError) {
                    origError(self, resp);
                }
            };

            options.success = function(model, resp) {
                options.success = origSuccess;
                self.save(null, options);
            }

            return this.addRelated(link, bean, options);
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