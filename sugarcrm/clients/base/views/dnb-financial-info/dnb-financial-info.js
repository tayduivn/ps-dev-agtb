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

    duns_num: null,

    events: 
    {
      'click .showMoreData':'showMoreData',
      'click .showLessData':'showLessData',
    },

    initialize: function(options)
    {
        this._super('initialize', [options]);
        if(this.layout.collapse)
            this.layout.collapse(true);      
        this.layout.on('dashlet:collapse', this.loadFinancials, this);
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

    loadFinancials: function(isCollapsed)
    {
        if(!isCollapsed)
        {
            //check if account is linked with a D-U-N-S
            if(this.duns_num)
                this.getCompanyFinancials(this.duns_num);
            //check if D-U-N-S is set in context by refresh dashlet
            else if(!_.isUndefined(app.controller.context.get('dnb_temp_duns_num')))
                this.getCompanyFinancials(app.controller.context.get('dnb_temp_duns_num'));
            else
            {
                this.template = app.template.get(this.name + '.dnb-no-duns');
                if (!this.disposed) 
                    this.render();
            }
        }
    },

    getCompanyFinancials: function(duns_num)
    {
        var self = this;
        self.template = app.template.get(self.name);
        if (!self.disposed) 
        {
            self.render();
            self.$('#dnb-financials-loading').show();
            self.$('#dnb-financials-details').hide();
        }

        var cacheKey = 'dnb:financial:' + duns_num;

        if(app.cache.get(cacheKey))
        {
            _.bind(self.renderFinancialDetails,self,app.cache.get(cacheKey))();
        }
        else
        {
            var dnbFinancialsURL = app.api.buildURL('connector/dnb/financial/' + duns_num,'',{},{});
            var resultData = {'product':null,'errmsg':null};
            app.api.call('READ', dnbFinancialsURL, {},{
                success: function(data) 
                {
                    
                    var resultIDPath = "OrderProductResponse.TransactionResult.ResultID",
                    errMsgPath = "OrderProductResponse.TransactionResult.ResultText";

                    if(self.checkJsonNode(data,resultIDPath) && 
                        data.OrderProductResponse.TransactionResult.ResultID == 'CM000')
                    {
                        if(self.isDataExists(data))
                        {
                            resultData.product = data;
                        }
                        else
                        {
                            resultData.errmsg = app.lang.get('LBL_DNB_NO_DATA');
                        }
                        
                        app.cache.set(cacheKey,resultData);
                    }
                        
                    else if(self.checkJsonNode(data,errMsgPath))
                        resultData.errmsg = data.OrderProductResponse.TransactionResult.ResultText;
                    else
                        resultData.errmsg =  app.lang.get('LBL_DNB_SVC_ERR');

                    _.bind(self.renderFinancialDetails,self,resultData)();
                }
            });
        }
    },

    /*
        to prevent a blank dashlet from being displayed
    */
    isDataExists: function(financialDetails)
    {
        var mktAnalysisPath = "OrderProductResponse.OrderProductResponseDetail.Product.Organization.ThirdPartyAssessment.ThirdPartyAssessment.0.ThirdPartyInformation.OtherInformation";
        var annlIncPath = "OrderProductResponse.OrderProductResponseDetail.Product.Organization.Financial.KeyFinancialFiguresOverview";
        var stckSymPath = "OrderProductResponse.OrderProductResponseDetail.Product.Organization.RegisteredDetail.StockExchangeDetails";

        if(!this.checkJsonNode(financialDetails,mktAnalysisPath) 
            && !this.checkJsonNode(financialDetails,annlIncPath)
            && !this.checkJsonNode(financialDetails,stckSymPath)
            )
            return false;
        else
            return true;
    },

    renderFinancialDetails: function(financialDetails)
    {
        var dunsPath = "OrderProductResponse.OrderProductResponseDetail.InquiryDetail.DUNSNumber";
        if(this.checkJsonNode(financialDetails.product,dunsPath))
            this.duns_num = financialDetails.product.OrderProductResponse.OrderProductResponseDetail.InquiryDetail.DUNSNumber;

         _.extend(this, financialDetails);
        this.render();

        this.$('#dnb-financials-loading').hide();
        this.$('#dnb-financials-details').show();
        this.$(".showLessData").hide();
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
