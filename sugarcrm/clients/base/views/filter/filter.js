({
    events: {},

    initialize: function(opts) {
        _.bindAll(this);
        app.view.View.prototype.initialize.call(this, opts);

        this.getFilters();
        this.getPreviouslyUsedFilter();

        this.layout.off("filter:refresh");
        this.layout.on("filter:refresh", this.getFilters);
    },

    /**
     * Retrieve filters from the server.
     */
    getFilters: function(id) {
        var self = this,
            url = app.api.buildURL('Filters', "filter");

        // TODO: here we might have issues when deleting filters. See removeAll().
        this.currentFilter = id || "default";
        this.filters = app.data.createBeanCollection('Filters');
        this.filters.fetch({
            filterDef: {
                filter: [
                    {"created_by": app.user.id},
                    {"module_name": this.module}
                ]
            },
            success: function() {
                self.render();
            }
        });
    },

    getPreviouslyUsedFilter: function() {
        var url = app.api.buildURL('Filters', this.module + "/used"),
            self = this;
        app.api.call("read", url, null, {
            success: function(data) {
                self.currentFilter = _.isEmpty(data)? "default" : _.last(data).id;
                self.filterDataSetAndSearch();
            }
        });
    },

    render: function() {
        if(this.filters.length) {
            var self = this,
                relatedModuleList = [],
                customFilterList = [],
                defaultId = (this.currentFilter === "default")? this.filters.where({default_filter: "1"})[0].id : this.currentFilter;

            this.layoutType = this.layout.context.get("layout") || app.controller.context.get("layout");

            _.each(this.filters.models, function(model){
                customFilterList.push({id:model.id, text:model.get("name")});
            }, this);

            customFilterList.push({id: "create", text: app.lang.get("LBL_FILTER_CREATE_NEW")});

            if(this.layoutType === "record") {
                relatedModuleList.push({id: "default", text: app.lang.get("LBL_TABGROUP_ALL")});

                // TODO: Fix this when we have a more concrete way of retrieving a list of related modules.
                var subpanels = app.metadata.getModule(this.module).subpanels.subpanel_setup;
                _.each(subpanels, function(value, key){
                    relatedModuleList.push({id:key, text:app.lang.get(value.title_key, self.module)});
                }, this);
            }

            app.view.View.prototype.render.call(this);

            this.relatedFilterNode = this.$(".related-filter");
            this.customFilterNode = this.$(".search-filter");

            this.relatedFilterNode.select2({
                data: relatedModuleList,
                multiple: false,
                minimumResultsForSearch: 7,
                formatSelection: this.formatRelatedSelection,
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

            // Disable the related module filter dropdown
            if(this.layoutType !== "record") {
                this.relatedFilterNode.select2("disable", true);
            }

            this.customFilterNode.on("change", this.sanitizeFilter);

            this.customFilterNode.select2("val", defaultId).trigger("change", defaultId);
            this.relatedFilterNode.select2("val", "default");

            this.relatedFilterNode.on("change", function(e) {
                // Relationships dropdown stuff goes here.
            });

            this.throttledSearch = _.debounce(function(e) {
                var newSearch = self.$(e.currentTarget).val();
                if(self.currentSearch !== newSearch) {
                    self.currentSearch = newSearch;
                    self.filterDataSetAndSearch();
                }
            }, 400);

            this.$('.search-name').on("keyup", this.throttledSearch);
        }
    },

    initSelection: function(el, callback) {
        var data, model;
        if(el.val() !== "create") {
            if(el.is(this.customFilterNode)) {
                model = this.filters.get(el.val());
                data = {id: model.id, text: model.get("name")};
            } else {
                data = {id: "default", text: (this.layoutType === "record")? app.lang.get("LBL_TABGROUP_ALL") : this.module};
            }

            callback(data);
        }
    },

    formatCustomSelection: function(item) {
        var self = this,
            result = $('<span class="select2-choice-type">' + app.lang.get("LBL_FILTER") + '<i class="icon-caret-down"></i></span><a class="select2-choice-filter" rel="'+ item.id + '" href="javascript:void(0)">'+ item.text +'</a>');

        // TODO: Only bind this event if the filter has been created by the user (do we want users to be able to edit pre-defined filters? probably not) [ABE-283].
        $(result[1]).on("click", function() {
            self.openPanel(self.filters.get(item.id));
        });
        return result;
    },
    formatRelatedSelection: function(item) {
        var selectionLabel = app.lang.get("LBL_RELATED") + '<i class="icon-caret-down"></i>';
        if(this.layoutType !== "record") {
            selectionLabel = app.lang.get("LBL_MODULE");
        }
        return '<span class="select2-choice-type">' + selectionLabel + '</span><a class="select2-choice-related" href="javascript:void(0)">'+ item.text +'</a>';
    },
    formatResult: function (option) {
        // TODO: Determine whether active filters should be highlighted in bold in this menu.
        return '<div><span class="select2-match"></span>'+ option.text +'</div>';
    },

    /**
     * This function is a handler for when the custom filter dropdown value changes.
     * (Either via a click or manually calling jQuery's .trigger("change") event).
     * @param  {obj} e      jQuery Change Event Object.
     * @param  {string} newVal (optional) ID passed in when manually changing the filter dropdown value.
     */
    sanitizeFilter: function(e, newVal) {
        var self = this,
            val = e.val || newVal;

        if(val === "create") {
            // Create a new filter.
            this.currentFilter = "default";
            this.openPanel();
        } else if(this.filters.get(val)) {
            // Is a valid filter.
            this.currentFilter = val;
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
        var filterDef = {
            filter: []
        };

        if(this.filters.get(this.currentFilter)) {
            filterDef = JSON.parse(JSON.stringify(this.filters.get(this.currentFilter).get('filter_definition')));
            this.customFilterNode.select2("val", this.currentFilter);
        }

        if(this.currentSearch) {
            filterDef.filter.push({"name": {"$starts": this.currentSearch}});
        }

        app.events.trigger("list:preview:decorate", null, this);
        app.events.trigger("list:filter:fire", filterDef, this);
    }
})
