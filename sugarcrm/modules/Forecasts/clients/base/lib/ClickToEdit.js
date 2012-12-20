/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
(function(app) {
    /**
     *
     * @param {Object} field
     * @param {Object} view
     * @return {String}
     * @constructor
     */
    app.view.ClickToEditField = function (field, view) {
        // set attr so tabbing can locate next field
        field.$el.attr('jeditable','true');
        field.$el.wrapInner('<span class="click format"></span>');
        field.$el.wrapInner('<div class="sugareditable"></div>');
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
        var ds = app.utils.regexEscape(app.user.getPreference('decimal_separator')) || '.';
        var gs = app.utils.regexEscape(app.user.getPreference('number_grouping_separator')) || ',';
        // matches a valid positive decimal number
        var reg1 = new RegExp("^\\+?(\\d+|\\d{1,3}("+gs+"\\d{3})*)?("+ds+"\\d+)?\\%?$");
        // matches a valid decimal percentage
        var reg2 = new RegExp("^[\\+\\-]?\\d+?("+ds+"\\d+)?\\%$");
        var reg3 = new RegExp("^\\+?\\d+$");
    	switch(field.type){
            case "int":
                return !_.isNull(value.match(reg3)) && value >= 0 && value <= 100;
            case "numeric":
            case "float":
            case "currency":
                return !_.isNull(value.match(reg1)) || !_.isNull(value.match(reg2));
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
                settings.field.isEditing = false;
                if(_.isEmpty(value)) {
                    value = settings.field.holder;
                    settings.field.isCancel = true;
                } else {
                    settings.field.isCancel = false;
                }
                /*
                if(settings.field.type == 'currency') {
                    $(this).parent().find(".cte-symbol").each(function(index, node){
                        $(node).remove();
                    });
                }
                */
                return value;
            },
            {
                width: 'none', // input width (not css)
                height: 'none', // input height (not css)
                style: 'margin: 0', // form style
                select: true,
                field: this.field,
                view: this.view,
                numberTypes: this.numberTypes,
                checkDatatype: this._checkDatatype,
                onblur: 'tab',
                /**
                 * This is called on cancel, such as clicking outside of input field.
                 * Remove currency symbol from front of input field.
                 *
                 * @param {Object} settings
                 * @param {Object} original
                 */
                onreset: function(settings, original) {
                    // remove any error messages from previous edit
                    $(this).find(".jeditable-error").remove();
                    settings.field.isEditing = false;
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
                    // run a safety check here.  we can't always be assured that the model contains a value
                    var fieldValue = settings.field.model.get(settings.field.name);

                    if(settings.field.type == 'int') {
                        return (fieldValue == null) ? 0 : fieldValue;
                    } else if (settings.field.type == 'currency' || settings.field.type == 'float') {
                        if(fieldValue == null)
                        {
                          return 0;
                        }
                        // format for currency/float editing, remove markup
                        return app.utils.formatNumber(
                            fieldValue,
                            app.user.getPreference('decimal_precision'),
                            app.user.getPreference('decimal_precision'),
                            '',
                            app.user.getPreference('decimal_separator')
                        );
                    } else {
                        return fieldValue;
                    }
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
                    settings.field.isEditing = true;
                    // hold value for use later in case user enters a +/- percentage, or user enters an empty value
                    if(settings.field.isValid) {
                        settings.field.holder = $(original).html();
                    }
                },
                /**
                 * just before form gets submitted, check if value is valid
                 * if not, do not submit the form
                 *
                 * @param {Object} settings
                 * @param {Object} self
                 * @return {Bool}  false on invalid value
                 */
                onsubmit: function(settings, self) {
                    value = $(this).find('input').val();
                    settings.field.isValid = settings.checkDatatype(settings.field, value);

                    // remove any error messages from previous edit
                    $(this).find(".jeditable-error").remove();

                    // check to see if the datatype matches the input.
                    // if not, show an error and cancel submit.
                    if(!settings.field.isValid) {
                        $(this).find('.control-group').addClass('error');
                        var invalid = $('<span class="help-inline jeditable-error" style="white-space: nowrap;"><span class="btn btn-danger"><i class="icon-white icon-exclamation-sign"></i></span> ' + app.lang.get("LBL_CLICKTOEDIT_INVALID", "Forecasts") + '</span>');
                        $(this).find('input').parent().parent().append(invalid);
                        $(this).find('input').select();
                    }
                    return settings.field.isValid;
                },
                /**
                 * access the form after created
                 *
                 * @param {Object} settings
                 * @param {Object} form
                 */
                afterform: function(settings, form) {
                    // set css on input field
                    var input = form.find('input');
                    var strlen = input.val().length;
                    if(_.include(settings.numberTypes, settings.field.type)) {
                        // format input field for numeric values
                        input.wrapAll('<div class="control-group"></div>');
                        input.wrapAll('<div class="controls"></div>');
                        if(strlen < 4) {
                            input.attr('class','input-mini focused tright');
                        } else if(strlen < 10) {
                            input.attr('class','input-small focused tright');
                        } else {
                            input.attr('class','input-medium focused tright');
                        }
                        input.attr('maxlength','26');
                    }
                    // append currency symbol
                    if(settings.field.type == 'currency') {
                        // add symbol before input field
                        var symbol = app.currency.getCurrencySymbol(settings.field.model.get('currency_id'));
                        input.wrapAll('<div class="input-prepend" style="white-space: nowrap"></div>');
                        input.before('<span class="add-on">'+symbol+'</span>');
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

                    try {
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

                        var isDirty = !_.isEqual(parseFloat(settings.field.model.get(settings.field.name)), parseFloat(value));

                        // unformat the value from user prefs before sending to model
                        value = app.currency.unformatAmountLocale(value);

                        var values = {};
                        values[settings.field.name] = value;
                        values["timeperiod_id"] = settings.field.context.forecasts.get("selectedTimePeriod").id;
            			values["current_user"] = app.user.get('id');
            			values["isDirty"] = isDirty;

                        //If there is an id, add it to the URL
                        if(settings.field.model.isNew())
                        {
                            settings.field.model.url = settings.view.url;
                        } else {
                            settings.field.model.url = settings.view.url + "/" + settings.field.model.get('id');
                        }
                        settings.field.model.set(values);
                        // force the formatting of the amount in the view, in case model did not change
                        //$(this).html(app.currency.formatAmountLocale(value, settings.field.model.get('currency_id')));
                        settings.field._render();
                        settings.field.isEditing = false;
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
        this.field.cteIcon = $('<span class="edit-icon"><i class="icon-pencil icon-sm"></i></span>');

        // add events
        this.field.showCteIcon = function() {
            if(!this.isEditing) {
                this.$el.find('.click.format').before(this.cteIcon);
            }
        };

        this.field.hideCteIcon = function() {
            this.$el.parent().find(this.cteIcon).detach();
        };

        var events = this.field.events || {};
        this.field.events = _.extend(events, {
            'mouseenter': 'showCteIcon',
            'mouseleave': 'hideCteIcon'
        });
        this.field.delegateEvents();
    };
    
    

})(SUGAR.App);
