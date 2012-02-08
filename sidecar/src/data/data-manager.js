(function(app) {

    var _primaries, _models, _collections;

    /**
     * Manages bean models and provides Backbone sync pattern.
     */
    app.augment("dataManager", {

        /*
         Declares bean models for each module definition.
         Sample metadata payload for a single module:
         {
            "Teams": {
                "primary_bean": "Team",
                "beans":        {
                    "Team":    {},
                    "TeamSet": {}
                }
            }
         }
         IMPORTANT:
         Each module may have multiple object types.
         We declare a class for each of the object type.
         */
        declareModels: function(metadata) {
            var defaults, module, model, beans;

            _primaries = {};
            _models = {};
            _collections = {};

            _.each(_.keys(metadata), function(moduleName) {
                module = metadata[moduleName];
                _primaries[moduleName] = module["primary_bean"];

                beans = module["beans"];
                _.each(_.keys(beans), function(beanType) {
                    // TODO: Initialize defaults by processing vardefs
                    defaults = null;

                    model = app.Bean.extend({
                        module:   moduleName,
                        beanType: beanType,
                        vardefs:  beans[beanType]["vardefs"],
                        defaults: defaults
                    });
                    _models[beanType] = model;

                    _collections[beanType] = app.BeanCollection.extend({
                        model:    model,
                        module:   moduleName,
                        beanType: beanType
                    });
                });

            });

        },

        /**
         * Creates a new instance of a bean.
         * @param module
         * @param attrs
         * @param beanType
         */
        createBean: function(module, attrs, beanType) {
            beanType = beanType || _primaries[module];
            return new _models[beanType](attrs);
        },

        /**
         * Creates a new instance of a bean collection.
         * @param module Module name
         * @param models
         * @param options
         * @param beanType
         */
        createBeanCollection: function(models, options, module, beanType) {
            beanType = beanType || _primaries[module];
            return new _collections[beanType](models, options);
        },

        sync: function(method, model, options) {
            // TODO: Implement
            // This method should sync beans with local storage (if it's enabled) and fall back to the REST API.
        }

    }, false);

})(SUGAR.App);

