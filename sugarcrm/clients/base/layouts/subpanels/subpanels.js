({
    /**
     * @override
     */
    initialize: function(opts) {
        if (!opts.meta) return;

        app.view.Layout.prototype.initialize.call(this, opts);
        this.layout.on("subpanel:change", this.showSubpanel, this);
    },

    /**
     *
     * Removes hidden subpanels from list of components before adding them to layout
     *
     * @param {Array} components list of child components from layout definition
     * @private
     * @override
     */
    _addComponentsFromDef: function(components) {
        var hiddenSubpanels = app.metadata.getHiddenSubpanels();
        var visibleSubpanels = _.filter(components, function(component){
            var relatedModule = app.data.getRelatedModule(this.module, component.context.link);
            return _.isEmpty(_.find(hiddenSubpanels, function(hiddenPanel){
                //hidden subpanels seem to come back in lower case, so we do a case insenstiive compare of module names
                return hiddenPanel.toLowerCase() === relatedModule.toLowerCase();
            }));
        }, this);
        app.view.Layout.prototype._addComponentsFromDef.call(this, visibleSubpanels);
    },

    /**
     * Show the subpanel for the given linkName and hide all others
     * @param {String} linkName name of subpanel link
     */
    showSubpanel: function(linkName) {
        var self = this,
            cacheKey = "subpanels:last:" + self.module;
        if (linkName) {
            app.cache.set(cacheKey, linkName);
        }
        _.each(this._components, function(component) {
            if(!linkName || linkName === component.context.get("link")) {
                component.context.set("hidden", false);
                component.show();
            } else {
                component.context.set("hidden", true);
                component.hide();
            }
        });
    }
})
