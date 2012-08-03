(function(app) {

    app.view.ClickToEditField = function (field, view) {
        this.field = field;
        this.view = view;
        this.numberTypes = ['int', 'float', 'currency'];
        return this.render();
    };
    
    /**
     * Checks to see if the entered value of the field matches its datatype
     */
    app.view.ClickToEditField.prototype._checkDatatype = function(field, value){
    	var matchVal = null;
    	
    	switch(field.type){
    	case "int":
    	case "currency":
    	case "numeric":
    	case "float":
    		matchVal = value.match(/[^\.\d+-]/);
    	default:
    	}
    	if(matchVal !== null){
    		return false;
    	}
    	return true;
    };

    app.view.ClickToEditField.prototype.render = function() {
        this._addCTEIcon(this.field);

        this.field.$el.editable(function(value, settings){
                // set back to original value if user manages to undefine or enters in a blank value.
                if(value == undefined || value == "") {
                    value = settings.field.holder;
                }
                return value;
            },
            {
                select: true,
                field: this.field,
                view: this.view,
                numberTypes: this.numberTypes,
                checkDatatype: this._checkDatatype,
                onedit:function(settings, original){
                    // hold value for use later in case user enters a +/- percentage, or user enters an empty value
                    settings.field.holder = $(original).html();
                },
                callback: function(value, settings) {
                    //check to see if the datatype matches the input, if not return and show an error.
                	$(this).css("background-color", "");
                	$(this).css("color", $.data(this, "color"));
                	$(this).parent().find(".tempMsg").each(function(index, node){
                		$(node).remove();
                	});
                    if(!settings.checkDatatype(settings.field, value)){
                    	var invalid = $("<span>" + app.lang.get("LBL_CLICKTOEDIT_INVALID", "Forecasts") + "</span>");
                    	
                    	$.data(this, "color", $(this).css("color"));
                    	$(this).css("background-color", "red");
                    	$(this).css("color", "white");
                    	invalid.css("color", "red");
                    	invalid.css("display", "block");
                    	invalid.addClass("tempMsg");
                    	
                    	$(this).parent().append(invalid);                  	
                    	return value;
                    }
                    try{
                        var orig = settings.field.holder;
                        // if it's an int, and the user entered a +/- percentage, calculate it
                        if(_.include(settings.numberTypes, settings.field.type)) {
                            if(value.match(/^[+-][0-1]?[0-9]?[0-9]%$/)) {
                                value = eval(orig + value[0] + "(" + value.substring(1,value.length-1) / 100 + "*" + orig +")");
                            } else if (!value.match(/^[0-9]*$/)) {
                                value = orig;
                            }
                        }
                                                                                          
                        var values = {};
                        var id = settings.field.model.get("id");
                        values[settings.field.name] = value;
                        values["timeperiod_id"] = settings.field.context.forecasts.get("selectedTimePeriod").id;
            			values["current_user"] = app.user.get('id');
                        
                        settings.field.model.url = settings.view.url + "/" + ((id != "") ? id : "-1");
                        settings.field.model.save(values, {wait:true});
                        	
                        
                    } catch (e) {
                        app.logger.error('Unable to save model in forecastsWorksheet.js: _renderClickToEditField - ' + e);
                    }
                    return value;
                }
            }
        );
        return this.field;
    };

    app.view.ClickToEditField.prototype._addCTEIcon = function(){
        // add icon markup
        this.field.cteIcon = $('<div style="position:absolute; margin-left:-10px"><span class="span2" style=" border-right: medium none; position: absolute; left: -5px; width: 15px"><i class="icon-pencil icon-sm"></i></span></div>');

        // add events
        this.field.showCteIcon = function(){
            this.$el.parent().css('overflow-x', 'visible');
            this.$el.before(this.cteIcon);
        };

        this.field.hideCteIcon = function(){
            this.$el.parent().find(this.cteIcon).detach();
            this.$el.parent().css('overflow-x', 'hidden');
        };

        var events = this.field.events || {};
        this.field.events = _.extend(events, {
            'mouseenter': 'showCteIcon',
            'mouseleave': 'hideCteIcon'
        });
        this.field.delegateEvents();
    };
    
    

})(SUGAR.App);