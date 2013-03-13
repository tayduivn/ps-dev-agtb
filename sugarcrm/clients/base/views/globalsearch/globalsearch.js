({
    searchModules: [],
    events: {
        'click .typeahead a': 'clearSearch',
        'click .icon-search': 'gotoFullSearchResultsPage',
        'click .globalsearch-adv': 'persistMenu',
        'click .select-module': 'selectModule'
    },
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        app.events.on('app:sync:complete', this.populateModules, this);
    },
    /**
     * Handle module 'select/unselect' event.
     * @param event
     */
    selectModule: function(event) {
        var module = this.$(event.currentTarget).data('module'),
            searchAll = this.$('input:checkbox[data-module="all"]'),
            searchAllLabel = searchAll.closest('label'),
            checkedModules = this.$('input:checkbox:checked[data-module!="all"]');

        if (module === 'all') {
            searchAll.attr('checked', true);
            searchAllLabel.addClass('active');
            checkedModules.removeAttr('checked');
            checkedModules.closest('label').removeClass('active');
        }
        else {
            var currentTarget = this.$(event.currentTarget),
                currentTargetLabel = currentTarget.closest('label');

            currentTarget.attr('checked') ? currentTargetLabel.addClass('active') : currentTargetLabel.removeClass('active');

            if (checkedModules.length) {
                searchAll.removeAttr('checked');
                searchAllLabel.removeClass('active');
            }
            else {
                searchAll.attr('checked', true);
                searchAllLabel.addClass('active');
            }
        }
        // This will prevent the module selection dropdown from disappearing.
        event.stopPropagation();
    },
    /**
     * Create the dropdown for the user to select which modules to search.
     */
    populateModules: function() {
        if (this.disposed) {
            return;
        }
        this.searchModules = [];
        var modules = app.metadata.getModules() || {};
        _.each(modules, function(meta, module) {
            // TODO: remove hard coded Documents below
            if (module !== 'Documents' && meta.ftsEnabled && app.acl.hasAccess('view', module)) {
                this.searchModules.push(module);
            }
        }, this);
        this.render();
    },
    _renderHtml: function() {
        if (!app.api.isAuthenticated() || app.config.appStatus == 'offline') return;

        app.view.View.prototype._renderHtml.call(this);

        // Search ahead drop down menu stuff
        var self = this,
            menuTemplate = app.template.getView(this.name + '.result');

        this.$('.search-query').searchahead({
            request: function(term) {
                self.fireSearchRequest.call(self, term, this);
            },
            compiler: menuTemplate,
            minChars: 4, // ignore first three
            throttleMillis: (app.config.requiredElapsed || 500),
            throttle: function(callback, millis) {
                if(!self.debounceFunction) {
                    self.debounceFunction = _.debounce(function(){
                        callback();
                    }, millis || 500);
                }
                self.debounceFunction();
            },
            onEnterFn: function(hrefOrTerm, isHref) {
                // there seems a bug in the function go() in sugar.searchahead.js. 
                // it will pass the first active record in the results even for 'enter' key
                // comment out this 'if' statement for now. it's not being used anyway
                // if full href treat as user clicking link
                //if(isHref) {  
                //    window.location = hrefOrTerm;
                //} else {
                    // It's the term only (user didn't select from drop down
                    // so this is essentially the term typed
                    var term = $.trim(self.$('.search-query').attr('value'));
                    if (!_.isEmpty(term)) {
                        self.fireSearchRequest.call(self, term, this);
                    }
                //}
            }
        });
        
        // Prevent the form from being submitted
        this.$('.navbar-search').submit(function() {
            return false;
        });
    },
    /**
     * Get the modules that current user selected for search. 
     * @returns {Array}
     */
    _getSearchModuleNames: function() {
        if (this.$('input:checkbox[data-module="all"]').attr('checked')) {
            return this.searchModules;
        }
        else {
            var searchModuleNames = [], 
                checkedModules = this.$('input:checkbox:checked[data-module!="all"]');
            _.each(checkedModules, function(val,index) {
                searchModuleNames.push(val.getAttribute('data-module'));
            }, this);
            return searchModuleNames;
        }
    },
    /**
     * Callback for the searchahead plugin .. note that
     * 'this' points to the plugin (not the header view!)
     */
    fireSearchRequest: function (term, plugin) {
        var searchModuleNames = this._getSearchModuleNames(), 
            params = {
                q: term,
                fields: 'name, id',
                module_list: searchModuleNames.join(","),
                max_num: 5
            };
        app.api.search(params, {
            success:function(data) {
                var formattedRecords = [];
                _.each(data.records, function(record) {
                    var formattedRecord = {id:record.id,name:record.name,module:record._module},
                        meta = app.metadata.getModule(record._module);

                    if (meta && meta.isBwcEnabled) {
                        formattedRecord.link = app.bwc.buildRoute(record._module, record.id, 'DetailView');
                    }
                    else {
                        formattedRecord.link = '#' + app.router.buildRoute(record._module, record.id);
                    }
                    if ((record._search.highlighted)) { // full text search
                        _.each(record._search.highlighted, function(val, key) {
                            if (key !== 'name') { // found in a related field
                               formattedRecord.field_name = app.lang.get(val.label, val.module);
                               formattedRecord.field_value = val.text;
                            }
                        });
                    }
                    formattedRecords.push(formattedRecord);
                });
                plugin.provide({next_offset: data.next_offset, records: formattedRecords});
            },
            error:function(error) {
                app.error.handleHttpError(error, plugin);
                app.logger.error("Failed to fetch search results in search ahead. " + error);
            }
        });
    },

    /**
     * Show full search results when the search button is clicked
     * (Show searchahead results for sugarcon because we don't have full results page yet)
     */
    gotoFullSearchResultsPage: function(evt) {
        evt.preventDefault();
        evt.stopPropagation();
        // Simulate 'enter' keyed so we can show searchahead results
        var e = jQuery.Event("keyup");
        e.keyCode = $.ui.keyCode.ENTER;
        this.$('.search-query').focus();
        this.$('.search-query').trigger(e);
    },

    /**
     * Clears out search upon user following search result link in menu
     */
    clearSearch: function(evt) {
        this.$('.search-query').val('');
    },

    /**
     * This will prevent the dropup menu from closing when clicking anywhere on it
     */
    persistMenu: function(e) {
        e.stopPropagation();
    }
})