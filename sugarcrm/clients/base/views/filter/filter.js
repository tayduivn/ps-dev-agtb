({
    events: {
        'click .filter-new': 'toggleOpen'
    },

    initialize: function(opts) {
        console.log("creating a filter view");
        app.view.View.prototype.initialize.call(this, opts);
    },

    render: function() {
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
        this.$(".search_filter").chosen({ searchChoiceConstructor: this.pill});

        this.$(".chzn-choices").prepend('<legend class="chzn-select-legend">Filter <i class="icon-caret-down"></i></legend>');


        // append checkmarks to filter dropdown results
        this.$('.chzn-results').find('li').after("<span class='icon-ok' />");
    },

    pill: function(id, item, type) {
        if(typeof type==='undefined') {
            type = item.classes.indexOf('filter-disabled')!==-1 ? 'Module' : 'Filter';
        }
        return item.classes.indexOf('filter-new')===-1 ? '<li class="search-choice search-choice-option '+ item.classes +'" id="'+ id +'"><span>'+ type +'</span><a href="javascript:void(0)" rel="'+ item.array_index +'">'+ item.html +'</a></li>' : '';
    },

    toggleOpen: function() {
        this.layout.trigger("filter:create:open:fire");
        this.$(".search_filter").trigger("liszt:close");
        var valueInputs = this.layout.$el.find(".filter-value input");
        
        // Focus when the default filter is the only row present
        if( valueInputs.length && valueInputs.length < 3 ) {
            $(valueInputs[1]).focus();
        }
        return true;
    }
})
