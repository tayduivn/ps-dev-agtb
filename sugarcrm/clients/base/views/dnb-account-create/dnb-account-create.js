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

    duns_num: '',

    //used to detect if import was clicked for the first time when company info was loaded
    //this flag is being used to avoid the warning message for account name
    //when the user clicks the import button first time when company info is loaded
    //a user clicks import
    importFlag: false,

    companyList: null,
   
    events: 
    {
        'click a.dnb-company-name':'getDNBCompInfo',
        'click .showMoreData':'showMoreData',
        'click .showLessData': 'showLessData',
        'click .importDNBData': 'importDNBData',
        'click .dnb_checkbox': 'importCheckBox',
        'click .clearDNBResults': 'clearDNBResults',
        'click .backToList' : 'backToCompanyList'
	},

	initialize:function(options)
	{
        app.view.View.prototype.initialize.call(this, options);
        this.context.on('input:name:keyup', this.dnbSearch, this);  
	},
	
    loadData: function (options) 
	{
        var self = this;
        self.template = app.template.get(self.name + '.dnb-search-hint');
        self.render();
    },

    backToCompanyList: function()
    {
        _.bind(this.renderCompanyList,this,this.companyList)();
    },

    renderCompanyList: function(companyList)
    {
        this.template = app.template.get(this.name);
        _.extend(this,companyList);
        this.render();
        this.$('div#dnb-company-list-loading').hide();
        this.$('div#dnb-search-results').show();
        this.$('.showLessData').hide();
    },

    /* event listener for keyup / autocomplete feature */
    dnbSearch: function(searchString)
    {
        var self = this;
        self.template = app.template.get(self.name);
        self.render();
        self.$('table#dnb_company_list').empty(); //empty results table
        self.$('div#dnb-search-results').hide(); //hide results div
        self.$('div#dnb-company-list-loading').show(); //show loading text
        self.$('.clearDNBResults').attr('disabled','disabled'); //disable clear button
        self.$('.clearDNBResults').removeClass('enabled');
        self.$('.clearDNBResults').addClass('disabled');

        var dnbSearchUrl = app.api.buildURL('connector/dnb/search/' + searchString,'',{},{});
        self.companyList = null;
        app.api.call('READ', dnbSearchUrl, {},{
                success: function(data) 
                {
                     var candidateData = {'companies':null,'errmsg':null};
                     self.template = app.template.get(self.name);

                     try
                     {
                        var resultdata = data;
                        var resultIDPath = "FindCompanyResponse.TransactionResult.ResultID";

                         if(self.checkJsonNode(resultdata,resultIDPath) && 
                            resultdata.FindCompanyResponse.TransactionResult.ResultID == 'CM000')
                         {
                            candidateData.companies = resultdata.FindCompanyResponse.FindCompanyResponseDetail.FindCandidate; 
                             _.each(candidateData.companies,function(companyObj){
                                
                                if(companyObj.FamilyTreeMemberRole)
                                {
                                    //we are relying on DNBCodeValue
                                    //higher the code value more the precedence in the family tree role
                                    //hence we are using the _.max function
                                    var locationType = _.max(companyObj.FamilyTreeMemberRole, function(memberRole)
                                    { 
                                        return memberRole.FamilyTreeMemberRoleText["@DNBCodeValue"]; 
                                    });

                                    if(locationType.FamilyTreeMemberRoleText['$'] != 'Parent')
                                        companyObj.locationType = locationType.FamilyTreeMemberRoleText['$'];
                                }
                            });
                            self.companyList = candidateData.companies;
                         }
                         else
                         {
                            candidateData.errmsg = resultdata.FindCompanyResponse.TransactionResult.ResultText;
                         }
                     }
                     catch(e)
                     {
                         candidateData.errmsg = app.lang.get('LBL_DNB_SVC_ERR');
                     }

                      _.extend(self, candidateData);
                      self.render();
                      self.$('div#dnb-company-list-loading').hide();
                      self.$('div#dnb-search-results').show();
                      self.$('.clearDNBResults').removeClass('disabled');
                      self.$('.clearDNBResults').addClass('enabled');
                      self.$(".showLessData").hide();
                }
        });
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
                return false;
            }
            obj = obj[args[i]];
        }
        return true;
    },

    /*
        Clear D&B Search Results
    */
    clearDNBResults:function()
    {
        this.$('table#dnb_company_list').empty();
        this.template = app.template.get(this.name + '.dnb-search-hint');
        this.render();
    },



    /* Get company details based on DUNS no*/
    getDNBCompInfo: function(evt)
    {
        var dunsNo = evt.target.id;
        
        var self = this;
        self.template = app.template.get(self.name + '.dnb-company-details');
        self.render();
        self.$('div#dnb-company-details').hide();
        self.$('.importDNBData').hide();

        if(dunsNo && dunsNo != '')
        {
           //check if cache has this data already
            var cacheKey = 'dnb:compstd:' + dunsNo;

            if(app.cache.get(cacheKey))
            {
                var duns_path = "OrderProductResponse.OrderProductResponseDetail.InquiryDetail.DUNSNumber";
                var resultData = app.cache.get(cacheKey);

                if(self.checkJsonNode(resultData,duns_path))
                    self.duns_num = resultData.OrderProductResponse.OrderProductResponseDetail.InquiryDetail.DUNSNumber;
                _.bind(self.renderCompanyDetails,self,app.cache.get(cacheKey))();
            }
            else
            {
               var dnbProfileUrl = app.api.buildURL('connector/dnb/profile/' + dunsNo,'',{},{});
               var resultData = {};
               app.api.call('READ', dnbProfileUrl, {},{
                        success: function(data) 
                        {
                            self.importFlag = false;

                            var duns_path = "OrderProductResponse.OrderProductResponseDetail.InquiryDetail.DUNSNumber";
                            var resultIDPath = "OrderProductResponse.TransactionResult.ResultID";
                            var resultTextPath = "OrderProductResponse.TransactionResult.ResultText";
                            var industry_path = "OrderProductResponse.OrderProductResponseDetail.Product.Organization.IndustryCode.IndustryCode";

                            if(self.checkJsonNode(data,resultIDPath) && 
                                data.OrderProductResponse.TransactionResult.ResultID == 'CM000')
                            {
                                resultData = data;
                                self.duns_num = resultData.OrderProductResponse.OrderProductResponseDetail.InquiryDetail.DUNSNumber;

                                if(self.checkJsonNode(resultData,industry_path))
                                {
                                    var industryCodeArray = resultData.OrderProductResponse.OrderProductResponseDetail.Product.Organization.IndustryCode.IndustryCode;
                                    //399 is the industry code type value for US SIC
                                    resultData.primarySIC = self.getPrimaryIndustry(industryCodeArray,'399'); 
                                }

                                self.$('.importDNBData').show();
                                app.cache.set(cacheKey,resultData);
                            }
                            else if(self.checkJsonNode(data,resultTextPath))
                            {
                                resultData.errmsg = data.OrderProductResponse.TransactionResult.ResultText;
                            }
                            else
                            {
                                resultData.errmsg = app.lang.get('LBL_DNB_SVC_ERR');
                            }

                         _.bind(self.renderCompanyDetails,self,resultData)();
                              
                        }
                });
            }
           
          
        }
    },

    renderCompanyDetails: function(companyDetails)
    {
        if (this.disposed) {
                return;
            }
        _.extend(this, companyDetails);
        this.render();
        this.$('div#dnb-company-detail-loading').hide();
        this.$('div#dnb-company-details').show(); 
    },

    showMoreData: function () {
        this.$(".dnb-show-less").attr("class","dnb-show-all");
        this.$(".showLessData").show();
        this.$(".showMoreData").hide();
    },

    showLessData: function () {
        this.$(".dnb-show-all").attr("class","dnb-show-less");
        this.$(".showLessData").hide();
        this.$(".showMoreData").show();
    },

    importDNBData: function()
    {
        var dnbCheckBox = this.$('.dnb_checkbox:checked');
        var accountsModel = this.model;

        // always import the duns_num
        accountsModel.set('duns_num',this.duns_num);
        accountsModel.save();

        // iterate through checkboxes
        // values being overriden stored in updatedData
        // values that are newly being set store in newData
        var updatedData = [];
        var newData = [];
        for(var checkBoxCounter = 0; checkBoxCounter < dnbCheckBox.length; checkBoxCounter++)
        {
            var dnbPropertyName = dnbCheckBox[checkBoxCounter].id;
            
            var dnbPropertyValue = $.trim(this.$('#' + dnbPropertyName).parent().next().next().text());
            
            //check if existing value is getting updated
            if(!_.isUndefined(accountsModel.get(dnbPropertyName)) 
                && accountsModel.get(dnbPropertyName) != '' && this.importFlag)
            { 
                updatedData.push({propName:dnbPropertyName,propVal:dnbPropertyValue});
            } else if(dnbPropertyValue != ''){
                newData.push({propName:dnbPropertyName,propVal:dnbPropertyValue});
            } 
        } 

        //importing new data
        if(newData.length > 0)
        {
            this.updateAccountsModel(newData);
        }

        //update existing data
        if(updatedData.length > 0)
        {
            var warningMessage = app.lang.get('LBL_DNB_DATA_OVERRIDE');
            //show a detailed warning message about the single data element being imported
            if(updatedData.length == 1)
            {
                warningMessage = warningMessage + app.lang.get(accountsModel.fields[updatedData[0].propName].vname,'Accounts')  + ': ' + accountsModel.get(updatedData[0].propName) 
                                  + app.lang.get('LBL_DNB_WITH') + updatedData[0].propVal + ' ?';                                      
            }
            //list all the data elements being imported
            else if(updatedData.length <= 3)
            {
                for (var i = 0; i < updatedData.length; i++) 
                {
                    warningMessage = warningMessage + (i == 0 ? '' : ', ') + app.lang.get(accountsModel.fields[updatedData[i].propName].vname,'Accounts');
                   
                }
            }
            //give a brief message about the data elements being imported
            else
            {
                for (var i = 0; i < 2; i++) 
                {
                    warningMessage = warningMessage + (i == 0 ? '' : ', ') + app.lang.get(accountsModel.fields[updatedData[i].propName].vname,'Accounts');
                    
                }
                warningMessage = warningMessage + app.lang.get('LBL_DNB_AND') + (updatedData.length - 2) + app.lang.get('LBL_DNB_OTHER_FIELDS');
            }

            
             app.alert.show('dnb-import-warning', 
                        {
                          level: 'confirmation',
                          title: 'Warning',
                          messages: warningMessage,
                          onConfirm: _.bind(this.updateAccountsModel,this,updatedData)
                        });
        }

        //setting the import flag to true after the first import is complete
        this.importFlag = true;
    },

    /* Overrite existing data with new data */
    updateAccountsModel: function(updatedData)
    {
        /*var accountsModel = this.model;
        for (var i = 0; i < updatedData.length; i++) 
        {
            accountsModel.set(updatedData[i].propName,updatedData[i].propVal);
            accountsModel.save();
        }*/

        var self = this;

        var changedAttributes = {};

        _.each(updatedData,function(updatedAttribute){
            // self.model.set(updatedAttribute.propName,updatedAttribute.propVal);
            changedAttributes[updatedAttribute.propName] = updatedAttribute.propVal;
        });

        self.model.save(changedAttributes);
        self.context.loadData();
        
        app.alert.show('dnb-import-success', {level: 'success',title: 'Success:',messages: app.lang.get('LBL_DNB_OVERRIDE_SUCCESS'),autoClose: true});
    },

    importCheckBox: function() 
    {       
        var dnbCheckBoxes = $('.dnb_checkbox:checked');

        if(dnbCheckBoxes.length > 0) 
        {
            this.$(".importDNBData").removeClass('disabled');   
        } 
        else 
        {
            this.$(".importDNBData").addClass('disabled');   
        }
    },

    /**
     * Gets the primary industry code from the array of industry codes
     * @param industryArray
     * @param industryCode
     * @return object
     */
    getPrimaryIndustry: function(industryArray,industryCode)
    {
        return _.find(industryArray,function(industryObj){

            return industryObj["@DNBCodeValue"] == industryCode && industryObj['DisplaySequence'] == '1';
        });
    },
})
