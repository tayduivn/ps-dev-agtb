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
        'click [data-facet-criteria]': 'itemClicked'
    },

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.facetId = this.meta.facet_id;
        this.isSingleItem = this.meta.ui_type === 'single';
        this.facetItems = [];

        if (this.context.get('facets') && this.context.get('facets')[this.facetId]) {
            this.parseFacetsData(this.context);
        }

        this.bindFacetsEvents();
    },

    /**
     * Binds context events related to facets changes.
     */
    bindFacetsEvents: function() {
        this.context.on('change:facets', this.parseFacetsData, this);
    },

    /**
     * Parses facets data and renders the view.
     *
     * @param {Data.Bean} context The context.
     */
    parseFacetsData: function(context) {
        var currentFacet = context.get('facets')[this.facetId];
        if (_.isUndefined(currentFacet)) {
            app.logger.warn('The facet type : ' + this.facetId + 'is not returned by the server.' +
                ' Therefore, the facet dashlet will have no data.');
            return;
        }
        var results = currentFacet.results;

        if (this.isSingleItem) {
            this.facetItems = [{
                key: this.facetId,
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
        var facetCriteriaId = this.$(event.currentTarget).data('facet-criteria');

        this.$(event.currentTarget).toggleClass('selected');

        this.context.trigger('facet:apply', this.facetId, facetCriteriaId, this.isSingleItem);
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
