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
 * @class View.Views.Base.DnbAccountCreateView
 * @alias SUGAR.App.view.views.BaseDnbAccountCreateView
 * @extends View.Views.Base.DnbView
 */
({
    extendsFrom: 'DnbView',

    duns_num: '',

    //used to detect if import was clicked for the first time when company info was loaded
    //this flag is being used to avoid the warning message for account name
    //when the user clicks the import button first time when company info is loaded
    //a user clicks import
    importFlag: false,

    companyList: null,

    keyword: null,

    plugins: ['Connector'],

    events: {
        'click a.dnb-company-name': 'dunsClickHandler',
        'click .showMoreData': 'showMoreData',
        'click .showLessData': 'showLessData',
        'click .importDNBData': 'importAccount',
        'click .dnb_checkbox': 'importCheckBox',
        'click .clearDNBResults': 'clearDNBResults',
        'click .backToList' : 'backToCompanyList'
    },

    configuredKey: 'dnb:account:create:configured',

    initialize: function(options) {
        this._super('initialize', [options]);
        this.initDashlet();
        this.loadData();
    },

    loadData: function() {
        if (this.disposed) {
            return;
        }
        this.checkConnector('ext_rest_dnb',
            _.bind(this.loadDataWithValidConnector, this),
            _.bind(this.handleLoadError, this),
            ['test_passed']);
    },

    /**
     * Success callback to be run when Connector has been verified and validated
     */
    loadDataWithValidConnector: function() {
        this.template = app.template.get(this.name + '.dnb-search-hint');
        this.render();
        this.context.on('input:name:keyup', this.dnbSearch, this);
        this.errmsg = null;
    },

    /**
     * Failure callback to be run if Connector verification fails
     * @param {object} connector that failed
     */
    handleLoadError: function(connector) {
        this.errmsg = 'LBL_DNB_NOT_CONFIGURED';
        this.template = app.template.get(this.name + '.dnb-need-configure');
        this.render();
        this.context.off('input:name:keyup', this.dnbSearch);
    },

    /**
     * Navigates from the company details screen to the search results screen
     */
    backToCompanyList: function() {
        this.renderCompanyList.call(this, this.companyList);
    },

    /**
     * Render search results
     * @param  {Object} dnbSrchApiResponse
     */
    renderCompanyList: function(dnbSrchApiResponse) {
        if (this.disposed) {
            return;
        }
        this.template = app.template.get(this.name);
        var dnbSrchResults = {};
        if (dnbSrchApiResponse.companies) {
            this.companyList = dnbSrchApiResponse;
            dnbSrchResults.product = this.formatSrchRslt(dnbSrchApiResponse.companies, this.searchDD);
        }
        if (dnbSrchApiResponse.errmsg) {
            dnbSrchResults.errmsg = dnbSrchApiResponse.errmsg;
        }
        this.dnbSrchResults = dnbSrchResults;
        this.render();
        this.$('div#dnb-company-list-loading').hide();
        this.$('div#dnb-search-results').show();
        this.$('.showLessData').hide();
    },

    /** event listener for keyup / autocomplete feature
     * @param {String} searchString
     */
    dnbSearch: function(searchString) {
        if (this.disposed) {
            return;
        }
        if (!this.keyword || (this.keyword && this.keyword !== searchString)) {
            this.keyword = searchString;
            this.template = app.template.get(this.name);
            this.render();
            this.$('table#dnb_company_list').empty(); //empty results table
            this.$('div#dnb-search-results').hide(); //hide results div
            this.$('div#dnb-company-list-loading').show(); //show loading text
            this.$('.clearDNBResults').attr('disabled', 'disabled'); //disable clear button
            this.$('.clearDNBResults').removeClass('enabled');
            this.$('.clearDNBResults').addClass('disabled');
            this.companyList = null;
            this.baseCompanySearch(searchString, this.renderCompanyList);
        }
    },

    /**
     * Clear D&B Search Results
     */
    clearDNBResults: function() {
        this.$('table#dnb_company_list').empty();
        this.template = app.template.get(this.name + '.dnb-search-hint');
        this.render();
    },

    /**
     * Event handler for handling clicks on D&B Search Results
     * @param  {Object} evt
     */
    dunsClickHandler: function(evt) {
        var duns_num = evt.target.id;
        this.dnbProduct = null;
        if (duns_num) {
            this.template = app.template.get(this.name + '.dnb-company-details');
            this.render();
            this.$('div#dnb-company-detail-loading').show();
            this.$('div#dnb-company-details').hide();
            this.$('.importDNBData').hide();
            this.baseCompanyInformation(duns_num, this.compInfoProdCD.std,
                app.lang.get('LBL_DNB_BACK_TO_SRCH'), this.renderCompanyDetails);
        }
    },


    /**
     * Renders the dnb company details with checkboxes
     * @param {Object} companyDetails
     */
    renderCompanyDetails: function(companyDetails) {
        if (this.disposed) {
            return;
        }
        this.dnbProduct = {};
        if (companyDetails.product) {
            var duns_num = this.getJsonNode(companyDetails.product, this.appendSVCPaths.duns);
            if (!_.isUndefined(duns_num)) {
                this.duns_num = duns_num;
                this.dnbProduct.product = this.formatCompanyInfo(companyDetails.product, this.accountsDD);
            }
        }
        if (companyDetails.errmsg) {
            this.dnbProduct.errmsg = companyDetails.errmsg;
        }
        this.render();
        this.$('div#dnb-company-detail-loading').hide();
        this.$('div#dnb-company-details').show();
        if (this.dnbProduct.errmsg) {
            this.$('.importDNBData').hide();
        } else {
            this.$('.importDNBData').show();
        }
    },

    /**
     * Import Account Information
     */
    importAccount: function() {
        this.importAccountsData(this.importFlag);
        this.importFlag = true;
    },

    /**
     * Checkbox change event handler
     */
    importCheckBox: function() {
        var dnbCheckBoxes = this.$('.dnb_checkbox:checked');
        this.$('.importDNBData').toggleClass('disabled', dnbCheckBoxes.length === 0);
    }
})
