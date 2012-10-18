(function(app) {
    /**
     *
     * @param {Object} field
     * @param {Object} view
     * @return {String}
     * @constructor
     */
    app.view.ClickToEditField = function (field, view) {
        this.field = field;
        this.view = view;
        this.numberTypes = ['int', 'float', 'currency'];
        return this.render();
    };
    
    /**
     * Checks to see if the entered value of the field matches its data type
     *
     * @param {Object} field
     * @param {String} value
     * @return {Boolean}
     * @private
     */
    app.view.ClickToEditField.prototype._checkDatatype = function(field, value) {
        var ds = app.utils.regexEscape(app.user.get('decimal_separator')) || '.';
        var gs = app.utils.regexEscape(app.user.get('number_grouping_separator')) || ',';
        var reg = new RegExp("^[\\+\\-]?(\\d+|\\d{1,3}("+gs+"\\d{3})*)?("+ds+"\\d+)?\\%?$");
    	switch(field.type){
            case "int":
            case "numeric":
            case "float":
            case "currency":
                return !_.isNull(value.match(reg));
                break;
            default:
                return true;
                break;
    	}
    };

    /**
     * render the field
     *
     * @return {Object}
     */
    app.view.ClickToEditField.prototype.render = function() {
        this._addCTEIcon(this.field);

        this.field.$el.editable(
            /**
             * this function returns the value that will be displayed after editing.
             * set back to original value if user manages to undefine or enters in a blank value.
             *
             * @param {String} value
             * @param {Object} settings
             * @return {String}
             */
            function(value, settings) {
                // check if input was valid for formatting in callback
                settings.field.isValid = settings.checkDatatype(settings.field, value);
                if(value == undefined || value == "") {
                    value = settings.field.holder;
                    settings.field.isCancel = true;
                } else {
                    settings.field.isCancel = false;
                }
                if(settings.field.type == 'currency') {
                    $(this).parent().find(".cte_currency_symbol").each(function(index, node){
                        $(node).remove();
                    });
                }
                return value;
            },
            {
                select: true,
                field: this.field,
                view: this.view,
                numberTypes: this.numberTypes,
                checkDatatype: this._checkDatatype,
                onblur: 'cancel',
                /**
                 * This is called on cancel, such as clicking outside of input field.
                 * Remove currency symbol from front of input field.
                 *
                 * @param {Object} settings
                 * @param {Object} original
                 */
                onreset: function(settings, original) {
                    if(settings.field.type == 'currency') {
                        $(this).parent().parent().find(".cte_currency_symbol").each(function(index, node){
                            $(node).remove();
                        });
                    }
                },
                /**
                 * data returns the string to be edited.
                 * we want to edit the raw decimal value.
                 *
                 * @param {String} value
                 * @param {Object} settings
                 * @return {String}
                 */
                data: function(value, settings) {
                  if(settings.field.type !== 'currency') {
                      return value;
                  }

                  // run a safety check here.  we can't always be assurred that the model contains a value
                  var fieldValue = settings.field.model.get(settings.field.name);
                  if(fieldValue == null)
                  {
                     return 0;
                  }

                  // format for currency editing, remove markup
                  return app.utils.formatNumber(
                      fieldValue,
                      app.user.get('decimal_precision'),
                      app.user.get('decimal_precision'),
                      '',
                      app.user.get('decimal_separator'));
                },
                /**
                 * sets up the field for editing
                 *
                 * @param {Object} settings
                 * @param {Object} original
                 */
                onedit: function(settings, original) {
                    if(_.isUndefined(settings.field.isValid)) {
                        settings.field.isValid = true;
                    }
                    // clear styling
                    settings.field.hideCteIcon();
                    $(this).css("background-color", "");
                    $(this).css("color", $.data(this, "color"));
                    $(this).parent().find(".tempMsg").each(function(index, node){
                        $(node).remove();
                    });
                    if(settings.field.type == 'currency') {
                        // add symbol before input field
                        var symbol = app.currency.getCurrencySymbol(settings.field.model.get('currency_id'));
                        $(this).before('<span class="cte_currency_symbol" style="float: left; padding-right: 2px;">'+symbol+'</span>');
                    }

                    // hold value for use later in case user enters a +/- percentage, or user enters an empty value
                    if(settings.field.isValid) {
                        settings.field.holder = $(original).html();
                    }
                },
                /**
                 * called when the field is submitted
                 *
                 * @param {String} value
                 * @param {Object} settings
                 * @return {String}
                 */
                callback: function(value, settings) {
                    // if canceled, do nothing
                    if(settings.field.isCancel) {
                        return value;
                    }
                    //check to see if the datatype matches the input, if not return and show an error.
                    if(!settings.field.isValid){
                    	var invalid = $("<span>" + app.lang.get("LBL_CLICKTOEDIT_INVALID", "Forecasts") + "</span>");
                    	
                    	$.data(this, "color", $(this).css("color"));
                    	$(this).css("background-color", "red");
                    	$(this).css("color", "white");
                    	invalid.css("color", "red");
                    	invalid.css("display", "block");
                    	invalid.addClass("tempMsg");
                    	
                    	$(this).parent().append(invalid);                  	
                    	return value;
                    } try {
                        var orig = settings.field.holder;
                        // if the user entered a +/- percentage, re-calculate the value based on the percentage
                        if(_.include(settings.numberTypes, settings.field.type)) {
                            var parts = value.match(/^([+-])([\d\.]+?)\%$/);
                            if(parts)
                            {
                                // use original number to apply calculations
                                orig = settings.field.model.get(settings.field.name);
                                value = eval(orig + parts[1] + "(" + parts[2] / 100 + "*" + orig +")");
                            }
                        }

                        // unformat the value from user prefs before sending to model
                        value = app.currency.unformatAmountLocale(value);

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

                        // re-render the field
                        $(this).html(this.render());

                    } catch (e) {
                        app.logger.error('Unable to save model in forecastsWorksheet.js: _renderClickToEditField - ' + e);
                    }
                    return value;
                }
            }
        );
        return this.field;
    };
    /**
     * add the CTE icon next to the field
     *
     * @private
     */
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