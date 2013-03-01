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

        // TODO: temporary fix. We need this condition or else record view is
        // prevented from loading (this is due to subpanels not having their own context).
        if(this.layoutType === "records") {
            this.context.set("skipFetch", true);
        }

        this.filters = app.data.createBeanCollection('Filters');
        this.getPreviouslyUsedFilter();

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
        this.render();
    },

    /**
     * Retrieve filters from the server.
     */
    getFilters: function() {
        this.filters.fetch({
            filter: [
                {"created_by": app.user.id},
                {"module_name": this.module}
            ]
        });
    },

    getPreviouslyUsedFilter: function() {
        var url = app.api.buildURL('Filters', this.module + "/used"),
            self = this;
        app.api.call("read", url, null, {
            success: function(data) {
                if (_.isEmpty(data)) {
                    self.currentFilter = self.previousFilter = "all_records";
                } else {
                    self.filters.add(data);
                    self.currentFilter = self.previousFilter = self.filters.first().id;
                }
                self.getFilters();
            }
        });
    },

    render: function() {
        if(!this.currentFilter || !app.acl.hasAccess("list", this.module)) { return;
        }

        var self = this,
            moduleFilterList = [],
            customFilterList = [],
            defaultId = this.currentFilter;

        customFilterList.push({id: "all_records", text: app.lang.get("LBL_FILTER_ALL_RECORDS")});

        _.each(this.filters.models, function(model){
            customFilterList.push({id:model.id, text:model.get("name")});
        }, this);

        customFilterList.push({id: "create", text: app.lang.get("LBL_FILTER_CREATE_NEW")});

        if(this.layoutType === "record") {
            moduleFilterList.push({id: "all_modules", text: app.lang.get("LBL_TABGROUP_ALL")});

            // TODO: Fix this when we have a more concrete way of retrieving a list of related modules.
            var subpanels = app.metadata.getModule(this.module).subpanels.subpanel_setup;
            _.each(subpanels, function(value, key){
                moduleFilterList.push({id:key, text:app.lang.get(value.title_key, self.module)});
            }, this);
        }

        app.view.View.prototype.render.call(this);

        this.moduleFilterNode = this.$(".related-filter");
        this.customFilterNode = this.$(".search-filter");

        this.moduleFilterNode.select2({
            data: moduleFilterList,
            multiple: false,
            minimumResultsForSearch: 7,
            formatSelection: this.formatModuleSelection,
            formatResult: this.formatResult,
            dropdownCss: {width:'auto'},
            dropdownCssClass: 'search-related-dropdown',
            initSelection: this.initSelection
        });

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

        // Disable the module filter dropdown
        if(this.layoutType !== "record") {
            this.moduleFilterNode.select2("disable", true);
        }

        this.customFilterNode.on("change", this.sanitizeFilter);

        this.customFilterNode.select2("val", defaultId).trigger("change", defaultId);
        this.moduleFilterNode.select2("val", "all_modules");

        this.moduleFilterNode.on("change", function(e) {
            // Relationships dropdown stuff goes here.
        });
    },

    throttledSearch: _.debounce(function(e) {
        var newSearch = this.$(e.currentTarget).val();
        if(this.currentSearch !== newSearch) {
            this.currentSearch = newSearch;
            this.filterDataSetAndSearch();
        }
    }, 400),

    initSelection: function(el, callback) {
        var data, model;
        if(el.val() !== "create") {
            if(el.is(this.customFilterNode)) {
                if (el.val() !== "all_records") {
                    model = this.filters.get(el.val());
                    data = {id: model.id, text: model.get("name")};
                } else {
                    data = {id: "all_records", text: app.lang.get("LBL_FILTER_ALL_RECORDS")};
                }
            } else {
                data = {id: "all_modules", text: (this.layoutType === "record")? app.lang.get("LBL_TABGROUP_ALL") : this.module};
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
     * Handler for when the custom filter dropdown value changes, either via a
     * click or manually calling jQuery's .trigger("change") event.
     * @param  {obj} e      jQuery Change Event Object.
     * @param  {string} overrideVal (optional) ID passed in when manually changing the filter dropdown value.
     */
    sanitizeFilter: function(e, newVal) {
        var self = this,
            val = e.val || newVal;

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
        if(!this.currentFilter) return;
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
                this.render();
            }, this);
        }
    },
    _dispose: function() {
        this.filters.off("reset");
        app.view.Component.prototype._dispose.call(this);
    }
})
