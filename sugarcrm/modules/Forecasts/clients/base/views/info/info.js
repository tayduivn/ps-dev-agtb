/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 ********************************************************************************/
/**
 * @class View.Views.Base.Forecasts.InfoView
 * @alias SUGAR.App.view.views.BaseForecastsInfoView
 * @extends View.View
 */
({
    /**
     * Timeperiod model 
     */
    tpModel: undefined,
    
    /**
     * {@inheritdoc}
     *
     */
    initialize: function(options) {
        this.tpModel = new Backbone.Model();
        this._super("initialize", [options]);
        this.resetSelection(this.context.get("selectedTimePeriod"));
    },
    
    /**
     * {@inheritdoc}
     *
     */
    bindDataChange: function(){
        this.tpModel.on("change", function(model){
            this.context.trigger(
                'forecasts:timeperiod:changed',
                model,
                this.getField('selectedTimePeriod').tpTooltipMap[model.get('selectedTimePeriod')]);
        }, this);
        
        this.context.on("forecasts:timeperiod:canceled", function(){
            this.resetSelection(this.tpModel.previous("selectedTimePeriod"));
        }, this);
        
    },
    
    /**
     * Sets the timeperiod to the selected timeperiod, used primarily for resetting
     * the dropdown on nav cancel
     */
    resetSelection: function(timeperiod_id){
        this.tpModel.set({selectedTimePeriod:timeperiod_id}, {silent:true});
        _.find(this.fields, function(field){
            if(_.isEqual(field.name, "selectedTimePeriod")){
                field.render();
                return true;
            }
        });
    }
    
})
