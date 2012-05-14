(function(app) {
    var contextCache = {};

    /**
     * The Context object is a state variable to hold the states of the current context. The context contains various
     * states of the current {@link View.View View} or {@link View.Layout Layout} -- this includes the current model and collection, as well as the current
     * module focused and also possibly the url hash that was matched.
     *
     * ###Creating a Context Object
     *
     * Use the getContext method to get a new instance of a context.
     * <pre><code>
     * var myContext = App.context.getContext({
     *     module: "Contacts",
     *     url: "contacts/id"
     * });
     * </code></pre>
     *
     * ###Retrieving Data from the Context
     *
     * <pre><code>
     * var module = myContext.get("module"); // module = "Contacts"
     * </pre></code>
     *
     * ###Global Context Object
     *
     * The Application has a global context that applies to top level layer. Contexts used within
     * nested {@link View.View Views} / {@link View.Layout Layouts} can be derived from the global context
     * object.
     *
     *
     * The global context object is stored in **`App.controller.context`**.
     *
     *
     * @class Core.Context
     * @param {Object} obj Any parameters and state properties to attach to the context
     * @param {Object} data Hash of collection and or models to save to the context
     */
    function Context(obj, data) {
        var contextId = _.uniqueId("context_");
        var context = _.extend({
            /**
             * Unique ID of the context
             * @property {String}
             */
            contextId: contextId,

            /**
             * State variables
             * @property {Object}
             */
            state: {},

            /**
             * Reference to the parent context (null the context is the global context)
             * @property {Object}
             */
            parent: null,

            /**
             * List of child contexts.
             * @property {Object[]}
             */
            children: [],

            /**
             * Returns a state on the context. If no properties are specified, the entire state is returned.
             * @method
             * @param {String} prop Requested state variable
             * @return {Object} val Value of retrieved key
             */
            get: function(prop) {
                var requested = {};

                if (prop) {
                    if (_.isString(prop)) {
                        return this.state[prop];
                    } else {
                        _.each(prop, function(key) {
                            requested[key] = this.state[key];
                        }, this);

                        return requested;
                    }
                }
                return this.state;
            },

            /**
             * Sets a state on the context
             * @method
             * @param {Object} obj Any parameters and state properties to attach to the context
             * @param {Object} data Hash of collection and or models to save to the context
             */
            set: function(obj, data) {
                if (obj && obj.contextId && obj.state) { // If obj is a context

                    // Set the relationships between the two contexts
                    this.parent = obj;
                    obj.children.push(this);

                    _.each(obj.state, function(state, name) {
                        // Don't copy over model or collection attributes
                        if (name !== "model" && name !== "collection") {
                            this.state[name] = state;
                        }

                    }, this);
                } else {
                    _.extend(this.state, obj);
                }

                _.extend(this.state, data);
                this.fire();
            },

            /**
             * Resets the context state to empty.
             * @method
             */
            reset: function() {
                this.state = {};
                _.each(this.children, function(child) {
                    child.reset();
                });
            },

            /**
             * Triggers two events. The first event is a plain "context:change" event, the second
             * event is the context's id concatenated with change.
             * @method
             * @private
             */
            fire: function() {
                this.trigger(contextId + ":change", this);
                this.trigger(
                    /**
                     * @event
                     * This event is triggered when an attribute on the context has been set.
                     * The current context is passed in as the argument.
                     * Another event with the contextId as the namespace is also fired. Ex: `"context10:change"`
                     */
                    "context:change",
                    this
                );
            },

            /**
             * Changes the focus of the context. Fires the context:focus event.
             * @param {Object} focus The model / bean to change the focus to
             * @method
             */
            focus: function(focus) {
                this.trigger("context:focus", focus);
            },

            /**
             * Takes parameters from another source and stores their state.
             *
             * Note: This function should be called everytime a new route routed.
             * @param {Object} obj Any parameters and state properties to attach to the context
             * @param {Object} data Hash of collection and or models to save to the context
             */
            init: function(obj, data) {
                this.reset(obj);
                this.set(obj, data);
            },

            /**
             * Gets a related context.
             * @param {String} name Related context name (usually it's relationship link name).
             * @param {String} module Module name.
             * @return {Core.Context} New instance of the related context.
             */
            getChildContext: function(def) {
                var context = app.context.getContext(def);

                context.parent = this;

                if (def.module) {
                    context.prepareData();
                } else if (def.link) {
                    context.set({parentModel: this.state.model});
                    context.prepareRelatedData();
                }

                this.children.push(context);

                return context;
            },

            /**
             * Prepares instances of model and collection.
             */
            prepareData: function() {
                var model, collection,
                    state = this.state;

                if (state.id) {
                    model = app.data.createBean(state.module, { id: state.id });
                    collection = app.data.createBeanCollection(state.module, [model]);
                } else if (state.create) {
                    model = app.data.createBean(state.module);
                    collection = app.data.createBeanCollection(state.module, [model]);
                } else {
                    model = app.data.createBean(state.module);
                    collection = app.data.createBeanCollection(state.module);
                }

                this.set({collection: collection, model: model});
            },

            /**
             * Prepares instances of related models and collections
             */
            prepareRelatedData: function() {
                var model, collection,
                    state = this.state;

                if (state.parentModel && state.link) {
                    collection = state.parentModel.getRelatedCollection(state.link);
                    model = app.data.createRelatedBean(state.parentModel, null, state.link);
                    this.set({collection: collection, model: model});
                }
            },


            /**
             * Loads data (calls fetch on either model or collection).
             */
            loadData: function() {
                if (this.state.create) return;

                var objectToFetch = null, options = {}, defaultOrdering;

                if (this.state.id) {
                    objectToFetch = this.state.model;
                } else {
                    objectToFetch = this.state.collection;
                }

                // If we have an orderByDefaults in the config, and this is a bean collection,
                // try to use ordering from there (only if orderBy is not already set.)
                if (objectToFetch instanceof app.BeanCollection && !objectToFetch.orderBy && 
                    this.state.module && app.config.orderByDefaults) {
                    defaultOrdering = app.config.orderByDefaults;
                    if(defaultOrdering[this.state.module]) {
                        objectToFetch.orderBy = defaultOrdering[this.state.module];
                    }
                }

                // TODO: Figure out what to do when models are not
                // instances of Bean or BeanCollection. No way to fetch.
                if (objectToFetch && (objectToFetch instanceof app.Bean || 
                    objectToFetch instanceof app.BeanCollection)) {

                    if (this.state.link) {
                        options.relate = true;
                    }
                    if (this.state.layout) {
                        options.fields = this.state.layout.getFields();
                    } else if (this.state.view) {
                        options.fields = this.state.view.getFields();
                    }

                    objectToFetch.fetch(options);
                } else {
                    app.logger.warn("Skipping fetch because model is not Bean, Bean Collection, or it is not defined.");
                }


                _.each(this.children, function(child) { //TODO optimize for batch
                    child.loadData();
                });
            }


        }, Backbone.Events);

        context.init((obj || {}), (data || {}));
        return context;
    }

    app.augment("context", {
        /**
         * Returns a new instance of the context object
         * @param {Object} obj Any parameters and state properties to attach to the context
         * @param {Object} data Hash of collection and or models to save to the context
         * @member Core.Context
         */
        getContext: function(obj, data) {
            return new Context(obj, data);
        }
    });
})(SUGAR.App);
