({
    extendsFrom:"PanelLayout",

    /**
     * @override
     */
    initialize: function(opts) {
        opts.type = "panel";
        console.log(opts);
        if (opts.meta && opts.def && opts.def.override_subpanel_list_view) {
            _.each(opts.meta.components, function(def){
                if (def.view && def.view == "subpanel-list") {
                    def.view = opts.def.override_subpanel_list_view;
                }
            });
        }

        app.view.invokeParent(this, {type: 'layout', name: 'panel', method: 'initialize', args:[opts]});
    }
})
