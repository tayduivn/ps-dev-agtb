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
    /**
     * @inheritDoc
     */
    tagName: "span",
    /**
     * @inheritDoc
     */
    events: {
        'click [data-public-module="kbs-contents"]': 'loginAsGuest'
    },

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        if (app.config.publicKnowledgeBase !== 'yes' && app.api.isAuthenticated() && _.isEmpty(app.user.id)) {
            app.logout();
        }
    },

    /**
     * @inheritDoc
     */
    _renderHtml: function() {
        this.isVisible = _.isEmpty(app.user.id) 
                        && app.config.publicKnowledgeBase === 'yes' 
                        && !app.api.isAuthenticated();
        this._super('_renderHtml');    
    },

    /**
     * Log in a guest user when enabled public access to Knowledge Base module.
     */
    loginAsGuest: function() {
        if (app.config.publicKnowledgeBase !== 'yes') {
            return;
        }
        app.login({
            username: 'SugarCustomerSupportPortalUser',
            password: 'SugarCustomerSupportPortalUser'
        }, null);
    }
})
