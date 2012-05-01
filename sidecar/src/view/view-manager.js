/**
 * View manager is used to create views, layouts, and fields based on optional metadata inputs.
 *
 * The view manager's factory methods (`createView`, `createLayout`, and `createField`) first checks
 * `views`, `layouts`, and `fields` hashes respectively for custom class declaration before falling back the base class.
 *
 * Put declarations of your custom views, layouts, fields in the corresponding hash:
 * <pre><code>
 * app.view.views.MyCustomView = app.view.View.extend({
 *  // Put your custom logic here
 * });
 *
 * app.view.layouts.MyCustomLayout = app.view.Layout.extend({
 *  // Put your custom logic here
 * });
 *
 * app.view.fields.MyCustomField = app.view.Field.extend({
 *  // Put your custom logic here
 * });
 *
 * </code></pre>
 *
 *
 * @class View.ViewManager
 * @alias SUGAR.App.view
 * @singleton
 */
(function(app) {

    // Ever incrementing field ID
    var _sfId = 0;

    // Create a new subclass of the given super class based on the controller definition passed.
    var _declareClass = function(cache, base, className, controller) {
        var klass = null, evaledController = null;
        if (controller) {
            try {
                evaledController = eval("[" + controller + "][0]");
            } catch (e) {
                app.logger.error("Failed to eval view controller for " + className + ": " + e + ":\n" + controller);
            }
        }

        if (_.isObject(evaledController)) {
            klass = cache[className] = base.extend(evaledController);
        }

        return klass;
    };

    var _viewManager = {

        /**
         * Gets ID of the last created field.
         * @return {Number} ID of the last created field.
         */
        getFieldId: function() {
            return _sfId;
        },

        /**
         * Hash of view classes.
         */
        views: {},
        /**
         * Hash of layout classes.
         */
        layouts: {},
        /**
         * Hash of field classes.
         */
        fields: {},

        /**
         * Creates a component and binds data changes to it.
         *
         * @param type Component type (`layout`, `view`, `field`).
         * @param name Component name.
         * @param params Parameters to pass to the Component's class constructor.
         * @return {View.Component} New instance of a component.
         * @private
         */
        _createComponent: function(type, name, params) {
            var ucType          = app.utils.capitalize(type),
                className       = app.utils.capitalize(name) + ucType,
                customClassName = (params.module || "") + className,
                pluralizedType  = type.toLowerCase() + "s",
                cache           = app.view[pluralizedType],
                controller      = params.controller, 
                // Fall back to base class (View, Layout, or Field)
                baseClass       = cache[className] || app.view[ucType],
                klass           = null;

            klass =
                // Next check if custom class per module already exists
                cache[customClassName] ||
                // If we don't have a customClassName 
                _declareClass(cache, baseClass, customClassName, controller) ||
                // Fall back to regular view class (ListView, FluidLayout, etc.)
                baseClass;

            var component = new klass(params);
            component.bindDataChange();

            return component;
        },

        /**
         * Creates an instance of a view.
         *
         * Parameters define creation rules as well as view properties.
         * The `param` hash must contain at least `name` property which is a view name.
         * Other parameters may be:
         *
         * - context: context to associate with the newly created view
         * - module: module name
         * - meta: custom metadata
         *
         * If context is not specified the controller's current context is assigned to the view (`SUGAR.App.controller.context`).
         *
         * Examples:
         *
         * * Create a list view. The view manager will use metadata for the view named 'list' defined in Contacts module.
         * The controller's current context will be set on the new view instance.
         * <pre><code>
         * var listView = app.view.createView({
         *    name: 'list',
         *    module: 'Contacts'
         * });
         * </code></pre>
         *
         * * Create a custom view class.
         * <pre><code>
         * // Declare your custom view class.
         * app.view.views.MyCustomView = app.view.View.extend({
         *  // Put your custom logic here
         * });
         *
         * var myCustomView = app.view.createView({
         *    name: 'myCustom'
         * });
         * </code></pre>
         *
         * * Create a view with custom metadata payload.
         * <pre><code>
         * var view = app.view.createView({
         *     name: 'detail',
         *     meta: { ... some custom metadata ... }
         * });
         * </code></pre>
         *
         * @param params view parameters
         * @return {View.View} new instance of view.
         */
        createView: function(params) {
            var module;
            // context is always defined on the controller
            params.context = params.context || app.controller.context;
            params.module  = params.module || params.context.get("module");
            params.meta    = params.meta || app.metadata.getView(params.module, params.name);

            return this._createComponent("View", params.name, params);
        },

        /**
         * Creates an instance of a layout.
         *
         * TODO: Add docs.
         * @param params
         * @return {View.Layout}
         */
        createLayout: function(params) {
            var clonedParams    = _.clone(params);
            clonedParams.module = params.module || params.context.get("module");
            clonedParams.meta   = params.meta || app.metadata.getLayout(clonedParams.module, params.name) || {};

            clonedParams.meta.type = clonedParams.meta.type || clonedParams.name;
            clonedParams.name      = clonedParams.name || clonedParams.meta.type;

            return this._createComponent("Layout", clonedParams.meta.type, clonedParams);
        },

        /**
         * Creates an instance of a field and registers it with the parent view (`params.view`).
         *
         * The parameters define creation rules as well as field properties.
         * The `params` hash must contain `def` property which is the field definition and `view`
         * property which is the reference to the parent view. For example,
         * <pre>
         * var params = {
         *    view: new Backbone.View,
         *    def: {
         *      type: 'text',
         *      name: 'first_name',
         *      label: 'LBL_FIRST_NAME'
         *    },
         *    context: optional context (if not specified, app.controller.context is used)
         *    model: optional model (if not specified, the model which is set on the context is used)
         *    meta: optional custom metadata
         * }
         * </pre>
         *
         * View manager queries metadata manager for field type specific metadata (templates and JS controller) unless custom metadata
         * is passed in the `params` hash.
         *
         * To create instances of custom fields, first declare its class in `app.view.fields` hash:
         * <pre><code>
         * app.view.views.MyCustomField = app.view.Field.extend({
         *  // Put your custom logic here
         * });
         *
         * var myCustomField = app.view.createField({
         *   view: someView,
         *   def: {
         *      type: 'myCustom',
         *      name: 'my_custom'
         *   }
         * });
         * </code></pre>
         *
         * @param params field parameters.
         * @return {View.Field} a new instance of field.
         */
        createField: function(params) {
            /**
             * Widget type (text, bool, int, etc.).
             * @property {String}
             * @member View.Field
             */
            var type       = params.def.type;
            params.meta    = params.meta || app.metadata.getField(type);
            params.context = params.context || app.controller.context;
            params.controller = (params.meta && params.meta.controller) ? params.meta.controller : null;
            params.model   = params.model || params.context.get("model");
            params.sfId = ++_sfId;
            
            var field = this._createComponent("Field", type, params);
            // Register new field within its parent view.
            params.view.fields[field.sfId] = field;
            return field;
        }

        declareCustomComponent: function(controller, name, module, type) {
            var ucType          = app.utils.capitalize(type),
                className       = app.utils.capitalize(name) + ucType,
                customClassName = module + className,
                cache           = app.view.views,
                baseClass       = cache[className] || app.view[ucType];

            _declareClass(cache, baseClass, customClassName, controller);
        }
    };

    app.augment("view", _viewManager, false);

}(SUGAR.App));

