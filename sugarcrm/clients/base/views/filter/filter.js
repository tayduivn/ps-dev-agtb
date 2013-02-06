({
    /**
     * Template fragment for select options
     */
    optionTemplate: Handlebars.compile("<option value='{{val}}' {{#if selected}}defaultSelected{{/if}}>{{val}}</option>"),

    events: {},

    initialize: function(opts) {
        _.bindAll(this);
        app.view.View.prototype.initialize.call(this, opts);

        this.currentQuery = "";
        this.activeFilterId = "";

        this.searchFilterId = _.uniqueId("search_filter");
        this.getFilters();
        this.getPreviouslyUsedFilter();

        this.layout.off("filter:refresh");
        this.layout.on("filter:refresh", this.getFilters);
    },

    getPreviouslyUsedFilter: function() {
        var url = app.api.buildURL('Filters', this.module + "/used"),
            self = this;
        app.api.call("read", url, null, {
            success: function(data) {
                self.activeFilterId = _.isEmpty(data)? "" : _.last(data).id;
                self.render();
            }
        });
    },

    render: function() {
        if(this.filters.length) {
            var self = this,
                relatedModuleList = [],
                customFilterList = [],
                defaultId = this.activeFilterId || "";
            this.layoutType = this.layout.context.get("layout") || app.controller.context.get("layout");

            _.each(this.filters.models, function(model){
                customFilterList.push({id:model.id, text:model.get("name")});
            }, this);

            customFilterList.push({id:-1, text:"Create New"});

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

            // TODO: find out if recently viewed as a default is the desired behaviour. Also remove hardcoded string.
            // For the custom filter, apply the previous filter, otherwise apply recently viewed
            var default_filter = this.filters.first(),
                selectedId = defaultId || default_filter.id;

            if(this.layoutType !== "record") {
                this.relatedFilterNode.select2("disable", true);
            }
            this.relatedFilterNode.select2("val", "default");
            this.customFilterNode.select2("val", selectedId);
            this.sanitizeFilter({added:{id: selectedId}});

            this.customFilterNode.on("change", function(e) {
                self.sanitizeFilter(e);
            });
        }
    },

    initSelection: function(el, callback) {
        var data, model;
        if(el.is(this.customFilterNode)) {
            model = this.filters.get(el.val());
            data = {id: model.id, text: model.get('name')};
        } else {
            data = {id: "default", text: (this.layoutType === 'record')? app.lang.get("LBL_TABGROUP_ALL") : this.module};
        }
        callback(data);
    },

    formatCustomSelection: function(item) {
        return '<span class="select2-choice-type">' + app.lang.get("LBL_FILTER") + '<i class="icon-caret-down"></i></span><a class="select2-choice-filter" href="javascript:void(0)">'+ item.text +'</a>';
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
     * Contains business logic to control the behavior of new filters being added.
     */
    sanitizeFilter: function(e){
        var id, val = this.customFilterNode.select2("val"), newVal, i, self = this;
        if(!_.isUndefined(e.added) && !_.isUndefined(e.added.id)) {
            id = e.added.id;

            if( id === -1 )  {
                // Create a new filter.
                val = _.without(val, id.toString());
                this.activeFilterId = "";
                newVal = "";
                this.openPanel();
            } else if( this.isInFilters(id) ) {
                // Is a valid filter.
                this.activeFilterId = id;
                newVal = id;
                if(!this.layout.$(".filter-options").hasClass('hide')) {
                    self.openPanel(self.filters.get(id));
                }
                _.defer(function(self) {
                    self.$("a[rel=" + id + "]").on("click", function(){
                        self.openPanel(self.filters.get(id));
                    });
                }, this);
            } else {
                // It's not a valid filter.
                this.activeFilterId = "";
                newVal = "";
            }
        } else if(!_.isUndefined(e.removed) && !_.isUndefined(e.removed.id)) {
            id = e.removed.id;
            newVal = _.without(val, id.toString());

            if( this.isInFilters(id) ) {
                // Removing a filter.
                this.activeFilterId = "";
            }
        }

        this.customFilterNode.select2("val", newVal);
        this.filterDataSetAndSearch(this.currentQuery, this.activeFilterId);
    },

    /**
     * Utility function to determine if the typed in filter is in the standard filter array
     *
     * @return boolean True if part of the set, false if not.
     */
    isInFilters: function(filter){
        if(!_.isUndefined(this.filters.get(filter))){
            return true;
        }
        return false;
    },

    /**
     * Retrieve filters from the server.
     */
    getFilters: function(defaultId) {
        var self = this,
            url = app.api.buildURL('Filters', "filter");

        this.activeFilterId = defaultId;
        this.filters = app.data.createBeanCollection('Filters');

        app.api.call("create", url, {"filter": [{"created_by": app.user.id}, {"module_name": this.module}]}, {
            success: function(data) {
                self.filters.reset(data.records);
                if(self.isInFilters(self.currentQuery)) {
                    self.currentQuery = "";
                }
                self.filterDataSetAndSearch(self.currentQuery, self.activeFilterId);
                self.render();
            }

        });
    },

    /**
     * Fires an event for the Filter editing widget to pop up.
     */
    openPanel: function(filter) {
        this.layout.trigger("filter:create:new", filter);
    },

    /**
     * Filters the data set by making a create call to the filter API.
     * @param  {string} query          Query for quick-searching, null for regular filters.
     * @param  {string} activeFilterId GUID of the filter.
     */
    filterDataSetAndSearch: function(query, activeFilterId) {
        var filterDef;
        this.currentQuery = query;
        this.activeFilterId = activeFilterId;
        if (this.filters.get(activeFilterId)) {
            filterDef = JSON.parse(JSON.stringify((this.filters.get(activeFilterId).get('filter_definition'))));
        } else {
            filterDef = {
                "filter":[
                    {
                        "$and":[]
                    }
                ]
            };
        }
        var ctx = app.controller.context,
        clause, self = this;
        // TODO: Make this extensible for OR operator.
        if(!_.isEmpty(query)) {
            clause = {"name": {"$starts": query}};
            filterDef.filter[0]["$and"].push(clause);
        }

        filterDef = filterDef.filter[0]["$and"].length? filterDef : {};

        var url, method;
        if( _.isEmpty(filterDef) ) {
            url = app.api.buildURL(this.module);
            method = "read";
        } else {
            url = app.api.buildURL(this.module, "filter");
            method = "create";
        }

        app.api.call(method, url, filterDef, {
            success: function(data) {
                ctx.get('collection').reset(data.records);
                app.events.trigger("list:preview:decorate", null, self);
                var url = app.api.buildURL('Filters/' + self.module + '/used', "update");
                app.api.call("update", url, {filters: [self.activeFilterId]}, {});
            }
        });
    }
})
