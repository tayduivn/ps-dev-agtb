(function(app) {

    app.view.ClickToEditField = function (field, view) {
        this.field = field;
        this.view = view;
        this.numberTypes = ['int', 'float', 'currency'];
        return this.render();
    };
    
    /**
     * Checks to see if the entered value of the field matches its data type
     */
    app.view.ClickToEditField.prototype._checkDatatype = function(field, value) {
        var ds = app.user.get('decimal_separator') || '.';
        var gs = app.user.get('number_grouping_separator') || ',';
        var reg = new RegExp("^[\\+\\-]?(\\d+|\\d{1,3}(\\"+gs+"\\d{3})*)?(\\"+ds+"\\d+)?\\%?$");
    	switch(field.type){
            case "int":
            case "numeric":
            case "float":
            case "currency":
                return value.match(reg);
            default:
                return true;
    	}
    };

    app.view.ClickToEditField.prototype.render = function() {
        this._addCTEIcon(this.field);

        this.field.$el.editable(function(value, settings){
                // set back to original value if user manages to undefine or enters in a blank value.
                if(value == undefined || value == "") {
                    value = settings.field.holder;
                }
                $(this).parent().find(".cte_currency_symbol").each(function(index, node){
                    $(node).remove();
                });

                return value;
            },
            {
                select: true,
                field: this.field,
                view: this.view,
                numberTypes: this.numberTypes,
                checkDatatype: this._checkDatatype,
                onblur: 'submit',
                onedit:function(settings, original){
                    // clear styling
                    $(this).css("background-color", "");
                    $(this).css("color", $.data(this, "color"));
                    $(this).parent().find(".tempMsg").each(function(index, node){
                        $(node).remove();
                    });

                    var symbol = app.currency.getCurrencySymbol(settings.field.model.get('currency_id'));
                    $(this).before('<span class="cte_currency_symbol" style="float: left; padding-right: 2px;">'+symbol+'</span>');

                    // hold value for use later in case user enters a +/- percentage, or user enters an empty value
                    settings.field.holder = $(original).html();
                    // format in numeric value
                    $(this).html(
                        app.utils.formatNumber(
                            settings.field.model.get(settings.field.name),
                            app.user.get('decimal_precision'),
                            app.user.get('decimal_precision'),
                            '',
                            app.user.get('decimal_separator'))
                    );
                },
                callback: function(value, settings) {
                    //check to see if the datatype matches the input, if not return and show an error.
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
                        // if the user entered a +/- percentage, re-calculate the value based on the percentage
                        if(_.include(settings.numberTypes, settings.field.type)) {
                            var parts = value.match(/^([+-])([\d\.]+?)\%$/);
                            if(parts)
                            {
                                // use original number to apply calculations
                                orig = app.currency.unformatAmountLocale(orig);
                                value = eval(orig + parts[1] + "(" + parts[2] / 100 + "*" + orig +")");
                            }
                        }

                        var values = {};
                        values[settings.field.name] = value;
                        values["timeperiod_id"] = settings.field.context.forecasts.get("selectedTimePeriod").id;
            			values["current_user"] = app.user.get('id');
            			values["isDirty"] = true;

                        //If there is an id, add it to the URL
                        if(settings.field.model.isNew())
                        {
                            settings.field.model.url = settings.view.url;
                        } else {
                            settings.field.model.url = settings.view.url + "/" + settings.field.model.get('id');
                        }
                        
                        settings.field.model.set(values);

                        //settings.field.context.forecasts.set({commitButtonEnabled: true});

                        // convert value and format for display
                        var currencyId = settings.field.model.get('currency_id');
                        if(settings.field.def.convertToBase) {
                            value = value * settings.field.model.get('base_rate');
                            currencyId = '-99';
                        }
                        value = app.currency.formatAmountLocale(
                            value,
                            currencyId);

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