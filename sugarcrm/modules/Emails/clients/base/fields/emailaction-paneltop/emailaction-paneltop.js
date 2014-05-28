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
 * @class View.Fields.Base.Emails.EmailactionPaneltopField
 * @alias SUGAR.App.view.fields.BaseEmailsEmailactionPaneltopField
 * @extends View.Fields.Base.EmailactionField
 */
({
    extendsFrom: 'EmailactionField',
    plugins: ['EmailClientLaunch'],

    /**
     * {@inheritDoc}
     * Set type to emailaction to get the template
     */
    initialize: function(options) {
        this._super("initialize", [options]);
        this.type = 'emailaction';
        this.on('emailclient:close', this.handleEmailClientClose, this);
    },

    /**
     * When email compose is done, refresh the data in the Emails subpanel
     */
    handleEmailClientClose: function() {
        var context = this.context.parent || this.context;
        context.trigger('panel-top:refresh', 'emails');
        context.trigger('panel-top:refresh', 'archived_emails');
    }
})
