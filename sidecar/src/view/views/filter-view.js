(function(app) {

    var requiredElapsed          = app.config.requiredElapsed,
        previousTerms            = {};

    function setPreviousTerm(term, module) {
        if(app.cache.has('previousTerms')) {
            previousTerms = app.cache.get('previousTerms');
        }
        if(module) {
            previousTerms[module] = term;
        }
        app.cache.set("previousTerms", previousTerms);
    }
    function getPreviousTerm(module) {
        if(app.cache.has('previousTerms')) {
            previousTerms = app.cache.get('previousTerms');
            return previousTerms[module];
        }
    }

    /**
     * View that displays a list of models pulled from the context's collection.
     * @class View.Views.FilterView
     * @alias SUGAR.App.layout.FilterView
     * @extends View.View
     */
    app.view.views.FilterView = app.view.View.extend({
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
                term, elapsed, timeleft, previousTerm, timerId;
                
            previousTerm = getPreviousTerm(this.module);
            term = self.$(evt.currentTarget).val();
            setPreviousTerm(term, this.module);

            if(term && term.length > 2) {
                if(timerId) { clearTimeout(timerId); }
                timerId = setTimeout(function() { 
                    self.fireSearchRequest(term);
                }, requiredElapsed);

            // If user removing characters and down to 2 chars reset table to all data
            } else if(previousTerm && term.length && term.length === 2 && term.length < previousTerm.length) {
                this.context.get('collection').fetch();

            // Edge case - just in case user might highlight the input and hit 'Back' to delete. 
            } else if(!term && evt.which === 8) {
                this.context.get('collection').fetch();
            }
        },

        fireSearchRequest: function(term) {
            setPreviousTerm(term, this.module);
            this.layout.trigger("list:search:fire", term);
        },

        toggleSearch: function() {
            var previousTerm = getPreviousTerm(this.module);
            this.$('.dataTables_filter').toggle();
            if(previousTerm) {
                this.$('.dataTables_filter input').val(previousTerm).focus();
            } else {
                this.$('.dataTables_filter input').focus();
            }
            return false;
        }
    });

})(SUGAR.App);
