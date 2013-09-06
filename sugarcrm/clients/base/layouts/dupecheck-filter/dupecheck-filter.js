({
    extendsFrom: 'FilterLayout',

    /**
     * {@inheritDoc}
     * Override getting relevant context logic in order to
     * filter on current context.
     */
    getRelevantContextList: function() {
        return [this.context];
    },

    /**
     * {@inheritDoc}
     * Deactivate stickness on find duplicate filter.
     */
    setLastFilter: function() {
        return '';
    },

    /**
     * {@inheritDoc}
     * Override getting last filter in order to
     * retrieve found duplicates for initial set.
     */
    getLastFilter: function() {
        return 'all_records';
    }
})
