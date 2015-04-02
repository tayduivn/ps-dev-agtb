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

        this.collection.query = this.context.get('searchTerm') || '';
        this.collection.module_list = [];

        this.context.on('change:searchTerm', function(context, searchTerm) {
            //TODO: collection.fetch shouldn't need a query to be passed. Will
            // be fixed by SC-3973.
            this.context.set('searchTerm', searchTerm);
            this.collection.fetch({query: searchTerm});
        }, this);

        this.collection.on('sync', function(collection, data) {
            var isCollection = (collection instanceof App.BeanCollection);
            if (!isCollection) {
                return;
            }
            app.utils.GlobalSearch.formatRecords(collection, true);
//            collection.facets = data.facets;
//            this.context.set('facets', data.facets);
        }, this);
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
        this._super('loadData', [options, setFields]);
    }
})
