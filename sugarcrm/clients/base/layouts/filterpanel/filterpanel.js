({
    extendsFrom: 'TogglepanelLayout',
    // This is set to the filter that's currently being edited.
    editingFilter: null,

    /**
     * @override
     * @param {Object} opts
     */
    initialize: function(opts) {
        this.on("filter:change", function(module, link) {
            this.currentModule = module;
            this.currentLink = link;
        }, this);

        this.on("filter:create:open", function(model) {
            this.$(".filter-options").show();
        }, this);

        this.on("filter:create:close", function(reinitialize, id) {
            if (reinitialize && !id) {
                this.trigger("filter:reinitialize");
            }
            this.$(".filter-options").hide();
        }, this);

        var lastViewed = app.user.lastState.get(this.toggleViewLastStateKey);
        app.view.invokeParent(this, {type: 'layout', name: 'togglepanel', method: 'initialize', args: [opts]});
        // Needed to initialize this.currentModule.
        this.trigger('filter:change', (lastViewed === "activitystream") ? 'Activities' : this.module);
    }
})
