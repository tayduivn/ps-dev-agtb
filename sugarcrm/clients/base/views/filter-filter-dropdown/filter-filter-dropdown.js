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
        "click .choice-filter.choice-filter-clickable": "handleEditFilter",
        "click .choice-filter-close": "handleClearFilter"
    },

    /**
     * These labels are used in the filter dropdown
     *  - labelDropdownTitle        label used as the dropdown title. ie `Filter`
     *  - labelCreateNewFilter      label for create new filter action. ie `Create`
     *  - labelAllRecords           label used on record view when all related modules are selected. ie `All Records`
     *
     *  - labelAllRecordsFormatted  label used when all records are selected. ie `All <Module>s`
     *
     *                              It is set to null because already defined per module. However, some views are
     *                              allowed to override it because of the context. For instance, `dupecheck-list` view
     *                              wants to display `All duplicates` instead of `All <Module>s`
     */
    labelDropdownTitle:         'LBL_FILTER',
    labelCreateNewFilter:       'LBL_FILTER_CREATE_NEW',
    labelAllRecords:            'LBL_FILTER_ALL_RECORDS',
    labelAllRecordsFormatted:   null,


    /**
     * @override
     * @param {Object} opts
     */
    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);

        //Load partials
        this._select2formatSelectionTemplate = app.template.get("filter-filter-dropdown.selection-partial");
        this._select2formatResultTemplate = app.template.get("filter-filter-dropdown.result-partial");

        this.listenTo(this.layout, "filter:select:filter", this.handleSelect);
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
        if (this.layout.canCreateFilter()) {
            filters.push({id: "create", text: app.lang.get(this.labelCreateNewFilter)});
        }
        if (this.layout.filters.get('all_records') && this.labelAllRecordsFormatted) {
            this.layout.filters.get('all_records').set('name',  this.labelAllRecordsFormatted);
            this.layout.filters.sort();
        }
        // This flag is used to determine when we have to add the border top (to separate categories)
        var firstNonEditable = false;
        this.layout.filters.each(function(model) {
            var opts = {
                id: model.id,
                text: this.layout._getTranslatedFilterName(model)
            };
            if (model.get("editable") === false && !firstNonEditable) {
                opts.firstNonUserFilter = true;
                firstNonEditable = true;
            }
            filters.push(opts);
        }, this);

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
            formatResultCssClass: _.bind(this.formatResultCssClass, this),
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
             *
             * @triggers filter:change:filter on filter layout to indicate a new
             *   filter has been selected.
             *
             * @param {Event} e The `change` event.
             */
            function(e) {
                self.layout.trigger('filter:change:filter', e.val);
            }
        );
    },

    /**
     * This handler is useful for other components that trigger
     * `filter:select:filter` in order to select the dropdown value.
     *
     * @param {String} id The id of the filter to select in the dropdown.
     */
    handleSelect: function(id) {
        this.filterNode.select2('val', id, true);
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

        if (val === 'create') {
            //It should show `Create`
            data = {id: "create", text: app.lang.get(this.labelCreateNewFilter)};

        } else {
            model = this.layout.filters.get(val);

            //Fallback to `all_records` filter if not able to retrieve selected filter
            if (!model) {
                data = {id: "all_records", text: app.lang.get(this.labelAllRecords)};

            } else if (val === "all_records") {
                data = this.formatAllRecordsFilter(null, model);
            } else {
                data = {id: model.id, text: this.layout._getTranslatedFilterName(model)};
            }
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

        //Don't remove this line. We want to update the selected filter name but don't want to change to the filter
        //name displayed in the dropdown
        item = _.clone(item);

        this.toggleFilterCursor(this.isFilterEditable(item.id));

        if (item.id === 'all_records') {
            item = this.formatAllRecordsFilter(item);
        }

        //Escape string to prevent XSS injection
        safeString = Handlebars.Utils.escapeExpression(item.text);
        // Update the text for the selected filter.
        this.$('.choice-filter-label').html(safeString);

        if (item.id !== 'all_records') {
            this.$('.choice-filter-close').show();
        } else {
            this.$('.choice-filter-close').hide();
        }

        ctx.label = app.lang.get(this.labelDropdownTitle);
        ctx.enabled = this.filterDropdownEnabled;

        return this._select2formatSelectionTemplate(ctx);
    },

    /**
     * Returns template
     * @param {Object} option
     * @returns {String}
     */
    formatResult: function(option) {
        if (option.id === this.layout.getLastFilter(this.layout.layout.currentModule, this.layout.layoutType)) {
            option.icon = 'icon-ok';
        } else if (option.id === 'create') {
            option.icon = 'icon-plus';
        } else {
            option.icon = undefined;
        }
        return this._select2formatResultTemplate(option);
    },

    /**
     * Adds a class to the `Create Filter` item (to add border bottom)
     * and a class to first user custom filter (to add border top)
     *
     * @param {Object} item
     * @returns {string} css class to attach
     */
    formatResultCssClass: function(item) {
        if (item.id === 'create') { return 'select2-result-border-bottom'; }
        if (item.firstNonUserFilter) { return 'select2-result-border-top'; }
    },

    /**
     * Determine if a filter is editable
     *
     * @param {String} id
     * @returns {Boolean} TRUE if filter is editable, FALSE otherwise
     */
    isFilterEditable: function(id) {
        if (!this.layout.canCreateFilter() || !this.filterDropdownEnabled || this.layout.showingActivities) {
            return false;
        }
        if (id === "create" || id === 'all_records') {
            return true;
        } else {
            return !this.layout.filters.get(id) || this.layout.filters.get(id).get('editable') !== false;
        }
    },

    /**
     * Toggles cursor depending if the filter is editable or not.
     *
     * @param {Boolean} active TRUE for a pointer cursor, FALSE for a not allowed cursor
     */
    toggleFilterCursor: function(editable) {
        if (editable) {
            this.$('.choice-filter').css("cursor", "pointer").addClass('choice-filter-clickable');
        } else {
            this.$('.choice-filter').css("cursor", "not-allowed").removeClass('choice-filter-clickable');
        }
    },

    /**
     * Formats label for `all_records` filter. When showing all subpanels, we expect `All records`
     *
     * @param {Object} item
     * @returns {Object} item with formatted label
     */
    formatAllRecordsFilter: function (item, model) {
        item = item || {id: 'all_records'};

        //SP-1819: Seeing "All Leads" instead of "All Records" in sub panel
        //For the record view our Related means all subpanels (so should show `All Records`)
        var allRelatedModules = _.indexOf([this.module, 'all_modules'], this.layout.layout.currentModule) > -1;

        //If ability to create a filter
        if (this.isFilterEditable(item.id)) {
            item.text = app.lang.get(this.labelCreateNewFilter);
        } else if (this.layout.layoutType === 'record' && allRelatedModules) {
            item.text = app.lang.get(this.labelAllRecords);
            this.toggleFilterCursor(false);
        } else if (model) {
            item.text = this.layout._getTranslatedFilterName(model);
        }
        return item;
    },

    /**
     * Handler for when the user selects a filter in the filter bar.
     */
    handleEditFilter: function() {
        var filterId = this.filterNode.val(),
            filterModel;

        if (filterId === 'all_records') {
            // Figure out if we have an edit state. This would mean user was editing the filter so we want him to retrieve
            // the filter form in the state he left it.
            this.layout.trigger("filter:select:filter", 'create');
        } else {
            filterModel = this.layout.filters.get(filterId);
        }

        if (filterModel && filterModel.get("editable") !== false) {
            this.layout.trigger("filter:create:open", filterModel);
        }
    },

    /**
     * Handler for when the user selects a module in the filter bar.
     */
    handleModuleChange: function(linkModuleName, linkName) {
        this.filterDropdownEnabled = (linkName !== "all_modules");
    },

    /**
     * When a click happens on the close icon, clear the last filter and trigger reinitialize
     * @param {Event} evt
     */
    handleClearFilter: function(evt) {
        //This event is fired within .choice-filter and another event is attached to .choice-filter
        //We want to stop propagation so it doesn't bubble up.
        evt.stopPropagation();
        this.layout.clearLastFilter(this.layout.layout.currentModule, this.layout.layoutType);
        this.layout.trigger('filter:select:filter', this.layout.filters.defaultFilterFromMeta);
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
