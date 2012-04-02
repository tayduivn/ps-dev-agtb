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

    /**
     *  Create a new subclass of the given parent class based on the controller definition passed in and adds it to the layout namespace.
     * @param parent
     * @param className
     * @param controller
     *
     * @private
     */
    var _extendAndRegister = function(parent, className, controller) {
        try {
            var obj = eval("(" + controller + ")");
            if (typeof (obj) == "object") {
                app.layout[className] = app.layout[parent].extend(obj);
            }
        } catch (e) {
            app.logger.error("invalid view controller " + className + " : " + controller);
            return parent;
        }
        return className;
    };

    var _layoutManager = {
        init: function(args) {
            Handlebars.registerHelper('get_field_value', function(bean, field) {
                return bean.get(field);
            });

            Handlebars.registerHelper('buildRoute', function(context, model, action, options) {
                var id = model.id,
                    route;

                options = options || {};

                if (action == 'create') {
                    id = '';
                }

                route = app.router.buildRoute(context.get("module"), id, action, options);
                return new Handlebars.SafeString(route);
            });

            Handlebars.registerHelper('getfieldvalue', function(bean, field) {
                return bean.get(field);
            });


            Handlebars.registerHelper('in', function(val, array, retTrue, retFalse) {
                // Since we need to check both just val = val 2 and also if val is in an array, we cast
                // non arrays into arrays
                if (!_.isArray(array) && !_.isObject(array)) {
                    array = [array];
                }

                if (_.find(array, function(item) {
                    return item === val;
                })) {
                    return retTrue;
                }

                return (!_.isUndefined(retFalse)) ? retFalse : "";
            });

            Handlebars.registerHelper('eq', function(val1, val2, retTrue, retFalse) {

                if (val1 == val2) {
                    return retTrue;
                }

                return (retFalse != undefined) ? retFalse : "";
            });

            Handlebars.registerHelper("handleBarsLog", function(value) {
                app.logger.debug("*****Current Context*****");
                app.logger.debug(this);
                app.logger.debug("*****Current Value*****");
                app.logger.debug(value);
                app.logger.debug("***********************");

            });
        },

        //All retreives of metadata should hit this function.
        //TODO: Probably refactor this function, it's quite large
        /**
         * Returns a Layout or View
         * @method
         * @param {Object} params Contains either view or layout to specify which type of
         * component you are retreiving.
         */
        get: function(params) {
            var meta = params.meta,
                layoutClass = "Layout",
                viewClass = "View",
                ucType, controller, view;

            if (!params.view && !params.layout)
                return null;

            var context = params.context || app.controller.context;
            var module = params.module || context.get("module");

            //Ensure we have a module for the layout
            if (meta && !meta.module) {
                meta.module = module;
            }

            if (params.view) {
                meta = meta || app.metadata.get({
                    type: "view",
                    module: module,
                    view: params.view
                }) || {};
                ucType = _ucfirst(meta.view || params.type || params.view);

                //First check if this module has a custom view class
                if (meta && app.layout[module + ucType + "View"]) {
                    viewClass = module + ucType + "View";
                }
                else if (meta && meta.controller) {
                    //If we didn't find a view class override and a controller was defined in the metadata,
                    //we need to define a new view class dynamically
                    viewClass = _extendAndRegister("View", module + ucType + "View", meta.controller);
                }
                //Check if the view type has its own view subclass
                else if (meta && app.layout[ucType + "View"]) {
                    viewClass = ucType + "View";
                }
                else if (meta && app.layout[ucType]) {
                    viewClass = ucType;
                }

                view = new app.layout[viewClass]({
                    context: params.context,
                    name: params.view,
                    meta: meta
                });

            } else if (params.layout) {
                meta = params.meta || app.metadata.get({
                    type: "layout",
                    module: module,
                    layout: params.layout
                });

                ucType = _ucfirst(meta.type);

                //Check if the layout type has its own layout subclass
                if (meta && app.layout[ucType + "Layout"]) {
                    layoutClass = ucType + "Layout";
                }

                controller = meta.controller;
                //If we didn't find a layout class override and a controller was defined in the metadata,
                //we need to define a new layout class dynamically
                if (layoutClass == "Layout" && controller) {
                    layoutClass = _extendAndRegister("Layout", ucType + "Layout", controller)
                }

                view = new app.layout[layoutClass]({
                    context: params.context,
                    name: params.layout,
                    module: module,
                    meta: meta
                });
            }

            return view;
        }
    };

    app.augment("layout", _layoutManager, false);


})(SUGAR.App);