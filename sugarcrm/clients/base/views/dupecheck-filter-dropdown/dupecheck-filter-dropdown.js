({
    extendsFrom: 'FilterFilterDropdownView',

    /**
     * {@inheritDoc}
     * Display 'DUPECHECK_FILTER_DEFAULT' for all record set label.
     */
    getTranslatedSelectionText: function(isAllRecords, label) {
        if (isAllRecords) {
            return app.lang.get('LBL_DUPECHECK_FILTER_DEFAULT', this.module);
        }
        return this._super('getTranslatedSelectionText', [isAllRecords, label]);
    }
})
