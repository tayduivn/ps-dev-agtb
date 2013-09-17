({
    extendsFrom:"PanelLayout",

    /**
     * @override
     */
    initialize: function(opts) {
        opts.type = "panel";
        //Check for the override_subpanel_list_view from the parent layout metadata and replace the list view if found.
        if (opts.meta && opts.def && opts.def.override_subpanel_list_view) {
            _.each(opts.meta.components, function(def){
                if (def.view && def.view == "subpanel-list") {
                    def.view = opts.def.override_subpanel_list_view;
                }
            });
            // override last_state.id with "override_subpanel_list_view" for unique state name.
            if(opts.meta.last_state.id) {
                opts.meta.last_state.id = opts.def.override_subpanel_list_view;
            }
        }

        // add the ability to add back in the mass update if it's required, currently used by the RLI Subpanel on
        // Opportunities
        if (opts.meta && opts.def && opts.def.include_mass_update_view) {
            opts.meta.components.push({view: 'massupdate'});
        }
        app.view.invokeParent(this, {type: 'layout', name: 'panel', method: 'initialize', args:[opts]});
    }
})
