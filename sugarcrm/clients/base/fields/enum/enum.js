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
    fieldTag: "input",
    bindKeyDown: function(callback) {
        this.$('input').on("keydown.record", {field: this}, callback);
    },
    _render: function() {
        var self = this;
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
        var select2Options = this.getSelect2Options(optionsKeys);
        var $el = this.$(this.fieldTag);
        if (!_.isEmpty(optionsKeys)) {
            if (this.tplName === 'edit' || this.tplName === 'list-edit') {
                $el.select2(select2Options);
                $el.select2("container").addClass("tleft");
                $el.on('change', function(ev){
                    var value = ev.val;
                    self.model.set(self.name, self.unformat(value));
                });
                if (this.def.ordered) {
                    $el.select2("container").find("ul.select2-choices").sortable({
                        containment: 'parent',
                        start: function() {
                            $el.select2("onSortStart");
                        },
                        update: function() {
                            $el.select2("onSortEnd");
                        }
                    });
                }
            } else if(this.tplName === 'disabled') {
                $el.select2(select2Options);
                $el.select2('disable');
            }
            //Setup selected value in Select2 widget
            var val = this.model.get(this.name);
            if(val){
                $el.select2('val', val);
            }
        } else {
            // Set loading message in place of empty DIV while options are loaded via API
            this.$el.html(app.lang.get("LBL_LOADING"));
        }
        return this;
    },
    focus: function () {
        if(this.action !== 'disabled') {
            this.$(this.fieldTag).select2('open');
        }
    },
    /**
     * Load the options for this field and pass them to callback function.  May be asynchronous.
     * @param {Boolean} fetch (optional) Force use of Enum API to load options.
     * @param {Function} callback (optional) Called when enum options are available.
     */
    loadEnumOptions: function(fetch, callback) {
        var self = this,
            meta = app.metadata.getModule(this.module, 'fields'),
            fieldMeta = meta && meta[this.name] ? meta[this.name] : this.def,
            items = this.def.options || fieldMeta.options;
        fetch = fetch || false;
        if (fetch || _.isUndefined(items)) {
            var _key = 'request:' + this.module + ':' + this.name;
            //if previous request is existed, ignore the duplicate request
            if (this.context.get(_key)) {
                var request = this.context.get(_key);
                request.xhr.done(_.bind(function(o) {
                    if (this.enumOptions !== o) {
                        this.enumOptions = o;
                        callback.call(this);
                    }
                }, this));
            } else {
                var request = app.api.enumOptions(self.module, self.name, {
                    success: function(o) {
                        if(self.disposed) { return; }
                        if (self.enumOptions !== o) {
                            self.enumOptions = o;
                            fieldMeta.options = self.enumOptions;
                            self.context.unset(_key);
                            callback.call(self);
                        }
                    }
                    // Use Sugar7's default error handler
                });
                this.context.set(_key, request);
            }
        } else {
            if (_.isString(items)) {
                items = app.lang.getAppListStrings(items);
            }
            self.enumOptions = items;
        }
        return items;
    },
    /**
     * Helper function for generating Select2 options for this enum
     * @param {Object} optionsKeys Set of option keys that will be loaded into Select2 widget
     * @returns {{}} Select2 options, refer to Select2 documentation for what each option means
     */
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

        /* From http://ivaynberg.github.com/select2/#documentation:
         * Initial value that is selected if no other selection is made
         */
        if(!this.def.isMultiSelect) {
            select2Options.placeholder = app.lang.get("LBL_SEARCH_SELECT");
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

        /* To get the Select2 multi-select pills to have our styling, we need to be able
         * to either pass a classname to the constructor to allow for custom styling
         * or set the 'select2-choices-pills-close' if the isMultiSelect option is set in def
         */
        select2Options.containerCssClass = this.def.container_class ? this.def.container_class : (this.def.isMultiSelect ? 'select2-choices-pills-close' : '');

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

        /* If is multi-select, set multiple option on Select2 widget.
         */
        if (this.def.isMultiSelect) {
            select2Options.multiple = true;
        }

        select2Options.initSelection = _.bind(this._initSelection, this);
        select2Options.query = _.bind(this._query, this);

        return select2Options;
    },

    /**
     * Set the option selection during select2 initialization.
     * Also used during drag/drop in multiselects.
     * @param {Element} $ele Select2 element
     * @param {Function} callback Select2 data callback
     * @private
     */
    _initSelection: function($ele, callback){
        var data = [];
        var options = _.isString(this.items) ? app.lang.getAppListStrings(this.items) : this.items;
        var values = $ele.val().split(",");
        _.each(_.pick(options, values), function(value, key){
            data.push({id: key, text: value});
        }, this);
        if(data.length === 1){
            callback(data[0]);
        } else {
            callback(data);
        }
    },

    /**
     * Adapted from eachOptions helper in hbt-helpers.js
     * Select2 callback used for loading the Select2 widget option list
     * @param {Object} query Select2 query object
     * @private
     */
    _query: function(query){
        var options = _.isString(this.items) ? app.lang.getAppListStrings(this.items) : this.items;
        var data = {
            results: [],
            // only show one "page" of results
            more: false
        };
        if (_.isObject(options)) {
            _.each(options, function(element, index) {
                var text = "" + element;
                //additionally filter results based on query term
                if(query.matcher(query.term, text)){
                    data.results.push({id: index, text: text});
                }
            });
        } else {
            options = null;
        }
        query.callback(data);
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
    },

    /**
     * Override to remove default DOM change listener, we use Select2 events instead
     * @override
     */
    bindDomChange: function() {
    },

    unbindDom: function() {
        this.$(this.fieldTag).select2('destroy');
        app.view.Field.prototype.unbindDom.call(this);
    },

    unbindData: function() {
        var _key = 'request:' + this.module + ':' + this.name;
        this.context.unset(_key);
        app.view.Field.prototype.unbindData.call(this);
    }
})
