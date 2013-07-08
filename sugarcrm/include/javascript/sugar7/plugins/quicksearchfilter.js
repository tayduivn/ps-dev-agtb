(function(app) {
    app.events.on("app:init", function() {
        app.plugins.register('quicksearchfilter', ['layout', 'view', 'field'], {
            _moduleSearchFields: {},
            _getQuickSearchFieldsByPriority: function(searchModule) {
                var meta = app.metadata.getModule(searchModule),
                    filters = meta ? meta.filters : [],
                    fields = [],
                    priority = 0;

                _.each(filters, function(value) {
                    if (value && value.meta && value.meta.quicksearch_field &&
                        priority < value.meta.quicksearch_priority) {
                        fields = value.meta.quicksearch_field;
                        priority = value.meta.quicksearch_priority;
                    }
                });

                return fields;
            },
            getModuleQuickSearchFields: function(searchModule) {
                this._moduleSearchFields[searchModule] = this._moduleSearchFields[searchModule] ||
                    this._getQuickSearchFieldsByPriority(searchModule);
                return this._moduleSearchFields[searchModule];
            },
            getFilterDef: function(searchModule, searchTerm) {
                var searchFilter = [], returnFilter = [], fieldNames, terms;

                //Special case where no specific module is selected
                if (searchModule === 'all_modules') {
                    return returnFilter;
                }
                // We allow searching based on the basic search filter.
                // For example, the Contacts module will search the records
                // whose first name or last name begins with the typed string.
                // To extend the search results, you should update the metadata for basic search filter
                fieldNames = this.getModuleQuickSearchFields(searchModule);

                if (searchTerm) {
                    if (fieldNames.length > 1) {
                        terms = searchTerm.split(' ');
                    }
                    _.each(fieldNames, function(name) {
                        if (terms) {
                            _.each(terms, function(term) {
                                var o = {};
                                o[name] = {'$starts': term};
                                searchFilter.push(o);
                            });
                        } else {
                            var o = {};
                            o[name] = {'$starts': searchTerm};
                            searchFilter.push(o);
                        }
                    });
                    returnFilter.push(searchFilter.length > 1 ? {'$or': searchFilter} : searchFilter[0]);
                }
                return returnFilter;
            }
        });
    });
})(SUGAR.App);
