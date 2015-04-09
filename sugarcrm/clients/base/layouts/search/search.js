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

        this.context.on('change:searchTerm change:module_list', function(context) {
            //TODO: collection.fetch shouldn't need a query to be passed. Will
            // be fixed by SC-3973.
            var searchTerm = this.context.get('searchTerm');
            var moduleList = this.context.get('module_list') || [];
            this.collection.fetch({query: searchTerm, module_list: moduleList, params: {xmod_aggs: true}});
        }, this);

        this.context.on('facet:apply', function(facetType, facetCriteriaId, isSingleItem) {
            this.applyFacetChange(facetType, facetCriteriaId, isSingleItem);
            //Here we'll trigger the search passing the facetFilters object in the query.
            var searchTerm = this.context.get('searchTerm');
            var moduleList = this.context.get('module_list') || [];
            this.collection.fetch({query: searchTerm, module_list: moduleList, params: {xmod_aggs: true},
                apiOptions:
                {
                    data: {agg_filters: this.facetFilters},
                    fetchWithPost: true
                }
            });
        }, this);
        this.collection.on('sync', function(collection, data, options) {
            var isCollection = (collection instanceof App.BeanCollection);
            if (!isCollection) {
                return;
            }
            app.utils.GlobalSearch.formatRecords(collection, true);

            if (!_.isEmpty(options.xmod_aggs)) {
                this.facetFilters = this._buildFiltersObject(options.xmod_aggs);
                this.context.set('facets', options.xmod_aggs);
            }

        }, this);
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
                facetFilters[key] = Object.keys(facet.results);
            } else {
                facetFilters[key] = true;
            }
        }, this);
        return facetFilters;
    },

    /**
     * Updates {@link #facetFilters} with the facet change.
     *
     * @param {String} facetType The facet type.
     * @param facetCriteriaId The id of the facet criteria.
     * @param isSingleItem `true` if it's a single item facet.
     */
    applyFacetChange: function(facetType, facetCriteriaId, isSingleItem) {
        if (isSingleItem) {
            this.facetFilters[facetType] = !this.facetFilters[facetType];
        } else {
            var index = this.facetFilters[facetType].indexOf(facetCriteriaId);
            if (index === -1) {
                this.facetFilters[facetType].splice(0, 0, facetCriteriaId);
            } else {
                this.facetFilters[facetType].splice(index, 1);
            }
        }
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
