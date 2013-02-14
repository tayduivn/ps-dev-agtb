///*********************************************************************************
// * The contents of this file are subject to the SugarCRM Master Subscription
// * Agreement (""License"") which can be viewed at
// * http://www.sugarcrm.com/crm/master-subscription-agreement
// * By installing or using this file, You have unconditionally agreed to the
// * terms and conditions of the License, and You may not use this file except in
// * compliance with the License.  Under the terms of the license, You shall not,
// * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
// * or otherwise transfer Your rights to the Software, and 2) use the Software
// * for timesharing or service bureau purposes such as hosting the Software for
// * commercial gain and/or for the benefit of a third party.  Use of the Software
// * may be subject to applicable fees and any use of the Software without first
// * paying applicable fees is strictly prohibited.  You do not have the right to
// * remove SugarCRM copyrights from the source code or user interface.
// *
// * All copies of the Covered Code must include on each user interface screen:
// *  (i) the ""Powered by SugarCRM"" logo and
// *  (ii) the SugarCRM copyright notice
// * in the same form as they appear in the distribution.  See full license for
// * requirements.
// *
// * Your Warranty, Limitations of liability and Indemnity are expressly stated
// * in the License.  Please refer to the License for the specific language
// * governing these rights and limitations under the License.  Portions created
// * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
// ********************************************************************************/
///**
// * View that displays a list of models pulled from the context's collection.
// * @class View.Views.ForecastsFilterView
// * @alias SUGAR.App.layout.FilterView
// * @extends View.View
// */
({
    /**
     * Used to hold the id of the filter field
     */
    rangeFilterId: '',

    /**
     * Initialize because we need to set the selectedUser variable
     * @param options
     */
    initialize:function (options) {
        app.view.View.prototype.initialize.call(this, options);

        this.selectedUser = {id:app.user.get('id'), isManager:app.user.get('isManager'), showOpps:false};

        this.rangeFilterId = _.uniqueId("search_filter");
    },

    /**
     * Watch for the selectedUser Change
     */
    bindDataChange:function () {

        var self = this;

        if (this.context && this.context) {
            this.context.on("change:selectedUser", function (context, user) {
                self.selectedUser = user;
                this.toggleRangesFieldVisibility();
            }, this);
        }
    },

    // prevent excessive renders when things change.
    bindDomChange: function() {},

    /**
     * Method to toggle the field visibility of the group by field
     */
    toggleRangesFieldVisibility:function () {
        if (this.selectedUser.isManager && this.selectedUser.showOpps === false) {
            this.node.parent().hide();
        } else {
            this.node.parent().show();
        }
    },

    /**
     * Override the render to have call the group by toggle
     *
     * @private
     */
    _render:function () {
        app.view.View.prototype._render.call(this);

        this.node = this.$("#" + this.rangeFilterId);
        
        // toggle the visibility of the group by field for the initial render
        this.toggleRangesFieldVisibility();

        // set up the filters
        this._setUpFilters();

        return this;
    },

    /**
     * Set up select2 for driving the filter UI
     * @param node the element to use as the basis for select2
     * @private
     */
    _setUpFilters: function() {
        var selectedRanges = this.context.has("selectedRanges") ?
            this.context.get("selectedRanges") :
            app.defaultSelections.ranges;

        this.node.select2({
            data:this._getRangeFilters(),
            initSelection: function(element, callback) {
                callback(_.filter(
                    this.data,
                    function(obj) {
                        return _.contains(this, obj.id);
                    },
                    $(element.val().split(","))
                ));
            },
            multiple:true,
            placeholder: app.lang.get("LBL_MODULE_FILTER"),
            dropdownCss: {width:'auto'},
            dropdownCssClass: 'search-filter-dropdown'
        });

        // set the default selections
        this.node.select2("val", selectedRanges);

        // add a change handler that updates the forecasts context appropriately with the user's selection
        this.node.change(
            {
                view: this
            },
            function(event) {
                event.data.view.context.set("selectedRanges", event.val);
            }
        );
    },

    /**
     * Gets the list of filters that correspond to the forecasts range settings that were selected by the admin during
     * configuration of the forecasts module.
     * @return {Array} array of the selected ranges
     */
    _getRangeFilters: function() {
        var options = app.utils.getConfigValue('buckets_dom') || 'commit_stage_binary_dom';

        return _.map(app.lang.getAppListStrings(options), function(value, key)  {
            return {id: key, text: value};
        });
    }

})
