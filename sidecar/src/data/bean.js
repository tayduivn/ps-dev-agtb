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

    var _relatedCollections;

    app.augment("Bean", Backbone.Model.extend({

        /**
         * Caches a collection of related beans in this bean instance.
         * @param {String} link Relationship link name.
         * @param collection A collection of related beans to cache.
         * @private
         */
        _setRelatedCollection: function(link, collection) {
            _relatedCollections = _relatedCollections || {};
            _relatedCollections[link] = collection;
        },

        /**
         * Gets a collection of related beans.
         *
         * This method returns a cached in memory instance of the collection. If the collection doesn't exist in the cache,
         * it will be created using {@link Data.DataManager#createRelatedCollection} method.
         * Use {@link Data.DataManager#createRelatedCollection} method to get a new instance of a related collection.
         *
         * <pre><code>
         * // Get a cached copy or create contacts collection for an existing opportunity.
         * var contacts = opportunity.getRelatedCollection("contacts");
         * contacts.fetch({ relate: true });
         * </code></pre>
         *
         * @param {String} link Relationship link name.
         * @return {Data.BeanCollection} Previously created collection or a new collection of related beans.
         */
        getRelatedCollection: function(link) {
            if (_relatedCollections && _relatedCollections[link]) {
                return _relatedCollections[link];
            }

            return app.data.createRelatedCollection(this, link);
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
            if (!this.skipValidation) {
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
            } else {
                this.skipValidation = false;
                return false;
            }

            return this.processValidationErrors(errors);
        },
        /**
         * Validates attributes for required fields
         * @param {Object} attrs
         * @return {Object}
         */
        validateRequired: function(attrs) {

            var errors = {}, self = this;
            var field, value, result, validator;

            _.each(_.keys(self.fields), function(fieldName) {
                field = self.fields[fieldName];
                value = attrs[fieldName];
                result = app.validation.requiredValidator(field, field.name, self, value);
                _addValidationError(errors, result, fieldName, "required");
            });

            if (!_.isEmpty(errors)) {
                return this.processValidationErrors(errors);
            }

        },

        /**
         * Processes generic validation errors and triggers model events
         * @param {Object} errors
         * @return {Object} errors
         */
        processValidationErrors: function(errors) {
            // "validate" method should not return anything in case there are no validation errors
            if (!_.isEmpty(errors)) {
                app.error.handleValidationError(this, errors);
                var self = this;
                _.each(errors, function(fieldErrors, fieldName) {
                    self.trigger("model.validation.error." + fieldName, fieldErrors);
                });
                this.trigger('model.validation.disableSave');
                // trigger error events on this object
                return errors;
            } else {
                this.trigger('model.validation.enableSave');
            }
        },

        /**
         * Overloads standard bean save so we can run required field validation outside of the standard validation loop
         * @param {Object} attributes model attributes
         * @param {Object} options standard save options as described by Backbone docs
         */
        save: function(attributes, options) {
            if (!this.validateRequired(this.attributes)) {
                Backbone.Model.prototype.save.call(this, attributes, options);
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