/**
 * Base bean class. Use {@link dataManager} to create instances of beans.
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

        /**
         * Fetches relationships.
         * @param link Link name
         * @param options Options hash (see Backbone.Collection.fetch method documentation for details).
         */
        fetchRelated: function(link, options) {
            var relations = app.dataManager.createRelationCollection(link, this);
            relations.fetch(options);
            return relations;
        },

        /*
         * Adds a relationship.
         * @param link Link name
         * @param beanOrId related bean instance or its ID.
         * @param options Options hash (success and error callbacks, etc.)
         * @param data Additional data to attach to the new relationship.
         */
        addRelated: function(link, beanOrId, options, data) {
            var relation = app.dataManager.createRelation(link, this, beanOrId, data);
            relation.save(options);
            return relation;
        },

        /*
         * Removes relationship.
         * @param link Link name
         * @param beanOrId Related bean instance or its ID.
         * @param options Options hash (success and error callbacks, etc.)
         */
        removeRelated: function(link, beanOrId, options) {
            var relation = app.dataManager.createRelation(link, this, beanOrId);
            relation.destroy(options);
            return relation;
        },

        /*
         * Updates a property of type 'relate'.
         * @param attribute Property name
         * @param bean Related bean
         * @param options Options hash (success and error callbacks, etc.)
         */
        setRelated: function(attribute, bean, options) {
            options = options || (options = {});
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
            };

            return this.addRelated(link, bean, options);
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
         * <code>bean:[module-name]/[bean-type]-[id]</code>
         * @return {String} string representation of this bean
         */
        toString: function() {
            return "bean:" + this.module + "/" + this.beanType + "-" + (this.id ? this.id : "<no-id>");
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