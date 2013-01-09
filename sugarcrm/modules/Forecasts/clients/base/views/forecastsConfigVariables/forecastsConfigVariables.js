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
({
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        if(!_.isUndefined(options.meta.registerLabelAsBreadCrumb) && options.meta.registerLabelAsBreadCrumb == true) {
            this.layout.registerBreadCrumbLabel(options.meta.panels[0].label);
        }
    },
    /**
     * Overriding _renderField because we need to set up the multiselect fields to work properly
     * @param field
     * @private
     */
    _renderField: function(field) {
        if (field.def.multi) {
            field = this._setUpMultiselectField(field);
        }
        app.view.View.prototype._renderField.call(this, field);

        // fix the width of the field's container
        field.$el.find('.chzn-container').css("width", "100%");
        field.$el.find('.chzn-drop').css("width", "100%");
    },

    /**
     * Sets up the save event and handler for the variables dropdown fields in the config settings.
     * @param field the dropdown multi-select field
     * @return {*}
     * @private
     */
    _setUpMultiselectField: function (field) {
        // INVESTIGATE:  This is to get around what may be a bug in sidecar. The field.value gets overriden somewhere and it shouldn't.
        field.def.value = this.model.get(field.name);

        field.events = _.extend({"change select": "_updateSelections"}, field.events);

        field.bindDomChange = function() {};

        /**
         * updates the selection when a change event is triggered from a dropdown/multiselect
         * @param event the event that was triggered
         * @param input the (de)selection
         * @private
         */
        field._updateSelections = function(event, input) {
            var fieldValue = this.model.get(this.name);
            var id;

            if (_.has(input, "selected")) {
                id = input.selected;
                if (!_.contains(fieldValue, id)) {
                    fieldValue = _.union(fieldValue, id);
                }
            } else if(_.has(input, "deselected")) {
                id = input.deselected;
                if (_.contains(fieldValue, id)) {
                    fieldValue = _.without(fieldValue, id);
                }
            }
            this.def.value = fieldValue;
            this.model.set(this.name, fieldValue);
        };

        return field;
    }
})