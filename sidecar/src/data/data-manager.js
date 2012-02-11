(function(app) {

    // Class cache:
    // _models[module].primaryBean - primary bean class name
    // _models[module].beans - hash of bean models
    // _models[module].collections - hash of bean collections
    var _models;

    function _addValidation(validations, attribute, validation) {
        if (_.isUndefined(validations[attribute])) validations[attribute] = [];
        validations[attribute].push(validation);
    }

    /**
     * Manages bean models and provides Backbone sync pattern.
     */
    app.augment("dataManager", {

        init: function() {
            Backbone.sync = this.sync;
        },

        /**
         * Resets class declarations.
         * @param module Optional module name. If not specified, resets models of all modules.
         */
        reset: function(module) {
            if (module) {
                _models[module] = {};
            }
            else {
                _models = {};
            }
        },

        /**
         * Declares models for a given module.
         * @param moduleName Module name.
         * @param module Module metadata object.
         */
        declareModel: function(moduleName, module) {
            var defaults, model, beans, vardefs, vardef, validations, fields;

            this.reset(moduleName);

            _models[moduleName].primaryBean = module["primary_bean"];
            _models[moduleName].beans = {};
            _models[moduleName].collections = {};
            beans = module["beans"];

            _.each(_.keys(beans), function(beanType) {
                // TODO: Initialize defaults by processing vardefs
                defaults = null;
                vardefs = beans[beanType]["vardefs"];
                fields = vardefs.fields;

                // Build validations
                if (fields) {
                    validations = {};
                    _.each(_.keys(fields), function(field) {
                        vardef = fields[field];
                        if (!_.isUndefined(vardef.required) && (vardef.required === true)) {
                            _addValidation(validations, field, app.validation.createValidator("required", field, true));
                        }

                        if (_.isNumber(vardef.maxLength)) {
                            _addValidation(validations, field, app.validation.createValidator("maxLength", field, vardef.maxLength));
                        }
                    });

                    if (_.isEmpty(validations)) validations = null;
                }

                model = app.Bean.extend({
                    module:      moduleName,
                    beanType:    beanType,
                    defaults:    defaults,
                    validations: validations
                });

                _models[moduleName].collections[beanType] = app.BeanCollection.extend({
                    model:    model,
                    module:   moduleName,
                    beanType: beanType
                });

                _models[moduleName].beans[beanType] = model;
            }, this);
        },

        /**
         * Declares bean models for each module definition.
         * @param metadata Metadata hash in which keys are module names and values are module definitions.
         *
         * IMPORTANT:
         * Each module may have multiple bean types.
         * We declare a class for each of the bean type.
         */
        declareModels: function(metadata) {
            this.reset();
            _.each(_.keys(metadata), function(moduleName) {
                this.declareModel(moduleName, metadata[moduleName]);
            }, this);

        },

        /**
         * Creates a new instance of a bean.
         * @param module Sugar module name.
         * @param attrs Bean attributes. See Backbone.Model documentation for details.
         * @param beanType Optional bean type. If not specified, an instance of primary bean type is returned.
         * @return A new instance of bean model.
         */
        createBean: function(module, attrs, beanType) {
            beanType = beanType || _models[module].primaryBean;
            return new _models[module].beans[beanType](attrs);
        },

        /**
         * Creates a new instance of bean collection.
         * @param module Sugar module name.
         * @param models See Backbone.Collection documentation for details.
         * @param options See Backbone.Collection documentation for details.
         * @param beanType Optional bean type. If not specified, a collection of primary bean types is returned.
         * @return A new instance of the bean collection.
         */
        createBeanCollection: function(module, models, options, beanType) {
            beanType = beanType || _models[module].primaryBean;
            return new _models[module].collections[beanType](models, options);
        },

        fetchBean: function(module, id, options, beanType) {
            var bean = this.createBean(module, null, beanType);
            bean.fetch(options);
            return bean;
        },

        fetchBeans: function(module, options, beanType) {
            var collection = this.createBeanCollection(module, null, options, beanType);
            collection.fetch(options);
            return collection;
        },

        /**
         * Custom implementation of Backbone.sync pattern.
         * @param method
         * @param model
         * @param options
         */
        sync: function(method, model, options) {
            // TODO: Implement
            // This method should sync beans with local storage (if it's enabled) and fall back to the REST API.
            app.logger.trace('sync called:' + method);
        }

    }, true);

})(SUGAR.App);

