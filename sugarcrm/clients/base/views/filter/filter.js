({
    /**
     * Template fragment for select options
     */
    optionTemplate: Handlebars.compile("<option value='{{val}}' {{#if selected}}defaultSelected{{/if}}>{{val}}</option>"),

    events: {},

    initialize: function(opts) {
        _.bindAll(this);
        app.view.View.prototype.initialize.call(this, opts);

        this.currentQuery = ""; this.activeFilterId = "";

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
        var self = this,
            data = [],
            defaultId = this.activeFilterId || "";

        _.each(this.filters.models, function(model){
            data.push({id:model.id, text:model.get("name")});
        }, this);

		data.push({id:-1, text:"Create New"});

        app.view.View.prototype.render.call(this);

        this.node = this.$("#" + this.searchFilterId);
        this.node.select2({
            tags:data,
            multiple:true,
            maximumSelectionSize:2,
            formatSelection: this.formatSelection,
            placeholder: app.lang.get("LBL_MODULE_FILTER"),
            dropdownCss: {width:'auto'},
            dropdownCssClass: 'search-filter-dropdown'
        });

        if(defaultId){
            this.node.select2("val", defaultId);
            this.sanitizeFilter({added:{id:defaultId}});
        }
        this.node.on("change", function(e){
            self.sanitizeFilter(e);
        });
    },

    formatSelection: function(item) {
        if (item.id === item.text) {
            return '<span>Name starts with</span><a href="javascript:void(0)" rel="' + item.id +'">'+ item.text +'</a>';
        } else {
            return '<span>Filter</span><a href="javascript:void(0)" rel="' + item.id +'">'+ item.text +'</a>';
        }
    },

    /**
     * Contains business logic to control the behavior of new filters being added.
     */
    sanitizeFilter: function(e){
        var id, val = this.node.select2("val"), newVal = [], i, self = this;
        if(!_.isUndefined(e.added) && !_.isUndefined(e.added.id)) {
            id = e.added.id;

            if( id === -1 && !this.isInFilters(id) )  {
                // Create a new filter.
                val = _.without(val, id.toString());
                this.activeFilterId = "";
                for(i = 0; i < val.length; i++) {
                    if(!this.isInFilters(val[i])) {
                        newVal.push(val[i]);
                    }
                }
                this.openPanel();
            } else if( this.isInFilters(id) ) {
                // Is a valid filter.
                this.activeFilterId = id;
                for(i = 0; i < val.length; i++) {
                    if(!this.isInFilters(val[i])) {
                        newVal.push(val[i]);
                    }
                }
                newVal.push(id);
                if(!this.layout.$(".filter-options").hasClass('hide')) {
                    self.openPanel(self.filters.get(id));
                }
                _.defer(function(self) {
                    self.$("a[rel=" + id + "]").on("click", function(){
                        self.openPanel(self.filters.get(id));
                    });
                }, this);
            } else {
                // It's a quick-search word.
                this.currentQuery = $.trim(id);
                for(i = 0; i < val.length; i++) {
                    if(this.isInFilters(val[i])) {
                        newVal.push(val[i]);
                    }
                }
                newVal.push(id);
            }
        } else if(!_.isUndefined(e.removed) && !_.isUndefined(e.removed.id)) {
            id = e.removed.id;
            newVal = _.without(val, id.toString());

            if( this.isInFilters(id) ) {
                // Removing a filter.
                this.activeFilterId = "";
            } else {
                // Removing a quick-search word.
                this.currentQuery = "";
            }
        }

        this.node.select2("val", newVal);
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

        this.filters = app.data.createBeanCollection('Filters');

        app.api.call("create", url, {"filter": [{"created_by": app.user.id}]}, {
            success: function(data) {
                self.filters.reset(data.records);
                if(self.isInFilters(self.currentQuery)) {
                    self.currentQuery = "";
                }
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
        var url = app.api.buildURL(this.module, "filter"),
        ctx = app.controller.context,
        clause, self = this;
        // TODO: Make this extensible for OR operator.
        if(!_.isEmpty(query)) {
            clause = {"name": {"$starts": query}};
            filterDef.filter[0]["$and"].push(clause);
        }

        filterDef = filterDef.filter[0]["$and"].length? filterDef : {};

        app.api.call("create", url, filterDef, {
            success: function(data) {
                ctx.get('collection').reset(data.records);
                var url = app.api.buildURL('Filters/' + self.module + '/used', "update");
                app.api.call("update", url, {filters: [self.activeFilterId]}, {});
            }
        });
    }
})
