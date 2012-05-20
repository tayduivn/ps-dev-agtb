/**
 * Manages bean model and collection classes.
 *
 * **Data manager provides:**
 *
 * - Interface to declare bean model and collection classes from metadata.
 * - Factory methods for creating instances of beans and bean collections.
 * - Factory methods for creating instances of bean relations and relation collections.
 * - Custom implementation of <code>Backbone.sync</code> pattern.
 *
 * **Data model metadata**
 *
 * Metadata that describes the data model contains information about module fields and its relationships.
 * From the following sample metadata, data manager would declare two classes: Opportunities and Contacts.
 * <pre><code>
 * var metadata =
 * {
 *   "modules": {
 *     "Opportunities": {
 *        "fields": {
 *            "name": { ... },
 *            ...
 *        },
 *        "relationships": {
 *             "opportunities_contacts": { ... },
 *             ...
 *        }
 *      },
 *      "Contacts": { ... }
 *    }
 * }
 * </code></pre>
 *
 * **Working with beans**
 *
 * <pre><code>
 * // Declare bean classes from metadata payload.
 * // This method should be called at application start-up and whenever the metadata changes.
 * SUGAR.App.data.declareModels(metadata);
 * // You may now create bean instances using factory methods.
 * var opportunity = SUGAR.App.data.createBean("Opportunities", { name: "Cool opportunity" });
 * // You can save a bean using standard Backbone.Model.save method.
 * // The save method will use data manager's sync method to communicate changes to the remote server.
 * opportunity.save();
 *
 * // Create an empty collection of contacts.
 * var contacts = SUGAR.App.data.createBeanCollection("Contacts");
 * // Fetch a list of contacts
 * contacts.fetch();
 * </code></pre>
 *
 * **Working with relationships**
 *
 * <pre><code>
 * var attrs = {
 *   firstName: "John",
 *   lastName: "Smith",
 *   // relationship field
 *   opportunityRole: "Influencer"
 * }
 * // Create a new instance of a contact related to an existing opportunity
 * var contact = SUGAR.App.data.createRelatedBean(opportunity, null, "contacts", attrs);
 * // This will save the contact and create the relationship
 * contact.save(null, { relate: true });
 *
 * // Create an instance of contact collection related to an existing opportunity
 * var contacts = SUGAR.App.data.createRelatedCollection(opportunity, "contacts");
 * // This will fetch related contacts
 * contacts.fetch({ relate: true });
 *
 * </code></pre>
 *
 * @class Data.DataManager
 * @alias SUGAR.App.data
 * @singleton
 */
(function(app) {

    // Bean class cache
    var _models = {};
    // Bean collection class cache
    var _collections = {};

    var _dataManager = {

        /**
         * Reference to the base bean model class. Defaults to {@link Data.Bean}.
         * @property {Data.Bean}
         */
        beanModel: app.Bean,
        /**
         * Reference to the base bean collection class. Defaults to {@link Data.BeanCollection}.
         * @property {Data.BeanCollection}
         */
        beanCollection: app.BeanCollection,

        /**
         * Initializes data manager.
         * @method
         */
        init: function() {
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
                 * Module name.
                 * @member Data.Bean
                 * @property {String}
                 */
                module: moduleName,
                /**
                 * Vardefs metadata.
                 * @member Data.Bean
                 * @property {Object}
                 */
                fields: fields,
                /**
                 * Relationships metadata.
                 * @member Data.Bean
                 * @property {Object}
                 */
                relationships: relationships
            });

            _collections[moduleName] = this.beanCollection.extend({
                model: model,
                /**
                 * Module name.
                 * @member Data.BeanCollection
                 * @property {String}
                 */
                module: moduleName,
                /**
                 * Pagination offset.
                 * @member Data.BeanCollection
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
            _.each(metadata.modules, function(module, name) {
                this.declareModel(name, module);
            }, this);
        },

        /**
         * Creates instance of a bean.
         * <pre>
         * // Create an account bean. The account's name property will be set to "Acme".
         * var account = SUGAR.App.data.createBean("Accounts", { name: "Acme" });
         *
         * // Create a team set bean with a given ID
         * var teamSet = SUGAR.App.data.createBean("TeamSets", { id: "xyz" });
         * </pre>
         * @param {String} module Sugar module name.
         * @param attrs(optional) initial values of bean attributes, which will be set on the model.
         * @return {Data.Bean} A new instance of bean model.
         */
        createBean: function(module, attrs) {
            return _models[module] ?  new _models[module](attrs) : new Backbone.Model();
        },

        /**
         * Creates instance of a bean collection.
         * <pre><code>
         * // Create an empty collection of account beans.
         * var accounts = SUGAR.App.data.createBeanCollection("Accounts");
         * </code></pre>
         * @param {String} module Sugar module name.
         * @param {Data.Bean[]} models(optional) initial array or collection of models.
         * @param {Object} options(optional) options hash.
         * @return {Data.BeanCollection} A new instance of bean collection.
         */
        createBeanCollection: function(module, models, options) {
            return _collections[module] ? 
                new _collections[module](models, options) : 
                new Backbone.Collection();
        },

        /**
         * Creates an instance of related {@link Data.Bean} or updates an existing bean with link information.
         *
         * <pre><code>
         * // Create a new contact related to the given opportunity.
         * var contact = SUGAR.App.data.createRelatedBean(opportunity, "1", "contacts", {
         *    "first_name": "John",
         *    "last_name": "Smith",
         *    "contact_role": "Decision Maker"
         * });
         * contact.save();
         * </code></pre>
         *
         * @param {Data.Bean} bean1 instance of the first bean
         * @param {Data.Bean/String} beanOrId2 instance or ID of the second bean. A new instance is created if this parameter is <code>null</code>
         * @param {String} link relationship link name
         * @param {Object} attrs(optional) bean attributes hash
         * @return {Data.Bean} a new instance of the related bean or existing bean instance updated with relationship link information.
         */
        createRelatedBean: function(bean1, beanOrId2, link, attrs) {
            var relatedModule = this.getRelatedModule(bean1.module, link);

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
             * Relationship link information.
             *
             * <pre>
             * {
             *   name: link name,
             *   bean: reference to the related bean
             *   isNew: flag indicating that it is a new relationship
             * }
             * </pre>
             *
             * The `link.isNew` flag is used to distinguish between an existing relationship and a relationship
             * that is about to be created. Please, refer to REST API specification for details.
             * In brief, REST API supports creating a new relationship for two existing records as well as
             * updating an existing relationship (updating relationship fields).
             * The `link.isNew` flag equals to `true` by default. The flag is set to `false` by data manager
             * once a relationship is created and whenever relationships are fetched from the server.
             *
             * @member Data.Bean
             */
            beanOrId2.link = {
                name: link,
                bean: bean1,
                isNew: true
            };

            return beanOrId2;
        },

        /**
         * Creates a new instance of related beans collection.
         *
         * <pre><code>
         * // Create contacts collection for an existing opportunity.
         * var contacts = SUGAR.App.data.createRelatedCollection(opportunity, "contacts");
         * contacts.fetch({ relate: true });
         * </code></pre>
         *
         * The newly created collection is cached in the given bean instance.
         *
         * @param {Data.Bean} bean the related beans are linked to the specified bean
         * @param {String} link relationship link name
         * @param {Array/Data.BeanCollection} models(optional) an array of related beans to populate the newly created collection with
         * @return {Data.BeanCollection} a new instance of the bean collection
         */
        createRelatedCollection: function(bean, link, models) {
            var relatedModule = this.getRelatedModule(bean.module, link);
            var collection = this.createBeanCollection(relatedModule, models, {
                /**
                 * Link information.
                 *
                 * <pre>
                 * {
                 *   name: link name,
                 *   bean: reference to the related bean
                 * }
                 * </pre>
                 *
                 * @member Data.BeanCollection
                 */
                link: {
                    name: link,
                    bean: bean
                }
            });

            bean._setRelatedCollection(link, collection);
            return collection;
        },

        /**
         * Checks if a bean for a given module can have multiple related beans via a given link.
         * @param {String} module Name of the module to do the check for.
         * @param {String} link Relationship link name.
         * @return {Boolean} `true` if the module's link is 'many'-type relationship, `false` otherwise.
         */
        canHaveMany: function(module, link) {
            var meta = app.metadata.getModule(module);
            var name = meta.fields[link].relationship;
            var relationship = meta.relationships[name];
            var t = relationship.relationship_type.split("-");
            var type = module === relationship.rhs_module ? t[0] : t[2];
            return type === "many";
        },

        /**
         * Gets related module name.
         * @param {String} module Name of the parent module.
         * @param {String} link Relationship link name.
         * @return {String} The name of the related module.
         */
        getRelatedModule: function(module, link) {
            var meta = app.metadata.getModule(module);
            var name = meta.fields[link].relationship;
            var relationship = meta.relationships[name];

            return module === relationship.rhs_module ?
                relationship.lhs_module : relationship.rhs_module;
        },

        /**
         * Custom implementation of <code>Backbone.sync</code> pattern. Syncs models with remote server using Sugar.Api lib.
         * @param {String} method the CRUD method (<code>"create", "read", "update", or "delete"</code>)
         * @param {Data.Bean/Data.BeanCollection} model the model to be synced (or collection to be read)
         * @param options(optional) standard Backbone options as well as Sugar specific options
         */
        sync: function(method, model, options) {
            var self = this;

            app.logger.trace('remote-sync-' + (options.relate ? 'relate-' : '') + method + ": " + model);

            options = options || {};
            options.params = options.params || {};
            
            if (options.fields) {
                options.params.fields = options.fields.join(",");
            }

            if ((method == "read") && (model instanceof app.BeanCollection)) {
                if (options.offset && options.offset !== 0) {
                    options.params.offset = options.offset;
                }

                if (app.config && app.config.maxQueryResult) {
                    options.params.max_num = app.config.maxQueryResult;
                }

                if (model.orderBy && model.orderBy.field) {
                    options.params.order_by = model.orderBy.field + ":" + model.orderBy.direction;
                }
            }

            var success = function(data) {
                if (options.success) {
                    if ((method == "read") && (model instanceof app.BeanCollection)) {
                        if (data.next_offset) {
                            model.offset = data.next_offset != -1 ? data.next_offset : model.offset + (data.records || []).length;
                            model.next_offset = data.next_offset;
                            model.page = model.getPageNumber();
                        }
                        data = data.records || [];
                    } else if ((options.relate === true) && (method != "read")) {
                        // Reset the flag to indicate that fetched relationship(s) do exist.
                        model.link.isNew = false;
                        // The response for create/update/delete relationship contains updated beans
                        if (model.link.bean) {
                            model.link.bean.set(data.record);
                        }
                        data = data.related_record;
                        // Attributes will be set automatically for create/update but not for delete
                        // Also, break the link
                        if (method == "delete") {
                            model.set(data);
                            delete model.link;
                        }
                    }
                    options.success(data);
                }
            };

            var error = function(xhr, error) {
                app.error.handleHTTPError(xhr, error, self);

                if (options.error) {
                    options.error(xhr, error);
                }
            };

            var callbacks = {
                success: success,
                error: error
            };

            if (options.relate === true) {
                // Related data is an object should contain:
                // - related bean (including relationship fields) in case of create method
                // - just relationship fields in case of update method
                // - null for read/delete method
                var relatedData = null;
                if (method == "create" || method == "update") {
                    // Pass all fields: bean fields + relationship fields
                    relatedData = model.attributes;
                    // Change 'update' method to 'create' if the relationship is a new one
                    if (method == "update" && model.link.isNew) {
                        method = "create";
                    }
                }

                app.api.relationships(
                    method,
                    model.link.bean.module,
                    {
                        id: model.link.bean.id,
                        link: model.link.name,
                        relatedId: model.id,
                        related: relatedData
                    },
                    options.params,
                    callbacks
                );
            }
            else {
                app.api.records(
                    method,
                    model.module,
                    model.attributes,
                    options.params,
                    callbacks
                );
            }

        }
    };

    app.augment("data", _dataManager, false);

})(SUGAR.App);

