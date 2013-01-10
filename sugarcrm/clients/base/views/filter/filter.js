({
    /**
     * Template fragment for select options
     */
    optionTemplate: Handlebars.compile("<option value='{{val}}' {{#if selected}}defaultSelected{{/if}}>{{val}}</option>"),

    events: {
        'click .filter-new': 'toggleOpen',
        'click .chzn-results li': 'toggleSelected',
        'keypress .chzn-choices .search-field input': 'selectedByEnter'
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
        
        _.each(self.filters, function(item){
            data.push({id:item.id, text:item.name});
            if(item.default){
                defaultId = item.id;
            }
        }, this);
        $("#" + self.searchFilterId).select2({tags:data, multiple:true});
        if(defaultId){
            $("#" + self.searchFilterId).select2("val", defaultId);
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
    }
})
