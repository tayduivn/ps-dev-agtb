(function(app) {
    var contextCache = {};

    function Context(obj, data) {
        var contextId = _.uniqueId("context_");
        var context = _.extend({
            contextId: contextId,
            state: {},
            parent: null,
            children: [],

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

            reset: function(obj) {
                this.state = {};
            },

            fire: function() {
                this.trigger(contextId + ":change", this);
                this.trigger("context:change", this);
            },

            /**
             * Takes parameters from another source and stores their state.
             *
             * Note: This function should be called everytime a new route routed.
             * @param obj
             */
            init: function(obj, data) {
                this.reset(obj);
                this.set(obj, data);
            }
        }, Backbone.Events);

        context.init((obj || {}), (data || {}));
        return context;
    }

    app.augment("context", {
        getContext: function(obj, data) {
            return new Context(obj, data);
        }
    });
})(SUGAR.App);