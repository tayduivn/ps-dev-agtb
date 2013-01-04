({
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

    pill: function(id, html, index) {
        return '<li class="search-choice search-choice-option" id="search_filter_' + index + '_choice"><span>Filter</span><a>' + html + '</a></li>';
    }
})