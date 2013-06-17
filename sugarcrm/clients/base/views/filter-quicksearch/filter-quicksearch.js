({
    /**
     * View for doing a quick search.
     * Part of BaseFilterLayout
     *
     * @class BaseFilterQuicksearchView
     * @extends View
     */

    tagName: 'input',

    className: 'search-name',

    attributes: {
        'type': 'text'
    },

    events: {
        "keyup": "throttledSearch",
        "paste": "throttledSearch"
    },

    /**
     * @override
     * @param {Object} opts
     */
    initialize: function(opts) {
        // We cannot set the placeholder in the attributes hash, as we may not
        // have SUGAR.App when the constructor is called. We can't add it to the
        // attributes hash here since Backbone.View._ensureElement() is called
        // before initialize.
        this.$el.attr('placeholder', app.lang.get('LBL_BASIC_QUICK_SEARCH'));
        app.view.View.prototype.initialize.call(this, opts);
        this.layout.on("filter:clear:quicksearch", this.clearInput, this);
    },

    /**
     * Fire quick search
     * @param {Event} e
     */
    throttledSearch: _.debounce(function(e) {
        var newSearch = this.$el.val();
        if(this.currentSearch !== newSearch) {
            this.currentSearch = newSearch;
            this.layout.trigger("filter:apply", newSearch);
        }
    }, 400),

    /**
     * Clear input
     */
    clearInput: function() {
        this.$el.val("").toggleClass('hide', this.layout.showingActivities);
        this.currentSearch = "";
        this.layout.trigger("filter:apply");
    }
})
