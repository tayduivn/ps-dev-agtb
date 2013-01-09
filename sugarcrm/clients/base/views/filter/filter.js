({
    /**
     * Id of the select box that chosen is built from
     */
    searchFilterId: "",
    
    /**
     * Array of previously selected values
     */
    previousValues: [],
    
    /**
     * Array of currently selected values
     */
    currentValues: [],
    
    /**
     * Flag for if the filter was selected by typing and hitting enter
     */
    changedByenter: false,

    /**
     * Template fragment for select options
     */
    optionTemplate: Handlebars.compile("<option value='{{val}}' {{#if selected}}SELECTED{{/if}}>{{val}}</option>"),

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
        var self = this;
        app.view.View.prototype.render.call(this);
    
        // Enable chosen on the menu
        this.$(".search_filter").chosen().change(function(e){
            self.previousValues = self.currentValues;
            self.currentValues = $(e.target).val();
            self.changeToPill();
            if(self.changedByEnter){
                self.previousValues = self.currentValues;
                self.changedByEnter = false;
            }
            
        });
        
        //mods to chosen
        this.$el.find(".chzn-choices").prepend("<li class='search-choice search-choice-option filter-disabled'><span>Module</span><a>" + this.module + "</a></li>");
        this.$(".chzn-choices").prepend('<legend class="chzn-select-legend">Filter <i class="icon-caret-down"></i></legend>');
        
        // append checkmarks to filter dropdown results
        this.$('.chzn-results').find('li').after("<span class='icon-ok' />");

        if (this.filters.length > 0) {
            // Iterate over the filters and render the pills
            _.each(this.filters, function(filter) {
                var params = {
                    val: filter.name,
                    selected: (filter.default) ? true : false
                };

                this.$(".search_filter option.disabled").before(this.optionTemplate(params));
            }, this);

            this.$(".search_filter").trigger("liszt:updated");
            this.changeToPill();
        }
    },

    /**
     * Retrieve filters from the server.
     */
    getFilters: function() {
        // Temperorarily mock filter data
        var filters = [
            {
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
                name: "My Little Pony",
                filter_definition: {
                    filter: [

                        {name: "Nelson Inc"}
                    ]
                },
                default: true
            },
            {
                name: "My Favorites",
                filter_definition: {
                    name: {$starts: "Nelson"}
                }
            }
        ];

        this.filters = filters;
        this.render();
    },

    /**
     * Changes UI element of the "pill" to match the styleguide
     * 
     * This function is an augmentation to chosen to allow us to restyle the breadcrumb "pills" after
     * they have been rendered.
     */
    changeToPill: function() {
        var selectedItems = this.$el.find(".chzn-choices li.search-choice"),
            latestItem = $(selectedItems[selectedItems.length - 1]),
            latestSpan = latestItem.find("span"),
            latestItemValue = latestSpan.html(),
            latestItemLink = latestItem.find("a"),
            filterName = latestItemValue.replace(/ /g, ""),
            menuItem = this.$("#" + this.searchFilterId + "_chzn_o_" + latestItem.attr("id").slice(latestItem.attr("id").indexOf("c_") + 2));
        
        latestItem.addClass("search-choice search-choice-option");
        latestItem.append($("<a>" + latestItemValue + "</a>"));
        latestItemLink.removeClass().addClass("closer").hide();
        latestItemLink.html("");
        latestSpan.html("Filter");
        
        $.data(menuItem[0], "pillId", latestItem.attr("id"));
    },
    
    /**
     * Toggles selected items from the chosen dropdown.  Enables and disables pills, and unselects
     * items.
     */
    toggleSelected: function(e){
        var selectId = e.currentTarget.id.slice(e.currentTarget.id.indexOf("o_")+2),
            selectValue = $("#" + this.searchFilterId + " option")[selectId].value;
        
        if($.inArray(selectValue, this.previousValues) != -1){
            var target = $(e.currentTarget);
            target.removeClass("result-selected");
            target.addClass("active-result");
            $("#" + $.data(e.currentTarget, "pillId") + " .closer").trigger("click");
            $("#" + $.data(e.currentTarget, "pillId")).remove();
        }
        this.previousValues = this.currentValues;
    },

    /**
     * Fires an event for the Filter editing widget to pop up.
     */
    toggleOpen: function() {
        this.layout.trigger("filter:create:open:fire");
        this.$(".search_filter").trigger("liszt:close");
        return true;
    },
    
    /**
     * Sets a boolean if the chosen option was selected by pressing Enter (and not clicking 
     * an item in the filter dropdown)
     */
    selectedByEnter: function(e){
        if(e.keyCode == 13){
            this.changedByEnter = true;
        }   
    }
})
