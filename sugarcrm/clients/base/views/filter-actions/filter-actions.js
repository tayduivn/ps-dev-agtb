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
    /**
     * Actions for BaseFilterRowsViews
     * Part of BaseFilterpanelLayout
     *
     * @class BaseFilterActionsView
     * @extends View
     */
    events: {
        "change input": "filterNameChanged",
        "keyup input": "filterNameChanged",
        "click a.filter-close": "triggerClose",
        "click a.save_button:not(.disabled)": "triggerSave",
        "click a.delete_button:not(.hide)": "triggerDelete"
    },

    tagName: "article",
    className: "filter-header",
    /**
     * row disabled state
     */
    rowState: false,

    /**
     * @override
     * @param {Object} opts
     */
    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);

        this.layout.on("filter:create:open", function(model) {
            var self = this,
                name = model ? model.get("name") : '';
            this.setFilterName(name);
            if (!name) {
                // We are creating a new filter.
                _.defer(function() {
                    self.$("input").focus();
                });
            }
        }, this);

        this.listenTo(this.layout, "filter:create:rowsValid", this.toggleRowState);
        this.listenTo(this.layout, "filter:set:name", this.setFilterName);
    },

    /**
     * Get input val
     * @returns {String}
     */
    getFilterName: function() {
        return this.$("input").val();
    },

    /**
     * Set input val and hides the delete button if we're clearing the name
     * @param name
     */
    setFilterName: function(name) {
        this.$("input").val(name);
        // We have this.layout.editingFilter if we're setting the name.
        this.toggleDelete(!name);
    },

    /**
     * Fired when the filter name changed
     * @param {Event} event
     */
    filterNameChanged: _.debounce(function(event) {
        if (this.disposed) {
            return;
        }
        this.layout.trigger('filter:create:validate');
        if (this.rowState && this.layout.getComponent('filter-rows')) {
            this.layout.getComponent('filter-rows').saveFilterEditState();
        }
    }, 400),

    /**
     * Toggle delete button
     * @param {Boolean} t true to hide the button
     */
    toggleDelete: function(t) {
        this.$(".delete_button").toggleClass("hide", t);
    },

    /**
     * Toggle save button
     */
    toggleDisabled: function() {
        this.$(".save_button").toggleClass('disabled', !(this.getFilterName() && this.rowState));
    },

    /**
     * Toggle row state
     * @param {*} t
     */
    toggleRowState: function(t) {
        this.rowState = _.isUndefined(t) ? !this.rowState : !!t;
        this.toggleDisabled();
    },

    /**
     * Trigger "filter:create:close" to close the filter create panel
     */
    triggerClose: function() {
        var id = this.layout.editingFilter.get('id');
        this.layout.getComponent('filter').trigger("filter:create:close", true, id);
    },

    /**
     * Trigger "filter:create:save" to save the created filter
     */
    triggerSave: function() {
        var filterName = this.getFilterName();
        this.layout.trigger("filter:create:save", filterName);
    },

    /**
     * Trigger "filter:create:delete" to delete the created filter
     */
    triggerDelete: function() {
        this.layout.trigger("filter:create:delete");
    }
})
