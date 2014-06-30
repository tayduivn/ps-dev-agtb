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
        'click .importDNBData': 'importDNBData',
        'click a.dnb-company-name': 'getCompanyDetails',
        'click .backToList': 'backToCompanyList'
    },

    selectors: {
        'load': '#dnb-bal-result-loading',
        'rslt': '#dnb-bal-result'
    },

    /*
     * @property {Object} balAcctDD Data Dictionary For D&B BAL Response
     */
    balAcctDD: null,

    /**
     * @override
     * @param {Object} options
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        app.events.on('dnbbal:invoke', this.invokeBAL, this);
        this.initDD();
    },

    /**
     * Initialize the bal data dictionary
     */
    initDD: function() {
        this.balAcctDD = {
            'name': this.searchDD.companyname,
            'duns_num': this.searchDD.dunsnum,
            'billing_address_street': this.searchDD.streetaddr,
            'billing_address_city': this.searchDD.town,
            'billing_address_state': this.searchDD.territory,
            'billing_address_country': this.searchDD.ctrycd
        };
        this.balAcctDD.locationtype = this.searchDD.locationtype;
        this.balAcctDD.isDupe = this.searchDD.isDupe;
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
            this.buildAListAccounts(balParams);
        } else {
            this.loadData();
        }
    },

    /**
     * Build a list of accounts
     * @param {Object} balParams
     */
    buildAListAccounts: function(balParams) {
        if (this.disposed) {
            return;
        }
        this.template = app.template.get(this.name + '.dnb-bal-acct-rslt');
        if (this.dnbBalRslt && this.dnbBalRslt.count) {
            delete this.dnbBalRslt['count'];
        }
        this.render();
        this.$(this.selectors.load).removeClass('hide');
        this.$(this.selectors.rslt).addClass('hide');
        this.baseAccountsBAL(balParams, this.renderBALAcct);
    },

    /**
     * Render BAL Accounts results
     * @param {Object} dnbBalApiRsp BAL API Response
     */
    renderBALAcct: function(dnbBalApiRsp) {
        if (this.disposed) {
            return;
        }
        var dnbBalRslt = {};
        if (dnbBalApiRsp.product) {
            this.companyList = dnbBalApiRsp.product;
            var apiCompanyList = this.getJsonNode(dnbBalApiRsp.product, this.commonJSONPaths.srchRslt);
            dnbBalRslt.product = this.formatSrchRslt(apiCompanyList, this.balAcctDD);
            dnbBalRslt.count = this.getJsonNode(dnbBalApiRsp.product, this.commonJSONPaths.srchCount);
            //template for displaying count in dashlet headers
            if (dnbBalRslt.count) {
                dnbBalRslt.count = app.lang.get('LBL_DNB_BAL_ACCT_HEADER') + "(" + this.formatSalesRevenue(dnbBalRslt.count) + ")";
            }
        }
        if (dnbBalApiRsp.errmsg) {
            dnbBalRslt.errmsg = dnbBalApiRsp.errmsg;
        }
        this.dnbBalRslt = dnbBalRslt;
        this.template = app.template.get(this.name + '.dnb-bal-acct-rslt');
        this.render();
        this.$(this.selectors.load).addClass('hide');
        this.$(this.selectors.rslt).removeClass('hide');
    },

    /**
     * Gets D&B Company Details For A DUNS number
     * DUNS number is stored as an id in the anchor tag
     * @param {Object} evt
     */
    getCompanyDetails: function(evt) {
        if (this.disposed) {
            return;
        }
        var duns_num = evt.target.id;
        if (duns_num) {
            this.template = app.template.get(this.name + '.dnb-company-details');
            this.render();
            this.$('div#dnb-company-details').hide();
            this.$('.importDNBData').hide();
            this.baseCompanyInformation(duns_num, this.compInfoProdCD.std, app.lang.get('LBL_DNB_BAL_LIST'), this.renderCompanyDetails);
        }
    },

    /**
     * Renders the dnb company details for adding companies from dashlets
     * Overriding the base dashlet function
     * @param {Object} companyDetails dnb api response for company details
     */
    renderCompanyDetails: function(companyDetails) {
        if (this.disposed) {
            return;
        }
        var formattedFirmographics, dnbFirmo = {};
        //if there are no company details hide the import button
        if (companyDetails.errmsg) {
            this.$('.importDNBData').hide();
            dnbFirmo.errmsg = companyDetails.errmsg;
        } else if (companyDetails.product) {
            this.$('.importDNBData').show();
            formattedFirmographics = this.formatCompanyInfo(companyDetails.product, this.accountsDD);
            dnbFirmo.product = formattedFirmographics;
            dnbFirmo.backToListLabel = companyDetails.backToListLabel;
            this.currentCompany = companyDetails.product;
        }
        this.dnbFirmo = dnbFirmo;
        this.render();
        this.$('div#dnb-company-detail-loading').hide();
        this.$('div#dnb-company-details').show();
    },

    /**
     * navigates users from company details back to results pane
     */
    backToCompanyList: function() {
        if (this.disposed) {
            return;
        }
        if (this.dnbBalRslt && this.dnbBalRslt.count) {
            delete this.dnbBalRslt['count'];
        }
        this.template = app.template.get(this.name + '.dnb-bal-acct-rslt');
        this.render();
        this.$(this.selectors.load).removeClass('hide');
        this.$(this.selectors.rslt).addClass('hide');
        var dupeCheckParams = {
            'type': 'duns',
            'apiResponse': this.companyList,
            'module': 'findcompany'
        };
        this.baseDuplicateCheck(dupeCheckParams, this.renderBALAcct);
    }
})
