({
    /**
     * View for the module dropdown
     * Part of BaseFilterLayout
     *
     * @class BaseFilterModuleDropdown
     * @extends View
     */

    tagName: "span",

    /**
     * @override
     * @param {Object} opts
     */
    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);

        this.layout.on("filter:change:module", this.handleChange, this);
        this.layout.on("filter:render:module", this._render, this);
    },

    /**
     * @override
     * @private
     */
    _render: function() {
        var self = this;
        app.view.View.prototype._render.call(this);
        this.filterNode = this.$(".related-filter");
        this.filterList = [];

        if (this.layout.showingActivities) {
            this.getModuleListForActivities();
        } else {
            this[(this.layout.layoutType === "record")? "getModuleListForSubpanels" : "getModuleListForRecords"]();
        }

        this.filterNode.select2({
            data: this.filterList,
            multiple: false,
            minimumResultsForSearch: 7,
            formatSelection: _.bind(this.formatSelection, this),
            formatResult: _.bind(this.formatResult, this),
            dropdownCss: {width:'auto'},
            dropdownCssClass: 'search-related-dropdown',
            initSelection: _.bind(this.initSelection, this),
            escapeMarkup: function(m) { return m; },
            width: 'off'
        });

        // Disable the module filter dropdown.
        if(this.layout.layoutType !== "record" || this.layout.showingActivities) {
            this.filterNode.select2("disable");
        }

        this.filterNode.off("change");
        this.filterNode.on("change", function(e) {
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
        if (linkName !== "all_modules") {
            this.layout.trigger("filter:create:close");
            this.layout.trigger("subpanel:change", linkName);
        } else {
            this.layout.trigger("subpanel:change");
            // Fixes SP-836; esentially, we need to clear subpanel:last:<module> anytime 'All' selected
            app.cache.cut("subpanels:last:" + this.module);
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
        this.filterList.push({id: "all_modules", text: app.lang.get("LBL_TABGROUP_ALL")});

        var subpanels = this.pullSubpanelRelationships();
        _.each(subpanels, function(value, key){
            var module = app.data.getRelatedModule(this.module, value);
            if (app.acl.hasAccess("list", module)) {
                this.filterList.push({id:value, text:app.lang.get(key, this.module)});
            }
        }, this);
    },

    /**
     * For records layout,
     * Populate the module dropdown with a single item
     */
    getModuleListForRecords: function() {
        this.filterList.push({id: this.module, text: app.lang.get('LBL_MODULE_NAME', this.module)});
    },

    /**
     * For Activity Stream,
     * Populate the module dropdown with a single item
     */
    getModuleListForActivities: function() {
        var text = (this.module == "Activities") ? app.lang.get("LBL_TABGROUP_ALL") : app.lang.get('LBL_MODULE_NAME', this.module);
        this.filterList.push({id: 'Activities', text: text});
    },

    /**
     * Pull the list of related modules from the subpanel metadata
     * @returns {Object}
     */
    pullSubpanelRelationships: function() {
        // Subpanels are retrieved from the global module and not the
        // subpanel module, therefore we use this.module instead of
        // this.currentModule.
        return app.metadata.getModule(this.module).layouts.subpanel ? app.metadata.getModule(this.module).layouts.subpanel.meta.subpanelList : {};
    },

    /**
     * Get the dropdown labels for the module dropdown
     * @param {Object} el
     * @param {Function} callback
     */
    initSelection: function(el, callback) {
        var obj, data;
        if (el.val() !== "all_modules") {
            obj = _.findWhere(this.filterList, {id: el.val()});
            data = {id: obj.id, text: obj.text};
        } else {
            data = {id: "all_modules", text: (this.layout.layoutType === "record")? app.lang.get("LBL_TABGROUP_ALL") : this.module};
        }
        callback(data);
    },

    /**
     * Update the text for the selected module and returns template
     *
     * @param {Object} item
     * @returns {string}
     */
    formatSelection: function(item) {
        var selectionLabel = app.lang.get("LBL_RELATED") + '<i class="icon-caret-down"></i>';

        if(this.layout.layoutType !== "record" || this.layout.showingActivities) {
            selectionLabel = app.lang.get("LBL_MODULE");
        }

        // Update the text for the selected module.
        this.$('.choice-related').html(item.text);
        return '<span class="select2-choice-type">' + selectionLabel + '</span>';
    },

    /**
     * Returns template
     * @param {Object} option
     * @returns {string}
     */
    formatResult: function (option) {
        // TODO: Determine whether active filters should be highlighted in bold in this menu.
        return '<div><span class="select2-match"></span>'+ app.lang.get(option.text, "Filters") +'</div>';
    },

    /**
     * @override
     * @private
     */
    _dispose: function() {
        this.filterNode.select2('destroy');
        app.view.View.prototype._dispose.call(this);
    }
})
