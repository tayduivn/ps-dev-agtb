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
    fieldTag: "select",
    _render: function() {
        var val;
        var options = this.items = this.items || this.enumOptions;
        if(_.isUndefined(options)){
            options = this.items = this.loadEnumOptions(false, function(){
                //Re-render widget since we have fresh options list
                if(!this.disposed){
                    this.render();
                }
            });
        }
        var optionsKeys = _.isObject(options) ? _.keys(options) : [];
        //After rendering the dropdown, the selected value should be the value set in the model,
        //or the default value. The default value fallbacks to the first option if no other is selected.
        if (_.isUndefined(this.model.get(this.name))) {
            var defaultValue = _.first(optionsKeys);
            if (defaultValue) {
                this.model.set(this.name, defaultValue);
            }
        }
        app.view.Field.prototype._render.call(this);
        if(this.tplName === 'edit') {
            var select2Options = this.getSelect2Options(optionsKeys);
            this.$(this.fieldTag).select2(select2Options);
            this.$(".select2-container").addClass("tleft");
            val = this.$(this.fieldTag).select2('val');
            if (val) {
                this.model.set(this.name, val);
            }
        } else if(this.tplName === 'disabled') {
            this.$(this.fieldTag).attr("disabled", "disabled").select2();
        } else if(_.isEmpty(optionsKeys)){
            // Set loading message in place of empty DIV while options are loaded via API
            this.$el.html(app.lang.get("LBL_LOADING"));
        }
        return this;
    },
    /**
     * Load the options for this field and pass them to callback function.  May be asynchronous.
     * @param {Boolean} fetch (optional) Force use of Enum API to load options
     * @param {Function} callback (optional) Called when enum options are available
     */
    loadEnumOptions: function(fetch, callback){
        var self = this;
        var items = self.def.options;
        fetch = fetch || false;
        if(fetch || _.isUndefined(items)){
            app.api.enum(self.module, self.name, {
                success: function(o){
                    if(self.enumOptions !== o){
                        self.enumOptions = o;
                        callback.call(self);
                    }
                }
                // Use Sugar7's default error handler
            });
        } else {
            if(_.isString(items)) {
                items = app.lang.getAppListStrings(items);
            }
            self.enumOptions = items;
        }
        return items;
    },
    getSelect2Options: function(optionsKeys){
        var select2Options = {};
        var emptyIdx = _.indexOf(optionsKeys, "");
        if (emptyIdx !== -1) {
            select2Options.allowClear = true;
            // if the blank option isn't at the top of the list we have to add it manually
            if (emptyIdx > 1) {
                this.hasBlank = true;
            }
        }

        // Options are being loaded via app.api.enum
        if(_.isEmpty(optionsKeys)){
            select2Options.placeholder = app.lang.get("LBL_LOADING");
        }

        /* From http://ivaynberg.github.com/select2/#documentation:
         * "Calculate the width of the container div to the source element"
         */
        select2Options.width = this.def.enum_width ? this.def.enum_width : '100%';

        /* Because the select2 dropdown is appended to <body>, we need to be able
         * to pass a classname to the constructor to allow for custom styling
         */
        select2Options.dropdownCssClass = this.def.dropdown_class ? this.def.dropdown_class : '';

        /* Because the select2 dropdown is calculated at render to be as wide as container
         * to make it differ the dropdownCss.width must be set (i.e.,100%,auto)
         */
        if (this.def.dropdown_width) {
            select2Options.dropdownCss = { width: this.def.dropdown_width };
        }

        /* All select2 dropdowns should only show the search bar for fields with 7 or more values,
         * this adds the ability to specify that threshold in metadata.
         */
        select2Options.minimumResultsForSearch = this.def.searchBarThreshold ? this.def.searchBarThreshold : 7;
        return select2Options;
    },
    /**
     *  Convert select2 value into model appropriate value for sync
     *
     * @param value Value from select2 widget
     * @return {String|Array} Unformatted value as String or String Array
     */
    unformat: function(value){
        if(this.def.isMultiSelect && _.isNull(value)){
            return [];  // Returning value that is null equivalent to server.  Backbone.js won't sync attributes with null values.
        } else {
            return value;
        }
    },
    /**
     * Convert server value into one appropriate for display in widget
     *
     * @param value
     * @return {Array} Value for select2 widget as String Array
     */
    format: function(value){
        if(this.def.isMultiSelect && _.isString(value)){
            return this.convertMultiSelectDefaultString(value);
        } else {
            return value;
        }
    },
    /**
     * Converts multiselect default strings into array of option keys for template
     * @param {String} defaultString string of the format "^option1^,^option2^,^option3^"
     * @return {Array} of the format ["option1","option2","option3"]
     */
    convertMultiSelectDefaultString: function(defaultString) {
        var result = defaultString.split(",");
        _.each(result, function(value, key) {
            result[key] = value.replace(/\^/g,"");
        });
        return result;
    }

})
