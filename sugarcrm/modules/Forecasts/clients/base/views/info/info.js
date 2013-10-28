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
        app.view.invokeParent(this, {type: 'view', name: 'view', method: 'initialize', args: [options]});
        this.resetSelection(this.context.get("selectedTimePeriod"));
    },
    
    /**
     * {@inheritdoc}
     *
     */
    bindDataChange: function(){
        this.tpModel.on("change", function(model){
            this.context.trigger("forecasts:timeperiod:changed", model);
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
