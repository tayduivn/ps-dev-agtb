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
   
    events: 
    {
      'click .showMoreData':'showMoreData',
      'click .showLessData':'showLessData',
    },

    initialize: function(options) {
        this._super('initialize', [options]);
        if(this.layout.collapse)
            this.layout.collapse(true);
        this.layout.on('dashlet:collapse', this.loadIndustryInfo, this);
        app.events.on("dnbcompinfo:duns_selected",this.collapseDashlet,this);
    },

    collapseDashlet: function()
    {
        if(this.layout.collapse)
            this.layout.collapse(true);      
    },

    loadData: function (options) {
        this.template = app.template.get(this.name + '.dnb-desc');
        if (!this.disposed) this.render();
    },

    /**
     * Loads Industry Information For Hoovers Industry Code
     * @param isCollapsed boolean (indicates if dashlet was expanded or collapsed)
     */
    loadIndustryInfo: function(isCollapsed)
    {
        //if the dashlet is not collapsed load data from D&B
        if(!isCollapsed)
        {
            //check if Hoovers Industry Code is set in context by refresh dashlet
            if(!_.isUndefined(app.controller.context.get('dnb_temp_hoovers_ind_code')))
                this.getDNBIndustryInfo(app.controller.context.get('dnb_temp_hoovers_ind_code'));
            else if(this.model.get('sic_code'))
            {
                var sicToHicParams = {'industryType' : '3599','industryCode': this.model.get('sic_code')};
                this.getDNBIndustryInfoFromSIC(sicToHicParams);
            }
            else
            {
                this.template = app.template.get(this.name + '.dnb-no-duns');
                if (!this.disposed) 
                    this.render();
            }
        }
        
    },

    showDNBLoading: function(duns_num)
    {
        this.template = app.template.get(this.name);
        this.render();
        this.$('#dnb-industry-list-loading').show();
        this.$('#dnb-industry-info').hide();
    },

    getDNBIndustryInfoFromSIC: function(sicToHicParams)
    {
        var self = this;
        if(sicToHicParams.industryType == '3599' && sicToHicParams.industryCode)
        {
            self.template = app.template.get(self.name);
            self.render();
            self.$('#dnb-industry-list-loading').show();
            self.$('#dnb-industry-info').hide();

            //check if cache has this data already
            var cacheKey = 'dnb:industrydet:' + sicToHicParams.industryType + ':' + sicToHicParams.industryCode;

            if(app.cache.get(cacheKey))
            {
                _.bind(self.renderIndustryInfo,self,app.cache.get(cacheKey))();
            }
            else
            {
                var dnbIndustryURL = app.api.buildURL('connector/dnb/industry','',{},{});
                var resultData = {'product':null,'errmsg':null};
                app.api.call('create', dnbIndustryURL,{'qdata':sicToHicParams},{
                    success: function(data) 
                    {
                        var resultIDPath = "OrderProductResponse.TransactionResult.ResultID",
                        errMsgPath = "OrderProductResponse.TransactionResult.ResultText";

                        if(self.checkJsonNode(data,resultIDPath) &&
                            data.OrderProductResponse.TransactionResult.ResultID == 'CM000')
                        {
                            resultData.product = data;
                            app.cache.set(cacheKey,resultData);
                        }
                        else if(self.checkJsonNode(data,errMsgPath))
                        {
                            resultData.errmsg = data.OrderProductResponse.TransactionResult.ResultText;
                        }
                        else
                            resultData.errmsg = app.lang.get('LBL_DNB_SVC_ERR');

                        _.bind(self.renderIndustryInfo,self,resultData)();
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

    getDNBIndustryInfo: function(industryCodeValue) {
        var self = this;
        if(industryCodeValue)
        {
            self.template = app.template.get(self.name);
            self.render();
            self.$('#dnb-industry-list-loading').show();
            self.$('#dnb-industry-info').hide();

            //check if cache has this data already
            var cacheKey = 'dnb:industrydet:' + industryCodeValue;

            if(app.cache.get(cacheKey))
            {
                _.bind(self.renderIndustryInfo,self,app.cache.get(cacheKey))();
            }
            else
            {
                var dnbIndustryURL = app.api.buildURL('connector/dnb/industry/' + industryCodeValue,'',{},{});
                var resultData = {'product':null,'errmsg':null};
                app.api.call('READ', dnbIndustryURL, {},{
                    success: function(data) 
                    {
                        var resultIDPath = "OrderProductResponse.TransactionResult.ResultID",
                        errMsgPath = "OrderProductResponse.TransactionResult.ResultText";

                        if(self.checkJsonNode(data,resultIDPath) &&
                            data.OrderProductResponse.TransactionResult.ResultID == 'CM000')
                        {
                            resultData.product = data;
                            app.cache.set(cacheKey,resultData);
                        }
                        else if(self.checkJsonNode(data,errMsgPath))
                        {
                            resultData.errmsg = data.OrderProductResponse.TransactionResult.ResultText;
                        }
                        else
                            resultData.errmsg = app.lang.get('LBL_DNB_SVC_ERR');

                        _.bind(self.renderIndustryInfo,self,resultData)();
                        
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

    renderIndustryInfo: function(industryDetails)
    {
        if (this.disposed) {
            return;
        }
        this.template = app.template.get(this.name);
        _.extend(this, industryDetails);
        if (!this.disposed) 
        {
            this.render();
            this.$('#dnb-industry-list-loading').hide();
            this.$('#dnb-industry-info').show();
            this.$(".showLessData").hide();
        }
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

})
