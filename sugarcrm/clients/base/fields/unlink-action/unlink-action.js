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
 * Unlink row action used in subpanels and dashlets.
 *
 * @class View.Fields.Base.UnlinkActionField
 * @alias SUGAR.App.view.fields.BaseUnlinkActionField
 * @extends View.Fields.Base.RowactionField
 */
({
    extendsFrom: 'RowactionField',

    /**
     * {@inheritDoc}
     *
     * By default `list:unlinkrow:fire` event is triggered if none supplied
     * through metadata.
     */
    initialize: function(options) {
        options.def.event = options.def.event || 'list:unlinkrow:fire';
        this._super('initialize', [options]);
        this.type = 'rowaction';
    },

    /**
     * {@inheritDoc}
     *
     * If parent module matches `Homepage` then `false` is returned.
     *
     * Plus, we cannot unlink one-to-many relationships when the relationship
     * is a required field - if that's the case `false` is returned as well.
     *
     * @return {Boolean} `true` if access is allowed, `false` otherwise.
     */
    hasAccess: function() {
        var parentModule = this.context.get('parentModule');
        if (parentModule === 'Home') {
            return false;
        }

        var link = this.context.get('link');
        if (link && app.utils.isRequiredLink(parentModule, link)) {
            return false;
        }

        return this._super('hasAccess');
    }
})
