/**
 * Manages bean model and collection classes.
 *
 * **DataManager provides:**
 *
 * - Interface to declare bean model and collection classes from metadata.
 * - Factory methods for creating instances of beans and bean collections.
 * - Custom implementation of <code>Backbone.sync</code> pattern.
 *
 * <pre>
 * // From the following sample metadata, data manager would declare two classes: Team and TeamSet.
 * var metadata =
 * {
 *   "Teams": {
 *      "primary_bean": "Team",
 *      "beans": {
 *        "Team": {
 *          "vardefs": {
 *            "fields": {}
 *          }
 *        },
 *        "TeamSet": {
 *          "vardefs": {
 *            "fields": {}
 *          }
 *        }
 *      }
 *    }
 * }
 *
 * // Declare bean classes from metadata payload.
 * // This method should be called at application start-up and whenever the metadata changes.
 * SUGAR.App.dataManager.declareModels(metadata);
 *
 * // You may now create bean instances using factory methods.
 * // Create an instance of primary bean.
 * var team = SUGAR.App.dataManager.createBean("Teams", { name: "Acme" });
 * // Create an instance of specific bean type.
 * var teamSet = SUGAR.App.dataManager.createBean("Teams", { name: "Acme" }, "TeamSet");
 * // Create an empty collection of team sets.
 * var teamSets = SUGAR.App.dataManager.createBeanCollection("Teams", null, "TeamSet");
 *
 * // You can save a bean using standard Backbone.Model.save method.
 * // The save method will use dataManager's sync method to communicate chages to the remote server.
 * team.save();
 *
 * </pre>
 *
 * @class dataManager
 * @alias SUGAR.App.dataManager
 * @singleton
 */
(function(app) {

    //
    // Class cache:
    // _models[module].primaryBean - primary bean class name
    // _models[module].beans - hash of bean models
    // _models[module].collections - hash of bean collections
    var _models;
    var _serverProxy = SUGAR.Api.getInstance();
    // Backbone.js sync methods correspond to Sugar API functions except "read/get" :)
    _serverProxy.read = function(model, attributes, params, callbacks) {
        return this.get(model, attributes, params, callbacks);
    }

    var _dataManager = {

        /**
         * Reference to the base bean model class. Defaults to {@link Bean}.
         * @type {Bean} [beanModel=
         */
        beanModel: app.Bean,
        /**
         * Reference to the base bean collection class. Defaults to {@link BeanCollection}.
         * @type {BeanCollection}
         */
        beanCollection: app.BeanCollection,

        /**
         * Initializes data manager.
         */
        init: function() {
            Backbone.sync = this.sync;
            app.events.publish("dataManager:ready", this);
            app.logger.trace("DataManager initialized");
        },

        /**
         * Resets class declarations.
         * @param {String} module(optional) module name. If not specified, resets models of all modules.
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
         * Declares bean model and collection classes for a given module.
         * @param {String} moduleName module name.
         * @param module module metadata object.
         */
        declareModel: function(moduleName, module) {
            this.reset(moduleName);

            _models[moduleName].primaryBean = module["primary_bean"];
            _models[moduleName].beans = {};
            _models[moduleName].collections = {};
            var beans = module["beans"];

            _.each(_.keys(beans), function(beanType) {
                var vardefs = beans[beanType]["vardefs"];
                var fields = vardefs.fields;
                var relationships = beans[beanType]["relationships"];

                var defaults = null;
                _.each(_.values(fields), function(field) {
                    if (!_.isUndefined(field["default"])) {
                        if (defaults == null) {
                            defaults = {};
                        }
                        defaults[field.name] = field["default"];
                    }
                });

                var model = this.beanModel.extend({
                    defaults: defaults,
                    /**
                     * Module name.
                     * @member Bean
                     * @type {String}
                     */
                    module: moduleName,
                    /**
                     * Bean type.
                     * @member Bean
                     * @type {String}
                     */
                    beanType: beanType,
                    /**
                     * Vardefs metadata.
                     * @member Bean
                     */
                    fields: fields,
                    /**
                     * Relationships metadata.
                     * @member Bean
                     */
                    relationships: relationships
                });

                _models[moduleName].collections[beanType] = this.beanCollection.extend({
                    model: model,
                    /**
                     * Module name.
                     * @member BeanCollection
                     * @type {String}
                     */
                    module: moduleName,
                    /**
                     * Bean type.
                     * @member BeanCollection
                     * @type {String}
                     */
                    beanType: beanType
                });

                _models[moduleName].beans[beanType] = model;
            }, this);
        },

        /**
         * Declares bean models and collections classes for each module definition.
         *
         * **IMPORTANT:**
         *
         * Each module may have multiple bean types.
         * We declare a class for each bean type.
         * <pre>
         * {
         *   "Teams": {
         *      "primary_bean": "Team",
         *      "beans": {
         *        "Team": {
         *          "vardefs": {
         *            "fields": {}
         *          }
         *        },
         *        "TeamSet": {
         *          "vardefs": {
         *            "fields": {}
         *          }
         *        }
         *      }
         *    }
         * }
         * </pre>
         *
         * @param metadata metadata hash in which keys are module names and values are module definitions.
         */
        declareModels: function(metadata) {
            this.reset();
            _.each(_.keys(metadata), function(moduleName) {
                this.declareModel(moduleName, metadata[moduleName]);
            }, this);
            this.trigger("dataManager:ready");
        },

        /**
         * Creates instance of a bean.
         * <pre>
         * // Create an account bean. The account's name property will be set to "Acme".
         * var account = SUGAR.App.dataManager.createBean("Accounts", { name: "Acme" });
         *
         * // Create a team set bean with a given ID
         * var teamSet = SUGAR.App.dataManager.createBean("Teams", { id: "xyz" }, "TeamSet");
         * </pre>
         * @param {String} module Sugar module name.
         * @param attrs(optional) initial values of bean attributes, which will be set on the model.
         * @param {String} beanType(optional) bean type. If not specified, an instance of primary bean type is returned.
         * @return {Bean} A new instance of bean model.
         */
        createBean: function(module, attrs, beanType) {
            beanType = beanType || _models[module].primaryBean;
            return new _models[module].beans[beanType](attrs);
        },

        /**
         * Creates instance of a bean collection.
         * <pre>
         * // Create an empty collection of account beans.
         * var accounts = SUGAR.App.dataManager.createBeanCollection("Accounts");
         *
         * // Create an empty collection of team set beans.
         * var teamSets = SUGAR.App.dataManager.createBeanCollection("Teams", null, "TeamSet");
         * </pre>
         * @param {String} module Sugar module name.
         * @param {Bean[]} models(optional) initial array of models.
         * @param {String} beanType(optional) bean type. If not specified, a collection of primary bean types is returned.
         * @return {BeanCollection} A new instance of bean collection.
         */
        createBeanCollection: function(module, models, beanType) {
            beanType = beanType || _models[module].primaryBean;
            return new _models[module].collections[beanType](models);
        },

        fetchBean: function(module, id, options, beanType) {
            var bean = this.createBean(module, { id: id }, beanType);
            bean.fetch(options);
            return bean;
        },

        fetchBeans: function(module, options, beanType) {
            var collection = this.createBeanCollection(module, null, beanType);
            collection.fetch(options);
            return collection;
        },

        /**
         * Custom implementation of <code>Backbone.sync</code> pattern. Syncs models with remote server using Sugar.Api lib.
         * @param {String} method the CRUD method (<code>"create", "read", "update", or "delete"</code>)
         * @param {Bean/BeanCollection/Relation} model the model to be saved (or collection to be read)
         * @param options(optional) success and error callbacks, and all other Sugar.Api request options
         */
        sync: function(method, model, options) {
            app.logger.trace('remote-sync-' + method + ": " + model);

            var callbacks = {
                success: options ? options.success : null,
                error: options ? options.error : null
            };

            var params = options ? options.params : null;

            if (model instanceof app.Bean || model instanceof app.BeanCollection) {
                _serverProxy[method](model.module, model.attributes, params, callbacks);
            }
            else {
                // TODO: Deal with relationships
            }

        }

    };

    app.augment("dataManager", _.extend(_dataManager, Backbone.Events), false);

})(SUGAR.App);

