({
    searchFilterId: "",
    previousValues: [],
    currentValues: [],
    changedByenter: false,

    events: {
        'click .filter-new': 'toggleOpen',
        'click .chzn-results li': 'toggleSelected',
        'keypress .chzn-choices .search-field input': 'selectedByEnter'
    },

    initialize: function(opts) {
        _.bindAll(this);
        app.view.View.prototype.initialize.call(this, opts);
        //this.searchFilterId = _.uniqueId("search_filter");
        this.searchFilterId = "search_filter";
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
    },

    changeToPill: function() {
        var selectedItems = this.$el.find(".chzn-choices li.search-choice"),
            latestItem = $(selectedItems[selectedItems.length - 1]),
            latestSpan = latestItem.find("span"),
            latestItemValue = latestSpan.html(),
            latestItemLink = latestItem.find("a"),
            filterName = latestItemValue.replace(/ /g, ""),
            menuItem = this.$("#" + this.searchFilterId + "_chzn_o_" + latestItem.attr("id").slice(latestItem.attr("id").indexOf("c_") + 2));
        
        latestItem.addClass("search-choice search-choice-option " + this.getFilterID(latestItemValue));
        latestItem.append($("<a>" + latestItemValue + "</a>"));
        latestItemLink.removeClass().addClass("closer").hide();
        latestItemLink.html("");
        latestSpan.html("Placeholder");
        
        $.data(menuItem[0], "pillId", latestItem.attr("id"));
    },
    
    getFilterID: function(name){
        return "filter_" + name.replace(/ /g, "");
    },
    
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

    toggleOpen: function() {
        this.layout.trigger("filter:create:open:fire");
        this.$(".search_filter").trigger("liszt:close");
        return true;
    },
    
    selectedByEnter: function(e){
        if(e.keyCode == 13){
            this.changedByEnter = true;
        }   
    }
})
