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
 * @class View.Views.Base.DnbPremiumCompanyInfoView
 * @alias SUGAR.App.view.views.BaseDnbPremiumCompanyInfoView
 * @extends View.Views.Base.DnbView
 */
({
    extendsFrom: 'DnbView',

    duns_num: null,

    //will contain the data elements selected by the user from the dashlet confid
    //filtered data dictionary
    filteredDD: null,

    events: {
        'click .showMoreData': 'showMoreData',
        'click .showLessData': 'showLessData'
    },

    initDashlet: function() {
        this._super('initDashlet');
        this.baseFilterData();
    },

    initialize: function(options) {
        this._super('initialize', [options]);
        if (this.disposed) {
            return;
        }
        if (this.layout.collapse) {
            this.layout.collapse(true);
        }
        this.layout.on('dashlet:collapse', this.loadCompanyInfo, this);
        app.events.on('dnbcompinfo:duns_selected', this.collapseDashlet, this);
    },

    loadData: function(options) {
        if (this.model.get('duns_num')) {
            this.duns_num = this.model.get('duns_num');
        }
        this.baseFilterData();
    },

    /**
     * Refresh dashlet once Refresh link clicked from gear button
     * To show updated premium company information from DNB service
     */
    refreshClicked: function() {
        this.loadCompanyInfo(false);
    },

    /**
     * Handles the dashlet expand | collapse events
     * @param  {Boolean} isCollapsed
     */
    loadCompanyInfo: function(isCollapsed) {
        if (!isCollapsed) {
            //check if account is linked with a D-U-N-S
            if (this.duns_num) {
                this.getDNBPremCompanyDetails(this.duns_num);
            } else if (!_.isUndefined(app.controller.context.get('dnb_temp_duns_num'))) {
                //check if D-U-N-S is set in context by refresh dashlet
                this.getDNBPremCompanyDetails(app.controller.context.get('dnb_temp_duns_num'));
            } else {
                this.template = app.template.get('dnb.dnb-no-duns');
                if (!this.disposed) {
                    this.render();
                }
            }
        }
    },

    /**
     * Gets Premium Company Information
     * @param duns_num duns_num
     */
    getDNBPremCompanyDetails: function(duns_num) {
        if (this.disposed) {
            return;
        }
        this.dnbFirmo = {};
        this.template = app.template.get('dnb.dnb-comp-info');
        this.dnbFirmo.loading_label = app.lang.get('LBL_DNB_PREMIUM_COMPANY_INFO_LOADING');
        this.render();
        this.$('div#dnb-compinfo-loading').show();
        this.$('div#dnb-compinfo-details').hide();
        this.baseCompanyInformation(duns_num, this.compInfoProdCD.prem, null, this.renderCompanyInformation);
    }
})
