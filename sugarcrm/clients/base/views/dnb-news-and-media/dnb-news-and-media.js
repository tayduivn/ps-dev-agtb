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

    initialize: function(options)
    {
        this._super('initialize', [options]);
        if(this.layout.collapse)
            this.layout.collapse(true);      
        this.layout.on('dashlet:collapse', this.loadNews, this);
        app.events.on("dnbcompinfo:duns_selected",this.collapseDashlet,this);
    },

    collapseDashlet: function()
    {
        if(this.layout.collapse)
            this.layout.collapse(true);      
    },

    loadData: function(options) {

       if(this.model.get("duns_num"))
          this.duns_num = this.model.get("duns_num");
       this.template = app.template.get(this.name + '.dnb-desc');
       if (!this.disposed) this.render();
    },

    loadNews: function(isCollapsed)
    {
        if(!isCollapsed)
        {
            //check if account is linked with a D-U-N-S
            if(this.duns_num)
                this.getNewsandMediaInfo(this.duns_num);
            //check if D-U-N-S is set in context by refresh dashlet
            else if(!_.isUndefined(app.controller.context.get('dnb_temp_duns_num')))
                this.getNewsandMediaInfo(app.controller.context.get('dnb_temp_duns_num'));
            else
            {
                this.template = app.template.get(this.name + '.dnb-no-duns');
                if (!this.disposed) 
                    this.render();
            }
        }
    },

    getNewsandMediaInfo: function(duns_num)
    {
        var self = this;
        self.template = app.template.get(self.name);
        self.render();
        self.$('div#dnb-news-detail-loading').show();
        self.$('div#dnb-news-detail').hide();

        if(duns_num && duns_num != '')
        {
               var dnbNewInfoURL = app.api.buildURL('connector/dnb/news/' + duns_num,'',{},{});
               var resultData = {};
               app.api.call('READ', dnbNewInfoURL, {},{
                    success: function(data) 
                    {
                        var resultIDPath = "OrderProductResponse.TransactionResult.ResultID";
                        var productPath = "OrderProductResponse.OrderProductResponseDetail.Product.Organization.News";
                         if(self.checkJsonNode(data,resultIDPath) 
                            && data.OrderProductResponse.TransactionResult.ResultID == 'CM000')
                        {
                            if(self.checkJsonNode(data,productPath))
                                resultData.product = data.OrderProductResponse.OrderProductResponseDetail.Product.Organization.News;
                            else
                               resultData.errmsg = app.lang.get('LBL_DNB_NO_DATA');
                        }
                        else
                            resultData = {'errmsg': app.lang.get('LBL_DNB_SVC_ERR')};
                            
                        if (self.disposed)
                            return;

                        _.extend(self, resultData);
                        self.render();
                        self.$('div#dnb-news-detail-loading').hide();
                        self.$('div#dnb-news-detail').show(); 
                        self.$(".showLessData").hide();
                    }
            });
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
    }
})
