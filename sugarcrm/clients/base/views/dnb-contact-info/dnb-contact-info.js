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

    plugins: ['Dashlet'],

    duns_num : null, 

    contactsList : null,

    currentContact : null,

    importBtn : null,
   
    events: 
    {
      'click .showMoreData' :'showMoreData',
      'click .showLessData' :'showLessData',
      'click .dnb-cnt-det'  :'getDNBContactDetails',
      'click .backToList'   : 'renderContactsList'
    },

    initialize: function(options) {
        this._super('initialize', [options]);
        if(this.layout.collapse)
            this.layout.collapse(true);      
        this.layout.on('dashlet:collapse', this.loadContacts, this);
        app.events.on("dnbcompinfo:duns_selected",this.collapseDashlet,this);
    },

    collapseDashlet: function()
    {
        if(this.layout.collapse)
            this.layout.collapse(true);      
    },

    loadData: function (options) {

        if(this.model.get("duns_num"))
          this.duns_num = this.model.get("duns_num");
        this.template = app.template.get(this.name + '.dnb-desc');
        if (!this.disposed) this.render();
    },

    loadContacts: function(isCollapsed)
    {
        if(!isCollapsed)
        {
            //check if account is linked with a D-U-N-S
            if(this.duns_num)
                this.getDNBContacts(this.duns_num);
            //check if D-U-N-S is set in context by refresh dashlet
            else if(!_.isUndefined(app.controller.context.get('dnb_temp_duns_num')))
                this.getDNBContacts(app.controller.context.get('dnb_temp_duns_num'));
            else
            {
                this.template = app.template.get(this.name + '.dnb-no-duns');
                if (!this.disposed) 
                    this.render();
            }
        }
    },

    renderContactsList: function()
    {
        this.template = app.template.get(this.name);
        var resultData = {'contacts':this.contactsList};
        _.extend(this, resultData);
        this.render();
        this.$('#dnb-contact-list-loading').hide();
        this.$('#dnb-contact-list').show();
        if(this.layout.getComponent('dashlet-toolbar').getField('import_dnb_data'))
            this.layout.getComponent('dashlet-toolbar').getField('import_dnb_data').getFieldElement().hide();
        this.$('.showLessData').hide();
    },

    /*
      Get contacts for the given duns number  
    */
    getDNBContacts: function(duns_num) 
    {
        var self = this;

        if(duns_num)
        {
            self.duns_num = duns_num;
            self.template = app.template.get(self.name);
            self.render();
            self.$('#dnb-contact-list-loading').show();
            self.$('#dnb-contact-list').hide();

            //check if cache has this data already
            var cacheKey = 'dnb:cntlist:' + duns_num;

            if(app.cache.get(cacheKey))
            {
                self.contactsList = app.cache.get(cacheKey);
                _.bind(self.renderContactsList,self)();
            }
            else
            {
                    var dnbFindContactsURL = app.api.buildURL('connector/dnb/findContacts/' + duns_num,'',{},{});
                    var resultData = {'contacts':null,'errmsg':null};
                    app.api.call('READ', dnbFindContactsURL, {},{
                        success: function(data) 
                        {
                            
                             var contactsPath = "FindContactResponse.FindContactResponseDetail.FindCandidate";
                             var resultTextPath = "FindContactResponse.TransactionResult.ResultText";

                             var contactsArray = self.checkJsonNode(data,contactsPath);
                             if(contactsArray)
                             {
                                resultData.contacts = _.map(contactsArray,function(contactObj)
                                                        { 
                                                            
                                                            var telephoneIndicator = self.checkJsonNode(contactObj,"DirectTelephoneInformationAvailableIndicator");
                                                            var emailIndicator = self.checkJsonNode(contactObj,"DirectEmailInformationAvailableIndicator");

                                                            if(telephoneIndicator || emailIndicator)
                                                            {
                                                                contactObj.moreDetails = true;
                                                            }

                                                            return contactObj;
                                                        });
                                //for back to list functionality
                                self.contactsList =  resultData.contacts; 

                                app.cache.set(cacheKey,resultData.contacts);
                             }
                             else
                             {
                                var errMsg = self.checkJsonNode(data,resultTextPath);
                                resultData.errmsg  = errMsg ? errMsg : app.lang.get('LBL_DNB_SVC_ERR');
                             }
                            
                           
                            if (self.disposed) {
                                return;
                            }

                            self.template = app.template.get(self.name);
                            _.extend(self, resultData);
                            if (!self.disposed) 
                            {
                                self.render();
                                self.$('#dnb-contact-list-loading').hide();
                                self.$('#dnb-contact-list').show();
                                self.$('.showLessData').hide();
                                if(self.layout.getComponent('dashlet-toolbar').getField('import_dnb_data'))
                                    self.layout.getComponent('dashlet-toolbar').getField('import_dnb_data').getFieldElement().hide();
                            }
                        }
                    });
            }


           
        }
        else
        {
            self.template = app.template.get(self.name + '.dnb-no-duns');
            if (!self.disposed) 
            {
                self.render();
            }
        }
    },

    /*
        Gets contact details for a duns and principal identification number combination
    */
    getDNBContactDetails: function(evt)
    {
        var contact_id =  evt.target.id;
        var contact_name = evt.target.text;

        var self = this;
        self.template = app.template.get(self.name + '.dnb-contact-details');
        _.extend(self,{'contactName' : contact_name});
        self.render();
        self.$('div#dnb-contact-details-loading').show();
        self.$('div#dnb-contact-details').hide();
        if(self.layout.getComponent('dashlet-toolbar').getField('import_dnb_data'))
            self.layout.getComponent('dashlet-toolbar').getField('import_dnb_data').getFieldElement().hide();

        var contactParams = {
            'duns_num' : self.duns_num,
            'contact_id' : contact_id
        };

        //check if cache has this data already
        var cacheKey = 'dnb:cntdet:' + contactParams.duns_num + ':' + contactParams.contact_id;

        if(app.cache.get(cacheKey)) _.bind(self.renderContactDetails,self,{'contactdetail' : app.cache.get(cacheKey)})();
        else
        {
            var dnbContactDetailsURL = app.api.buildURL('connector/dnb/contacts','',{},{});
            var resultData = {'contactdetail':null,'errmsg' :null};
            self.currentContact = null;
            app.api.call('create', dnbContactDetailsURL,{'qdata':contactParams},{
                    success: function(data) 
                    {
                        var responseStatusPath = "OrderProductResponse.TransactionResult.ResultID";
                        var resultTextPath = "OrderProductResponse.TransactionResult.ResultText";
                        var contactDetailPath = "OrderProductResponse.OrderProductResponseDetail.Product";

                        var responseStatus = self.checkJsonNode(data,responseStatusPath);
                        var contactDetail = self.checkJsonNode(data,contactDetailPath);
                        if(responseStatus && responseStatus == 'CM000' && contactDetail)
                        {
                            resultData.contactdetail = contactDetail;
                            self.currentContact = resultData.contactdetail;
                            //store result in cache only on success
                            app.cache.set(cacheKey,resultData.contactdetail);
                        }
                        else
                        {
                            var errMsg = self.checkJsonNode(data,resultTextPath);
                            resultData.errmsg = errMsg ? errMsg : app.lang.get('LBL_DNB_SVC_ERR');
                        }
                        _.bind(self.renderContactDetails,self,resultData)();
                    }
                });
        }
    },

    /*
        Render contact details
    */
    renderContactDetails: function(contactDetails)
    {
        this.template = app.template.get(this.name + '.dnb-contact-details');
        _.extend(this, contactDetails);
        if (!this.disposed) 
        {
            this.render();
            this.$('div#dnb-contact-details-loading').hide();
            this.$('div#dnb-contact-details').show();
            if(!contactDetails.errmsg) 
            {
                if(this.layout.getComponent('dashlet-toolbar').getField('import_dnb_data'))
                    this.layout.getComponent('dashlet-toolbar').getField('import_dnb_data').getFieldElement().show();
            }
        }
    },

    /*
        imports the current contact information
    */
    importDNBData: function()
    {
        var parentModel = this.context.get("model"),            
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
            if(!model) {
                return;
            }

            self.context.resetLoadFlag();
            self.context.set('skipFetch', false);
            self.context.loadData();

             _.each(app.controller.context.children,function(childContext){
                if(childContext.get('module') == 'Contacts')
                {
                    childContext.reloadData(true);
                }
            });
        });
    },

    /*
        Get contacts bean
    */
    getContactsModel: function()
    {
        var contactBean = 
        {
            'account_id':this.model.get('id'),
            'account_name':this.model.get('name')
        };

        var emailIDPath = "Organization.PrincipalsAndManagement.CurrentPrincipal.0.Telecommunication.EmailAddress.0.TelecommunicationAddress";
        var phonePath = "";
        var principalIDPath = "Organization.PrincipalsAndManagement.CurrentPrincipal.0.PrincipalIdentificationNumberDetail.PrincipalIdentificationNumber";
        var firstNamePath = "Organization.PrincipalsAndManagement.CurrentPrincipal.0.PrincipalName.FirstName";
        var lastNamePath = "Organization.PrincipalsAndManagement.CurrentPrincipal.0.PrincipalName.LastName";
        var fullNamePath = "Organization.PrincipalsAndManagement.CurrentPrincipal.0.PrincipalName.FullName";
        var departmentPath = "Organization.PrincipalsAndManagement.CurrentPrincipal.0.CurrentManagementResponsibility.0.ManagementResponsibilityText.$";
        var jobTitlePath = "Organization.PrincipalsAndManagement.CurrentPrincipal.0.JobTitle.0.JobTitleText.$";
        var salutationPath = "Organization.PrincipalsAndManagement.CurrentPrincipal.0.PrincipalName.NamePrefix.NamePrefixText";

        //to do handle array
        var email1,first_name,last_name,full_name,salutation,title,department,salutation,dnb_principal_id;

        if(email1 = this.checkJsonNode(this.currentContact,emailIDPath))
        {
          // contactBean.email = email1;
          var emailObj = 
          {
            email_address: email1,
            flagClass: "primary",
            flagLabel: "Primary",
            hasAnchor: true,
            invalid_email: false,
            opt_out: false,
            primary_address: true,
            reply_to_address: false
          };

          contactBean.email = new Array(emailObj);

        } 
        if(first_name = this.checkJsonNode(this.currentContact,firstNamePath)) contactBean.first_name = first_name;
        if(last_name = this.checkJsonNode(this.currentContact,lastNamePath)) contactBean.last_name = last_name;
        if(full_name = this.checkJsonNode(this.currentContact,fullNamePath)) contactBean.full_name = full_name;
        if(title = this.checkJsonNode(this.currentContact,jobTitlePath)) contactBean.title = title;
        if(department = this.checkJsonNode(this.currentContact,departmentPath)) contactBean.department = department;
        if(salutation = this.checkJsonNode(this.currentContact,salutationPath)) contactBean.salutation = salutation;
        if(dnb_principal_id = this.checkJsonNode(this.currentContact,principalIDPath)) contactBean.dnb_principal_id = dnb_principal_id;

        var contactsModel = app.data.createRelatedBean(this.model,null,'contacts',contactBean);
        return contactsModel;
    },

    /**
      Utility function to check if a node exists in a json object
    **/
    checkJsonNode: function(obj,path) 
    {
        var args = path.split(".");

        for (var i = 0; i < args.length; i++) 
        {
            if (obj == null || !obj.hasOwnProperty(args[i]) ) 
            {
                return;
            }
            obj = obj[args[i]];
        }
        return obj;
    },

    showMoreData: function () {
        this.$(".dnb-show-less").attr("class","dnb-show-all");
        this.$('.showMoreData').hide();
        this.$('.showLessData').show();
    },

    showLessData: function () {
        this.$(".dnb-show-all").attr("class","dnb-show-less");
        this.$('.showMoreData').show();
        this.$('.showLessData').hide();
    }

})
