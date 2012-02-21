(function(app){
    //Metadata that has been loaded from offline storage
    var _metadata = {};


    //Function that attempts to retrieve metadata from offline cache.
    //If its not there, it will make a server call to start a sync
    //The sync will block the app
    var _get = function(module, type) {
        if (typeof(_metadata[module]) == "undefined")
        {
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

    /**
     *
     * @param string module name of module to retrieve from
     * @param string view optional name of view to get
     */
    var _getView = function(module, view) {
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

    var _getLayout = function(module, layout) {
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

    var _getVardef = function(module, bean) {
        var beans = _get(module, "beans");
        if (!bean)
            bean = _get(module, "primary_bean");

        if (bean && beans[bean] && beans[bean].vardefs) {
            return beans[bean].vardefs
        }

        return null;
    }

    var _getFieldDef = function(module, bean, field) {
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
        get: function(params) {
            if (!params || !params.module) {
                app.logger.error("No module provided to metadata.get");
                return null;
            }
            if (!params.type)
                return _get(params.modules);

            if(params.type == "view")
                return _getView(params.module, params.view);

            if(params.type == "layout")
                return _getLayout(params.module, params.layout);

            if(params.type == "vardef")
                return _getVardef(params.module, params.bean);

            if(params.type == "fieldDef")
                return _getFieldDef(params.module, params.bean, params.field);
        },
        // set is going to be used by the sync function and will transalte
        // from server format to internal format for metadata
        set: function(data) {
            _.each(data, function(entry, module) {
                _metadata[module] = entry;
                app.cache.set("metadata." + module, entry);
            });
        }
    })
})(SUGAR.App);