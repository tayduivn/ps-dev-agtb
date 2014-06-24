/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Layouts.Base.SubpanelsLayout
 * @alias SUGAR.App.view.layouts.BaseSubpanelsLayout
 * @extends View.Layout
 */
({
    /**
     * @override
     */
    initialize: function(opts) {
        if (!opts.meta) return;
        app.view.Layout.prototype.initialize.call(this, opts);
        this.layout.on("subpanel:change", this.showSubpanel, this);

        this.listenTo(this.layout, 'filter:change', function(linkModuleName, linkName) {
            //broadcast on subpanels layout so supanel-lists update
            this.trigger('filter:change', linkModuleName, linkName);
        });
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
                if (relatedModule !== false) {
                    //hidden subpanels seem to come back in lower case, so we do a case insenstiive compare of module names
                    return hiddenPanel.toLowerCase() === relatedModule.toLowerCase();
                }
                return true;
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
        this._markComponentsAsSubpanels();
        this._disableSubpanelToggleButton(allowedComponents);
    },

    /**
     * If no subpanels are left after pruning hidden and ACL prevented subpanels, we disable the filter panel's subpanel toggle button
     * @param  {Array} allowedComponents pruned subpanels
     */
    _disableSubpanelToggleButton: function(allowedComponents) {
        if (!allowedComponents || !allowedComponents.length) {
            this.layout.trigger('filterpanel:change', 'activitystream', true, true);
            this.layout.trigger('filterpanel:toggle:button', 'subpanels', false);//disable subpanels toggle button
        }
    },

    /**
     * Show the subpanel for the given linkName and hide all others
     * @param {String} linkName name of subpanel link
     */
    showSubpanel: function(linkName) {
        var self = this,
            //this.layout is the filter layout which subpanels is child of; we
            //use it here as it has a last_state key in its meta
            cacheKey = app.user.lastState.key('subpanels-last', this.layout);

        if (linkName) {
            app.user.lastState.set(cacheKey, linkName);
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
    },

    /**
     * Mark component context as being subpanels
     */
    _markComponentsAsSubpanels: function() {
        _.each(this._components, function(component) {
            component.context.set("isSubpanel", true);
        });
    },

    /**
     * Load data for all subpanels. Need to override the layout's loadData() because
     * it calls loadData() for the context, which we do not want to do here.
     * @param options
     */
    loadData: function(options) {
        var self = this,
            load = function(){
                _.each(this._components, function(component) {
                    component.loadData(options);
                });
            };
        if (self.context.parent && !self.context.parent.isDataFetched()) {
            var parent = this.context.parent.get("model");
            parent.once("sync", load);
        }
        else {
            load();
        }
    }
})
