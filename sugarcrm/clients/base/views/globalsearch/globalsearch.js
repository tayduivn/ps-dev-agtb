({
    events: {
        'click .typeahead a': 'clearSearch',
        'click .navbar-search span.add-on': 'gotoFullSearchResultsPage'
    },

    _renderHtml: function() {
        if (!this.app.api.isAuthenticated() || this.app.config.appStatus == 'offline') return;

        this.app.view.View.prototype._renderHtml.call(this);

        // Search ahead drop down menu stuff
        var self = this,
            menuTemplate = this.app.template.getView('dropdown-menu');
        this.$('.search-query').searchahead({
            request:  this.fireSearchRequest,
            compiler: menuTemplate,
            throttleMillis: (this.app.config.requiredElapsed || 500),
            throttle: function(callback, millis) {
                if(!self.debounceFunction) {
                    self.debounceFunction = _.debounce(function(){
                        callback();
                    }, millis || 500);
                }
                self.debounceFunction();
            },
            onEnterFn: function(hrefOrTerm, isHref) {
                // if full href treat as user clicking link
                if(isHref) {
                    window.location = hrefOrTerm;
                } else {
                    // It's the term only (user didn't select from drop down
                    // so this is essentially the term typed
                    self.app.router.navigate('#search/'+hrefOrTerm, {trigger: true});
                }
            }
        });
    },

    /**
     * Callback for the searchahead plugin .. note that
     * 'this' points to the plugin (not the header view!)
     */
    fireSearchRequest: function (term) {
        var plugin = this, mlist, params;
        mlist = app.metadata.getModuleNames(true).join(','); // visible
        params = {q: term, fields: 'name, id', module_list: mlist, max_num: app.config.maxSearchQueryResult};
        app.api.search(params, {
            success:function(data) {
                data.module_list = app.metadata.getModuleNames(true,"create");
                plugin.provide(data);
            },
            error:function(error) {
                app.error.handleHttpError(error, plugin);
                app.logger.error("Failed to fetch search results in search ahead. " + error);
            }
        });
    },

    /**
     * Takes user to full search results page
     */
    gotoFullSearchResultsPage: function(evt) {
        var term;
        // Don't let plugin kick in. Navigating directly to search results page
        // when clicking on adjacent button is, to my mind, special case portal
        // application requirements so I'd rather do here than change plugin.
        evt.preventDefault();
        evt.stopPropagation();
        // URI encode search query string so that it can be safely
        // decoded by search handler (bug55572)
        term = encodeURIComponent(this.$('.search-query').val());
        if(term && term.length) {
            this.app.router.navigate('#search/'+term, {trigger: true});
        }
    },

    /**
     * Clears out search upon user following search result link in menu
     */
    clearSearch: function(evt) {
        this.$('.search-query').val('');
    }
})