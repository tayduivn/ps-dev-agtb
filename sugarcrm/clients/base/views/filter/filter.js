/**
 * View that displays a Bar with module name and filter toggles for per module
 * search and module creation.
 *
 * @class View.Views.FilterView
 * @alias SUGAR.App.layout.FilterView
 * @extends View.View
 */
({
    previousTerms: {},
    events: {
        'keyup .dataTables_filter input': 'filterList'
    },
    _renderSelf: function() {
        // this needs to be reset every render because the field set on the collection changes
        this.searchFields = this.getSearchFields();
        app.view.View.prototype._renderSelf.call(this);
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
                fMeta.label = app.lang.get(fMeta.vname, self.module);
                results.push(fieldMeta);
            }
        });
        return results;
    },
    filterList: function(evt) {
        var self = this,
            term, previousTerm;

        previousTerm = self.getPreviousTerm(this.module);
        term = self.$(evt.currentTarget).val();
        self.setPreviousTerm(term, this.module);

        if(term && term.length > 2) {
            _.delay(function() {
                self.fireSearchRequest(term);
            }, app.config.requiredElapsed);

        // If user removing characters and down to 2 chars reset table to all data
        } else if(previousTerm && term.length && term.length === 2 && term.length < previousTerm.length) {
            this.collection.fetch({limit: this.context.get('limit') || null });

        // Edge case - just in case user might highlight the input and hit 'Back' to delete.
        } else if(!term && evt.which === 8) {
            this.collection.fetch({limit: this.context.get('limit') || null });
        }
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
        this._renderSelf();
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
