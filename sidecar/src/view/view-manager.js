/**
 * Layout Manager is used to create views, layouts, and fields based on metadata inputs.
 *
 * @class View.ViewManager
 * @alias SUGAR.App.view
 * @singleton
 */
(function(app) {

    // Create a new subclass of the given parent class based on the controller definition passed.
    var _declareClass = function(cache, parent, className, controller) {
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
                // First check if custom class per module already exists
                cache[customClassName] ||
                // Fall back to base views
                cache[className] ||
                // Otherwise, create custom class if the metadata has a controller
                _declareClass(cache, baseClass, customClassName, controller) ||
                // Fall back to regular view class (ListView, FluidLayout, etc.)
                cache[className] ||
                // Fall back to base class (View, Layout, or Field)
                baseClass;

            return new klass(params);
        },

        createView: function(params) {
            var clonedParams = _.clone(params);
            clonedParams.module = params.module || params.context.get("module");
            clonedParams.meta = params.meta || app.metadata.getView(clonedParams.module, params.name);
            return this._createComponent("View", params.name, clonedParams);
        },

        createLayout: function(params) {
            var clonedParams = _.clone(params);
            clonedParams.module = params.module || params.context.get("module");
            clonedParams.meta = params.meta || app.metadata.getLayout(clonedParams.module, params.name) || {};

            clonedParams.meta.type = clonedParams.meta.type || clonedParams.name;
            clonedParams.name = clonedParams.name || clonedParams.meta.type;

            return this._createComponent("Layout", clonedParams.meta.type, clonedParams);
        },

        createField: function(params) {
            var clonedParams = _.clone(params);

            // TODO: We clone field definition (params.def) not to mess it up down the road
            // Consider the opposite: pre-process the field defs at app start-up and patch the definitions once
            // instead of every time we create a new field

            // Definition can be an object or a string
            // If it's a string than it's just the field name -- grab its definition from module vardefs.
            if (_.isString(clonedParams.def) && clonedParams.model){
                clonedParams.def = _.clone(params.model.fields[clonedParams.def]);
            }
            else {
                clonedParams.def = _.clone(params.def);
            }

            var type = clonedParams.def.type;
            type = this.fieldTypeMap[type] ? this.fieldTypeMap[type] : type;
            clonedParams.meta = clonedParams.meta || app.metadata.getField(type);
            clonedParams.def.type = type; // patch the original type with mapped type if any

            return this._createComponent("Field", type, clonedParams);
        }

    };

    app.augment("view", _viewManager, false);

})(SUGAR.App);