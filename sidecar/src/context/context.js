(function(app) {
    var contextCache = {};

    /**
     * A state variable to hold the states of the current context.
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
             * Returns a state on the context
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
             */
            fire: function() {
                this.trigger(contextId + ":change", this);
                this.trigger("context:change", this);
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

            getData: function() {
                var data, bean, collection;

                if (this.get('id')) {
                    bean = app.dataManager.fetchBean(this.get('module'), this.get('id'));
                    collection = app.dataManager.createBeanCollection(this.get('module'), [bean]);
                }
                else if (this.get('create')) {
                    bean = app.dataManager.createBean(this.get('module'));
                    collection = app.dataManager.createBeanCollection(this.get('module'), [bean]);
                }
                else if (this.get('url')) {
                    // TODO: Make this hit a custom url
                } else {
                    collection = app.dataManager.fetchBeans(this.get('module'));
                    bean = collection.models[0] || {};
                }

                this.set({collection: collection, model: bean});
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
         *
         * <
         */
        getContext: function(obj, data) {
            return new Context(obj, data);
        }
    });
})(SUGAR.App);