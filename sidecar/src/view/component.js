/**
 * Represents base view class for layouts, views, and fields.
 * @class View.Component
 * @alias SUGAR.app.view.Component
 */
(function(app) {

    app.view.Component = Backbone.View.extend({

        /**
         * Reference to the application instance.
         * @property {App}
         */
        app: app,

        /**
         * Initializes a component.
         * @constructor
         * @param options
         *
         * - context
         * - meta
         * - module
         * - model
         * - collection
         *
         * `context` is the only required option.
         * @return {View.Component}
         */
        initialize: function(options) {

            /**
             * Reference to the context (required).
             * @property {Core.Context}
             */
            this.context = options.context;

            /**
             * Component metadata (optional).
             * @property {Object}
             */
            this.meta = options.meta;

            /**
             * Module name (optional).
             * @property {String}
             */
            this.module = options.module || this.context.get("module");

            /**
             * Reference to the model this component is bound to.
             * @property {Data.Bean}
             */
            this.model = options.model || this.context.get("model");

            /**
             * Reference to the collection this component is bound to.
             * @property {Data.BeanCollection}
             */
            this.collection = options.collection || this.context.get("collection");
        },

        /**
         * Binds data to this component.
         *
         * This method should be overridden by derived views.
         */
        bindDataChange: function() {
            // Override this method to wire up model/collection events
        },

        /**
         * Gets a string representation of this component.
         * @return {String} String representation of this component.
         */
        toString: function() {
            var type = "component";
            if (this instanceof app.view.Layout) type = "layout";
            else if (this instanceof app.view.View) type = "view";
            else if (this instanceof app.view.Field) type = "field";
            return type + "-" + this.cid +
                "/" + this.module +
                "/model:" + this.model +
                "collection:" + this.collection;
        }

    });

})(SUGAR.App);