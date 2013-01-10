({  
    /**
     * Template fragment for select options
     */
    optionTemplate: Handlebars.compile("<option value='{{val}}' {{#if selected}}defaultSelected{{/if}}>{{val}}</option>"),

    events: {
        'click .filter-new': 'toggleOpen'
    },

    initialize: function(opts) {
        _.bindAll(this);
        app.view.View.prototype.initialize.call(this, opts);

//        this.searchFilterId = _.uniqueId("search_filter");
        this.searchFilterId = "search_filter";
        this.filters = [];
        this.getFilters();
    },

    render: function() {
        var self = this,
            data = [],
            defaultId = "";

        app.view.View.prototype.render.call(this);
        self.node = self.$("#" + self.searchFilterId);

        _.each(self.filters, function(item){
            data.push({id:item.id, text:item.name});
            if(item.default){
                defaultId = item.id;
            }
        }, this);

		data.push({id:-1, text:"Create New"});

        self.node.select2({
            tags:data,
            multiple:true,
            maximumSelectionSize:2,
            formatSelection: self.formatSelection,
            formatResult: self.formatResult
        });

        if(defaultId){
            self.node.select2("val", defaultId);
        }
        self.node.on("change", function(e){
            self.sanitizeFilter(e);
        });
    },

    formatSelection: function(item) {
        return '<span>Filter</span><a href="javascript:void(0)" rel="'+ item.id +'">'+ item.text +'</a>';
    },

    formatResult: function (item) {
        console.log('result',item);
        var rtn = '<span data-value="'+ item.id +'">'+ item.text +'</span>';
            rtn += '<span class="'+ (item.text.indexOf('Create New Filter')===-1?'icon-ok':'icon-plus') +'"></span>';
        return rtn;
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
                self.node.select2("val", e.added.id);
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
        if(_.contains(_.pluck(self.filters, "id"), filter)){
            return true;
        }
        return false;
    },

    /**
     * Retrieve filters from the server.
     */
    getFilters: function() {
        var self = this;
       
        /*console.log(app.api.buildURL("Filters"));
        app.api.call("read", "", app.api.buildURL("Filters"), function(data){
            console.log("Here");
            self.filters = data;
        });
        console.log(self.filters);
        */
        // Temperorarily mock filter data
        var filters = [
            {
                id: 1,
                name: "My Filter",
                filter_definition: {
                    filter: [
                        {
                            $or: [
                                {name: "Nelson Inc"},
                                {name: "Nelson LLC"}
                            ]
                        },
                        {$owner: "_this"}
                    ],
                    max_num: 30
                }
            },
            {
                id: 2,
                name: "My Little Pony",
                filter_definition: {
                    filter: [

                        {name: "Nelson Inc"}
                    ]
                },
                default: true
            },
            {
                id:3,
                name: "My Favorites",
                filter_definition: {
                    name: {$starts: "Nelson"}
                }
            }
        ];

        this.filters = filters;
    },

    /**
     * Fires an event for the Filter editing widget to pop up.
     */
    toggleOpen: function() {
        this.layout.trigger("filter:create:new");
        this.$(".search_filter").trigger("liszt:close");
        return true;
    }
})
