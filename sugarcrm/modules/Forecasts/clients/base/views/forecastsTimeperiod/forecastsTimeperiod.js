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
 * View that displays committed forecasts for current user.  If the manager view is selected, the Forecasts
 * of Rollup type are shown; otherwise the Forecasts of Direct type are shown.
 *
 * @class View.Views.ForecastsTimeperiod
 * @alias SUGAR.App.layout.ForecastsTimeperiod
 * @extends View.View
 */
({
    /**
     * the timeperiod field metadata that gets used at render time
     */
    timeperiod: {},

    initialize : function(options) {
        app.view.View.prototype.initialize.call(this, options);

        this.timePeriodId = app.defaultSelections.timeperiod_id.id;

        _.each(this.meta.panels, function(panel) {
            this.timeperiod = _.find(panel.fields, function (item){
                return _.isEqual(item.name, 'timeperiod');
            });
        }, this);
    },


    /**
     * Overriding _renderField because we need to set up the events to set the proper value depending on which field is
     * being changed.
     * @param field
     * @protected
     */
    _renderField: function(field) {
        if (field.name == "timeperiod") {
            field = this._setUpTimeperiodField(field);
        }
        app.view.View.prototype._renderField.call(this, field);
    },

    /**
     * Sets up the save event and handler for the dropdown fields in the timeperiod view.
     * @param field the commit_stage field
     * @return {*}
     * @private
     */
    _setUpTimeperiodField: function (field) {

        field.events = _.extend({"change select": "_updateSelections"}, field.events);
        field.bindDomChange = function() {};

        /**
         * updates the selection when a change event is triggered from a dropdown
         * @param event the event that was triggered
         * @param input the (de)selection
         * @private
         */
        field._updateSelections = function(event, input) {
            var label = this.$el.find('option:[value='+input.selected+']').text();
            //Set the default selection so that when render is called on the view it will use the newly selected value
            app.defaultSelections.timeperiod_id.id = input.selected;
            this.view.context.forecasts.set('selectedTimePeriod', {"id": input.selected, "label": label});
            // make it close the container to act like a normal dropdown
            this.$el.find('div.chzn-container-active').removeClass('chzn-container-active');
        };

        // INVESTIGATE: Should this be retrieved from the model, instead of directly?
        app.api.call("read", app.api.buildURL("Forecasts", "timeperiod"), '', {success: function(results) {
            this.field.def.options = results;
            if(!this.field.disposed) {
                this.field.render();
            }
        }}, {field: field, view: this});

        field.def.value = app.defaultSelections.timeperiod_id.id;
        return field;
    }

})
