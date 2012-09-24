(function(app) {

    app.view.BucketGridEnum = function (field, view) {
        this.field = field;
        this.field.def.options = app.config.buckets_dom || [];
        this.view = view;
        return this.render();
    };

    app.view.BucketGridEnum.prototype.render = function() {
    	
    	var self = this;
        this.field.enableOverFlow = function(){
            this.$el.parent().css('overflow', 'visible');
        };

        this.field.disableOverFlow = function(){
            this.$el.parent().css('overflow', 'hidden');
        };
        
        this.field.changed = function(){
        	var el = this.$el.find(this.fieldTag);
        	console.log(el.val());
        	console.log(self.field.name);
        	var values = {};
        	values[self.field.name] = el.val();
            values["timeperiod_id"] = self.field.context.forecasts.get("selectedTimePeriod").id;
			values["current_user"] = app.user.get('id');
			values["isDirty"] = true;

            //If there is an id, add it to the URL
            if(self.field.model.isNew())
            {
            	self.field.model.url = app.api.buildURL('ForecastWorksheets', 'create');
            } else {
            	self.field.model.url = app.api.buildURL('ForecastWorksheets', 'update', {"id":self.field.model.get('id')});
            }
            
            self.field.model.set(values);
           
        };

        var events = this.field.events || {};
        this.field.events = _.extend(events, {
            'mouseenter': 'enableOverFlow',
            'mouseleave': 'disableOverFlow',
            'change'  : 'changed'
        });
        
        this.field.delegateEvents();

        return this.field;
    };

})(SUGAR.App);