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

    var _getVardef = function(module, bean) {
        var beans = _get(module, "beans");
        if (!bean)
            bean = _get(module, "primary_bean");

        if (bean && beans[bean] && beans[bean].vardefs) {
            return beans[bean].vardefs
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

    app.augment("metadata", {
        //All retreives of metadata should hit this function.
        get: function(params) {
            if (!params || !params.module)
                return null;
            if (!params.type)
                return _get(params.modules);

            if(params.type == "view")
                return _getView(params.module, params.view);

            if(params.type == "layout")
                return _getLayout(params.module, params.layout);

            if(params.type == "vardef")
                return _getVardef(params.module, params.view);
        },
        // set is going to be used by the sync function and will transalte
        // from server format to internal format for metadata
        set: function(data) {

        }
    })
})(SUGAR.App);