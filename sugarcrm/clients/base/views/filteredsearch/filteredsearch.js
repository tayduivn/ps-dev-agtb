({
    events: {
        'keyup [data-searchfield]': 'searchFired'
    },

    /**
     * {@inheritDoc}
     * Update searchable fields.
     */
    bindDataChange: function() {
        this.context.on('filteredlist:filter:set', this.setFilter, this);
    },

    /**
     * Update quick search placeholder to display searchable fields.
     * @param {Array} filter list of field name.
     */
    setFilter: function(filter) {
        var label = app.lang.get('LBL_SEARCH_BY') + ' ' + filter.join(', ') + '...';
        this.$('[data-searchfield]').attr('placeholder', label);
    },

    /**
     * Updated current typed search term.
     */
    searchFired: _.debounce(function(evt) {
        var value = $(evt.currentTarget).val();
        this.context.trigger('filteredlist:search:fired', value);
    }, 100)
})
