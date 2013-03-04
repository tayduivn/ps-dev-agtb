//FILE SUGARCRM flav=pro ONLY
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    /**
     * Attach a Change event to the field
     */
    events: { 'change': 'bucketsChanged' },

    /**
     * flag for if the field should render disabled
     */
    disabled: false,

    /**
     * Language string value of the data in the view.
     */
    langValue: "",

    /**
     * Current view type this field is rendered.
     *
     * This is needed because this.def.view is shared across all instances of the view.
     */
    currentView: "",

    /**
     * The select DOM element for this field
     */
    select: "",

    /**
     * Current value of the field.
     *
     * This is used to check the "current" value with the changed value on the close event.  If
     * these match, that means the user clicked the same value in the select box.  If that is the case,
     * the change event never fires (which removes the select2 dropdown).  This is used in the close
     * event handler to reset the field if the current selection is selected.
     */
    currentVal: "",

    /**
     * Initialize
     */
    initialize: function(options) {
        app.view.Field.prototype.initialize.call(this, options);
        var forecastRanges = app.metadata.getModule('Forecasts', 'config').forecast_ranges;

        //Check to see if the field is editable
        this.isEditable();

        //show_binary, show_buckets, show_n_buckets logic
        if(forecastRanges == "show_binary") {
            //If we're in binary mode
            this.def.view = "bool";
            this.currentView = "bool";
            this.format = function(value) {
                return value == "include";
            };
            this.unformat = _.bind(function(value) {
                return this.$el.find(".checkbox").prop('checked') ? "include" : "exclude";
            }, this);
        }
        else if(forecastRanges == "show_buckets") {
            this.def.view = "default";
            this.currentView = "default";
            this.getLanguageValue();
            this.createCTEIconHTML();
            //create buckets, but only if we are on our sheet.
            if(!this.disabled) {
                this.createBuckets();
            }
        }
    },

    /**
     * Render Field
     */
    _render: function() {
        var select = null;
        app.view.Field.prototype._render.call(this);

        /* If we are on our own sheet, and need to show the dropdown, init things
         * and disable events
         */
        if(!this.disabled && this.currentView == "enum") {
            this.$el.off("click");

            //custom namespaced window click event to destroy the chosen dropdown on "blur".
            //this is removed in this.resetBuckets
            $(window).on("click." + self.cid, function(e) {
                if(!_.isEqual(self.cid, $(e.target).attr("cid"))) {
                    this.resetBucket();
                }
            }, this);

            this.$el.off("mouseenter");
            this.$el.off("mouseleave");
            this.select = this.$el.find("select");
            this.select.select2({minimumResultsForSearch: !_.isUndefined(this.def.searchBarThreshold) ? this.def.searchBarThreshold : 0});
            this.currentVal = this.value;
            this.select.select2("val", this.value);
            this.select.select2("open");
            this.select.on("close", function() {
                if(_.isEqual(this.currentVal, this.select.select2("val"))) {
                    this.resetBucket();
                }
            });
            this.$(".select2-input").keydown(function(e) {
                this.onKeyDown(e);
            });
        }
    },

    /**
     * Change handler for the buckets field
     */
    bucketsChanged: function() {
        var values = {},
            moduleName = this.moduleName;

        if(this.currentView == "bool") {
            this.value = this.unformat();
            values[this.def.name] = this.value;
        }
        else if(this.currentView == "enum") {
            this.value = this.select.select2("val");
            values[this.def.name] = this.value;
        }

        this.model.set(values);

        if(this.currentView == "enum") {
            this.resetBucket();
        }
    },

    /**
     * Handle event when key is pressed down (tab)
     *
     * @param evt
     */
    onKeyDown: function(evt) {
        if(evt.which == 9) {
            evt.preventDefault();
            // tab key pressed, trigger event from context
            this.context.trigger('forecasts:tabKeyPressed', evt.shiftKey, this);
        }
    },

    /**
     * Creates the HTML for the bucket selectors
     *
     * This function is used to create the select tag for the buckets.  For performance reasons, we only want
     * to iterate over the option list once, so we do that here and store it as a jQuery data element on the Body tag.
     * Also, we check to make sure this hasn't already been done (so we don't do it again, of course).
     */
    createBuckets: function() {
        this.buckets = $.data(document.body, "commitStageBuckets");

        if(_.isUndefined(this.buckets)) {
            var options = app.lang.getAppListStrings(this.def.options) || 'commit_stage_dom';
            this.buckets = "<select data-placeholder=' ' name='" + this.name + "' style='width: 100px;'>";
            this.buckets += "<option value='' selected></option>";
            _.each(options, function(item, key) {
                this.buckets += "<option value='" + key + "'>" + item + "</options>"
            });
            this.buckets += "</select>";
            $.data(document.body, "commitStageBuckets", this.buckets);
        }
    },

    /**
     * Sets up CTE Icon HTML
     *
     * If the HTML hasn't been set up yet, create it and store it on the DOM.  If it has, simply use it
     */
    createCTEIconHTML: function() {
        var cteIcon = $.data(document.body, "cteIcon"),
            events = this.events || {},
            sales_stage = this.model.get("sales_stage");

        if(_.isUndefined(cteIcon)) {
            cteIcon = '<span class="edit-icon"><i class="icon-pencil icon-sm"></i></span>';
            $.data(document.body, "cteIcon", cteIcon);
        }

        //Events
        // if it's not a bucket, and it's not editable, we don't want to try to add the pencil
        this.showCteIcon = _.bind(function() {
            if((this.currentView != "enum") && (!this.disabled)) {
                this.$el.find("span.editable").before($(cteIcon));
            }
        }, this);

        // if it's not a bucket, and it's not editable, we don't want to try to remove the pencil  
        this.hideCteIcon = _.bind(function() {
            if((this.currentView != "enum") && (!this.disabled)) {
                this.$el.parent().find(".edit-icon").detach();
            }
        }, this);

        this.events = _.extend(events, {
            'mouseenter': 'showCteIcon',
            'mouseleave': 'hideCteIcon',
            'click': 'clickToEdit'
        });
    },

    /**
     * Gets proper value of the item out of the language file.
     *
     * If we are in buckets mode and are on a non-editable sheet, we need to display the proper value of this
     * field as determined by the language file.  This function sets the proper key in the field for the hbt to pick it up and
     * display it.
     */
    getLanguageValue: function() {
        var options = app.lang.getAppListStrings(this.def.options) || 'commit_stage_dom';
        this.langValue = options[this.model.get(this.def.name)];
    },

    /**
     * Click to edit handler
     *
     * Handles the click to make the field editable.
     */
    clickToEdit: function(e) {
        var sales_stage = this.model.get("sales_stage");
        if(!this.disabled) {
            $(e.target).attr("cid", this.cid);
            this.def.view = "enum";
            this.currentView = "enum";
            e.preventDefault();
            this.render();
        }
    },

    /**
     * Removes chosen dropdown from unfocused field
     */
    resetBucket: function() {
        //remove custom click handler
        $(window).off("click." + this.cid);
        this.$el.off("click");
        this.def.view = "default";
        this.currentView = "default";
        this.getLanguageValue();
        this.delegateEvents();
        this.render();
    },

    /**
     * Utility Method to check if the field is editable
     */
    isEditable: function() {
        var sales_stages,
            isOwner = true;

        //Check to see if the sales stage is one of the configured lost or won stages.
        sales_stages = app.metadata.getModule('Forecasts', 'config').sales_stage_won.concat(app.metadata.getModule('Forecasts', 'config').sales_stage_lost);
        var hasStage = _.contains(sales_stages, this.model.get('sales_stage'));

        //Check to see if you're a manager on someone else's sheet, disable changes
        if(this.context.get("selectedUser")["id"] != app.user.id) {
            isOwner = false;
        }

        this.disabled = hasStage || !isOwner;
    }
})
