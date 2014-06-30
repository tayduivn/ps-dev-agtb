/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ('Company') that Company is bound by
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
    extendsFrom: 'DnbView',

    events: {
        'click .importContacts': 'importContacts',
        'click .backToContactsList': 'backToContactsList',
        'click .dnb-cnt-prem': 'baseGetContactDetails',
        'click .dnb-cnt-std': 'baseGetContactDetails'
    },

    selectors: {
        'load': '#dnb-bal-result-loading',
        'rslt': '#dnb-bal-result',
        'contactrslt': '#dnb-bal-contact-list'
    },

    /**
     * @override
     * @param {Object} options
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        app.events.on('dnbbal:invoke', this.invokeBAL, this);
        var originalMeta = app.metadata.getView('','dnb-bal-results');
        if (originalMeta.import_enabled_modules) {
            this.import_enabled_modules = originalMeta.import_enabled_modules;
        }
    },

    /**
     * Overriding the render function to populate the import type drop down
     */
    _render: function() {
        //TODO: Investigate why using this._super('_renderHtml');
        //we get Unable to find method _renderHtml on parent class of dnb-bal-results
        app.view.View.prototype._renderHtml.call(this);
        this.$('#importType').select2();
    },

    loadData: function(options) {
        if (this.disposed) {
            return;
        }
        this.template = app.template.get(this.name + '.dnb-bal-hint');
        this.render();
    },

    /**
     * Listens for model change for certain attributes
     * Captures these attributes and invokes bal
     * @param {Object} balParams
     */
    invokeBAL: function(balParams) {
        if (!_.isEmpty(balParams)) {
            this.buildAList(balParams);
        } else {
            this.loadData();
        }
    },

    /**
     * Build a list of accounts
     * @param {Object} balParams
     */
    buildAList: function(balParams) {
        if (this.disposed) {
            return;
        }
        this.template = app.template.get(this.name + '.dnb-bal-contacts-rslt');
        if (this.dnbContactsList && this.dnbContactsList.count) {
            delete this.dnbContactsList['count'];
        }
        this.render();
        this.$(this.selectors.load).removeClass('hide');
        this.$(this.selectors.rslt).addClass('hide');
        //this is required for duplicate check
        balParams.contactType = this.module;
        this.baseContactsBAL(balParams, this.renderBAL);
    },

    /**
     * Renders the list of D&B Contacts
     * @param {Object} dnbApiResponse
     */
    renderBAL: function(dnbApiResponse) {
        if (this.disposed) {
            return;
        }
        this.template = app.template.get(this.name + '.dnb-bal-contacts-rslt');
        var dnbContactsList = {};
        if (dnbApiResponse.product) {
            this.contactsList = dnbApiResponse.product;
            var apiContactList = this.getJsonNode(dnbApiResponse.product, this.contactConst.contactsPath);
            dnbContactsList.product = this.formatContactList(apiContactList, this.contactsListDD);
            dnbContactsList.count = this.getJsonNode(dnbApiResponse.product, this.contactConst.srchCount);
            if (dnbContactsList.count) {
                dnbContactsList.count = app.lang.get('LBL_DNB_BAL_RSLT_CNT', this.module) + " (" + this.formatSalesRevenue(dnbContactsList.count) + ")";
            }
        } else if (dnbApiResponse.errmsg) {
            dnbContactsList.errmsg = dnbApiResponse.errmsg;
        }
        this.dnbContactsList = dnbContactsList;
        this.render();
        this.$(this.selectors.load).addClass('hide');
        this.$(this.selectors.rslt).removeClass('hide');
    },

    /**
     * Back to contacts list functionality
     */
    backToContactsList: function() {
        if (this.disposed) {
            return;
        }
        this.template = app.template.get(this.name + '.dnb-bal-contacts-rslt');
        if (this.dnbContactsList && this.dnbContactsList.count) {
            delete this.dnbContactsList['count'];
        }
        this.render();
        this.$(this.selectors.load).removeClass('hide');
        this.$(this.selectors.rslt).addClass('hide');
        var dupeCheckParams = {
            'type': this.module,
            'apiResponse': this.contactsList,
            'module': 'contacts'
        };
        this.baseDuplicateCheck(dupeCheckParams, this.renderBAL);
    },

    importContacts: function() {
        var module = this.$('#importType').val();
        this.baseImportContact(module);
    }
})
