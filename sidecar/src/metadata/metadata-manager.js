(function(app){
    //Private cache of metadata
    var _cache = app.Cache;
    //Metadata that has been loaded from offline storage
    var _metadata = {};


    //Function that attempts to retrieve metadata from offline cache.
    //If its not there, it will make a server call to start a sync
    //The sync will block the app
    var _get = function(module, type) {
        if (typeof(_metadata[module]) == "undefined")
        {
            _metadata[module] = _cache.get("metadata." + module + "." + type);
        }
        if (!type)
            return _metadata[module];

        if (typeof(_metadata[module][type]) == "undefined") {
            app.Sync();
            return null;
        }

        return _metadata[module][type];
    }

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

    app.augment("MetadataManager", {
    //app.MetadataManager = {
        //All retreives of metadata should hit this function.
        get: function(params) {
            if (!params || !params.module)
                return null;
            if (!params.type)
                return _get(params.modules);
            if(params.type == "view")
                return _getView(params.module, params.view);
        },
        //set is going to be used by the sync function and will transalte from server format to internal format for metadata
        set: function(data) {

        }
    })
})(SUGAR.App);