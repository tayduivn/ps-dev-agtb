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
        self.node.select2({tags:data, multiple:true, maximumSelectionSize:2});
        if(defaultId){
            self.node.select2("val", defaultId);
        }
        self.node.on("change", function(e){
            self.sanitizeFilter(e);
        });
    },
    
    sanitizeFilter: function(e){
        var self = this;
        if(_.contains(_.pluck(self.filters, "id"), e.added.id)){
            self.node.select2("val", "");
            self.node.select2("val", e.added.id);
        }
    },

    /**
     * Retrieve filters from the server.
     */
    getFilters: function() {
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
        this.layout.trigger("filter:create:open:fire");
        this.$(".search_filter").trigger("liszt:close");
        return true;
    }
})
