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

    statesList: '',

    countryList: '',

    duns_num: null,

    selectedCountry: 'Country',

    companyList: null,

    //array of data elements to be imported into the accounts model
    dataElements: ['name', 'billing_address_street', 'billing_address_city', 'billing_address_state',
        'billing_address_country', 'billing_address_postalcode', 'website', 'phone_office',
        'employees', 'annual_revenue', 'ownership', 'sic_code'],

    events: {
        'click a#dnb-lookup': 'dnbCompanySearch',
        'click a.dnb-company-name': 'dunsClickHandler',
        'click .showMoreData': 'showMoreData',
        'click .showLessData': 'showLessData',
        'click .importDNBData': 'importDNBData',
        'click .dnb_checkbox': 'importCheckBox',
        'change #countryList': 'changeState',
        'change #stateList': 'validateMatchParams',
        'click #dnb-match-btn': 'invokeCMRequest',
        'click .backToList' : 'renderCompanyList',
        'click #dnb-refresh' : 'getCompInfo'
    },

    initialize: function(options) {
        this._super('initialize', [options]);
        app.events.register('dnbcompinfo:duns_selected', this);
        app.events.register('dnbcompinfo:industry_code', this);
        this.statesList = app.lang.getAppListStrings('dnb_states_iso');
        this.countryList = app.lang.getAppListStrings('dnb_countries_iso');
    },

    _render: function() {
        app.view.View.prototype._renderHtml.call(this);
        this.$('#countryList').select2({
            placeholder: 'Select a Country',
            data: this.populateCountry()
        });
        this.$('#stateList').select2({
            placeholder: 'Select a State',
            data: this.populateState(this.selectedCountry)
        });
    },

    /**
     * Gets company information for the duns num
     */
    getCompInfo: function() {
        this.getDNBCompanyDetails(this.model.get('duns_num'));
    },

    /**
     * Gets the refresh information on the duns_num
     * @param  {String} duns_num
     */
    refreshcheck: function(duns_num) {
        var dnbRefreshCheck = app.api.buildURL('connector/dnb/refreshcheck/' + duns_num, '', {},{});
        var self = this;
        self.template = app.template.get(self.name);
        self.render();
        self.$('div#dnb-refresh-loading').show();
        self.$('div#dnb-refresh-details').hide();
        var resultData = {'uptodate': null, 'errmsg': null};
        app.api.call('READ', dnbRefreshCheck, {},{
            success: function(data) {
                //to do error handling
                var lastRefreshedDatePath = 'GetRefreshByOrganizationsResponse.GetRefreshByOrganizationsResponseDetail.CheckRefreshCandidateDetail.0.LastUpdateDate';
                if (self.checkJsonNode(data, lastRefreshedDatePath)) {
                    try {
                        var lastRefreshDate = app.date.parse(data.GetRefreshByOrganizationsResponse.GetRefreshByOrganizationsResponseDetail.CheckRefreshCandidateDetail[0].LastUpdateDate, 'YYYY-mm-dd');
                        lastRefreshDate.setMonth(lastRefreshDate.getMonth() + 3);
                        var currentDate = new Date();
                        //if lastRefreshDate + 30 > currentDate, information is Up To Date
                        if (lastRefreshDate > currentDate) {
                            resultData.uptodate = true;
                            self.isDataUptoDate = true;
                        }
                        //else information is Out Of Date
                    } catch (e) {
                        app.logger.error('Error parsing dates');
                    }
                } else {
                    resultData.errmsg = app.lang.get('LBL_DNB_SVC_ERR');
                }
                _.extend(self, resultData);
                self.template = app.template.get(self.name);
                self.render();
                self.$('div#dnb-refresh-loading').hide();
                self.$('div#dnb-refresh-details').show();
                if (self.layout.getComponent('dashlet-toolbar').getField('data_valid_ind')) {
                    if (resultData.uptodate) {
                        self.layout.getComponent('dashlet-toolbar').getField('data_valid_ind').getFieldElement().hide();
                    } else {
                        self.layout.getComponent('dashlet-toolbar').getField('data_valid_ind').getFieldElement().show().addClass('label-pending').text(app.lang.get('LBL_DNB_OUTOFDATE'));
                    }
                }
            },
            error: _.bind(self.checkAndProcessError, self)
        });
    },

    /**
     * Gets the primary industry code from the array of industry codes
     * @param  {Array} industryArray
     * @param  {String} industryCode
     * @return {Object}
     */
    getPrimaryIndustry: function(industryArray, industryCode) {
        return _.find(industryArray, function(industryObj) {
            return industryObj['@DNBCodeValue'] === industryCode && industryObj['DisplaySequence'] === '1';
        });
    },

    loadData: function(options) {
        if (!_.isUndefined(this.model.get('duns_num')) && $.trim(this.model.get('duns_num')) !== '') {
            this.duns_num = this.model.get('duns_num');
            this.refreshcheck(this.duns_num);
        } else {
            this.template = app.template.get(this.name + '.dnb-no-duns');
        }
    },

    /**
     * Renders company list for search and cleanse and match results
     */
    renderCompanyList: function() {
        if (this.companyList[0].MatchQualityInformation) {
            this.template = app.template.get(this.name + '.dnb-cm-results');
        } else {
            this.template = app.template.get(this.name + '.dnb-company-list');
        }
        _.extend(this, this.companyList);
        this.render();
        this.$('div#dnb-company-list-loading').hide();
        this.$('div#dnb-company-list').show();
        //hide the import button
        //display it only when the company details are displayed
        if (!_.isUndefined(this.layout.getComponent('dashlet-toolbar').getField('dnb_import'))) {
            this.layout.getComponent('dashlet-toolbar').getField('dnb_import').getFieldElement().hide();
        }
        if (!_.isUndefined(this.layout.getComponent('dashlet-toolbar').getField('data_valid_ind'))) {
            this.layout.getComponent('dashlet-toolbar').getField('data_valid_ind').getFieldElement().hide();
        }
    },

    /**
     * D&B search based on the current account name
     * @param  {Object} options
     */
    dnbCompanySearch: function(options) {
        var self = this;
        if (self.disposed) {
            return;
        }
        self.template = app.template.get(self.name + '.dnb-company-list');
        self.render();
        $('div#dnb-company-list').hide();
        var accountsModel = this.model;
        var companyName = accountsModel.get('name');
        var candidateData = {'companies': null, 'errmsg': null};
        var dnbSearchUrl = app.api.buildURL('connector/dnb/search/' + companyName, '', {},{});
        self.companyList = null;
        app.api.call('READ', dnbSearchUrl, {},{
            success: function(data) {
                var candidateData = {'companies': null, 'errmsg': null};
                try {
                    var resultIDPath = 'FindCompanyResponse.TransactionResult.ResultID';
                    if (self.checkJsonNode(data, resultIDPath) &&
                        data.FindCompanyResponse.TransactionResult.ResultID === 'CM000') {
                        candidateData.companies = data.FindCompanyResponse.FindCompanyResponseDetail.FindCandidate;
                        self.companyList = candidateData.companies;
                    } else {
                        candidateData.errmsg = data.FindCompanyResponse.TransactionResult.ResultText;
                    }
                } catch (e) {
                    candidateData.errmsg = app.lang.get('LBL_DNB_SVC_ERR');
                }
                _.extend(self, candidateData);
                self.render();
                self.$('div#dnb-company-list-loading').hide();
                self.$('div#dnb-company-list').show();
                self.$('.showLessData').hide();
            },
            error: _.bind(self.checkAndProcessError, self)
        });
    },

    /**
     * Event handler for clicking on DUNS
     * @param  {Object} evt
     */
    dunsClickHandler: function(evt) {
        var duns_num = evt.target.id;
        this.getDNBCompanyDetails(duns_num);
    },

    /**
     * Gets company information for the duns num
     * @param  {String} duns_num
     */
    getDNBCompanyDetails: function(duns_num) {
        var self = this;
        self.template = app.template.get(self.name + '.dnb-company-details');
        self.render();
        self.$('div#dnb-company-detail-loading').show();
        self.$('div#dnb-company-details').hide();
        self.trigger('dnbcompinfo:duns_selected', duns_num);
        if (duns_num && duns_num !== '') {
            //check if cache has this data already
            var cacheKey = 'dnb:compstd:' + duns_num;
            if (!_.isUndefined(app.cache.get(cacheKey))) {
                var resultData = app.cache.get(cacheKey);
                self.renderCompanyDetails.call(self, app.cache.get(cacheKey));
                // show import button
                if (!_.isUndefined(self.layout.getComponent('dashlet-toolbar').getField('dnb_import'))) {
                    self.layout.getComponent('dashlet-toolbar').getField('dnb_import').getFieldElement().show();
                }
            } else {
                var dnbProfileUrl = app.api.buildURL('connector/dnb/profile/' + duns_num, '', {},{});
                var resultData = {'product': null, 'errmsg': null};
                app.api.call('READ', dnbProfileUrl, {},{
                    success: function(data) {
                        var duns_path = 'OrderProductResponse.OrderProductResponseDetail.InquiryDetail.DUNSNumber';
                        var resultIDPath = 'OrderProductResponse.TransactionResult.ResultID';
                        var resultTextPath = 'OrderProductResponse.TransactionResult.ResultText';
                        var industry_path = 'OrderProductResponse.OrderProductResponseDetail.Product.Organization.IndustryCode.IndustryCode';
                        if (self.checkJsonNode(data, resultIDPath) &&
                            data.OrderProductResponse.TransactionResult.ResultID === 'CM000') {
                            resultData.product = data;
                            self.duns_num = resultData.product.OrderProductResponse.OrderProductResponseDetail.InquiryDetail.DUNSNumber;
                            if (self.checkJsonNode(resultData.product, industry_path)) {
                                var industryCodeArray = resultData.product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.IndustryCode.IndustryCode;
                                //399 is the industry code type value for US SIC
                                resultData.product.primarySIC = self.getPrimaryIndustry(industryCodeArray, '399');
                            }
                            if (!_.isUndefined(self.layout.getComponent('dashlet-toolbar').getField('dnb_import'))) {
                                self.layout.getComponent('dashlet-toolbar').getField('dnb_import').getFieldElement().show();
                            }
                            app.cache.set(cacheKey, resultData);
                        } else if (self.checkJsonNode(data, resultTextPath)) {
                            resultData.errmsg = data.OrderProductResponse.TransactionResult.ResultText;
                        } else {
                            resultData.errmsg = app.lang.get('LBL_DNB_SVC_ERR');
                        }
                        self.renderCompanyDetails.call(self, resultData);
                    },
                    error: _.bind(self.checkAndProcessError, self)
                });
            }
        }
    },

    /**
     * Renders D&B Company Information
     * @param  {Object} resultData
     */
    renderCompanyDetails: function(resultData) {
        if (this.disposed) {
            return;
        }
        if (!this.model.get('duns_num')) {
            resultData.isNotLinked = true;
        }
        if (resultData.product) {
            var duns_path = 'OrderProductResponse.OrderProductResponseDetail.InquiryDetail.DUNSNumber';
            var industry_path = 'OrderProductResponse.OrderProductResponseDetail.Product.Organization.IndustryCode.IndustryCode';
            if (this.checkJsonNode(resultData.product, duns_path)) {
                this.duns_num = resultData.product.OrderProductResponse.OrderProductResponseDetail.InquiryDetail.DUNSNumber;
                app.controller.context.set('dnb_temp_duns_num', this.duns_num);
            }
            if (this.checkJsonNode(resultData.product, industry_path)) {
                var industryCodeArray = resultData.product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.IndustryCode.IndustryCode;
                //399 is the industry code type value for US SIC
                resultData.product.primarySIC = this.getPrimaryIndustry(industryCodeArray, '399');
                //extracting the primary hoovers industry code and passing it on
                //to the industry info dashlet
                //25838 indicates hoovers industry code
                //if the DisplaySequence == 1
                //it indicates that the industry code is the primary hoovers industry code
                var primaryHooversCode = this.getPrimaryIndustry(industryCodeArray, '25838');
                if (primaryHooversCode) {
                    app.controller.context.set('dnb_temp_hoovers_ind_code', primaryHooversCode.IndustryCode.$ + '-' + primaryHooversCode['@DNBCodeValue']);
                }
            }
            resultData.product.dataIndicatorMap = this.getDataIndicators(resultData);
        }
        _.extend(this, resultData);
        this.render();
        this.$('div#dnb-company-detail-loading').hide();
        this.$('div#dnb-company-details').show();
        this.importCheckBox();
    },

    /**
     * Expands the dashlets to reveal more data
     */
    showMoreData: function() {
        this.$('.dnb-show-less').attr('class', 'dnb-show-all');
        this.$('.showLessData').show();
        this.$('.showMoreData').hide();
    },

    /**
     * Truncates the dashlets
     */
    showLessData: function() {
        this.$('.dnb-show-all').attr('class', 'dnb-show-less');
        this.$('.showLessData').hide();
        this.$('.showMoreData').show();
    },

    /**
     * returns an object of data elements
     with indicators indicating
     if the data element exists
     and is duplicate then add 'dup'
     else add 'upd'
     else do not add it to the map
     * @param  {Object} dnbApiResponse
     * @return {Object}
     */
    getDataIndicators: function(dnbApiResponse) {
        var accountsModel = this.model;
        var dataIndicatorMap = {};
        var dnbResponseMap = {};
        var name, billing_address_street, billing_address_city, billing_address_state,
            billing_address_country, billing_address_postalcode, website, phone_office,
            ownership, annual_revenue, employees, sic_code;
        if (this.checkJsonNode(dnbApiResponse, 'product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.OrganizationName.OrganizationPrimaryName.0.OrganizationName.$')) {
            dnbResponseMap.name = dnbApiResponse.product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.OrganizationName.OrganizationPrimaryName[0].OrganizationName.$;
        }
        if (this.checkJsonNode(dnbApiResponse, 'product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.Location.PrimaryAddress.0.StreetAddressLine.0.LineText')) {
            dnbResponseMap.billing_address_street = dnbApiResponse.product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.Location.PrimaryAddress[0].StreetAddressLine[0].LineText;
        }
        if (this.checkJsonNode(dnbApiResponse, 'product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.Location.PrimaryAddress.0.PrimaryTownName')) {
            dnbResponseMap.billing_address_city = dnbApiResponse.product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.Location.PrimaryAddress[0].PrimaryTownName;
        }
        if (this.checkJsonNode(dnbApiResponse, 'product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.Location.PrimaryAddress.0.TerritoryAbbreviatedName')) {
            dnbResponseMap.billing_address_state = dnbApiResponse.product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.Location.PrimaryAddress[0].TerritoryAbbreviatedName;
        }
        if (this.checkJsonNode(dnbApiResponse, 'product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.Location.PrimaryAddress.0.CountryISOAlpha2Code')) {
            dnbResponseMap.billing_address_country = dnbApiResponse.product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.Location.PrimaryAddress[0].CountryISOAlpha2Code;
        }
        if (this.checkJsonNode(dnbApiResponse, 'product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.Location.PrimaryAddress.0.PostalCode')) {
            dnbResponseMap.billing_address_postalcode = dnbApiResponse.product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.Location.PrimaryAddress[0].PostalCode;
        }
        if (this.checkJsonNode(dnbApiResponse, 'product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.Telecommunication.WebPageAddress.0.TelecommunicationAddress')) {
            dnbResponseMap.website = dnbApiResponse.product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.Telecommunication.WebPageAddress[0].TelecommunicationAddress;
        }
        if (this.checkJsonNode(dnbApiResponse, 'product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.Telecommunication.TelephoneNumber.0.TelecommunicationNumber')) {
            dnbResponseMap.phone_office = dnbApiResponse.product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.Telecommunication.TelephoneNumber[0].TelecommunicationNumber;
        }
        if (this.checkJsonNode(dnbApiResponse, 'product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.OrganizationDetail.ControlOwnershipTypeText.$')) {
            dnbResponseMap.ownership = dnbApiResponse.product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.OrganizationDetail.ControlOwnershipTypeText.$;
        }
        if (this.checkJsonNode(dnbApiResponse, 'product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.Financial.KeyFinancialFiguresOverview.0.SalesRevenueAmount.0.$')) {
            dnbResponseMap.annual_revenue = dnbApiResponse.product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.Financial.KeyFinancialFiguresOverview[0].SalesRevenueAmount[0].$;
        }
        if (this.checkJsonNode(dnbApiResponse, 'product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.EmployeeFigures.IndividualEntityEmployeeDetails.TotalEmployeeQuantity'))dnbResponseMap.employees = dnbApiResponse.product.OrderProductResponse.OrderProductResponseDetail.Product.Organization.EmployeeFigures.IndividualEntityEmployeeDetails.TotalEmployeeQuantity;
        if (this.checkJsonNode(dnbApiResponse, 'product.primarySIC.IndustryCode.$'))dnbResponseMap.sic_code = dnbApiResponse.product.primarySIC.IndustryCode.$;
        _.each(this.dataElements, function(dataElementName) {
            //if value is set on accounts obj and same as dnb response then mark as dup
            if (!_.isUndefined(accountsModel.get(dataElementName)) && accountsModel.get(dataElementName) !== '' &&
                !_.isUndefined(dnbResponseMap[dataElementName]) && dnbResponseMap[dataElementName] !== '' &&
                $.trim(accountsModel.get(dataElementName)) === $.trim(dnbResponseMap[dataElementName])) {
                dataIndicatorMap[dataElementName] = 'dup';
            } else if (!_.isUndefined(accountsModel.get(dataElementName)) && accountsModel.get(dataElementName) !== '' &&
                !_.isUndefined(dnbResponseMap[dataElementName]) && dnbResponseMap[dataElementName] !== '' &&
                $.trim(accountsModel.get(dataElementName)) !== $.trim(dnbResponseMap[dataElementName])) {
                dataIndicatorMap[dataElementName] = 'upd';
            }
            //else value is set on accounts obj and different from dnb response then mark as upd
        });
        return dataIndicatorMap;
    },

    /**
     * Import D&B data into accounts
     */
    importDNBData: function() {
        var dnbCheckBox = this.$('.dnb_checkbox:checked');
        var accountsModel = this.model;
        // always import the duns_num
        accountsModel.set('duns_num', this.duns_num);
        accountsModel.save();
        // iterate through checkboxes
        // values being overriden stored in updatedData
        // values that are newly being set store in newData
        var updatedData = [];
        var newData = [];
        for (var checkBoxCounter = 0; checkBoxCounter < dnbCheckBox.length; checkBoxCounter++) {
            var dnbPropertyName = dnbCheckBox[checkBoxCounter].id;
            var dnbPropertyValue = $.trim(this.$('#' + dnbPropertyName).parent().next().next().contents().filter(function()
            {
                return this.nodeType === 3;
            })[0].nodeValue);
            //check if existing value is getting updated
            if (!_.isUndefined(accountsModel.get(dnbPropertyName)) &&
                accountsModel.get(dnbPropertyName) !== '') {
                updatedData.push({propName: dnbPropertyName, propVal: dnbPropertyValue});
            } else if (dnbPropertyValue !== '') {
                newData.push({propName: dnbPropertyName, propVal: dnbPropertyValue});
            }
        }
        //importing new data
        if (newData.length > 0) {
            this.updateAccountsModel(newData);
        }
        //update existing data
        if (updatedData.length > 0) {
            var confirmationMsgKey, confirmationMsgData;
            //show a detailed warning message about the single data element being imported
            if (updatedData.length === 1) {
                var fieldName = app.lang.get(accountsModel.fields[updatedData[0].propName].vname, 'Accounts');
                confirmationMsgKey = 'LBL_DNB_DATA_OVERRIDE_SINGLE_FIELD';
                confirmationMsgData = {
                    fieldName: fieldName.toLowerCase(),
                    value: updatedData[0].propVal
                };
            } else {
                var fieldList = [
                    app.lang.get(accountsModel.fields[updatedData[0].propName].vname, 'Accounts').toLowerCase(),
                    app.lang.get(accountsModel.fields[updatedData[1].propName].vname, 'Accounts').toLowerCase()
                ];
                if (updatedData.length === 2) {
                    //list the two fields being imported
                    confirmationMsgKey = 'LBL_DNB_DATA_OVERRIDE_TWO_FIELDS';
                    confirmationMsgData = {
                        fields: fieldList.join(' ' + app.lang.get('LBL_DNB_AND') + ' ')
                    };
                } else {
                    //list the two first fields and append ` and other(s) field(s)`
                    confirmationMsgKey = 'LBL_DNB_DATA_OVERRIDE_MULTIPLE_FIELDS';
                    confirmationMsgData = {
                        fields: fieldList.join(', ')
                    };
                }
            }
            var confirmationMsgTpl = Handlebars.compile(app.lang.get(confirmationMsgKey));
            app.alert.show('dnb-import-warning',
                {
                    level: 'confirmation',
                    title: 'LBL_WARNING',
                    messages: confirmationMsgTpl(confirmationMsgData),
                    onConfirm: _.bind(this.updateAccountsModel, this, updatedData)
                }
            );
        }
    },

    /**
     * Overrite existing data with new data
     * @param  {Object} updatedData
     */
    updateAccountsModel: function(updatedData) {
        var self = this;
        var changedAttributes = {};
        _.each(updatedData, function(updatedAttribute) {
            changedAttributes[updatedAttribute.propName] = updatedAttribute.propVal;
        });
        self.model.save(changedAttributes);
        self.context.loadData();
        app.alert.show('dnb-import-success', {level: 'success', title: 'Success:', messages: app.lang.get('LBL_DNB_OVERRIDE_SUCCESS'), autoClose: true});
    },

    /**
     * Checkbox change event handler
     */
    importCheckBox: function() {
        var dnbCheckBoxes = this.$('.dnb_checkbox:checked');
        if (!_.isUndefined(this.layout.getComponent('dashlet-toolbar').getField('dnb_import'))) {
            if (dnbCheckBoxes.length > 0) {
                this.layout.getComponent('dashlet-toolbar').getField('dnb_import').setDisabled(false);
                this.layout.getComponent('dashlet-toolbar').getField('dnb_import').getFieldElement().removeClass('disabled');
                this.layout.getComponent('dashlet-toolbar').getField('dnb_import').getFieldElement().removeClass('hide');
            } else {
                this.layout.getComponent('dashlet-toolbar').getField('dnb_import').setDisabled(true);
                this.layout.getComponent('dashlet-toolbar').getField('dnb_import').getFieldElement().addClass('disabled');
            }
        }
    },

    /**
     * Check if a particular json path is valid
     * @param {Object} obj
     * @param {String} path
     * @return {Boolean}
     */
    checkJsonNode: function(obj, path) {
        var args = path.split('.');
        for (var i = 0; i < args.length; i++) {
            if (_.isNull(obj) || _.isUndefined(obj) || !obj.hasOwnProperty(args[i])) {
                return false;
            }
            obj = obj[args[i]];
        }
        return true;
    },

    /**
     * Populate the country drop down
     * @return {Array}
     */
    populateCountry: function() {
        var self = this;
        var countryOptionsArray = [];
        $.each(self.countryList, function(index, value) {
            countryOptionsArray.push({
                id: index,
                text: self.countryList[index]
            });
        });
        return countryOptionsArray;
    },

    /**
     * Populate the state drop down
     * @param  {String} selectedCountry
     * @return {Array}
     */
    populateState: function(selectedCountry) {
        var self = this;
        var stateOptionsArray = [];
        var state_arr = self.statesList[selectedCountry];
        if (selectedCountry !== 'Country' && !_.isUndefined(state_arr)) {
            $.each(state_arr, function(index, value) {
                stateOptionsArray.push({
                    id: state_arr[index].code,
                    text: state_arr[index].name
                });
            });
        }
        return stateOptionsArray;
    },

    /**
     * Change state drop down based on country selection
     */
    changeState: function() {
        this.selectedCountry = this.$('#countryList').val();
        //disable match button
        this.$('#dnb-match-btn').addClass('disabled');
        this.$('#stateList').select2({
            placeholder: 'Select a State',
            data: this.populateState(this.selectedCountry)
        });
    },


    /**
     * validate if all the parameters for cleanse and match are available
     enable the match btn
     */
    validateMatchParams: function() {
        var accountName = this.model.get('name');
        if (!_.isUndefined(accountName) &&
            this.$('#countryList').val() !== 'Country' &&
            this.$('#statesList').val() !== 'State') {
            this.$('#dnb-match-btn').removeClass('disabled');
        }
    },

    /**
     * The D&B Direct API Cleanse Match API:
     * Provides address standardization in two features:
     * On-Demand Single Entity Resolution and On-Demand Address Cleanse & Update.
     * The purpose of these features is to produce machine sortable mailing addresses
     * that are optimized for accurate and quick delivery
     * @param  {Object} evt
     * invoke CleanseMatch POST API call
     * api post data format
     * {
     *      "IncludeCleansedAndStandardizedInformationIndicator":"true", //mandatory
     *      "CountryISOAlpha2Code":"US", //country code mandatory
     *      "cleansematch":"true",//mandatory
     *      "SubjectName":"ibm", //company name mandatory
     *      "PrimaryTownName":"town name", //optional
     *      "TerritoryName": "territory" //optional
     * }
     */
    invokeCMRequest: function(evt) {
        if (this.disposed) {
            return;
        }
        //if match btn is not disabled then invoke cleanse match
        if (!_.contains(evt.target.classList, 'disabled')) {
            var self = this;
            var cmRequestParams = {
                'IncludeCleansedAndStandardizedInformationIndicator': true, //mandatory
                'CountryISOAlpha2Code': this.$('#countryList').val(), //country code mandatory
                'cleansematch': 'true',//mandatory
                'SubjectName': this.model.get('name'), //company name mandatory
                // "PrimaryTownName":"town name", //optional
                'TerritoryName': this.$('#stateList').val() //optional
            };
            self.template = app.template.get(self.name + '.dnb-cm-results');
            self.render();
            self.$('div#dnb-company-list-loading').show();
            self.$('div#dnb-company-list').hide();
            var dnbCMRequestURL = app.api.buildURL('connector/dnb/cmRequest', '', {},{});
            var resultData;
            var candidateData = {'companies': null, 'errmsg': null};
            app.api.call('create', dnbCMRequestURL, {'qdata': cmRequestParams},{
                success: function(data) {
                    try {
                        resultdata = data;
                        var resultIDPath = 'GetCleanseMatchResponse.TransactionResult.ResultID';
                        if (self.checkJsonNode(resultdata, resultIDPath) &&
                            resultdata.GetCleanseMatchResponse.TransactionResult.ResultID === 'CM000') {
                            candidateData.companies = resultdata.GetCleanseMatchResponse.GetCleanseMatchResponseDetail.MatchResponseDetail.MatchCandidate;
                            self.companyList = candidateData.companies;
                        } else {
                            candidateData.errmsg = resultdata.GetCleanseMatchResponse.TransactionResult.ResultText;
                        }
                    } catch (e) {
                        candidateData.errmsg = app.lang.get('LBL_DNB_SVC_ERR');
                    }
                    _.extend(self, candidateData);
                    self.render();
                    self.$('div#dnb-company-list-loading').hide();
                    self.$('div#dnb-company-list').show();
                },
                error: _.bind(self.checkAndProcessError, self)
            });
        }
    }
});
