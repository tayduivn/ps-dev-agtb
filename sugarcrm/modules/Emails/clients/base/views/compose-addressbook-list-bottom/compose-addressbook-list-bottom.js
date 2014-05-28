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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
/**
 * @class View.Views.Base.Emails.ComposeAddressbookListBottomView
 * @alias SUGAR.App.view.views.BaseEmailsComposeAddressbookListBottomView
 * @extends View.Views.Base.ListBottomView
 */
({
    extendsFrom: "ListBottomView",

    /**
     * Assign proper label for 'show more' link.
     * Label should be "More recipients...".
     */
    setShowMoreLabel: function() {
        this.showMoreLabel = app.lang.get('LBL_SHOW_MORE_RECIPIENTS', this.module);
    }
})
