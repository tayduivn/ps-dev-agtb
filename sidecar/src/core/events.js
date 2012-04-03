(function(app) {
    /**
     * Events proxy object. For inter-component communications, please register your events and please subscribe
     * your events from the events hub. This allows components to not depend on each other in a tightly coupled capacity.
     *
     * <pre><code>
     * var foo = {
     *     initialize: function() {
     *         // Register the event with the events hub.
     *         SUGAR.App.events.register("mynamespaced:event", this);
     *     },
     *     action: function() {
     *         // Broadcast you revent to the events hub.
     *         // The events hub will then broadcast this event to all its subscribers.
     *         this.trigger("mynamespaced:event");
     *     }
     * }
     *
     * var bar = {
     *     initialize: function() {
     *         // Call a callback when the event is received.
     *         SUGAR.App.events.on("mynamespaced:event", function() {
     *             alert("Event!");
     *         });
     *     }
     * }
     * </pre></code>
     * @class Events
     */
    app.augment("events", _.extend({
        /**
         * Registers an event with the event proxy.
         *
         * @param {String} event The name of the event. A good practice is to namespace your events with a colon. For example: `"app:start"`
         * @param context
         * @method
         */
        register: function(event, context) {
            context.on(event, function(args) {
                this.trigger(event, args);
            }, this);
        },

        /**
         * Resets the event on the event proxy.
         *
         * @param {String} event Event name to be cleared
         * @param {Object} context Source to be cleared from
         * @method
         */
        clear: function(event, context) {
            if (arguments.length < 2) {
                event.off();
            } else {
                context.off(event);
            }
        }
    }, Backbone.Events));
})(SUGAR.App);