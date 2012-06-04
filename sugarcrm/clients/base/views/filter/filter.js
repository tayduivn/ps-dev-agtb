/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.FilterView
 * @alias SUGAR.App.layout.FilterView
 * @extends View.View
 */
({
    previousTerms: {},
    events: {
        'keyup .dataTables_filter input': 'filterList'
    },
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
    },
    render: function() {
        var self = this;
        app.view.View.prototype.render.call(self);
        self.layout.off("list:search:toggle", null, this);
        self.layout.on("list:search:toggle", self.toggleSearch, this);
    },
    filterList: function(evt) {
        var self = this,
            term, elapsed, timeleft, previousTerm, timerId, throttled;
            
        previousTerm = self.getPreviousTerm(this.module);
        term = self.$(evt.currentTarget).val();
        self.setPreviousTerm(term, this.module);

        if(term && term.length > 2) {
            _.delay(function() {
                self.fireSearchRequest(term);
            }, app.config.requiredElapsed);
            
        // If user removing characters and down to 2 chars reset table to all data
        } else if(previousTerm && term.length && term.length === 2 && term.length < previousTerm.length) {
            this.context.get('collection').fetch();

        // Edge case - just in case user might highlight the input and hit 'Back' to delete. 
        } else if(!term && evt.which === 8) {
            this.context.get('collection').fetch();
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
        var previousTerm = this.getPreviousTerm(this.module);
        this.$('.dataTables_filter').toggle();
        if(previousTerm) {
            this.$('.dataTables_filter input').val(previousTerm).focus();
        } else {
            this.$('.dataTables_filter input').focus();
        }
        return false;
    }
})
