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
     * View for the module dropdown
     * Part of BaseFilterLayout
     *
     * @class BaseFilterModuleDropdown
     * @extends View
     */

    //Override default Backbone tagName
    tagName: "span",

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        //Load partials
        this._select2formatSelectionTemplate = app.template.get("filter-module-dropdown.selection-partial");
        this._select2formatResultTemplate = app.template.get("filter-module-dropdown.result-partial");

        this.listenTo(this.layout, "filter:change:module", this.handleChange);
        this.listenTo(this.layout, "filter:render:module", this._render);
    },

    /**
     * @inheritdoc
     */
    _render: function() {
        this._super('_render');

        if (this.layout.showingActivities) {
            this.filterList = this.getModuleListForActivities();
        } else if (this.layout.layoutType === "record") {
            this.filterList = this.getModuleListForSubpanels();
        } else {
            this.$el.hide();
            return this;
        }

        this._renderDropdown(this.filterList);
    },

    /**
     * Render select2 dropdown
     * @private
     */
    _renderDropdown: function(data) {
        var self = this;

        this.filterNode = this.$(".related-filter");

        this.filterNode.select2({
            data: data,
            multiple: false,
            minimumResultsForSearch: 7,
            formatSelection: _.bind(this.formatSelection, this),
            formatResult: _.bind(this.formatResult, this),
            dropdownCss: {width: 'auto'},
            dropdownCssClass: 'search-related-dropdown',
            initSelection: _.bind(this.initSelection, this),
            escapeMarkup: function(m) {
                return m;
            },
            width: 'off'
        });

        // Disable the module filter dropdown.
        if (this.layout.layoutType !== "record" || this.layout.showingActivities) {
            this.filterNode.select2("disable");
        }

        this.filterNode.off("change");
        this.filterNode.on("change", function(e) {
            /**
             * Called when the user selects a module in the dropdown
             * Triggers filter:change:module on filter layout
             * @param {Event} e
             */
            var linkModule = e.val;
            if (self.layout.layoutType === "record" && linkModule !== "all_modules") {
                linkModule = app.data.getRelatedModule(self.module, linkModule);
            }
            self.layout.trigger("filter:change:module", linkModule, e.val);
        });
    },

    /**
     * Trigger events when a change happens
     * @param {String} linkModuleName
     * @param {String} linkName
     * @param {Boolean} silent
     */
    handleChange: function(linkModuleName, linkName, silent) {
        //this.layout is the filter layout which filter-module-dropdown view
        //is a child of; we use it here as it has a last_state key in its meta
        var cacheKey = app.user.lastState.key('subpanels-last', this.layout);
        if (linkName === "all_modules") {
            this.layout.trigger("subpanel:change");
            // Fixes SP-836; esentially, we need to clear subpanel-last-<module> anytime 'All' selected
            app.user.lastState.remove(cacheKey);
        } else if (linkName) {
            this.layout.trigger("subpanel:change", linkName);
            app.user.lastState.set(cacheKey, linkName);
        }

        // It is important to reset the `currentFilterId` in order to retrieve
        // the last filter from cache later.
        this.context.set('currentFilterId', null);

        if (this.filterNode) {
            this.filterNode.select2("val", linkName || linkModuleName);
        }
        if (!silent) {
            this.layout.layout.trigger("filter:change", linkModuleName, linkName);
            this.layout.trigger('filter:get', linkModuleName, linkName);
            //Clear the search input and apply filter
            this.layout.trigger('filter:clear:quicksearch');
        }
    },

    /**
     * For record layout,
     * Populate the module dropdown by reading the subpanel relationships
     */
    getModuleListForSubpanels: function() {
        var filters = [];
        filters.push({id: "all_modules", text: app.lang.get("LBL_MODULE_ALL")});

        var subpanels = this.pullSubpanelRelationships();
        subpanels = this._pruneHiddenModules(subpanels);
        if (subpanels) {
            _.each(subpanels, function(value, key) {
                var module = app.data.getRelatedModule(this.module, value);
                if (app.acl.hasAccess("list", module)) {
                    filters.push({id: value, text: app.lang.get(key, this.module)});
                }
            }, this);
        }
        return filters;
    },

    /**
     * For Activity Stream,
     * Populate the module dropdown with a single item
     */
    getModuleListForActivities: function() {
        var filters = [], label;
        if (this.module == "Activities") {
            label = app.lang.get("LBL_MODULE_ALL");
        } else {
            label = app.lang.get('LBL_MODULE_NAME', this.module);
        }
        filters.push({id: 'Activities', text: label});
        return filters;
    },

    /**
     * Pull the list of related modules from the subpanel metadata
     * @returns {Object}
     */
    pullSubpanelRelationships: function() {
        // Subpanels are retrieved from the global module and not the
        // subpanel module, therefore we use this.module instead of
        // this.currentModule.
        return app.utils.getSubpanelList(this.module);
    },

    /**
     * Prunes hidden modules from related dropdown list
     * @param {Object} subpanels List of candidate subpanels to display
     * @return {Object} pruned list of subpanels
     * @private
     */
    _pruneHiddenModules: function(subpanels){
        var hiddenSubpanels = _.map(app.metadata.getHiddenSubpanels(), function(subpanel) {
            return subpanel.toLowerCase();
        });
        var pruned = _.reduce(subpanels, function(obj, value, key) {
            var relatedModule = app.data.getRelatedModule(this.module, value);
            if (relatedModule && !_.contains(hiddenSubpanels, relatedModule.toLowerCase())) {
                obj[key] = value;
            }
            return obj;
        }, {}, this);
        return pruned;
    },

    /**
     * Get the dropdown labels for the module dropdown
     * @param {Object} el
     * @param {Function} callback
     */
    initSelection: function(el, callback) {
        var selection, label;
        if (el.val() === "all_modules") {
            label = (this.layout.layoutType === "record") ? app.lang.get("LBL_MODULE_ALL") : app.lang.get("LBL_MODULE_NAME", this.module);
            selection = {id: "all_modules", text: label};
        } else if (_.findWhere(this.filterList, {id: el.val()})) {
            selection = _.findWhere(this.filterList, {id: el.val()});
        } else if(this.filterList && this.filterList.length > 0)  {
            selection = this.filterList[0];
        }
        callback(selection);
    },

    /**
     * Update the text for the selected module and returns template
     *
     * @param {Object} item
     * @returns {string}
     */
    formatSelection: function(item) {
        var selectionLabel, safeString;

        //Escape string to prevent XSS injection
        safeString = Handlebars.Utils.escapeExpression(item.text);
        // Update the text for the selected module.
        this.$('.choice-related').html(safeString);

        if (this.layout.layoutType !== "record" || this.layout.showingActivities) {
            selectionLabel = app.lang.get("LBL_MODULE");
        } else {
            selectionLabel = app.lang.get("LBL_RELATED") + '<i class="icon-caret-down"></i>';
        }


        return this._select2formatSelectionTemplate(selectionLabel);
    },

    /**
     * Returns template
     * @param {Object} option
     * @returns {string}
     */
    formatResult: function(option) {
        // TODO: Determine whether active filters should be highlighted in bold in this menu.
        return this._select2formatResultTemplate(option.text);
    },

    /**
     * @inheritdoc
     */
    _dispose: function() {
        if (!_.isEmpty(this.filterNode)) {
            this.filterNode.select2('destroy');
        }
        this._super('_dispose');
    }
})
