/**
 * Manages bean model and collection classes.
 *
 * **DataManager provides:**
 *
 * - Interface to declare bean model and collection classes from metadata.
 * - Factory methods for creating instances of beans and bean collections.
 * - Factory methods for creating instances of bean relations and relation collections.
 * - Custom implementation of <code>Backbone.sync</code> pattern.
 *
 * <pre><code>
 * // From the following sample metadata, data manager would declare two classes: Accounts and Contacts.
 * var metadata =
 * {
 *   "modules": {
 *     "Accounts": {
 *        "fields": {},
 *        "relationships": {}
 *      },
 *      "Contacts": {}
 *    }
 * }
 *
 * // Declare bean classes from metadata payload.
 * // This method should be called at application start-up and whenever the metadata changes.
 * SUGAR.App.dataManager.declareModels(metadata);
 * // You may now create bean instances using factory methods.
 * var account = SUGAR.App.dataManager.createBean("Accounts", { name: "Acme" });
 * // You can save a bean using standard Backbone.Model.save method.
 * // The save method will use dataManager's sync method to communicate changes to the remote server.
 * account.save();
 *
 * // Create an empty collection of contacts.
 * var contacts = SUGAR.App.dataManager.createBeanCollection("Contacts");
 * // Fetch a list of contacts
 * contacts.fetch();
 * </code></pre>
 *
 * TODO: Document relationship management.
 *
 * @class DataManager
 * @alias SUGAR.App.dataManager
 * @singleton
 */
(function(app) {

    // Bean class cache
    var _models = {};
    // Bean collection class cache
    var _collections = {};

    var _serverProxy;
    var _dataManager = {

        /**
         * Reference to the base bean model class. Defaults to {@link Bean}.
         * @property {Bean}
         */
        beanModel: app.Bean,
        /**
         * Reference to the base bean collection class. Defaults to {@link BeanCollection}.
         * @property {BeanCollection}
         */
        beanCollection: app.BeanCollection,

        /**
         * Initializes data manager.
         * @method
         */
        init: function() {
            _serverProxy = app.api;
            // Backbone.js sync methods correspond to Sugar API functions except "read/get" :)
            _serverProxy.read = function(model, attributes, params, callbacks) {
                return this.get(model, attributes, params, callbacks);
            };

            Backbone.sync = this.sync;
        },

        /**
         * Resets class declarations.
         * @param {String} module(optional) module name. If not specified, resets models of all modules.
         * @method
         */
        reset: function(module) {
            if (module) {
                delete _models[module];
                delete _collections[module];
            }
            else {
                _models = {};
                _collections = {};
            }
        },

        /**
         * Declares bean model and collection classes for a given module.
         * @param {String} moduleName module name.
         * @param module module metadata object.
         * @method
         */
        declareModel: function(moduleName, module) {
            this.reset(moduleName);

            var fields = module.fields;
            var relationships = module.relationships;
            var defaults = null;

            _.each(_.values(fields), function(field) {
                if (!_.isUndefined(field["default"])) {
                    if (defaults === null) {
                        defaults = {};
                    }
                    defaults[field.name] = field["default"];
                }
            });

            var model = this.beanModel.extend({
                defaults: defaults,
                /**
                 * TODO: Documentation required
                 * @member Bean
                 * @property {Object}
                 *
                 */
                sugarFields: {},
                /**
                 * Module name.
                 * @member Bean
                 * @property {String}
                 */
                module: moduleName,
                /**
                 * Vardefs metadata.
                 * @member Bean
                 * @property {Object}
                 */
                fields: fields,
                /**
                 * Relationships metadata.
                 * @member Bean
                 * @property {Object}
                 */
                relationships: relationships
            });

            _collections[moduleName] = this.beanCollection.extend({
                model: model,
                /**
                 * Module name.
                 * @member BeanCollection
                 * @property {String}
                 */
                module: moduleName,
                /**
                 * Pagination offset.
                 * @member BeanCollection
                 * @property {Number}
                 */
                offset: 0
            });

            _models[moduleName] = model;
        },

        /**
         * Declares bean models and collections classes for each module definition.
         * @param metadata metadata hash in which keys are module names and values are module definitions.
         */
        declareModels: function(metadata) {
            this.reset();
            _.each(metadata.modules, function(module, name) {
                this.declareModel(name, module);
            }, this);
        },

        /**
         * Creates instance of a bean.
         * <pre>
         * // Create an account bean. The account's name property will be set to "Acme".
         * var account = SUGAR.App.dataManager.createBean("Accounts", { name: "Acme" });
         *
         * // Create a team set bean with a given ID
         * var teamSet = SUGAR.App.dataManager.createBean("TeamSets", { id: "xyz" });
         * </pre>
         * @param {String} module Sugar module name.
         * @param attrs(optional) initial values of bean attributes, which will be set on the model.
         * @return {Bean} A new instance of bean model.
         */
        createBean: function(module, attrs) {
            return new _models[module](attrs);
        },

        /**
         * Creates instance of a bean collection.
         * <pre><code>
         * // Create an empty collection of account beans.
         * var accounts = SUGAR.App.dataManager.createBeanCollection("Accounts");
         * </code></pre>
         * @param {String} module Sugar module name.
         * @param {Bean[]} models(optional) initial array or collection of models.
         * @param {Object} options(optional) options hash.
         * @return {BeanCollection} A new instance of bean collection.
         */
        createBeanCollection: function(module, models, options) {
            return new _collections[module](models, options);
        },

        /**
         * Creates an instance of related {@link Bean} or updates an existing bean with link information.
         *
         * <pre><code>
         * // Create a new contact related to the given opportunity.
         * var contact = SUGAR.App.dataManager.createRelatedBean(opportunity, "1", "contacts", {
         *    "first_name": "John",
         *    "last_name": "Smith",
         *    "contact_role": "Decision Maker"
         * });
         * contact.save();
         * </code></pre>
         *
         * @param {Bean} bean1 instance of the first bean
         * @param {Bean/String} beanOrId2 instance or ID of the second bean. A new instance is created if this parameter is <code>null</code>
         * @param {String} link relationship link name
         * @param {Object} attrs(optional) bean attributes hash
         * @return {Bean} a new instance of the related bean
         */
        createRelatedBean: function(bean1, beanOrId2, link, attrs) {
            var name = bean1.fields[link].relationship;
            var relationship = bean1.relationships[name];
            var relatedModule = bean1.module == relationship.lhs_module ? relationship.rhs_module : relationship.lhs_module;

            attrs = attrs || {};
            if (_.isString(beanOrId2)) {
                attrs.id = beanOrId2;
                beanOrId2 = this.createBean(relatedModule, attrs);
            }
            else if (_.isNull(beanOrId2)) {
                beanOrId2 = this.createBean(relatedModule, attrs);
            }
            else {
                beanOrId2.set(attrs);
            }

            /**
             * Link information.
             *
             * <pre>
             * <code>
             * {
             *   name: link name,
             *   bean: reference to the related bean
             * }
             * </code>
             * </pre>
             *
             * @member Bean
             */
            beanOrId2.link = {
                name: link,
                bean: bean1
            };

            return beanOrId2;
        },

        /**
         * Creates an instance of {@link BeanCollection} class of related beans.
         *
         * <pre><code>
         * // Create contacts collection for an opportunity.
         * var contact = SUGAR.App.dataManager.createRelatedCollection(opportunity, "contacts");
         * contacts.fetch();
         * </code></pre>
         *
         * @param {Bean} bean the related beans are linked to the specified bean
         * @param {String} link relationship link name
         * @return {BeanCollection} a new instance of the bean collection
         */
        createRelatedCollection: function(bean, link) {
            var name = bean.fields[link].relationship;
            var relationship = bean.relationships[name];
            var relatedModule = relationship.lhs_module == bean.module ? relationship.rhs_module : relationship.lhs_module;
            return this.createBeanCollection(relatedModule, undefined, {
                /**
                 * Link information.
                 *
                 * <pre>
                 * <code>
                 * {
                 *   name: link name,
                 *   bean: reference to the related bean
                 * }
                 * </code>
                 * </pre>
                 *
                 * @member BeanCollection
                 */
                link: {
                    name: link,
                    bean: bean
                }
            });
        },

        /**
         * Custom implementation of <code>Backbone.sync</code> pattern. Syncs models with remote server using Sugar.Api lib.
         * @param {String} method the CRUD method (<code>"create", "read", "update", or "delete"</code>)
         * @param {Bean/BeanCollection} model the model to be saved (or collection to be read)
         * @param options(optional) success and error callbacks, and all other Sugar.Api request options
         */
        sync: function(method, model, options) {

            app.logger.trace('remote-sync-' + method + ": " + model);

            options = options || {};
            options.params = options.params || [];

            if ((method == "read") && (model instanceof app.BeanCollection)) {
                if (options.offset && options.offset !== 0) {
                    options.params.push({key: "offset", value: options.offset});
                }

                if (app.config && app.config.maxQueryResult) {
                    options.params.push({key: "maxresult", value: app.config.maxQueryResult});
                }
            }

            var success = function(data) {
                if (options.success) {
                    if ((method == "read") && (model instanceof app.BeanCollection)) {
                        if (data.next_offset) {
                            model.offset = data.next_offset;
                            model.page = model.getPageNumber();
                        }
                        // TODO: Hack to overcome wrong response format of get-relations request until fixed
                        data = data.records ? data.records : data;
                    }
                    options.success(data);
                }
            };

            var callbacks = {
                success: success,
                error: options.error
            };

            if ((method == "read") && (model instanceof app.BeanCollection) && (model.link)) {
                _serverProxy.getRelations(model.link.bean.module, model.link.bean.id, model.link.name, options.params, callbacks);
            }
            else if (model instanceof app.Bean || model instanceof app.BeanCollection) {
                _serverProxy[method](model.module, model.attributes, options.params, callbacks);
            }
            else if (options.relate) {
                // TODO: Implement create/Delete relationships once the API is spec'ed out
            }

        }
    };

    app.augment("dataManager", _.extend(_dataManager, Backbone.Events), false);

})(SUGAR.App);

