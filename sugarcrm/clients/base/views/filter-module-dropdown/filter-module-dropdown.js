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
     * @override
     * @param {Object} opts
     */
    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);

        //Load partials
        this._select2formatSelectionTemplate = app.template.get("filter-module-dropdown.selection-partial");
        this._select2formatResultTemplate = app.template.get("filter-module-dropdown.result-partial");

        this.listenTo(this.layout, "filter:change:module", this.handleChange);
        this.listenTo(this.layout, "filter:render:module", this._render);
    },

    /**
     * Trigger events when a change happens
     * @param {String} linkModuleName
     * @param {String} linkName
     * @param {Boolean} silent
     */
    handleChange: function(linkModuleName, linkName, silent) {
        if (linkName === "all_modules") {
            //Trigger subpanel change
            this.layout.trigger("subpanel:change");
        } else {
            //Trigger closing the Create Filter form
            this.layout.trigger("filter:create:close");
            //Trigger subpanel change
            this.layout.trigger("subpanel:change", linkName);
        }

        this.filterNode.select2("val", linkName || linkModuleName);
        if (!silent) {
            this.layout.layout.trigger("filterpanel:change:module", linkModuleName, linkName);
            this.layout.trigger("filter:get", linkModuleName, linkName);
        }
    },

    /**
     * @override
     * @private
     */
    _render: function() {
        app.view.View.prototype._render.call(this);

        if (this.layout.showingActivities) {
            this.filterList = this.getModuleListForActivities();
        } else if (this.layout.layoutType === "record") {
            this.filterList = this.getModuleListForSubpanels();
        } else {
            this.filterList = this.getModuleListForRecords();
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
        if (linkName === "all_modules") {
            this.layout.trigger("subpanel:change");
            // Fixes SP-836; esentially, we need to clear subpanel:last:<module> anytime 'All' selected
            app.cache.cut("subpanels:last:" + this.module);
        } else if (linkName && linkName !=='all_modules') {
            this.layout.trigger("filter:create:close");
            this.layout.trigger("subpanel:change", linkName);
            app.cache.set("subpanels:last:"+ app.controller.context.get('module'), linkName);
        }

        this.filterNode.select2("val", linkName || linkModuleName);
        if (!silent) {
            this.layout.layout.trigger("filter:change", linkModuleName, linkName);
            this.layout.trigger("filter:get", linkModuleName, linkName);
        }
    },

    /**
     * For record layout,
     * Populate the module dropdown by reading the subpanel relationships
     */
    getModuleListForSubpanels: function() {
        var filters = [];
        filters.push({id: "all_modules", text: app.lang.get("LBL_TABGROUP_ALL")});

        var subpanels = this.pullSubpanelRelationships();
        _.each(subpanels, function(value, key) {
            var module = app.data.getRelatedModule(this.module, value);
            if (app.acl.hasAccess("list", module)) {
                filters.push({id: value, text: app.lang.get(key, this.module)});
            }
        }, this);
        return filters;
    },

    /**
     * For records layout,
     * Populate the module dropdown with a single item
     */
    getModuleListForRecords: function() {
        var filters = [];
        filters.push({id: this.module, text: app.lang.get('LBL_MODULE_NAME', this.module)});
        return filters;
    },

    /**
     * For Activity Stream,
     * Populate the module dropdown with a single item
     */
    getModuleListForActivities: function() {
        var filters = [], label;
        if (this.module == "Activities") {
            label = app.lang.get("LBL_TABGROUP_ALL");
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
     * Get the dropdown labels for the module dropdown
     * @param {Object} el
     * @param {Function} callback
     */
    initSelection: function(el, callback) {
        var selection, label;
        if (el.val() === "all_modules") {
            label = (this.layout.layoutType === "record") ? app.lang.get("LBL_TABGROUP_ALL") : app.lang.get("LBL_MODULE_NAME", this.module);
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
        var selectionLabel;

        // Update the text for the selected module.
        this.$('.choice-related').html(item.text);

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
