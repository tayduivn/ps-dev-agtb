({
    previousTerms: {},
    events: {
        'keyup .dataTables_filter input': 'queueAndDelay'
    },

    _renderHtml: function() {
        // this needs to be reset every render because the field set on the collection changes
        this.searchFields = this.getSearchFields();
        app.view.View.prototype._renderHtml.call(this);
        this.layout.off("list:search:toggle", null, this);
        this.layout.on("list:search:toggle", this.toggleSearch, this);
    },

    getSearchFields: function() {
        var self = this;
        var moduleMeta = app.metadata.getModule(this.module);
        var results = new Array();
        _.each(moduleMeta.fields, function(fieldMeta, fieldName) {
            var fMeta = fieldMeta;
            if(fMeta.unified_search && _.indexOf(self.collection.fields, fieldName) >= 0) {
                results.push(app.lang.get(fMeta.vname, self.module));
            }
        });
        return results;
    },

    queueAndDelay: function(evt) {
        var self = this;

        if(!self.debounceFunction) {
            self.debounceFunction = _.debounce(function(){
                var term, previousTerm;

                previousTerm = self.getPreviousTerm(this.module);
                term = self.$(evt.currentTarget).val();
                self.setPreviousTerm(term, this.module);

                if(term && term.length) {
                    self.setPreviousTerm(term, this.module);
                    self.fireSearchRequest(term);
                } else if(previousTerm && !term.length) {
                    // If user removing characters and down to 0 chars reset table to all data
                    this.collection.fetch({limit: this.context.get('limit') || null });
                }
            }, app.config.requiredElapsed || 500);
        }
        self.debounceFunction();
    },

    fireSearchRequest: function(term) {
        var self = this;
        self.setPreviousTerm(term, this.module);
        this.layout.trigger("list:search:fire", term);
    },
    setPreviousTerm: function(term, module) {
        if(app.cache.has('previousTerms')) {
            this.previousTerms = app.cache.get('previousTerms');
        }
        if(module) {
            this.previousTerms[module] = term;
        }
        app.cache.set("previousTerms", this.previousTerms);
    },
    getPreviousTerm: function(module) {
        if(app.cache.has('previousTerms')) {
            this.previousTerms = app.cache.get('previousTerms');
            return this.previousTerms[module];
        }
    },
    toggleSearch: function() {
        var isOpened,
            previousTerm = this.getPreviousTerm(this.module);
        this._renderHtml();
        this.$('.dataTables_filter').toggle();

        // Trigger toggled event. Presently, this is for the list-bottom view.
        // If the 'Show More' button is clicked and filter is opened,
        // list-bottom adds q:term to pagination call.
        isOpened = this.$('.dataTables_filter').is(':visible');
        this.layout.trigger('list:filter:toggled', isOpened);

        // Always clear last search term
        this.$('.dataTables_filter input').val('').focus();

        // If toggling filters closed, return to full "unfiltered" records
        if(!isOpened) {
            this.collection.fetch({limit: this.context.get('limit') || null });
        }
        return false;
    }
})
