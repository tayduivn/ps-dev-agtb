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

    initialize:function(options)
    {
        this._super('initialize', [options]);    
        if(this.layout.collapse)
            this.layout.collapse(true);      
        this.layout.on('dashlet:collapse', this.loadCompetitors, this);
        app.events.on("dnbcompinfo:duns_selected",this.collapseDashlet,this);
    },
   
    loadData: function (options) {
        if(this.model.get("duns_num"))
		  this.duns_num = this.model.get("duns_num");
        this.template = app.template.get(this.name + '.dnb-desc');
        if (!this.disposed) this.render();
    },

    collapseDashlet: function()
    {
        if(this.layout.collapse)
            this.layout.collapse(true);      
    },

    loadCompetitors: function(isCollapsed)
    {
        //if the dashlet is not collapsed load data from D&B
        if(!isCollapsed)
        {
            //check if account is linked with a D-U-N-S
            if(this.duns_num)
                this.getDNBCompetitors(this.duns_num);
            //check if D-U-N-S is set in context by refresh dashlet
            else if(!_.isUndefined(app.controller.context.get('dnb_temp_duns_num')))
                this.getDNBCompetitors(app.controller.context.get('dnb_temp_duns_num'));
            else
            {
                this.template = app.template.get(this.name + '.dnb-no-duns');
                if (!this.disposed) 
                    this.render();
            }
        }
        
    },

    /*
        retrieves dnb competitors for the given duns no.
    */
    getDNBCompetitors : function(duns_num)
    {
        var self = this;
        if(duns_num)
        {
            self.template = app.template.get(self.name);
            if (!self.disposed) 
            {
                self.render();
                self.$('div#dnb-competitors-list').hide();
                self.$('div#dnb-no-data').hide();
            }

            //check if cache has this data already
            var cacheKey = 'dnb:competitors:' + duns_num;

            if(app.cache.get(cacheKey))
                _.bind(self.renderCompetitors,self,app.cache.get(cacheKey))();
            else
            {
                var dnbCompetitorsURL = app.api.buildURL('connector/dnb/competitors/' + duns_num,'',{},{});
                var resultData = {'competitors':null,'errmsg' :null};
                app.api.call('READ', dnbCompetitorsURL, {},{
                    success: function(data) 
                    {
                        var competitorsPath = "FindCompetitorResponse.FindCompetitorResponseDetail.Competitor";
                        var resultTextPath = "FindCompetitorResponse.TransactionResult.ResultText";

                        if(self.checkJsonNode(data,competitorsPath))
                        {
                            var topCompGroup = _.groupBy(data.FindCompetitorResponse.FindCompetitorResponseDetail.Competitor,
                                                function(competitorObj){
                                                    return competitorObj.TopCompetitorIndicator;
                                                });
                            
                            if(topCompGroup.hasOwnProperty('true') && topCompGroup.hasOwnProperty('false'))
                                resultData.competitors = _.union(topCompGroup.true,topCompGroup.false);
                            else
                                resultData.competitors = data.FindCompetitorResponse.FindCompetitorResponseDetail.Competitor;

                            app.cache.set(cacheKey,resultData);
                        }
                        else if(self.checkJsonNode(data,resultTextPath))
                        {
                            resultData.errmsg = data.FindCompetitorResponse.TransactionResult.ResultText;
                        }
                        else
                        {
                            resultData.errmsg =  app.lang.get('LBL_DNB_SVC_ERR');
                        }

                        _.bind(self.renderCompetitors,self,resultData)();
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

    renderCompetitors: function(competitorsList)
    {
        if (this.disposed) {
            return;
        }
        _.extend(this, competitorsList);
        this.render();
        this.$('div#dnb-competitors-loading').hide();
        this.$('div#dnb-no-data').hide();
        this.$('div#dnb-competitors-list').show();
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
