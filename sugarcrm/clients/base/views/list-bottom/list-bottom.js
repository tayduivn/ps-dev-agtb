({
/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.ListViewBottom
 * @alias SUGAR.App.layout.ListViewBottom
 * @extends View.View
 */
    // We listen to event and keep track if search filter is toggled open/close
    filterOpened: false,

    events: {
        'click [name=show_more_button]': 'showMoreRecords',
        'click .search': 'showSearch'
    },
    _renderHtml: function() {
        if (app.acl.hasAccess('create', this.module)) {
            this.context.set('isCreateEnabled', true);
        }

        // Dashboard layout injects shared context with limit: 5. 
        // Otherwise, we don't set so fetches will use max query in config.
        this.limit = this.context.get('limit') ? this.context.get('limit') : null;

        app.view.View.prototype._renderHtml.call(this);

        // We listen for if the search filters are opened or not. If so, when 
        // user clicks show more button, we treat this as a search, otherwise,
        // normal show more for list view.
        this.layout.off("list:filter:toggled", null, this);
        this.layout.on("list:filter:toggled", this.filterToggled, this);
    },        
    filterToggled: function(isOpened) {
        this.filterOpened = isOpened;
    },
    showMoreRecords: function(evt) {
        var self = this, options;
        app.alert.show('show_more_records', {level:'process', title:'Loading'});

        // If in "search mode" (the search filter is toggled open) set q:term param
        options = self.filterOpened ? self.getSearchOptions() : {};

        // Indicates records will be added to those already loaded in to view
        options.add = true;
            
        options.success = function() {
            app.alert.dismiss('show_more_records');
            self.layout.trigger("list:paginate:success");
            self.render();
            window.scrollTo(0, document.body.scrollHeight);
        };
        options.limit = this.limit;
        this.collection.paginate(options);
    },
    showSearch: function() {
        // Toggle on search filter and off the pagination buttons
        this.$('.search').toggleClass('active');
        this.layout.trigger("list:search:toggle");
    },
    getSearchOptions: function() {
        var collection, options, previousTerms, term = '';
        collection = this.context.get('collection');

        // If we've made a previous search for this module grab from cache
        if(app.cache.has('previousTerms')) {
            previousTerms = app.cache.get('previousTerms');
            if(previousTerms) {
                term = previousTerms[this.module];
            } 
        }
        // build search-specific options and return
        options = {
            params: { 
                q: term
            },
            fields: collection.fields ? collection.fields : this.collection
        };
        return options;
    },
    bindDataChange: function() {
        if(this.collection) {
            this.collection.on("reset", this.render, this);
        }
    }

})
