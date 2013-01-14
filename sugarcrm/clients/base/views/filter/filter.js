({
    /**
     * Template fragment for select options
     */
    optionTemplate: Handlebars.compile("<option value='{{val}}' {{#if selected}}defaultSelected{{/if}}>{{val}}</option>"),

    events: {
    },

    initialize: function(opts) {
        _.bindAll(this);
        app.view.View.prototype.initialize.call(this, opts);

        this.searchFilterId = _.uniqueId("search_filter");
        this.getFilters();
        this.layout.off("filter:refresh");
        this.layout.on("filter:refresh", this.getFilters);
    },

    render: function() {
        var self = this,
            data = [],
            defaultId = "";

        _.each(this.filters.models, function(model){
            data.push({id:model.id, text:model.get("name")});
            if(model.get("default_filter")){
                defaultId = model.id;
            }
        }, this);

		data.push({id:-1, text:"Create New"});

        app.view.View.prototype.render.call(this);

        this.node = this.$("#" + this.searchFilterId);
        this.node.select2({
            tags:data,
            multiple:true,
            maximumSelectionSize:2,
            formatSelection: this.formatSelection,
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
            return item.text;
        } else {
            return '<span>Filter</span><a href="javascript:void(0)" rel="' + item.id +'">'+ item.text +'</a>';
        }
    },

    /**
     * Contains business logic to control the behavior of new filters being added.
     */
    sanitizeFilter: function(e){
        if(!_.isUndefined(e.added) && !_.isUndefined(e.added.id)){
            var self = this,
            isInFilters = this.isInFilters(e.added.id);

            if((e.added.id == -1) && !isInFilters){
                this.node.select2("val", _.without(this.node.select2("val"), e.added.id.toString()));
                this.toggleOpen();
            } else if(isInFilters){
                this.node.select2("val", "");
                this.node.select2("val", [e.added.id]);
                this.filterDataSet(e.added.id);
                this.$("a[rel=" + e.added.id + "]").on("click", function(){
                    self.toggleOpen(self.filters.get(e.added.id));
                });
            }
        } else {
            this.filterDataSet('');
        }
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
    getFilters: function() {
        var self = this;

        this.filters = app.data.createBeanCollection('Filters');
        this.filters.fetch({success: function() {
            self.render();
        }});
    },

    /**
     * Fires an event for the Filter editing widget to pop up.
     */
    toggleOpen: function(filter) {
        this.layout.trigger("filter:create:new", filter);
        var valueInputs = this.layout.$el.find(".filter-value input");
        return true;
    },

    filterDataSet: function(activeFilterId) {
        var ctx = app.controller.context,
            url = app.api.buildURL(this.module, "filter/" + activeFilterId);
        app.api.call("read", url, null, {
            success: function(data) {
                ctx.get('collection').reset(data.records);
            }
        });
    }
})
