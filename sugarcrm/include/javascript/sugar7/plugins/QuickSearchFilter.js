/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
(function(app) {
    app.events.on('app:init', function() {
        app.plugins.register('QuickSearchFilter', ['layout', 'view', 'field'], {
            /**
             * Metadata about how quick search should be performed.
             *
             * @private
             */
            _moduleQuickSearchMeta: {},

            /**
             * Retrieve the highest priority quick search metadata.
             *
             * @param {string} searchModule Module name against which quick
             *   search is applied.
             *
             * @return {Object} Field names and whether to split search terms.
             * @private
             */
            _getQuickSearchMetaByPriority: function(searchModule) {
                var meta = app.metadata.getModule(searchModule),
                    filters = meta ? meta.filters : [],
                    fieldNames = [],
                    priority = 0,
                    splitTerms = false;

                _.each(filters, function(value) {
                    if (value && value.meta && value.meta.quicksearch_field &&
                        priority < value.meta.quicksearch_priority) {
                        fieldNames = value.meta.quicksearch_field;
                        priority = value.meta.quicksearch_priority;
                        if (_.isBoolean(value.meta.quicksearch_split_terms)) {
                            splitTerms = value.meta.quicksearch_split_terms;
                        }
                    }
                });

                return {
                    fieldNames: fieldNames,
                    splitTerms: splitTerms
                };
            },

            /**
             * Retrieve and cache the quick search metadata.
             *
             * @param {string} searchModule Module name against which quick
             *   search is applied.
             *
             * @return {Object} Quick search metadata (with highest priority).
             * @return {Array} return.fieldNames The fields to be used in
             *   quick search.
             * @return {Boolean} return.splitTerms Whether to split the search
             *   terms when there are multiple search fields.
             */
            getModuleQuickSearchMeta: function(searchModule) {
                this._moduleQuickSearchMeta[searchModule] = this._moduleQuickSearchMeta[searchModule] ||
                    this._getQuickSearchMetaByPriority(searchModule);
                return this._moduleQuickSearchMeta[searchModule];
            },

            /**
             * Retrieve just the array of field names for a quick search.
             *
             * @param {string} searchModule Module name against which quick
             *   search is applied.
             *
             * @return {Array} An array of field names for the searchModule.
             */
            getModuleQuickSearchFields: function(searchModule) {
                return this.getModuleQuickSearchMeta(searchModule).fieldNames;
            },

            /**
             * Get the filter definition based on quick search metadata.
             *
             * @param {string} searchModule Module name against which quick
             *   search is applied.
             * @param {string} searchTerm Search input entered.
             *
             * @return {Array} The search filter definition of quick search,
             *   otherwise an empty array.
             */
            getFilterDef: function(searchModule, searchTerm) {
                if (searchModule === 'all_modules' || !searchTerm) {
                    return [];
                }

                searchTerm = searchTerm.trim();

                var splitTermFilter;
                var filterList = [];
                var fieldNames = this.getModuleQuickSearchFields(searchModule);

                // Iterate through each field and check if the field is a simple
                // or complex field, and build the filter object accordingly
                _.each(fieldNames, function(name) {
                    if (!_.isArray(name)) {
                        var filter = this._buildSimpleFilter(name, '$starts', searchTerm);
                        if (filter) {
                            // Simple filters are pushed to `filterList`
                            filterList.push(filter);
                        }
                        return;
                    }

                    if (splitTermFilter) {
                        app.logger.error('Cannot have more than 1 split term filter');
                        return;
                    }
                    
                    // `splitTermFilter` is stored as a variable for a later push to `filterList`
                    splitTermFilter = this._buildSplitTermFilter(name, '$starts', searchTerm);
                }, this);

                // Push the split term filter
                if (splitTermFilter) {
                    filterList.push(splitTermFilter);
                }

                // If more than 1 filter was created, wrap them in `$or`
                if (filterList.length > 1) {
                    var filter = this._buildComplexFilter('$or', filterList);
                    if (filter) {
                        filterList = new Array(filter);
                    }
                }

                // FIXME [SC-3560]: This should be moved to the metadata
                if (searchModule === 'Users' || searchModule === 'Employees') {
                    filterList = this._simplifyFilter(filterList);
                    filterList = [{
                        '$and': [
                            {'status': {'$not_equals': 'Inactive'}},
                            filterList
                        ]
                    }];
                }

                return filterList || [];
            },

            /**
             * Returns the first filter from `filterList`, if the length of
             * `filterList` is 1.
             *
             * The *simplified* filter is in the form of the one returned by
             * @link{#_buildSimpleFilter} or @link{#_buildComplexFilter}.
             *
             * @param {Array} filterList An array of filter definitions.
             *
             * @return {Array|Object} First element of `filterList`, if the
             *   length of the array is 1, otherwise, the original `filterList`.
             * @private
             */
            _simplifyFilter: function(filterList) {
                return filterList.length > 1 ? filterList : filterList[0];
            },

            /**
             * Builds a `simple filter` object.
             *
             * A `simple filter` object is in the form of:
             *  @example
             *  { name: { operator: searchTerm } }
             *
             * @param {string} name Name of the field to search by.
             * @param {string} operator Operator to search by.
             * @param {string} searchTerm Search input entered.
             *
             * @return {Object} The search filter definition for quick search.
             * @private
             */
            _buildSimpleFilter: function(name, operator, searchTerm) {
                var def = {};
                var filter = {};
                filter[operator] = searchTerm;
                def[name] = filter;
                return def;
            },

            /**
             * Builds a `complex filter` object.
             *
             * A `complex filter` object is in the form of:
             *  @example
             *  { operator: filterList }
             *
             * @param {string} operator Operator to search by.
             * @param {Array} filterList Array of filters.
             *
             * @return {Object|Array} Complex filter object,
             *   or a simple filter object if `filterList` is of length 1,
             *   otherwise an empty `Array`.
             * @private
             */
            _buildComplexFilter: function(operator, filterList) {
                if (_.isEmpty(filterList)) { return []; }

                // if the length of the `filterList` is less than 2, then just return the simple filter
                if (filterList.length < 2) {
                    return filterList[0];
                }

                var filter = {};
                filter[operator] = filterList;
                return filter;
            },

            /**
             * Builds a filter object by using unique combination of the
             * searchTerm delimited by spaces.
             *
             * @param {Array} fieldNames Field within `quicksearch_field`
             *   in the metadata to perform split term filtering.
             * @param {string} operator Operator to search by for a field.
             * @param {string} searchTerm Search input entered.
             *
             * @return {Object|undefined} The search filter definition for
             *   quick search or `undefined` if no filter to apply or supported.
             * @private
             */
            _buildSplitTermFilter: function(fieldNames, operator, searchTerm) {
                if (fieldNames.length > 2) {
                    app.logger.error('Cannot have more than 2 fields in a complex filter');
                    return;
                }

                var filterList = [];

                // Splitting the search input is required only if there are more than 1 field
                if (fieldNames.length > 1) {
                    var tokens = searchTerm.split(' ');

                    // When the searchTerm is composed of at least 2 terms delimited by a space character,
                    // Divide the searchTerm in 2 unique sets
                    // e.g. For the name "Jean Paul Durand",
                    // first = "Jean", rest = "Paul Durand" (1st iteration)
                    // first = "Jean Paul", rest = "Durand" (2nd iteration)
                    for (var i = 1; i < tokens.length; ++i) {
                        var first = _.first(tokens, i).join(' ');
                        var rest = _.rest(tokens, i).join(' ');

                        // Push the 2 unique sets per field
                        _.each(fieldNames, function(name) {
                            filterList.push(this._buildSimpleFilter(name, operator, first));
                            filterList.push(this._buildSimpleFilter(name, operator, rest));
                        }, this);
                    }
                }

                // Try with full search term in each field
                // e.g. `first_name: Sangyoun Kim` or `last_name: Sangyoun Kim`
                _.each(fieldNames, function(name) {
                    filterList.push(this._buildSimpleFilter(name, operator, searchTerm));
                }, this);

                return this._buildComplexFilter('$or', filterList);
            }
        });
    });
})(SUGAR.App);
