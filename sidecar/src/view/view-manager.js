/**
 * Layout Manager is used to retrieve views and layouts based on metadata inputs.
 * @class View.LayoutManager
 * @alias SUGAR.App.layout
 * @singleton
 */
(function(app) {

    var _ucfirst = function(str) {
        if (_.isString(str)) {
            return str.charAt(0).toUpperCase() + str.substr(1);
        }
    };

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

    var _fieldTypeMap = {
        varchar: "text",
        name: "text",
        text: "textarea"
    };

    var _createComponent = function(params) {
        var customClassName = params.module + params.className;
        var cache = params.cache;
        var klass =
            // first check if custom class already exists
            cache[customClassName] ||
            // create a custom class if the metadata has a controller
            _extendAndRegister(cache, params.base, customClassName, params.meta.controller) ||
            // fall back to regular view class (ListView, EditView, etc.)
            cache[params.className] ||
            params.base;  // fall back to our default View implementation

        return new klass(params);
    };

    var _viewManager = {

        views: {},
        layouts: {},
        fields: {},

        createView: function(params) {
            var options = _.clone(params);
            options.module = params.module || params.context.get("module");
            options.meta = params.meta || app.metadata.getView(options.module, params.name) || {};
            options.className = _ucfirst(params.name) + "View";
            options.cache = this.views;
            options.base = app.view.View;
            return _createComponent(options);
        },

        createLayout: function(params) {
            var options = _.clone(params);
            options.module = params.module || params.context.get("module");
            options.meta = params.meta || app.metadata.getLayout(options.module, params.name) || {};
            options.className = _ucfirst(options.meta.type) + "Layout";
            options.cache = this.layouts;
            options.base = app.view.Layout;
            options.name = options.name || options.meta.type;
            return _createComponent(options);
        },

        createField: function(params) {
            var options = _.clone(params);
            var type = params.def.type;

            options.module = ""; // fields are not customizable per module
            options.meta = params.meta || app.metadata.getField({ type: type }) || {};

            var fClass = _fieldTypeMap[type] ? _fieldTypeMap[type] : type;
            options.className = _ucfirst(fClass) + "Field";
            options.cache = this.fields;
            options.base = app.view.Field;
            return _createComponent(options);
        }

    };

    app.augment("view", _viewManager, false);

})(SUGAR.App);