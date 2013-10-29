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
     * View for the filter dropdown.
     * Part of BaseFilterLayout
     *
     * @class BaseFilterFilterDropdown
     * @extends View
     */
    tagName: "span",

    events: {
        "click .choice-filter": "handleEditFilter"
    },

    /**
     * @override
     * @param {Object} opts
     */
    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);

        //Load partials
        this._select2formatSelectionTemplate = app.template.get("filter-filter-dropdown.selection-partial");
        this._select2formatResultTemplate = app.template.get("filter-filter-dropdown.result-partial");

        this.listenTo(this.layout, "filter:change:filter", this.handleChange);
        this.listenTo(this.layout, "filter:change:module", this.handleModuleChange);
        this.listenTo(this.layout, "filter:render:filter", this._renderHtml);
    },

    /**
     * Truthy when filter dropdown is enabled.  Updated whenever the filter module changes.
     */
    filterDropdownEnabled: true,

    /**
     * @override
     * @private
     */
    _renderHtml: function() {
        app.view.View.prototype._renderHtml.call(this);

        this.filterList = this.getFilterList();

        this._renderDropdown(this.filterList);
    },

    /**
     * Get the list of filters to fill the dropdown
     * @returns {Array}
     */
    getFilterList: function() {
        var filters = [];
        this.layout.filters.each(function(model) {
            var isAllRecords = model.id !== "all_records" ? false : true;
            filters.push({id: model.id, text: this.getTranslatedSelectionText(isAllRecords, model.get("name"))});
        }, this);

        if (this.layout.canCreateFilter()) {
            filters.push({id: "create", text: app.lang.get("LBL_FILTER_CREATE_NEW")});
        }
        return filters;
    },

    /**
     * Render select2 dropdown
     * @private
     */
    _renderDropdown: function(data) {
        var self = this;
        this.filterNode = this.$(".search-filter");

        this.filterNode.select2({
            data: data,
            multiple: false,
            minimumResultsForSearch: 7,
            formatSelection: _.bind(this.formatSelection, this),
            formatResult: _.bind(this.formatResult, this),
            dropdownCss: {width: 'auto'},
            dropdownCssClass: 'search-filter-dropdown',
            initSelection: _.bind(this.initSelection, this),
            escapeMarkup: function(m) {
                return m;
            },
            width: 'off'
        });

        if (!this.filterDropdownEnabled) {
            this.filterNode.select2("disable");
        }

        this.filterNode.off("change");
        this.filterNode.on("change",
            /**
             * Called when the user selects a filter in the dropdown
             * Triggers filter:change:filter on filter layout
             *
             * @param {Event} e
             */
            function(e) {
                self.layout.trigger("filter:change:filter", e.val);
            }
        );
    },

    /**
     * Handler for when the custom filter dropdown value changes.
     * @param  {String} id      The GUID of the filter to apply.
     */
    handleChange: function(id) {
        var filter;
        // Figure out if we have an edit state. This would mean user was editing the filter so we want him to retrieve
        // the filter form in the state he left it.
        var editState = this.layout.retrieveFilterEditState();
        if (id === 'create' || (editState && editState.id === id)) {
            filter = app.data.createBean('Filters');
            filter.set(editState);
        } else {
            filter = this.layout.filters.get(id) || this.layout.emptyFilter;
        }
        if (id === "create") {
            this.$('.choice-filter').css("cursor", "not-allowed");
            this.layout.trigger("filter:create:open", filter);
        } else {
            if (filter.get("editable") === false) {
                this.layout.trigger("filter:create:close");
                this.$('.choice-filter').css("cursor", "not-allowed");
            } else {
                this.$('.choice-filter').css("cursor", "pointer");
            }

            if (this.layout.createPanelIsOpen()) {
                this.layout.trigger("filter:create:open", filter);
            }
        }

        this.filterNode.select2("val", id);
    },

    /**
     * Get the dropdown labels for the filter
     * @param {Object} el
     * @param {Function} callback
     */
    initSelection: function(el, callback) {
        var data,
            model,
            val = el.val();

        if (val !== "create") {
            model = this.layout.filters.get(val);

            if (model) {
                if (val !== "all_records") {
                    data = {id: model.id, text: this.getTranslatedSelectionText(false, model.get("name"))};
                } else {
                    data = {id: "all_records", text: this.getTranslatedSelectionText(true, model.get("name"))};
                }
            } else {
                data = {id: "all_records", text: app.lang.get("LBL_FILTER_ALL_RECORDS")};
            }

        } else {
            data = {id: "create", text: app.lang.get("LBL_FILTER_CREATE_NEW")};
        }
        callback(data);
    },

    /**
     * Update the text for the selected filter and returns template
     * @param {Object} item
     * @returns {string}
     */
    formatSelection: function(item) {
        var ctx = {}, safeString;

        //Escape string to prevent XSS injection
        safeString = Handlebars.Utils.escapeExpression(item.text);
        // Update the text for the selected filter.
        this.$('.choice-filter').html(safeString);

        ctx.label = app.lang.get("LBL_FILTER");
        ctx.enabled = this.filterDropdownEnabled;

        return this._select2formatSelectionTemplate(ctx);
    },

    /**
     * Returns template
     * @param {Object} option
     * @returns {String}
     */
    formatResult: function(option) {
        // TODO: Determine whether active filters should be highlighted in bold in this menu.
        return this._select2formatResultTemplate(option.text);
    },

    /**
     * Handler for when the user selects a filter in the filter bar.
     */
    handleEditFilter: function() {
        var filterId = this.filterNode.val(),
            filterModel = this.layout.filters.get(filterId);

        if (filterModel && filterModel.get("editable") !== false) {
            this.layout.trigger("filter:create:open", filterModel);
        }
    },

    /**
     * Handler for when the user selects a module in the filter bar.
     */
    handleModuleChange: function(linkModuleName, linkName) {
        this.filterDropdownEnabled = (linkName !== "all_modules" && !app.metadata.getModule(linkModuleName).isBwcEnabled);
    },

    /**
     * Translates the selection text's labels
     * @param {Boolean} isAllRecords
     * @param {String} label
     * @returns {String}
     */
    getTranslatedSelectionText: function(isAllRecords, label) {
        var translatedText, moduleName;

        if (isAllRecords) {
            moduleName = app.lang.get('LBL_MODULE_NAME', this.layout.layout.currentModule);
            translatedText = app.lang.get(label, null, {'moduleName': moduleName});
        }
        else {
            translatedText = app.lang.get(label, ['Filters', this.layout.layout.currentModule]);
        }
        return translatedText;
    },

    /**
     * @override
     * @private
     */
    _dispose: function() {
        if (!_.isEmpty(this.filterNode)) {
            this.filterNode.select2('destroy');
        }
        app.view.View.prototype._dispose.call(this);
    }
})
