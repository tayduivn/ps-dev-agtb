/**
 * Layout Manager is used to create views, layouts, and fields based on metadata inputs.
 *
 * @class View.ViewManager
 * @alias SUGAR.App.view
 * @singleton
 */
(function(app) {

    // Create a new subclass of the given parent class based on the controller definition passed.
    var _declareClass = function(cache, base, className, controller) {
        var klass = null;
        var evaledController = null;
        if (controller) {
            try {
                evaledController = eval("(" + controller + ")");
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
            clonedParams.module = params.module || (params.context ? params.context.get("module") : null);
            clonedParams.meta = params.meta || (clonedParams.module ? app.metadata.getView(clonedParams.module, params.name) : null);
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
            var type = params.def.type;
            params.meta = params.meta || app.metadata.getField(type);
            return this._createComponent("Field", type, params);
        }

    };

    app.augment("view", _viewManager, false);

})(SUGAR.App);