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
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 */
({
    // TODO: Remove this View completely, when it is possible to place a standard help-dashlet to the Create layout

    /**
     * {@inheritDoc}
     */
    _renderHtml: function() {
        var helpObject = app.help.get(this.context.get('module'), 'create');
        this._super('_renderHtml', [helpObject, this.options]);
    }
})
