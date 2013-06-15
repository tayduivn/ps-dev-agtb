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
     * Removes subpanels that user doesn't have access to. SP-924: Error message when opening subpanel
     * user doesn't have access to.
     *
     * @param {Array} components list of child components from layout definition
     * @return {Object} pruned components
     * @private
     * @override
     */
    _pruneNoAccessComponents: function(components) {
        var prunedComponents = [];
        var layoutFromContext = this.context ? this.context.get('layout') || this.context.get('layoutName') : null;
        this.layoutType = layoutFromContext ? layoutFromContext : app.controller.context.get('layout');
        this.aclToCheck = this.aclToCheck || (this.layoutType === 'record') ? 'view' : 'list';
        _.each(components, function(component) {
            var relatedModule,
                link = component.context ? component.context.link : null;
            if (link) {
                relatedModule = app.data.getRelatedModule(this.module, link);
                if (!relatedModule || relatedModule && app.acl.hasAccess(this.aclToCheck, relatedModule)) {
                    prunedComponents.push(component);
                }
            }
        }, this);
        return prunedComponents;
    },

    /**
     *
     * Removes hidden subpanels from list of components before adding them to layout
     *
     * @param {Array} components list of child components from layout definition
     * @return {Object} pruned components
     * @private
     * @override
     */
    _pruneHiddenComponents: function(components) {
        var hiddenSubpanels = app.metadata.getHiddenSubpanels();
        var visibleSubpanels = _.filter(components, function(component){
            var relatedModule = app.data.getRelatedModule(this.module, component.context.link);
            return _.isEmpty(_.find(hiddenSubpanels, function(hiddenPanel){
                //hidden subpanels seem to come back in lower case, so we do a case insenstiive compare of module names
                return hiddenPanel.toLowerCase() === relatedModule.toLowerCase();
            }));
        }, this);
        return visibleSubpanels;
    },

    /**
     * We override this method which is called early in the Sidecar framework to prune any hidden or acl prohibited components.
     * @param {Object} components The components
     * @param {Object} context    Current context
     * @param {String} module     Module
     */
    _addComponentsFromDef: function(components, context, module) {
        // First checks for hidden components, then checks for ACLs on those components.
        var allowedComponents = this._pruneHiddenComponents(components);
        allowedComponents = this._pruneNoAccessComponents(allowedComponents);
        // Call original Layout with pruned components
        app.view.Layout.prototype._addComponentsFromDef.call(this, allowedComponents, context, module);
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
            var link = component.context.get('link');
            if(!linkName || linkName === link) {
                component.context.set("hidden", false);
                component.show();
            } else {
                component.context.set("hidden", true);
                component.hide();
            }
        });
    }
})
