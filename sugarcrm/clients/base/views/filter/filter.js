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

        _.each(self.filters.models, function(model){
            data.push({id:model.id, text:model.get("name")});
            if(model.get("default_filter")){
                defaultId = model.id;
            }
        }, this);

		data.push({id:-1, text:"Create New"});

        app.view.View.prototype.render.call(this);

        self.node = self.$("#" + self.searchFilterId);
        self.node.select2({
            tags:data,
            multiple:true,
            maximumSelectionSize:2,
            formatSelection: self.formatSelection
        });

        if(defaultId){
            self.node.select2("val", defaultId);
            self.sanitizeFilter({added:{id:defaultId}});
        }
        self.node.on("change", function(e){
            self.sanitizeFilter(e);
        });

    },

    formatSelection: function(item) {
        var self = this;

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
            isInFilters = self.isInFilters(e.added.id);

            if((e.added.id == -1) && !isInFilters){
                self.node.select2("val", _.without(self.node.select2("val"), e.added.id.toString()));
                self.toggleOpen();
            } else if(isInFilters){
                self.node.select2("val", "");
                self.node.select2("val", [e.added.id]);
                self.$("a[rel=" + e.added.id + "]").on("click", function(){
                    self.toggleOpen(self.filters.get(e.added.id));
                });
            }
        }
    },

    /**
     * Utility function to determine if the typed in filter is in the standard filter array
     *
     * @return boolean True if part of the set, false if not.
     */
    isInFilters: function(filter){
        var self = this;
        if(!_.isUndefined(self.filters.get(filter))){
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

        // Focus when the default filter is the only row present
        if( valueInputs.length && valueInputs.length < 3 ) {
            $(valueInputs[1]).focus();
        }
        return true;
    },

    filterDataSet: function(activeFilter) {
        var ctx = app.controller.context,
            url = app.api.buildURL(this.module, "filter/" + activeFilter.id);
        app.api.call("read", url, null, {
            success: function(data) {
                ctx.get('collection').reset(data);
            }
        });
    }
})
