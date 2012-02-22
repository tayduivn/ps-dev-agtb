(function (app) {
    //Metadata that has been loaded from offline storage
    var _metadata = {};
    var fieldTypeMap = {
        varchar:"text",
        name:"text",
        text:"textarea"
    };


    //Function that attempts to retrieve metadata from offline cache.
    //If its not there, it will make a server call to start a sync
    //The sync will block the app
    //TODO add infinite loop prevetion in sync
    var _get = function (module, type) {
        if (typeof(_metadata[module]) == "undefined") {
            _metadata[module] = app.cache.get("metadata." + module);
        }
        if (!type)
            return _metadata[module];

        if (typeof(_metadata[module][type]) == "undefined") {
            app.Sync();
            return null;
        }

        return _metadata[module][type];
    }

    //Function that attempts to retrieve sugarFields from offline caches
    //If its not there, it will make a server call to start a sync
    //The sync will block the app
    //TODO add infinite loop prevetion in sync
    var _getSugarField = function (field) {

        // init results
        var result = {};

        var name = fieldTypeMap[field.name] || field.name;

        if (!name) {
            app.logger.error("No field name provided to getSugarField");
            return null;
        }

        // get sugarfield from app cache if we dont have it in memory
        if (typeof(_metadata.sugarFields[name]) == "undefined") {
            _metadata.sugarFields[name] = app.cache.get("metadata.sugarFields." + field.name);
        }

        // assign fields to results if set
        if (field.view && _metadata.sugarFields[name] && _metadata.sugarFields[name][field.view]) {
            result = _metadata.sugarFields[name][field.view];
            // fall back to detailview if field for this view doesnt exist
        } else if (_metadata.sugarFields[name] && _metadata.sugarFields[name]['detailView']) {
            result = _metadata.sugarFields[name]['detailView'];
            //fall back to base field detailview if none of the above exist
        } else if (_metadata.sugarFields['base'] && _metadata.sugarFields['base']['detailView']) {
            result = _metadata.sugarFields['base']['detailView'];
        } else {
            app.Sync();
            return null;
        }

        // get compiled template
        if (result.template && !result.templateC) {
            result.templateC = app.template.get(name + ":" + field.view);
            if (!result.templateC)
                result.templateC = app.template.compile(result.template, name + ":" + field.view);
        }

        return result;
    }

    /**
     *
     * @param string module name of module to retrieve from
     * @param string view optional name of view to get
     */
    var _getView = function (module, view) {
        var views = _get(module, "views");
        if (views != null) {
            if (view) {
                if (typeof(views[view]) != "undefined")
                    return views[view];
            } else {
                return views;
            }
        }
        return null;
    }

    var _getLayout = function (module, layout) {
        var layouts = _get(module, "layouts");
        if (layouts != null) {
            if (layout) {
                if (typeof(layouts[layout]) != "undefined")
                    return layouts[layout];
            } else {
                return layouts;
            }
        }
        return null;
    }

    var _getVardef = function (module, bean) {
        var beans = _get(module, "beans");
        if (!bean)
            bean = _get(module, "primary_bean");

        if (bean && beans[bean] && beans[bean].vardefs) {
            return beans[bean].vardefs
        }

        return null;
    }

    var _getFieldDef = function (module, bean, field) {
        var vardef = _getVardef(module, bean);
        if (vardef && vardef.fields)
            return vardef.fields[field];

        return null;
    }


    app.augment("metadata", {
        /**
         * The Metadata Manager get method should be the be the only accessor for metadata.
         *
         * @param Object params. Params can have the following properties. <ul>
         * <li>String module : Module to retrieve metadata for</li>
         * <li>String type : Type of metadata to retrieve, possible values are
         *   "view", "layout", and "vardef". If not specified, all the metadata
         *    for the given module is returned (Optional)</li>
         * <li>String view : Specific view to retrieve. If not specified, all views for the given
         *    module are returned.(Optional)</li>
         * <li>String layout : Specific layout to retrieve. If not specified, all layouts for the
         *     given module are returned.(Optional)</li>
         * <li>String bean : Specific bean to retrieve. If not specified, the vardefs for the
         *     primary bean are returned.(Optional) </li> </ul>
         *
         * @return Object metadata
         */
        get:function (params) {
            if (params && params.sugarField) {
                return _getSugarField(params.sugarField);
            }
            if (!params || !params.module) {
                app.logger.error("No module provided to metadata.get");
                return null;
            }
            if (!params.type)
                return _get(params.modules);

            if (params.type == "view")
                return _getView(params.module, params.view);

            if (params.type == "layout")
                return _getLayout(params.module, params.layout);

            if (params.type == "vardef")
                return _getVardef(params.module, params.bean);

            if (params.type == "fieldDef")
                return _getFieldDef(params.module, params.bean, params.field);
        },
        // set is going to be used by the sync function and will transalte
        // from server format to internal format for metadata
        set:function (data) {
            _.each(data, function (entry, module) {
                _metadata[module] = entry;
                app.cache.set("metadata." + module, entry);
            });
        }
    })
})(SUGAR.App);