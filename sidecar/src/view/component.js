/**
 * Represents base view class for layouts, views, and fields.
 *
 * This is an abstract class.
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
         * Renders a component.
         *
         * Override this method to provide custom logic.
         * The default implementation does nothing.
         * See Backbone.View documentation for details.
         * @protected
         */
        _render: function() {
            // Do nothing. Override.
        },

        /**
         * Renders a component.
         *
         * IMPORTANT: Do not override this method.
         * Instead, override {@link View.Component#_render} to provide render logic.
         * @return {View.Component} Instance of this component.
         */
        render: function() {
            if (this.disposed === true) throw new Error("Unable to render component because it's disposed: " + this);
            this._render();
            return this;
        },

        /**
         * Binds data changes to this component.
         *
         * This method should be overridden by derived views.
         */
        bindDataChange: function() {
            // Override this method to wire up model/collection events
        },

        /**
         * Removes this component's event handlers from model and collection.
         *
         * Performs the opposite of what {@link View.Component#bindDataChange} method does.
         * Override this method to provide custom logic.
         */
        unbindData: function() {
            if (this.model) this.model.off(null, null, this);
            if (this.collection) this.collection.off(null, null, this);
        },

        /**
         * Removes all event callbacks registered within this component
         * and undelegates Backbone events.
         *
         * Override this method to provide custom logic.
         */
        unbind: function() {
            this.off();
            this.undelegateEvents();
            app.events.off(null, null, this);
            app.events.unregister(this);
        },

        /**
         * Fetches data for layout's model or collection.
         *
         * The default implementation does nothing.
         * See {@link View.Layout#loadData} and {@link View.View#loadData} methods.
         */
        loadData: function() {
            // Do nothing (view and layout will override)
        },

        /**
         * Disposes a component.
         *
         * This method:
         *
         * - unbinds the component from model and collection.
         * - removes all event callbacks registered within this component.
         * - removes the component from the DOM.
         *
         * Override this method to provide custom logic:
         * <pre><code>
         * app.view.views.MyView = app.view.View.extend({
         *      _dispose: function() {
         *          // Perform custom clean-up. For example, clear timeout handlers, etc.
         *          ...
         *          // Call super
         *          app.view.View.prototype._dispose.call(this);
         *      }
         * });
         * </code></pre>
         * @protected
         */
        _dispose: function() {
            this.unbindData();
            this.unbind();
            this.remove();
            this.model = null;
            this.collection = null;
            this.context = null;
        },

        /**
         * Disposes a component.
         *
         * Once the component gets disposed it can not be rendered.
         * Do not override this method. Instead override {@link View.Component#_dispose} method
         * if you need custom disposal logic.
         */
        dispose: function() {
            if (this.disposed === true) return;
            this._dispose();
            this.disposed = true;
        },

        /**
         * Gets a string representation of this component.
         * @return {String} String representation of this component.
         */
        toString: function() {
            return this.cid +
                "-" + (this.$el && this.$el.id ? this.$el.id : "<no-id>") +
                "/" + this.module +
                "/" + this.model +
                "/" + this.collection;
        }

    });

})(SUGAR.App);