({
    events: {
        'click .filter-new': 'toggleOpen',
        'click .chzn-results li': 'toggleSelected'
    },

    initialize: function(opts) {
        console.log("creating a filter view");
        app.view.View.prototype.initialize.call(this, opts);
        //this.searchFilterId = _.uniqueId("search_filter");
        this.searchFilterId = "search_filter";
    },

    render: function() {
        var self = this;
        app.view.View.prototype.render.call(this);
        
        /* HTML setup
         ---------------------- */

        // copied from styleguide

        // add the current module as default filter
//        if (window.location.pathname.split('/').pop() !== 'dashboard.html') {
//            $('#{{sourceid}}.form-search ul.chzn-choices').prepend('<li class="search-choice search-choice-option" id="search_filter_00_choice"><span>Module</span><span>{{title}}</span></li>');
//        }

        // prepend legend to .chzn-choices
//        $('#{{sourceid}} #search_filter_chzn .chzn-choices').prepend(
//            '<legend class="chzn-select-legend">Filter <i class="icon-caret-down"></i></legend>'
//        );


        // append create filter icon
        //$('#{{sourceid}} #search_filter_chzn').append('<div class="filter-create"><i class="icon-plus"></i></div>');
//        $('#{{sourceid}} .filter-new').on('mousedown click', function(e) {
//            $(this).parents('.form-search').find('.filter-options').removeClass('hide');
//            e.stopPropagation();
//            e.preventDefault();
//        });


        // Enable chosen on the menu
        this.$(".search_filter").chosen().change(function(){
            self.changeToPill();
        });
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
        latestItemLink.removeClass();
        latestItemLink.html(latestItemValue);
        latestSpan.html("Placeholder");
        //$.data(menuItem[0], "selected", true);
        
    },
    
    getFilterID: function(name){
        return "filter_" + name.replace(/ /g, "");
    },
    
    toggleSelected: function(e){
        if($.data(e.currentTarget, "selected")){
            $.data(e.currentTarget, "selected", false);
            var target = $(e.currentTarget);
            target.removeClass("result-selected");
            target.addClass("active-result");
            this.$("." + this.getFilterID(target.html())).remove();
        }
        else{
            $.data(e.currentTarget, "selected", true);
        }
    },

    toggleOpen: function() {
        this.layout.trigger("filter:create:open:fire");
        this.$(".search_filter").trigger("liszt:close");
        return true;
    }
})
