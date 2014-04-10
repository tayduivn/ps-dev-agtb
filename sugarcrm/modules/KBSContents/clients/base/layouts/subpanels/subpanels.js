/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
({
    extendsFrom: 'SubpanelsLayout',

    /**
     * {@inheritDoc}
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
                var aclToCheck = component.acl_action || this.aclToCheck;
                if (!relatedModule || relatedModule && app.acl.hasAccess(aclToCheck, relatedModule)) {
                    prunedComponents.push(component);
                }
            }
        }, this);
        return prunedComponents;
    }
})
