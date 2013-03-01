({
    events: {
        "keyup .search-name": "throttledSearch",
        "paste .search-name": "throttledSearch",
        "click .choice-filter": "handleEditFilter"
    },

    initialize: function(opts) {
        // Select2 callbacks require us to _.bindAll(this)
        _.bindAll(this);
        app.view.View.prototype.initialize.call(this, opts);

        this.layoutType = this.context.get("layout") || app.controller.context.get("layout");
        this.currentModule = (this.layoutType === "record")? "all_modules" : this.module;
        this.currentFilter = "all_records";
        this.aclToCheck = (this.layoutType === "record")? "view" : "list";

        // TODO: temporary fix. We need this condition or else record view is
        // prevented from loading (this is due to subpanels not having their own context).
        if(this.layoutType === "records") {
            this.context.set("skipFetch", true);
        }

        this.filters = app.data.createBeanCollection('Filters');

        this.layout.off("filter:add");
        this.layout.off("filter:set");
        this.layout.on("filter:add", this.addFilter, this);
        this.layout.on("filter:set", this.setFilter, this);
    },

    addFilter: function(model) {
        this.filters.add(model);
        this.setFilter(model.id);
    },

    setFilter: function(id) {
        this.currentFilter = id;
        this.customFilterNode.select2("val", id).trigger("change", id);
        this.updateFilterList();
    },

    /**
     * Retrieve filters from the server.
     */
    getFilters: function() {
        this.filters.fetch({
            filter: [
                {"created_by": app.user.id},
                {"module_name": this.currentModule}
            ]
        });
    },

    getPreviouslyUsedFilter: function() {
        if (this.currentModule !== "all_modules") {
            var url = app.api.buildURL('Filters', this.currentModule + "/used"),
            self = this;

            app.api.call("read", url, null, {
                success: function(data) {
                    if (!_.isEmpty(data)) {
                        self.filters.add(data);
                        self.currentFilter = self.previousFilter = self.filters.first().id;
                    }
                    self.handleFilterSelection(null, self.currentFilter);
                    self.getFilters();
                }
            });
        }
    },

    render: function() {
        // The ACL check needs to refer to the global module, not the subpanel
        // module, so we use this.module instead of this.currentModule.
        if(app.acl.hasAccess(this.aclToCheck, this.module)) {

            app.view.View.prototype.render.call(this);
            this.updateModuleList();
            this.updateFilterList();

            this.moduleFilterNode.select2("val", this.currentModule).trigger("change");
            this.customFilterNode.select2("val", this.currentFilter);
        }
    },

    updateFilterList: function() {
        var customFilterList = [];
        this.customFilterNode = this.$(".search-filter");

        customFilterList.push({id: "all_records", text: app.lang.get("LBL_FILTER_ALL_RECORDS")});

        _.each(this.filters.models, function(model){
            customFilterList.push({id:model.id, text:model.get("name")});
        }, this);

        customFilterList.push({id: "create", text: app.lang.get("LBL_FILTER_CREATE_NEW")});

        this.customFilterNode.select2({
            data: customFilterList,
            multiple: false,
            minimumResultsForSearch: 7,
            formatSelection: this.formatCustomSelection,
            formatResult: this.formatResult,
            dropdownCss: {width:'auto'},
            dropdownCssClass: 'search-filter-dropdown',
            initSelection: this.initSelection
        });

        this.customFilterNode.off("change");
        this.customFilterNode.on("change", this.handleFilterSelection);
    },

    updateModuleList: function() {
        this.moduleFilterNode = this.$(".related-filter");
        this.moduleFilterList = [];

        if(this.layoutType === "record") {
            this.moduleFilterList.push({id: "all_modules", text: app.lang.get("LBL_TABGROUP_ALL")});

            var subpanels = this.pullSubpanelRelationships();
            _.each(subpanels, function(value, key){
                var module = app.data.getRelatedModule(this.module, value);
                if (app.acl.hasAccess("list", module)) {
                    this.moduleFilterList.push({id:value, text:app.lang.get(key, this.module)});
                }
            }, this);
        } else {
            this.moduleFilterList.push({id: this.module, text: app.lang.get('LBL_MODULE_NAME', this.module)});
        }

        this.moduleFilterNode.select2({
            data: this.moduleFilterList,
            multiple: false,
            minimumResultsForSearch: 7,
            formatSelection: this.formatModuleSelection,
            formatResult: this.formatResult,
            dropdownCss: {width:'auto'},
            dropdownCssClass: 'search-related-dropdown',
            initSelection: this.initSelection
        });

        // Disable the module filter dropdown.
        if(this.layoutType !== "record") {
            this.moduleFilterNode.select2("disable");
        }

        this.moduleFilterNode.off("change");
        this.moduleFilterNode.on("change", this.handleModuleSelection);
    },

    pullSubpanelRelationships: function() {
        // Subpanels are retrieved from the global module and not the
        // subpanel module, therefore we use this.module instead of
        // this.currentModule.
        return app.metadata.getModule(this.module).layouts.subpanel.meta.subpanelList;
    },

    throttledSearch: _.debounce(function(e) {
        var newSearch = this.$(e.currentTarget).val();
        if(this.currentSearch !== newSearch) {
            this.currentSearch = newSearch;
            this.filterDataSetAndSearch();
        }
    }, 400),

    initSelection: function(el, callback) {
        var data, model, obj;

        if(el.val() !== "create") {
            if(el.is(this.customFilterNode)) {
                if (el.val() !== "all_records") {
                    model = this.filters.get(el.val());
                    data = {id: model.id, text: model.get("name")};
                } else {
                    data = {id: "all_records", text: app.lang.get("LBL_FILTER_ALL_RECORDS")};
                }
            } else {
                if (el.val() !== "all_modules") {
                    obj = _.findWhere(this.moduleFilterList, {id: el.val()});
                    data = {id: obj.id, text: obj.text};
                } else {
                    // TODO: Use an i18n version of the module name instead of
                    // this.module.
                    data = {id: "all_modules", text: (this.layoutType === "record")? app.lang.get("LBL_TABGROUP_ALL") : this.module};
                }
            }
            callback(data);
        }
    },

    formatCustomSelection: function(item) {
        // Update the text for the selected filter.
        this.$('.choice-filter').html(item.text);

        return '<span class="select2-choice-type">' + app.lang.get("LBL_FILTER") + '<i class="icon-caret-down"></i></span>';
    },

    formatModuleSelection: function(item) {
        var selectionLabel = app.lang.get("LBL_RELATED") + '<i class="icon-caret-down"></i>';

        if(this.layoutType !== "record") {
            selectionLabel = app.lang.get("LBL_MODULE");
        }

        // Update the text for the selected module.
        this.$('.choice-related').html(item.text);
        return '<span class="select2-choice-type">' + selectionLabel + '</span>';
    },

    formatResult: function (option) {
        // TODO: Determine whether active filters should be highlighted in bold in this menu.
        return '<div><span class="select2-match"></span>'+ option.text +'</div>';
    },

    /**
     * Handler for when the user clicks the filter in the filter bar.
     * Triggers the openPanel() function.
     */
    handleEditFilter: function() {
        // TODO: Only call openPanel if the filter has been created by the user
        // (do we want users to be able to edit pre-defined filters? probably not) [ABE-283].
        if (this.currentFilter !== "all_records" /* && if not editable */) {
            this.openPanel(this.filters.get(this.currentFilter));
        }
    },

    /**
     * Handler for when the module filter dropdown value changes.
     * @param  {obj} e jQuery Change Event Object.
     */
    handleModuleSelection: function(e) {
        var val;
        this.currentModule = e.val || this.currentModule;
        this.handleFilterSelection(e, "all_records");
        if (this.currentModule === "all_modules") {
            this.customFilterNode.select2("disable");
        } else {
            // Close the panel.
            this.layout.trigger("filter:create:close");
            this.customFilterNode.select2("enable");
            this.getPreviouslyUsedFilter();
            val = this.currentModule;
        }
        this.layout.trigger("subpanel:change", val);
    },

    /**
     * Handler for when the custom filter dropdown value changes, either via a
     * click or manually calling jQuery's .trigger("change") event.
     * @param  {obj} e      jQuery Change Event Object.
     * @param  {string} overrideVal (optional) ID passed in when manually changing the filter dropdown value.
     */
    handleFilterSelection: function(e, overrideVal) {
        var self = this,
            val = overrideVal || e.val;

        if(val === "create") {
            // Create a new filter.
            this.currentFilter = "all_records";
            this.$('.choice-filter').css("cursor", "text");
            this.openPanel();
        } else if(val === "all_records") {
            // All records.
            this.currentFilter = "all_records";
            this.$('.choice-filter').css("cursor", "text");
            // Close the panel.
            this.layout.trigger("filter:create:close");
        } else if(this.filters.get(val)) {
            // Is a valid filter.
            this.currentFilter = val;

            // TODO: Check to see whether this is a default filter [ABE-283].
            this.$('.choice-filter').css("cursor", "pointer");

            if(!this.layout.$(".filter-options").hasClass('hide')) {
                self.openPanel(self.filters.get(val));
            }
        }

        this.customFilterNode.select2("val", val);
        this.filterDataSetAndSearch();
    },

    /**
     * Fires an event for the Filter editing widget to pop up.
     */
    openPanel: function(filter) {
        this.layout.trigger("filter:create:new", filter);
    },

    /**
     * Filters the data set by making a create call to the filter API.
     */
    filterDataSetAndSearch: function() {
        if (!this.previousFilter && this.layoutType === "records") {
            this.previousFilter = this.currentFilter;
            return;
        }
        var filterDef = {
            filter: []
        },
            isNewFilter = (this.currentFilter !== this.previousFilter);

        if(this.filters.get(this.currentFilter)) {
            filterDef = JSON.parse(JSON.stringify(this.filters.get(this.currentFilter).get('filter_definition')));
            this.customFilterNode.select2("val", this.currentFilter);
        }

        if(this.currentSearch) {
            filterDef.filter.push({"name": {"$starts": this.currentSearch}});
        }

        app.events.trigger("list:preview:decorate", null, this);
        app.events.trigger("list:filter:fire", filterDef, isNewFilter, this);
        this.previousFilter = this.currentFilter;
    },

    bindDataChange: function() {
        if(this.filters) {
            this.filters.on("reset", function() {
                this.updateFilterList();
            }, this);
        }
    },
    _dispose: function() {
        this.filters.off("reset");
        app.view.Component.prototype._dispose.call(this);
    }
})
