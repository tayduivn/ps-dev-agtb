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
/**
 * Layout for the global search results page.
 *
 * @class View.Layouts.Base.SearchLayout
 * @alias SUGAR.App.view.layouts.BaseSearchLayout
 * @extends View.Layout
 */
({
    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.context.set('search', true);
        this.collection.query = this.context.get('searchTerm') || '';
        this.collection.neededResponseProperties = ['xmod_aggs'];
        this.filteredSearch = false;

        this.context.on('change:searchTerm change:module_list', function() {
            this.search();
        }, this);

        this.context.on('facet:apply', this.filter, this);

        this.collection.on('sync', function(collection, data, options) {
            var isCollection = (collection instanceof App.BeanCollection);
            if (!isCollection) {
                return;
            }
            app.utils.GlobalSearch.formatRecords(collection, true);

            if (!_.isEmpty(options.xmod_aggs)) {
                if (!this.filteredSearch) {
                    this.selectedFacets = this._buildFiltersObject(options.xmod_aggs);
                }

                this.context.set('selectedFacets', this.selectedFacets);
                this.context.set('facets', options.xmod_aggs, {silent: true});
                this.context.trigger('facets:change', options.xmod_aggs);
            }

        }, this);

        this.context.on('facets:reset', this.search, this);
    },

    /**
     * Builds the filter object to be sent to the server.
     *
     * @param {Object} facets The facets object that comes from the server.
     * @return {Object} facetFilters The formatted object to send to the server.
     * @private
     */
    _buildFiltersObject: function(facets) {
        var facetFilters = {};
        _.each(facets, function(facet, key) {
            if (key === 'modules') {
                facetFilters[key] = [];
            } else {
                facetFilters[key] = false;
            }
        }, this);
        return facetFilters;
    },

    /**
     * Updates {@link #facetFilters} with the facet change.
     *
     * @param {String} facetId The facet type.
     * @param facetCriteriaId The id of the facet criteria.
     * @param isSingleItem `true` if it's a single item facet.
     * @private
     */
    _updateFilters: function(facetsObject, facetId, facetCriteriaId, isSingleItem) {
        if (isSingleItem) {
            facetsObject[facetId] = !facetsObject[facetId];
        } else {
            var index;
            if (!facetsObject[facetId]) {
                facetsObject[facetId] = [];
            } else {
                index = facetsObject[facetId].indexOf(facetCriteriaId);
            }
            if (_.isUndefined(index) || index === -1) {
                facetsObject[facetId].splice(0, 0, facetCriteriaId);
            } else {
                facetsObject[facetId].splice(index, 1);
                if (facetsObject[facetId].length === 0) {
                    delete facetsObject[facetId];
                }
            }
        }
    },

    /**
     * Searches on a term and a module list.
     *
     * @param {boolean} reset `true` if we reset the filters.
     */
    search: function(reset) {
        // Prevents to trigger a new fetch if the user clicks on
        if (reset && !this.filteredSearch) {
            return;
        }
        var searchTerm = this.context.get('searchTerm');
        var moduleList = this.context.get('module_list') || [];
        this.filteredSearch = false;

        //TODO: collection.fetch shouldn't need a query to be passed. Will
        // be fixed by SC-3973.
        this.collection.fetch({query: searchTerm, module_list: moduleList, params: {xmod_aggs: true}});
    },

    /**
     * Refines the search applying a facet change.
     *
     * @param facetId The facet id.
     * @param facetCriteriaId The facet criteria id.
     * @param isSingleItem `true` if it's a single criteria facet.
     */
    filter: function(facetId, facetCriteriaId, isSingleItem) {
        this._updateFilters(this.selectedFacets, facetId, facetCriteriaId, isSingleItem);

        var searchTerm = this.context.get('searchTerm');
        var moduleList = this.context.get('module_list') || [];
        this.filteredSearch = true;
        this.collection.fetch({query: searchTerm, module_list: moduleList, params: {xmod_aggs: true},
            apiOptions:
            {
                data: {agg_filters: this.selectedFacets},
                fetchWithPost: true
            }
        });
    },

    /**
     * We override `loadData` to not send the `fields` param in the
     * request, so it's consistent with the request sent by
     * {@link View.Views.Base.QuicksearchBarView#fireSearchRequest fireSearchRequest}
     * method in the quicksearch bar.
     * Note that the `fields` param is not used anymore by the globalsearch API.
     *
     * @inheritDoc
     */
    loadData: function(options, setFields) {
        setFields = false;
        options = options || {};
        options.params = {};
        options.params.xmod_aggs = true;
        this._super('loadData', [options, setFields]);
    }
})
