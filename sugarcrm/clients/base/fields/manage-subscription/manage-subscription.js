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
/**
 * @class View.Fields.Base.ManageSubscriptionField
 * @alias SUGAR.App.view.fields.BaseManageSubscriptionField
 * @extends View.Fields.Base.RowactionField
 */
({
    extendsFrom: 'RowactionField',

    initialize: function (options) {
        this._super("initialize", [options]);
        this.type = 'rowaction';
    },

    /**
     * Event to navigate to the BWC Manage Subscriptions
     */
    rowActionSelect: function() {

        var route = app.bwc.buildRoute('Campaigns', this.model.id, 'Subscriptions', {
            return_module: this.module,
            return_id: this.model.id
        });
        app.router.navigate(route, {trigger: true});
    }
})
