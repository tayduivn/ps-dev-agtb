/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({
    extendsFrom: 'RowactionField',

    initialize: function (options) {
        app.view.invokeParent(this, {type: 'field', name: 'rowaction', method:'initialize', args:[options]});
        this.type = 'rowaction';
    },

    /**
     * Event to navigate to the BWC Manage Subscriptions
     */
    rowActionSelect: function() {
        var params = [
            {'name': 'sidecar_return', value: app.router.buildRoute(this.module, this.model.id)},
            {'name': 'return_module', value: this.module},
            {'name': 'record', value: this.model.id},
            {'name': 'action', value: 'Subscriptions'},
            {'name': 'module', value: 'Campaigns'}
        ];

        var route = '#bwc/index.php?' + $.param(params);
        app.router.navigate(route, {trigger: true});
    }
})
