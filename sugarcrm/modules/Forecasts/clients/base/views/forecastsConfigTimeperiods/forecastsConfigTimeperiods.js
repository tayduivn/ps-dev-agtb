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
/**
 * Events Triggered
 *
 * liszt:updated
 *      on: timperiod_start_day
 *      by: _setUpTimeperiodStartMonthBind()
 */
({

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        if(!_.isUndefined(options.meta.registerLabelAsBreadCrumb) && options.meta.registerLabelAsBreadCrumb == true) {
            this.layout.registerBreadCrumbLabel(options.meta.panels[0].label);
        }
    },

    /**
     * Overriding _renderField because we need to set up a binding to the start month drop down to populate the day drop down on change
     * @param field
     * @private
     */
    _renderField: function(field) {

        field = this._setUpTimeperiodConfigField(field);

        // TODO-sfa this will get removed when the timeperiod mapping functionality is added (SFA-214)
        /**
         * This is needed to make sure that this view is read only when forecasts module has been set up.
         */
        if(this.model.get('is_setup')) {
            // if forecasts has been setup, this is read only!
            field.options.def.view = 'detail';
        }
        app.view.View.prototype._renderField.call(this, field);

    },

    /**
     * Sets up the fields with the handlers needed to properly get and set their values for the timeperiods config view.
     * @param field the field to be setup for this config view.
     * @return {*} field that has been properly setup and augmented to function for this config view.
     * @private
     */
    _setUpTimeperiodConfigField: function(field) {
        switch(field.name) {
            case "timeperiod_shown_forward":
            case "timeperiod_shown_backward":
                return this._setUpTimeperiodShowField(field);
//BEGIN SUGARCRM flav=pro ONLY
            case "timeperiod_interval":
                return this._setUpTimeperiodIntervalBind(field);
//END SUGARCRM flav=pro ONLY
            default:
                return field;
        }
    },

    /**
     * Sets up the timeperiod_shown_forward and timeperiod_shown_backward dropdowns to set the model and values properly
     * @param field The field being set up.
     * @return {*} The configured field.
     * @private
     */
    _setUpTimeperiodShowField: function (field) {
        // ensure Date object gets an additional function
        field.events = _.extend({"change select":  "_updateSelection"}, field.events);
        field.bindDomChange = function() {};

        field._updateSelection = function(event) {
            var value =  $(event.target).val();
            this.def.value = value;
            this.model.set(this.name, value);
        };

        field.def.value = this.model.get(field.name) || 1;
        return field;
    }

    //BEGIN SUGARCRM flav=pro ONLY
    ,
    /**
     * Sets up the change event on the timeperiod_interval drop down to maintain the interval selection
     * and push in the default selction for the leaf period
     * @param field the dropdown interval field
     * @return {*}
     * @private
     */
    _setUpTimeperiodIntervalBind: function(field) {

        field.def.value = this.model.get(field.name);

        // ensure selected day functions like it should
        field.events = _.extend({"change select":  "_updateIntervals"}, field.events);
        field.bindDomChange = function() {};

        if(typeof(field.def.options) == 'string') {
            field.def.options = app.lang.getAppListStrings(field.def.options);
        }

        /**
         * function that updates the selected interval
         * @param event
         * @private
         */
        field._updateIntervals = function(event) {
            //get the timeperiod interval selector
            var selected_interval = $(event.target).val();
            this.def.value = selected_interval;
            this.model.set(this.name, selected_interval);
            this.model.set('timeperiod_leaf_interval', selected_interval == 'Annual' ? 'Quarter' : 'Month');
        }
        return field;

    }
    //END SUGARCRM flav=pro ONLY
})