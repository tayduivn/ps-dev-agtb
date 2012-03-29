(function(app) {
    var contextCache = {};

    /**
     * The Context object is a state variable to hold the states of the current context. The context contains various
     * states of the current View or Layout -- this includes the current model and collection, as well as the current
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
     * nested {@link Layout.View Views} / {@link Layout.Layout Layouts} are can be derived from the global context
     * object.
     *
     *
     * The global context object is stored in **`App.controller.context`**.
     *
     *
     * @class Context
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
             * Populates the data based on the state and stores it internally.
             * @method
             */
            getData: function() {
                var fields, bean, collection,
                    options = {},
                    self = this,
                    state = this.get();

                if (state.view) {
                    fields = state.view.getFields();
                    this.set({fields: fields});

                    options.params = { fields: fields.join(",") };
                }

                if (state.id) {
                    bean = app.dataManager.createBean(state.module, { id: state.id });
                    collection = app.dataManager.createBeanCollection(state.module, [bean]);

                    bean.fetch(options);
                } else if (state.create) {
                    bean = app.dataManager.createBean(state.module);
                    collection = app.dataManager.createBeanCollection(state.module, [bean]);
                } else if (state.url) {
                    // TODO: Make this hit a custom url
                } else {
                    options.success = function() {
                        self.set({model: collection.models[0]});
                        if (state.view) {
                            //state
                            state.view.render();
                        }
                    };

                    collection = app.dataManager.createBeanCollection(state.module);
                    collection.on("app:collection:fetch", state.view.render, this);
                    collection.fetch(options);

                    bean = collection.models[0] || {};
                }

                this.set({collection: collection, model: bean});
                if ((state.id || state.create) && state.view) {
                    state.view.render();
                }
                _.each(this.children, function(child) { //TODO optimize for batch
                    child.getData();
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
         * @member Context
         */
        getContext: function(obj, data) {
            return new Context(obj, data);
        }
    });
})(SUGAR.App);