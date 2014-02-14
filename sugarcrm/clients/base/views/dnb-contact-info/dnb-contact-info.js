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

    contactsList: null,

    currentContact: null,

    importBtn: null,

    events: {
        'click .showMoreData' : 'showMoreData',
        'click .showLessData' : 'showLessData',
        'click .dnb-cnt-prem' : 'getDNBContactDetails',
        'click .dnb-cnt-std' : 'getDNBContactDetails',
        'click .backToList' : 'renderContactsList'
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
     * Collapses the dashlet
     */
    collapseDashlet: function() {
        if (this.layout.collapse) {
            this.layout.collapse(true);
        }
    },

    loadData: function(options) {
        if (this.model.get('duns_num')) {
            this.duns_num = this.model.get('duns_num');
        }
        this.template = app.template.get(this.name + '.dnb-desc');
        if (!this.disposed) {
            this.render();
        }
    },

    /**
     * Handles the dashlet expand | collapse events
     * @param  {Boolean} isCollapsed
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
     * Renders the list of D&B Contacts
     */
    renderContactsList: function() {
        if (this.disposed) {
            return;
        }
        this.template = app.template.get(this.name);
        var resultData = {'contacts': this.contactsList};
        _.extend(this, resultData);
        this.render();
        this.$('#dnb-contact-list-loading').hide();
        this.$('#dnb-contact-list').show();
        if (this.layout.getComponent('dashlet-toolbar').getField('import_dnb_data')) {
            this.layout.getComponent('dashlet-toolbar').getField('import_dnb_data').getFieldElement().hide();
        }
        this.$('.showLessData').hide();
    },

    /**
     * Gets the list of contacts for a DUNS number
     * @param {String} duns_num
     */
    getDNBContacts: function(duns_num) {
        var self = this;
        if (duns_num) {
            self.duns_num = duns_num;
            self.template = app.template.get(self.name);
            if (!self.disposed) {
                self.render();
            }
            self.$('#dnb-contact-list-loading').show();
            self.$('#dnb-contact-list').hide();
            //check if cache has this data already
            var cacheKey = 'dnb:cntlist:' + duns_num;
            if (app.cache.get(cacheKey)) {
                self.contactsList = app.cache.get(cacheKey);
                self.renderContactsList.call(self);
            } else {
                var dnbFindContactsURL = app.api.buildURL('connector/dnb/findContacts/' + duns_num, '', {},{});
                var resultData = {'contacts': null, 'errmsg': null};
                app.api.call('READ', dnbFindContactsURL, {},{
                    success: function(data) {
                        var contactsPath = 'FindContactResponse.FindContactResponseDetail.FindCandidate';
                        var resultTextPath = 'FindContactResponse.TransactionResult.ResultText';
                        var contactsArray = self.getJsonNode(data, contactsPath);
                        if (contactsArray) {
                            resultData.contacts = _.map(contactsArray, function(contactObj) {
                                var telephoneIndicator = self.getJsonNode(contactObj, 'DirectTelephoneInformationAvailableIndicator');
                                var emailIndicator = self.getJsonNode(contactObj, 'DirectEmailInformationAvailableIndicator');
                                if (telephoneIndicator || emailIndicator) {
                                    contactObj.moreDetails = true;
                                }
                                return contactObj;
                            });
                            //for back to list functionality
                            self.contactsList = resultData.contacts;
                            app.cache.set(cacheKey, resultData.contacts);
                        } else {
                            var errMsg = self.getJsonNode(data, resultTextPath);
                            resultData.errmsg = errMsg ? errMsg : app.lang.get('LBL_DNB_SVC_ERR');
                        }
                        self.template = app.template.get(self.name);
                        _.extend(self, resultData);
                        if (!self.disposed) {
                            self.render();
                            self.$('#dnb-contact-list-loading').hide();
                            self.$('#dnb-contact-list').show();
                            self.$('.showLessData').hide();
                            if (self.layout.getComponent('dashlet-toolbar').getField('import_dnb_data')) {
                                self.layout.getComponent('dashlet-toolbar').getField('import_dnb_data').getFieldElement().hide();
                            }
                        }
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
     * @param  {Object} evt
     */
    getDNBContactDetails: function(evt) {
        var contact_id = evt.target.id;
        var contact_name = evt.target.text, contact_type;
        if ($.inArray('dnb-cnt-prem', evt.target.classList) === 1) {
            contact_type = 'dnb-cnt-prem';
        } else if ($.inArray('dnb-cnt-std', evt.target.classList) === 1) {
            contact_type = 'dnb-cnt-std';
        }
        var self = this;
        self.template = app.template.get(self.name + '.dnb-contact-details');
        _.extend(self, {'contactName' : contact_name});
        if (self.disposed) {
            return;
        }
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
        var cacheKey = 'dnb:' + contactParams.contact_type + ':' + contactParams.duns_num + ':' +
            contactParams.contact_id;
        if (app.cache.get(cacheKey)) {
            self.currentContact = app.cache.get(cacheKey);
            self.renderContactDetails.call(self, {'contactdetail' : app.cache.get(cacheKey)});
        } else {
            var dnbContactDetailsURL = app.api.buildURL('connector/dnb/contacts', '', {},{});
            var resultData = {'contactdetail': null, 'errmsg' : null};
            self.currentContact = null;
            app.api.call('create', dnbContactDetailsURL, {'qdata': contactParams},{
                success: function(data) {
                    var responseStatusPath = 'OrderProductResponse.TransactionResult.ResultID';
                    var resultTextPath = 'OrderProductResponse.TransactionResult.ResultText';
                    var contactDetailPath = 'OrderProductResponse.OrderProductResponseDetail.Product';
                    var responseStatus = self.getJsonNode(data, responseStatusPath);
                    var contactDetail = self.getJsonNode(data, contactDetailPath);
                    if (responseStatus && responseStatus === 'CM000' && contactDetail) {
                        resultData.contactdetail = contactDetail;
                        self.currentContact = resultData.contactdetail;
                        //store result in cache only on success
                        app.cache.set(cacheKey, resultData.contactdetail);
                    } else {
                        var errMsg = self.getJsonNode(data, resultTextPath);
                        resultData.errmsg = errMsg ? errMsg : app.lang.get('LBL_DNB_SVC_ERR');
                    }
                    self.renderContactDetails.call(self, resultData);
                },
                error: _.bind(self.checkAndProcessError, self)
            });
        }
    },

    /**
     * Renders the contact details
     * @param {Object} contactDetails
     */
    renderContactDetails: function(contactDetails) {
        this.template = app.template.get(this.name + '.dnb-contact-details');
        _.extend(this, contactDetails);
        if (!this.disposed) {
            this.render();
            this.$('div#dnb-contact-details-loading').hide();
            this.$('div#dnb-contact-details').show();
            if (!contactDetails.errmsg) {
                if (this.layout.getComponent('dashlet-toolbar').getField('import_dnb_data')) {
                    this.layout.getComponent('dashlet-toolbar').getField('import_dnb_data').getFieldElement().show();
                }
            }
        }
    },

    /**
     * Imports the current contact information
     * Opens the contact drawer
     */
    importDNBData: function() {
        var parentModel = this.context.get('model'),
            model = this.getContactsModel();
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
     * Gets the contact bean
     * @return {Object}
     */
    getContactsModel: function() {
        var contactBean = {
            'account_id': this.model.get('id'),
            'account_name': this.model.get('name')
        };
        var emailIDPath = 'Organization.PrincipalsAndManagement.CurrentPrincipal.0.Telecommunication.EmailAddress.0.TelecommunicationAddress';
        var phonePath = 'Organization.PrincipalsAndManagement.CurrentPrincipal.0.Telecommunication.TelephoneNumber.0.TelecommunicationNumber';
        var principalIDPath = 'Organization.PrincipalsAndManagement.CurrentPrincipal.0.PrincipalIdentificationNumberDetail.PrincipalIdentificationNumber';
        var firstNamePath = 'Organization.PrincipalsAndManagement.CurrentPrincipal.0.PrincipalName.FirstName';
        var lastNamePath = 'Organization.PrincipalsAndManagement.CurrentPrincipal.0.PrincipalName.LastName';
        var fullNamePath = 'Organization.PrincipalsAndManagement.CurrentPrincipal.0.PrincipalName.FullName';
        var departmentPath = 'Organization.PrincipalsAndManagement.CurrentPrincipal.0.CurrentManagementResponsibility.0.ManagementResponsibilityText.$';
        var jobTitlePath = 'Organization.PrincipalsAndManagement.CurrentPrincipal.0.JobTitle.0.JobTitleText.$';
        var salutationPath = 'Organization.PrincipalsAndManagement.CurrentPrincipal.0.PrincipalName.NamePrefix.NamePrefixText';
        //to do handle array
        var email1, phone_work, first_name, last_name, full_name, salutation, title,
            department, salutation, dnb_principal_id;

        if (email1 = this.getJsonNode(this.currentContact, emailIDPath)) {
            var emailObj = {
                email_address: email1,
                flagClass: 'primary',
                flagLabel: 'Primary',
                hasAnchor: true,
                invalid_email: false,
                opt_out: false,
                primary_address: true,
                reply_to_address: false
            };
            contactBean.email = new Array(emailObj);
        }
        if (phone_work = this.getJsonNode(this.currentContact, phonePath)) {
            contactBean.phone_work = phone_work;
        }
        if (first_name = this.getJsonNode(this.currentContact, firstNamePath)) {
            contactBean.first_name = first_name;
        }
        if (last_name = this.getJsonNode(this.currentContact, lastNamePath)) {
            contactBean.last_name = last_name;
        }
        if (full_name = this.getJsonNode(this.currentContact, fullNamePath)) {
            contactBean.full_name = full_name;
        }
        if (title = this.getJsonNode(this.currentContact, jobTitlePath)) {
            contactBean.title = title;
        }
        if (department = this.getJsonNode(this.currentContact, departmentPath)) {
            contactBean.department = department;
        }
        if (salutation = this.getJsonNode(this.currentContact, salutationPath)) {
            contactBean.salutation = salutation;
        }
        if (dnb_principal_id = this.getJsonNode(this.currentContact, principalIDPath)) {
            contactBean.dnb_principal_id = dnb_principal_id;
        }
        var contactsModel = app.data.createRelatedBean(this.model, null, 'contacts', contactBean);
        return contactsModel;
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
    }
});
