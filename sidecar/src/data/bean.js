/**
 * Base bean class. Use {@link Data.DataManager} to create instances of beans.
 *
 * **CRUD**
 *
 * Use standard Backbone's `fetch`, `save`, and `destroy`
 * methods to perform CRUD operations on beans. See {@link Data.DataManager} class for details.
 *
 * **Validation**
 *
 * This class does not override Backbone.Model's `validate` method.
 * The validation is done in `save` method. If the bean is invalid the save is rejected.
 * Use {@link Data.Bean#isValid} method to check if the bean is valid in other situations.
 * Failed validations trigger an `"app:error:validation:<field-name>"` event.
 *
 * @class Data.Bean
 * @extends Backbone.Model
 * @alias SUGAR.App.Bean
 */
(function(app) {

    app.augment("Bean", Backbone.Model.extend({

        initialize: function(attributes){
            Backbone.Model.prototype.initialize.call(this, attributes);
            this._relatedCollections = null;
        },

        /**
         * Caches a collection of related beans in this bean instance.
         * @param {String} link Relationship link name.
         * @param collection A collection of related beans to cache.
         * @private
         */
        _setRelatedCollection: function(link, collection) {
            if (!this._relatedCollections) this._relatedCollections = {};
            this._relatedCollections[link] = collection;
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
            if (this._relatedCollections && this._relatedCollections[link]) {
                return this._relatedCollections[link];
            }

            return app.data.createRelatedCollection(this, link);
        },

        /**
         * Checks if a bean is valid.
         *
         * This method is called before {@link Data.Bean#save}.
         * Failed validations trigger an `"error:validation:<field-name>"` event.
         *
         * @param {Array} fields(optional) A hash of fields to validate.
         * If not specified, all fields will be validated. View-agnostic validation will be run.
         * Keys are field names, values are field definitions (combination of view defs and vardefs).
         * @return {Boolean} Flag indicating if this bean is valid or not.
         */
        isValid: function(fields) {
            var errors = this._doValidate(fields);
            return this._processValidationErrors(errors);
        },

        /**
         * Validates a bean.
         *
         * @param {Array} fields(optional) A hash of fields to validate. See {@link Data.Bean#isValid} method for details.
         * @return {Object} validation errors object.
         *
         * - keys: field names, values: errors hash
         * - errors hash is a collection of error definitions
         * - error definition can be a primitive type or an object. It depends on validator.
         *
         * Example:
         * <pre><code>
         * {
         *    first_name: {
         *       maxLength: 20,
         *       someOtherValidatorName: { some complex error definition... }
         *    },
         *    last_name: {
         *       required: true
         *    }
         * }
         * </code></pre>
         *
         * @private
         */
        _doValidate: function(fields) {
            var value, errors = {};
            _.each(fields || this.fields, function(field, fieldName) {
                value = this.get(fieldName);

                _addValidationError(errors,
                    app.validation.requiredValidator(field, field.name, this, value), fieldName, "required");

                if (value) {
                    _.each(app.validation.validators, function(validator, validatorName) {
                        _addValidationError(errors, validator(field, value), fieldName, validatorName);
                    });
                }
            }, this);

            return errors;
        },

        /**
         * Processes validation errors and triggers validation error events.
         * @param {Object} errors validation errors.
         * @return {Boolean} `true` if `errors` parameter is empty, otherwise `false`.
         * @private
         */
        _processValidationErrors: function(errors) {
            var isValid = true;
            if (!_.isEmpty(errors)) {
                app.error.handleValidationError(this, errors);
                _.each(errors, function(fieldErrors, fieldName) {
                    this.trigger("error:validation:" + fieldName, fieldErrors);
                }, this);
                this.trigger("error:validation");
                isValid = false;
            }

            return isValid;
        },

        /**
         * Overloads standard bean save so we can run validation outside of the standard validation loop.
         *
         * This method checks if this bean is valid if `options` hash contains `fieldsToValidate` parameter.
         *
         * @param {Object} attributes(optional) model attributes
         * @param {Object} options(optional) standard save options as described by Backbone docs and
         * optional `fieldsToValidate` parameter.
         */
        save: function(attributes, options) {
            var isValid = true;
            if (options && options.fieldsToValidate) {
                isValid = this.isValid(options.fieldsToValidate);
            }

            return isValid ? Backbone.Model.prototype.save.call(this, attributes, options) : false;
        },

        /**
         * Uploads a file.
         * @param {String} fieldName Name of the file field.
         * @param $files List of DOM elements that contain file inputs.
         * @param callbacks(options) Callback hash.
         * @param options(optional) Upload options. See {@link SUGAR.Api#file} method for details.
         * @return {Object} XHR object.
         */
        uploadFile: function(fieldName, $files, callbacks, options) {
            callbacks = callbacks || {};

            return app.api.file(
                'create',
                {
                    id: this.id,
                    module: this.module,
                    field: fieldName
                },
                $files,
                {
                    success: function(data, textStatus, xhr) {
                        var rspField = data[fieldName];
                        if (rspField) {
                            if (callbacks.success) callbacks.success(data);
                        } else {
                            var error = new SUGAR.HttpError(xhr);
                            error.responseText = data.xhr ? data.xhr.message : error.responseText;
                            if (callbacks.error) callbacks.error(error);
                        }
                    }
                },
                options
            );
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
     * Adds validation error to the passed in errorr object.
     * @param {Object} errors
     * @param {Object} result
     * @param {String} fieldName
     * @param {String} validatorName
     * @private
     * @ignore
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