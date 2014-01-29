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

    contactsMap: {
        'email': 'Telecommunication.EmailAddress.0.TelecommunicationAddress',
        'phone_work': 'Telecommunication.TelephoneNumber.0.TelecommunicationNumber',
        'dnb_principal_id': 'PrincipalIdentificationNumberDetail.PrincipalIdentificationNumber',
        'first_name': 'PrincipalName.FirstName',
        'last_name': 'PrincipalName.LastName',
        'full_name': 'PrincipalName.FullName',
        'department': 'CurrentManagementResponsibility.0.ManagementResponsibilityText.$',
        'title': 'JobTitle.0.JobTitleText.$',
        'salutation': 'PrincipalName.NamePrefix.NamePrefixText'
    },

    currentPrincipalPath: 'Organization.PrincipalsAndManagement.CurrentPrincipal',

    //contacts list data dictionary
    contactsListDD: {
        'jobTitle' : {
            'json_path' : 'JobTitle.0.JobTitleText.$'
        },
        'fullName' : {
            'json_path' : 'ContactName.FullName'
        },
        'principalId' : {
            'json_path' : 'PrincipalIdentificationNumberDetail.0.PrincipalIdentificationNumber'
        },
        'emailInd' : {
            'json_path' : 'DirectTelephoneInformationAvailableIndicator'
        },
        'phoneInd' : {
            'json_path' : 'DirectEmailInformationAvailableIndicator'
        },
        'isDupe' : {
            'json_path' : 'isDupe'
        }
    },

    //contacts detail data dictionary
    contactsDetailDD: {
        'email' : {
            'json_path' : 'Telecommunication.EmailAddress.0.TelecommunicationAddress',
            'label' : 'LBL_DNB_CONTACT_EMAIL'
        },
        'phone_work' : {
            'json_path' : 'Telecommunication.TelephoneNumber.0.TelecommunicationNumber',
            'label' : 'LBL_DNB_CONTACT_PHONE'
        },
        'full_name' : {
            'json_path' : 'PrincipalName.FullName',
            'label' : 'LBL_DNB_CONTACT_NAME'
        },
        'department' : {
            'json_path' : 'CurrentManagementResponsibility.0.ManagementResponsibilityText.$',
            'label' : 'LBL_DNB_CONTACT_RESP'
        },
        'job_title' : {
            'json_path' : 'JobTitle',
            'sub_object': {
                'data_type' : 'job_hist',
                'title' : 'JobTitleText.$',
                'start_date' : 'StartDate.$',
                'end_date' : 'EndDate.$'
            }
        },
        'emp_bio' : {
            'json_path' : 'EmploymentBiography.EmploymentBiographyText',
            'label' : 'LBL_DNB_CONTACT_BIO'
        },
        'comp_hist' : {
            'json_path' : 'FormerCompensation',
            'sub_object': {
                'data_type' : 'comp_hist',
                'comp_det' : 'CompensationDetail',
                'comp_date' : 'CompensationDate.$',
                'comp_type' : 'CompensationTypeText.$',
                'comp_amt' : 'CompensationAmount.$',
                'comp_curr' : 'CompensationAmount.@CurrencyISOAlpha3Code'
            }
        }
    },

    //contact constants
    contactConst: {
        'responseCode' : 'FindContactResponse.TransactionResult.ResultID',
        'responseMsg' : 'FindContactResponse.TransactionResult.ResultText',
        'contactsPath' : 'FindContactResponse.FindContactResponseDetail.FindCandidate',
        'contactsDetailPath' : 'OrderProductResponse.OrderProductResponseDetail.Product.Organization.PrincipalsAndManagement.CurrentPrincipal.0',
        'premCntct' : 'dnb-cnt-prem',
        'stdCntct' : 'dnb-cnt-std'
    },

    //for storing the duns_num
    duns_num: null,
    //for storing the contacts list
    //to be user for archiving
    contactsList: null,
    //current search parameters
    cntctSrchParams: null,
    //for storing the current contact details
    currentContact: null,
    importBtn: null,

    events: {
        'click .showMoreData' : 'showMoreData',
        'click .showLessData' : 'showLessData',
        'click .dnb-cnt-prem' : 'getDNBContactDetails',
        'click .dnb-cnt-std' : 'getDNBContactDetails',
        'click .backToList' : 'backToContactsList',
        'click #dnb-srch-clear' : 'clearSearchResults',
        'click #dnb-cntct-srch-btn' : 'searchContacts',
        'keyup .input-large': 'validateSearchParams'
    },

    initialize: function(options) {
        this._super('initialize', [options]);
        if (this.layout.collapse) {
            this.layout.collapse(true);
        }
        this.layout.on('dashlet:collapse', this.loadContacts, this);
        app.events.on('dnbcompinfo:duns_selected', this.collapseDashlet, this);
    },

    /**
     * Refresh dashlet once Refresh link clicked from gear button
     * To show updated contact information from DNB service
    */
    refreshClicked: function() {
        this.loadContacts(false);
    },

    loadData: function(options) {
        if (this.model.get('duns_num')) {
            this.duns_num = this.model.get('duns_num');
        }
    },

    /**
     * Triggered when the dashlet is collapsed / expanded
     * @param {Boolean} isCollapsed  true indicating the dashlet was collapsed
     */
    loadContacts: function(isCollapsed) {
        if (!isCollapsed) {
            //check if account is linked with a D-U-N-S
            if (this.duns_num) {
                this.getDNBContacts(this.duns_num);
            } else if (!_.isUndefined(app.controller.context.get('dnb_temp_duns_num'))) {
                //check if D-U-N-S is set in context by refresh dashlet
                this.getDNBContacts(app.controller.context.get('dnb_temp_duns_num'));
            } else {
                this.template = app.template.get(this.name + '.dnb-no-duns');
                if (!this.disposed) {
                    this.render();
                }
            }
        }
    },

    /**
     * handles the back to contact list functionality
     */
    backToContactsList: function() {
        if (this.contactsList) {
            this.renderContactsList(this.contactsList);
        }
    },

    /**
     * Render the list of contacts
     * @param {Array} dnbApiResponse Dnb contacts list
     */
    renderContactsList: function(dnbApiResponse) {
        if (this.disposed) {
            return;
        }
        this.template = app.template.get(this.name);
        var dnbContactsList = {};
        if (dnbApiResponse.contacts) {
            dnbContactsList.product = this.formatContactList(dnbApiResponse.contacts, this.contactsListDD);
        } else if (dnbApiResponse.errmsg) {
            dnbContactsList.errmsg = dnbApiResponse.errmsg;
        }
        this.dnbContactsList = dnbContactsList;
        this.render();
        this.$('#dnb-contact-list-loading').hide();
        this.$('#dnb-contact-list').show();
        if (this.layout.getComponent('dashlet-toolbar').getField('import_dnb_data')) {
            this.layout.getComponent('dashlet-toolbar').getField('import_dnb_data').getFieldElement().hide();
        }
        this.$('.showLessData').hide();
    },

    /**
     * Clears the search results
     * @param {Object} evt
     */
    clearSearchResults: function(evt) {
        var srchParams = {'fname': null, 'lname': null, 'jobTitle': null};
        this.cntctSrchParams = srchParams;
        this.getDNBContacts(this.duns_num);
    },

    /**
     * Gets the list of contacts for a DUNS number
     * @param {String} duns_num
     */
    getDNBContacts: function(duns_num) {
        var self = this;
        if (this.disposed) {
            return;
        }
        if (duns_num) {
            self.duns_num = duns_num;
            self.template = app.template.get(self.name);
            self.render();
            self.$('#dnb-contact-list-loading').show();
            self.$('#dnb-contact-list').hide();
            //check if cache has this data already
            var cacheKey = 'dnb:cntlist:' + duns_num;
            var cacheContent = app.cache.get(cacheKey);
            if (cacheContent) {
                self.contactsList = cacheContent;
                self.renderContactsList(cacheContent);
            } else {
                var dnbFindContactsURL = app.api.buildURL('connector/dnb/findContacts/' + duns_num, '', {},{});
                var resultData = {'contacts': null, 'errmsg': null};
                app.api.call('READ', dnbFindContactsURL, {},{
                    success: function(data) {
                        var responseCode = self.getJsonNode(data, self.contactConst.responseCode),
                            responseMsg = self.getJsonNode(data, self.contactConst.responseMsg);
                        if (responseCode && responseCode === self.responseCodes.success) {
                            var contactsArray = self.getJsonNode(data, self.contactConst.contactsPath);
                            if (contactsArray) {
                                resultData.contacts = contactsArray;
                                //for back to list functionality
                                self.contactsList = resultData;
                                app.cache.set(cacheKey, resultData);
                            } else {
                                resultData.errmsg = app.lang.get('LBL_DNB_NO_DATA');
                            }
                        } else {
                            resultData.errmsg = responseMsg || app.lang.get('LBL_DNB_SVC_ERR');
                        }
                        self.renderContactsList.call(self, resultData);
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
     * Gets contact details for a duns and principal identification number combination
     * @param {Object} evt
     */
    getDNBContactDetails: function(evt) {
        if (this.disposed) {
            return;
        }
        var contact_id = evt.target.id;
        var contact_name = evt.target.text, contact_type;
        if (this.$(evt.target).hasClass(this.contactConst.premCntct)) {
            contact_type = this.contactConst.premCntct;
        } else if (this.$(evt.target).hasClass(this.contactConst.stdCntct)) {
            contact_type = this.contactConst.stdCntct;
        }
        var self = this;
        self.cntctLoadMsg = null,
        self.template = app.template.get(self.name + '.dnb-contact-details');
        self.cntctLoadMsg = {'contactName' : contact_name};
        self.render();
        self.$('div#dnb-contact-details-loading').show();
        self.$('div#dnb-contact-details').hide();
        if (self.layout.getComponent('dashlet-toolbar').getField('import_dnb_data')) {
            self.layout.getComponent('dashlet-toolbar').getField('import_dnb_data').getFieldElement().hide();
        }
        var contactParams = {
            'duns_num' : self.duns_num,
            'contact_id' : contact_id,
            'contact_type' : contact_type
        };
        //check if cache has this data already
        var cacheKey = 'dnb:' + contactParams.contact_type + ':'
                        + contactParams.duns_num + ':' + contactParams.contact_id;
        var cacheContent = app.cache.get(cacheKey);
        if (cacheContent) {
            self.currentContact = cacheContent.contactDetail;
            self.renderContactDetails(cacheContent);
        } else {
            var dnbContactDetailsURL = app.api.buildURL('connector/dnb/contacts', '', {},{});
            var resultData = {'contactDetail': null, 'errmsg' : null};
            self.currentContact = null;
            app.api.call('create', dnbContactDetailsURL, {'qdata': contactParams},{
                success: function(data) {
                    var responseCode = self.getJsonNode(data, self.appendSVCPaths.responseCode),
                        responseMsg = self.getJsonNode(data, self.appendSVCPaths.responseMsg);
                    if (responseCode && responseCode === self.responseCodes.success) {
                        var contactDetail = self.getJsonNode(data, self.contactConst.contactsDetailPath);
                        if (contactDetail) {
                            resultData.contactDetail = contactDetail;
                            self.currentContact = resultData.contactDetail;
                            app.cache.set(cacheKey, resultData);
                        } else {
                            resultData.errmsg = app.lang.get('LBL_DNB_NO_DATA');
                        }
                    } else {
                        resultData.errmsg = responseMsg || app.lang.get('LBL_DNB_SVC_ERR');
                    }
                    self.renderContactDetails(resultData);
                },
                error: _.bind(self.checkAndProcessError, self)
            });
        }
    },

    /**
     * Renders the contact details
     * @param {Object} dnbApiResponse
     */
    renderContactDetails: function(dnbApiResponse) {
        this.template = app.template.get(this.name + '.dnb-contact-details');
        this.dnbCntctDet = null;
        var frmtCntctDet, dnbCntctDet = {};
        if (dnbApiResponse.contactDetail) {
            frmtCntctDet = this.formatContactDetails(dnbApiResponse.contactDetail, this.contactsDetailDD);
            if (frmtCntctDet) {
                dnbCntctDet.product = frmtCntctDet;
            } else {
                dnbCntctDet.errmsg = app.lang.get('LBL_DNB_NO_DATA');
            }
        } else if (dnbApiResponse.errmsg) {
            dnbCntctDet.errmsg = dnbApiResponse.errmsg;
        }
        this.dnbCntctDet = dnbCntctDet;
        if (!this.disposed) {
            this.render();
            this.$('div#dnb-contact-details-loading').hide();
            this.$('div#dnb-contact-details').show();
            //display import btn if there is no err msg
            if (!dnbCntctDet.errmsg) {
                if (this.layout.getComponent('dashlet-toolbar').getField('import_dnb_data')) {
                    this.layout.getComponent('dashlet-toolbar').getField('import_dnb_data').getFieldElement().show();
                }
            }
        }
    },

    /**
     * Preprocessing contact details
     * @param  {Object} contactDetail DNB API Response for contact details
     * @param  {Object} contactsDetailDD Contact Details Data Dictionary
     * @return {Array} frmtCntctDet Format Contact Details Array
     */
    formatContactDetails: function(contactDetail, contactsDetailDD) {
        var frmtCntctDet = {};
        _.each(contactsDetailDD, function(value, key) {
            var dataElement = this.getJsonNode(contactDetail, value.json_path);
            if (dataElement) {
                if (key === 'job_title') {
                    var frmtJobTitles = this.formatJobTitles(dataElement, value.sub_object);
                    if (frmtJobTitles && frmtJobTitles.length > 0) {
                        //first job title is the current job title
                        frmtCntctDet[key] = frmtJobTitles[0].title;
                        //the rest are used to display job history
                        if (frmtJobTitles.length > 1) {
                            frmtJobTitles.splice(0, 1);
                            frmtCntctDet[value.sub_object.data_type] = frmtJobTitles;
                        }
                    }
                } else if (key === 'comp_hist') {
                    var frmtCompHist = this.formatCompHist(dataElement, value.sub_object);
                    if (frmtCompHist && frmtCompHist.length > 0) {
                        frmtCntctDet[value.sub_object.data_type] = frmtCompHist;
                    }
                } else {
                    if (key === 'email') {
                        dataElement = this.emailMask(dataElement);
                    } else if (key === 'phone_work') {
                        dataElement = this.phoneMask(dataElement);
                    }
                    frmtCntctDet[key] = dataElement;
                }
            }
        },this);
        return frmtCntctDet;
    },

    /**
     *	Imports the current contact information
     */
    importDNBContact: function() {
        var parentModel = this.context.get('model'),
            model = this.getContactsModel(this.currentContact);
        var self = this;
        app.drawer.open({
            layout: 'create-actions',
            context: {
                create: true,
                module: model.module,
                model: model
            }
        }, function(model) {
            if (!model) {
                return;
            }
            self.context.resetLoadFlag();
            self.context.set('skipFetch', false);
            self.context.loadData();
            _.each(app.controller.context.children, function(childContext) {
                if (childContext.get('module') === 'Contacts') {
                    childContext.reloadData(true);
                }
            });
        });
    },

    /**
     * Creates and returns an Contact bean
     * @param {Object} contactsApiResponse
     * @return {Object} contactsModel
     */
    getContactsModel: function(contactsApiResponse) {
        var contactBean = {
            'account_id': this.model.get('id'),
            'account_name': this.model.get('name')
        };
        _.each(this.contactsMap, function(dataElementPath, sugarColumnName) {
            var dnbDataElement = this.getJsonNode(contactsApiResponse, dataElementPath);
            if (dnbDataElement) {
                if (sugarColumnName === 'email') {
                    var emailObj = {
                        email_address: dnbDataElement,
                        flagClass: 'primary',
                        flagLabel: 'Primary',
                        hasAnchor: true,
                        invalid_email: false,
                        opt_out: false,
                        primary_address: true,
                        reply_to_address: false
                    };
                    contactBean.email = [emailObj];
                } else {
                    contactBean[sugarColumnName] = dnbDataElement;
                }
            }
        },this);
        var contactsModel = app.data.createRelatedBean(this.model, null, 'contacts', contactBean);
        return contactsModel;
    },

    /**
     * Searches for contacts from the D&B API
     * Based on the first name or last name or job title
     * Either one of these three inputs must be given in order to
     * perform the search
     * @param {Object} evt
     */
    searchContacts: function(evt) {
        if (this.disposed) {
            return;
        }
        //if search btn is not disabled and a duns exists then invoke contact search
        if (!this.$(evt.target).hasClass('disabled') && this.duns_num) {
            var self = this;
            var cntctSrchParams = {},
                srchParams = {'fname': null, 'lname': null, 'jobTitle': null};
            var fname = $.trim(self.$('#dnb-fname').val()),
                lname = $.trim(self.$('#dnb-lname').val()),
                jobTitle = $.trim(self.$('#dnb-job').val());
            var contactName;
            if (fname) {
                contactName = fname;
                srchParams.fname = fname;
            }
            if (lname) {
                if (contactName) {
                    contactName = contactName + ' ' + lname;
                } else {
                    contactName = lname;
                }
                srchParams.lname = lname;
            }
            if (contactName !== '') {
                cntctSrchParams.ContactName = contactName;
            }
            if (jobTitle !== '') {
                cntctSrchParams.KeywordContactText = jobTitle;
                if (jobTitle) {
                    srchParams.jobTitle = jobTitle;
                }
            }
            cntctSrchParams['DUNSNumber-1'] = self.duns_num;
            self.template = app.template.get(self.name);
            self.cntctSrchParams = srchParams;
            self.render();
            self.$('div#dnb-contact-list-loading').show();
            self.$('div#dnb-contact-list').hide();
            var cacheKey = 'dnb:cntlist';
            _.each(cntctSrchParams, function(val, key) {
                cacheKey = cacheKey + ':' + key + '_' + val;
            });
            var cacheContent = app.cache.get(cacheKey);
            if (cacheContent) {
                self.renderContactsList(cacheContent);
            } else {
                var dnbFindContactsURL = app.api.buildURL('connector/dnb/findcontacts', '', {},{});
                var resultData = {'contacts': null, 'errmsg': null};
                app.api.call('create', dnbFindContactsURL, {'qdata': cntctSrchParams},{
                    success: function(data) {
                        var responseCode = self.getJsonNode(data, self.contactConst.responseCode),
                            responseMsg = self.getJsonNode(data, self.contactConst.responseMsg);
                        if (responseCode && responseCode === self.responseCodes.success) {
                            var contactsArray = self.getJsonNode(data, self.contactConst.contactsPath);
                            if (contactsArray) {
                                resultData.contacts = contactsArray;
                                //for back to list functionality
                                self.contactsList = resultData;
                                app.cache.set(cacheKey, resultData);
                            } else {
                                resultData.errmsg = app.lang.get('LBL_DNB_NO_DATA');
                            }
                        } else {
                            resultData.errmsg = responseMsg || app.lang.get('LBL_DNB_SVC_ERR');
                        }
                        self.renderContactsList.call(self, resultData);
                    },
                    error: _.bind(self.checkAndProcessError, self)
                });
            }
        }
    },

    /**
     * Preprocessing contacts list
     * @param {Array} dnbApiResponse DNB API Response for Contacts
     * @param {Object} contactsListDD Contacts data dictionary
     * @return {Array} frmtCntctList formatted contacts
     */
    formatContactList: function(dnbApiResponse, contactsListDD) {
        var frmtCntctList = [];
        _.each(dnbApiResponse, function(contactObj) {
            //initialize empty formatted obj
            var frmCntctObj = {};
            //iterate through data dictionary and extract info
            _.each(contactsListDD, function(value, key) {
                var dataElement = this.getJsonNode(contactObj, value.json_path);
                if (dataElement) {
                    frmCntctObj[key] = dataElement;
                }
            },this);
            //only if the contact has a name and a principal id will we display it
            if (frmCntctObj.principalId && frmCntctObj.fullName) {
                if (frmCntctObj.emailInd || frmCntctObj.phoneInd) {
                    frmCntctObj.contactType = this.contactConst.premCntct;
                } else {
                    frmCntctObj.contactType = this.contactConst.stdCntct;
                }
                frmtCntctList.push(frmCntctObj);
            }
        },this);
        return frmtCntctList;
    },

    /**
     * Preprocessing job titles
     * @param {Array} jobTitles job titles
     * @param {Object} jobTitleDD job titles data dictionary
     * @return {Array} formatted job titles
     */
    formatJobTitles: function(jobTitles, jobTitleDD) {
        var jobTitleArray = [];
        _.each(jobTitles, function(jobObj) {
            var jobTitleObj = {
                title: this.getJsonNode(jobObj, jobTitleDD.title),
                start_date: this.getJsonNode(jobObj, jobTitleDD.start_date),
                end_date: this.getJsonNode(jobObj, jobTitleDD.end_date)
            };
            if (jobTitleObj.title) {
                jobTitleArray.push(jobTitleObj);
            }
        },this);
        return jobTitleArray;
    },

    /**
     * Preprocessing compensation history
     * @param {Array} compHist compensation history
     * @param {Object} compHistDD compensation history data dictionary
     * @return {Array} frmtCompHist formatted compensation history
     */
    formatCompHist: function(compHist, compHistDD) {
        var frmtCompHist = [];
        _.each(compHist, function(compHistObj) {
            var compDate = this.getJsonNode(compHistObj, compHistDD.comp_date),
                compDet = this.getJsonNode(compHistObj, compHistDD.comp_det),
                frmtCompHistObj = {};
            var frmtCompDet = [];
            _.each(compDet, function(compDetObj) {
                var frmtCompDetObj = {
                    'comp_type' : this.getJsonNode(compDetObj, compHistDD.comp_type),
                    'comp_amt' : this.getJsonNode(compDetObj, compHistDD.comp_amt),
                    'comp_curr' : this.getJsonNode(compDetObj, compHistDD.comp_curr)
                };
                if (frmtCompDetObj.comp_amt) {
                    frmtCompDetObj.comp_amt = this.formatSalesRevenue(frmtCompDetObj.comp_amt);
                    frmtCompDet.push(frmtCompDetObj);
                }
            },this);
            if (frmtCompDet.length > 0 && compDate) {
                frmtCompHistObj.comp_date = compDate;
                frmtCompHistObj.comp_det = frmtCompDet;
                frmtCompHist.push(frmtCompHistObj);
            }
        },this);
        return frmtCompHist;
    },

    /**
     * Validates the search parameters
     * Either one of the first name / last name / job title must be given in order
     * to enable the search button
     */
    validateSearchParams: function() {
        var self = this;
        self.$('#dnb-cntct-srch-btn').addClass('disabled');
        var searchInputsColl = this.$('.input-large');
        //A Search can be performed only if the accounts is associated with a DUNS
        if (self.duns_num) {
            _.each(searchInputsColl, function(searchInputObj) {
                if ($.trim($(searchInputObj).val()) !== '')
                    self.$('#dnb-cntct-srch-btn').removeClass('disabled');
            });
        }
    },

    /**
     * Masks the email address
     * @param  {String} email
     * @return {String} masked email
     */
    emailMask: function(email) {
        var match = email.match(/([A-Za-z]{2})(.*)(@)(.*)/);
        return match[1] + match[2].replace(/./g, 'x') + match[3] + match[4];
    },

    /**
     * Masks the email address
     * @param  {String} phone
     * @return {String} masked phone
     */
    phoneMask: function(phone) {
        var match = phone.match(/([1-9]{2})(.*)([1-9]{2})/);
        return match[1] + match[2].replace(/./g, 'x') + match[3];
    }
})
