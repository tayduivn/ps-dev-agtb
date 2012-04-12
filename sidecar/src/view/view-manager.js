/**
 * Layout Manager is used to create views, layouts, and fields based on metadata inputs.
 *
 * @class View.ViewManager
 * @alias SUGAR.App.view
 * @singleton
 */
(function(app) {

    // Create a new subclass of the given parent class based on the controller definition passed in and adds it to the layout namespace.
    var _extendAndRegister = function(cache, parent, className, controller) {
        var klass = null;
        if (controller) {
            try {
                var obj = eval("(" + controller + ")");
                if (_.isObject(obj)) {
                    klass = cache[className] = parent.extend(obj);
                }
            } catch (e) {
                app.logger.error("invalid view controller " + className + " : " + controller);
            }
        }
        return klass;
    };

    var _viewManager = {

        /**
         * Map of fields types.
         *
         * Specifies correspondence between module field types and field widget types.
         *
         * - `varchar`, `name`, `currency` are mapped to `text` widget
         * - `text` - `textarea`
         * - `decimal` - `float`
         */
        fieldTypeMap: {
            varchar: "text",
            name: "text",
            text: "textarea",
            decimal: "float",
            currency: "text"
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

        _createComponent: function(type, name, params) {
            var className = app.utils.capitalize(name) + type;
            var customClassName = (params.module || "") + className;
            var cache = this[type.toLowerCase() + "s"];
            var controller = params.meta ? params.meta.controller : null;
            var baseClass = app.view[type];
            var klass =
                // First check if custom class already exists
                cache[customClassName] ||
                // Otherwise, create custom class if the metadata has a controller
                _extendAndRegister(cache, baseClass, customClassName, controller) ||
                // Fall back to regular view class (ListView, EditView, etc.)
                cache[params.className] ||
                // Fall back to base class (View, Layout, or Field)
                baseClass;

            return new klass(params);
        },

        createView: function(params) {
            var options = _.clone(params);
            options.module = params.module || params.context.get("module");
            options.meta = params.meta || app.metadata.getView(options.module, params.name);
            return this._createComponent("View", params.name, options);
        },

        createLayout: function(params) {
            var options = _.clone(params);
            options.module = params.module || params.context.get("module");
            options.meta = params.meta || app.metadata.getLayout(options.module, params.name);
            options.name = options.name || options.meta.type;
            return this._createComponent("Layout", options.meta.type, options);
        },

        createField: function(params) {
            var options = _.clone(params);
            var type = params.def.type;
            var name = this.fieldTypeMap[type] ? this.fieldTypeMap[type] : type;
            options.meta = params.meta || app.metadata.getField(type);
            return this._createComponent("Field", name, options);
        }

    };

    app.augment("view", _viewManager, false);

})(SUGAR.App);