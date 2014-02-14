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

    duns_num: null,

    competitorsList: null,

    //D&B Firmographic API product codes
    competitorsDD: {
        'dunsnum': {
            'json_path': 'DUNSNumber'
        },
        'orgname': {
            'json_path': 'OrganizationPrimaryName.OrganizationName.$',
            'case_fmt': true
        },
        'town': {
            'json_path': 'PrimaryAddress.PrimaryTownName',
            'case_fmt': true
        },
        'ctrycd': {
            'json_path': 'PrimaryAddress.CountryISOAlpha2Code',
            'case_fmt': false
        },
        'territoryabbreviatedname': {
            'json_path': 'PrimaryAddress.TerritoryAbbreviatedName',
            'case_fmt': true
        },
        'countryofficialname': {
            'json_path': 'PrimaryAddress.CountryOfficialName',
            'case_fmt': true
        },
        'salesrevenueamt': {
            'json_path': 'SalesRevenueAmount.0.$',
            'case_fmt': false
        },
        'topcompetitorindicator': {
            'json_path': 'TopCompetitorIndicator',
            'case_fmt': false
        }
    },

    competitorsConst: {
        'responseCode' : 'FindCompetitorResponse.TransactionResult.ResultID',
        'responseMsg' : 'FindCompetitorResponse.TransactionResult.ResultText',
        'competitorsPath' : 'FindCompetitorResponse.FindCompetitorResponseDetail.Competitor'
    },

    initialize: function(options) {
        this._super('initialize', [options]);
        if (this.layout.collapse) {
            this.layout.collapse(true);
        }
        this.layout.on('dashlet:collapse', this.loadCompetitors, this);
        app.events.on('dnbcompinfo:duns_selected', this.collapseDashlet, this);
    },

    loadData: function(options) {
        if (this.model.get('duns_num')) {
            this.duns_num = this.model.get('duns_num');
        }
    },

    /**
     * Refresh dashlet once Refresh link clicked from geat button
     * To show updated data from D&B Competitors service
     */
    refreshClicked: function() {
        this.loadCompetitors(false);
    },

    /**
     * Handles the dashlet expand | collapse events
     * @param  {Boolean} isCollapsed
     */
    loadCompetitors: function(isCollapsed) {
        //if the dashlet is not collapsed load data from D&B
        if (!isCollapsed) {
            //check if account is linked with a D-U-N-S
            if (this.duns_num) {
                this.getDNBCompetitors(this.duns_num);
            } else if (!_.isUndefined(app.controller.context.get('dnb_temp_duns_num'))) {
                //check if D-U-N-S is set in context by refresh dashlet
                this.getDNBCompetitors(app.controller.context.get('dnb_temp_duns_num'));
            } else {
                this.template = app.template.get(this.name + '.dnb-no-duns');
                if (!this.disposed) {
                    this.render();
                }
            }
        }
    },

    /**
     * Retrieves dnb competitors for the given duns no.
     * @param  {String} duns_num
     */
    getDNBCompetitors: function(duns_num) {
        var self = this;
        if (duns_num) {
            self.template = app.template.get(self.name);
            if (!self.disposed) {
                self.render();
                self.$('div#dnb-competitors-list').hide();
                self.$('div#dnb-no-data').hide();
            }
            //check if cache has this data already
            var cacheKey = 'dnb:competitors:' + duns_num;
            if (app.cache.get(cacheKey)) {
                self.renderCompetitors.call(self, app.cache.get(cacheKey));
            } else {
                var dnbCompetitorsURL = app.api.buildURL('connector/dnb/competitors/' + duns_num, '', {},{});
                var resultData = {'competitors': null, 'errmsg' : null};
                app.api.call('READ', dnbCompetitorsURL, {},{
                    success: function(data) {
                        var responseCode = self.getJsonNode(data, self.competitorsConst.responseCode),
                            responseMsg = self.getJsonNode(data, self.competitorsConst.responseMsg);
                        if (responseCode && responseCode === self.responseCodes.success) {
                            var topCompGroup = _.groupBy(self.getJsonNode(data, self.competitorsConst.competitorsPath),
                                function(competitorObj) {
                                    return competitorObj.TopCompetitorIndicator;
                                });
                            if (topCompGroup.hasOwnProperty('true') && topCompGroup.hasOwnProperty('false')) {
                                resultData.competitors = _.union(topCompGroup.true, topCompGroup.false);
                            } else {
                                resultData.competitors = data.FindCompetitorResponse.FindCompetitorResponseDetail.Competitor;
                            }
                            _.each(resultData.competitors, function(competitorObj) {
                                var salesPath = 'SalesRevenueAmount.0.$';
                                if (self.checkJsonNode(competitorObj, salesPath)) {
                                    competitorObj.SalesRevenueAmount[0].$ = '$' + self.formatSalesRevenue(competitorObj.SalesRevenueAmount[0].$) +
                                        app.lang.get('LBL_DNB_MILLION');
                                }
                            });
                            self.competitorsList = resultData;
                            app.cache.set(cacheKey, resultData);
                        } else {
                            resultData.errmsg = responseMsg || app.lang.get('LBL_DNB_SVC_ERR');
                        }
                        self.renderCompetitors.call(self, resultData);
                    },
                    error: _.bind(self.checkAndProcessError, self)
                });
            }
        } else {
            self.template = app.template.get(self.name + '.dnb-no-duns');
            if (!self.disposed) {
                self.render();
            }
        }
    },

    /**
     * Renders the list of competitors
     * @param  {Object} competitorsList
     */
    renderCompetitors: function(competitorsList) {
        if (this.disposed) {
            return;
        }
        this.template = app.template.get(this.name);
        this.dnbComp = {};
        if (competitorsList.competitors) {
            this.dnbComp.product = this.formatCompetitors(competitorsList.competitors, this.competitorsDD);
        } else {
            this.dnbComp.errmsg = competitorsList.errmsg;
        }
        this.render();
        this.$('div#dnb-competitors-loading').hide();
        this.$('div#dnb-no-data').hide();
        this.$('div#dnb-competitors-list').show();
    },

    /**
     * Preprocessing search results
     * @param {Object} competitorsList DNB API Response for Competitors
     * @param {Object} competitorsDD Data Elements Map
     * @return {Array} Formatted array of competitors
     */
    formatCompetitors: function(competitorsList, competitorsDD) {
        var formattedCompetitors = [];
        //iterate thru the search results, extract the necessary info
        //populate a js object
        //push it through an array
        _.each(competitorsList, function(competitorObj) {
            var frmtCompetitorsObj = {};
            _.each(competitorsDD, function(value, key) {
                var dataElement = this.getJsonNode(competitorObj, value.json_path);
                if (dataElement) {
                    if (value.case_fmt) {
                        dataElement = this.properCase(dataElement);
                    }
                    frmtCompetitorsObj[key] = dataElement;
                }
            },this);
            formattedCompetitors.push(frmtCompetitorsObj);
        },this);

        return formattedCompetitors;
    }
});
