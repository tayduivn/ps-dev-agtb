({
    tagName: 'input',

    className: 'search-name',

    attributes: {
        'type': 'text',
        'placeholder': app.lang.get('LBL_BASIC_SEARCH') + '…'
    },

    events: {
        "keyup": "throttledSearch",
        "paste": "throttledSearch"
    },

    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);
        this.layout.on("filter:clear:quicksearch", this.clearInput, this);
    },

    throttledSearch: _.debounce(function(e) {
        var newSearch = this.$el.val();
        if(this.currentSearch !== newSearch) {
            this.currentSearch = newSearch;
            this.layout.trigger("filter:change:quicksearch", newSearch);
        }
    }, 400),

    clearInput: function() {
        this.$el.val("");
        this.currentSearch = "";
        this.layout.trigger("filter:change:quicksearch");
    }
})
