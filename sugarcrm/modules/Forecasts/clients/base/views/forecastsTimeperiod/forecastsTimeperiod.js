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

        _.each(this.meta.panels, function(panel) {
            this.timeperiod = _.find(panel.fields, function (item){
                return _.isEqual(item.name, 'selectedTimePeriod');
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
        if (field.name == "selectedTimePeriod") {
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

        /**
         * The model for the field should be context, since that is where the value gets set for all the
         * views using it.
         */
        field.model = this.context;

        /**
         * Overtake the way the field sets the value, since we need it set on context as an
         * object containing the id and the label of the selected timeperiod
         */
        field.unformat = _.bind(
            function(value) {
                return {id: value, label: this.$el.find('option:[value=' + value + ']').text()};
            },
            field
        );

        /**
         * Populates the dropdown from the endpoint with the timeperiods that were created by the admin when they set up
         * forecasts module
         */
        app.api.call("read", app.api.buildURL("Forecasts", "timeperiod"), '', {
            success: function(results) {
                this.field.def.options = results;
                if(!this.field.disposed) {
                    this.field.render();
                }
            }
        }, {field: field, view: this});

        /**
         * Set the initial selection value from app defaults, eventually, this should probably come from a user pref.
         */
        field.def.value = app.defaultSelections.timeperiod_id.id;
        return field;
    }

})
