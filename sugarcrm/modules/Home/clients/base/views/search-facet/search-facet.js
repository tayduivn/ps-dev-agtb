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
 * @class View.Views.Base.Home.SearchFacetView
 * @alias SUGAR.App.view.views.BaseSearchFacetView
 * @extends View.View
 */
({
    events: {
        'click [data-facet]': 'itemClicked'
    },

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.facetType = this.meta.facet_type;
        this.facetItems = [];

        if (this.context.get('facets') && this.context.get('facets')[this.facetType]) {
            this.parseFacetsData(this.context);
        }

        this.bindFacetsEvents();
    },

    /**
     * Binds context events related to facets changes.
     */
    bindFacetsEvents: function() {
        this.context.on('facets:added', this.parseFacetsData, this);
    },

    /**
     * Parses facets data and renders the view.
     *
     * @param {Data.Bean} context The context.
     */
    parseFacetsData: function(context) {
        var currentFacet = context.get('facets')[this.facetType];
        if (_.isUndefined(currentFacet)) {
            app.logger.warn('The facet type : ' + this.facetType + 'is not returned by the server.' +
                ' Therefore, the facet dashlet will have no data.');
            return;
        }
        var results = currentFacet.results;
        // If no buckets are defined we are in the case of `Assigned to me`,
        // `Created by me`, `Modified by me` or `My favorites` facet.
        if (currentFacet.type === 'my_items') {
            this.facetItems = [{
                key: Object.keys(currentFacet)[0],
                label: app.lang.get(this.meta.label, 'Filters'),
                count: currentFacet.results.count
            }];
            this.render();
            return;
        }
        var labelFunction = this._getDefaultLabel;

        _.each(results, function(val, key) {
            if (val > 0) {
                this.facetItems.push({key: key, label: labelFunction(key), count: val});
            }
        }, this);

        this.facetItems = _.sortBy(this.facetItems, 'count').reverse();
        this.render();
    },

    /**
     * Selects or unselect a facet item.
     *
     * @param {Event} event The `click` event.
     */
    itemClicked: function(event) {
        this.$(event.currentTarget).toggleClass('selected');
        var contextFacets = this.context.get('facets');

        // Here we need to remove or add facet items from the context. Then in
        // search.js we'll listen to `change:facets` and then trigger a new search.
        var facetItemToToggle = this.$(event.currentTarget).data('facet');
        if (!_.isUndefined(contextFacets[this.facetType].buckets)) {
            facetItemToToggle = _.find(contextFacets[this.facetType].buckets, function(bucket) {
                return bucket.key === facetItemToToggle;
            });
        }
        facetItemToToggle.disabled = true;

        this.context.set('facets', contextFacets);
        this.context.trigger('change:facets', facetItemToToggle);
    },

    /**
     * Gets the bucket key to use it as a label.
     *
     * @param {Object} bucket The facet item.
     * @return {string} The label for this item.
     * @private
     */
    _getDefaultLabel: function(key) {
        return app.lang.getModuleName(key, {plural: true});
    }
})
