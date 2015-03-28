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

        this.context.on('change:searchTerm change:module_list', function(context) {
            //TODO: collection.fetch shouldn't need a query to be passed. Will
            // be fixed by SC-3973.
            var searchTerm = this.context.get('searchTerm');
            var moduleList = this.context.get('module_list') || [];
            this.collection.fetch({query: searchTerm, module_list: moduleList});
        }, this);

        this.context.on('change:facets', function(model) {
            //Here we'll trigger the search passing the updated facets in the query.
        }, this);
        this.collection.on('sync', function(collection, data) {
            var isCollection = (collection instanceof App.BeanCollection);
            if (!isCollection) {
                return;
            }
            app.utils.GlobalSearch.formatRecords(collection, true);
            //Fake aggregations.
            data.aggregations = {
                modules: {
                    type: 'terms_modules',
                    results:
                    {
                        Contacts: 5,
                        Leads: 5
                    }
                },
                assigned_to_me: {
                    type: 'my_items',
                    results: {
                        count: 10
                    }
                },
                created_by_me: {
                    type: 'my_items',
                    results: {
                        count: 10
                    }
                },
                modified_by_me: {
                    type: 'my_items',
                    results: {
                        count: 12
                    }
                },
                my_favorites: {
                    type: 'my_items',
                    results: {
                        count: 12
                    }
                },
                date_modified: {
                    type: 'date_range',
                    'results':
                    {
                        last_year: 4,
                        this_year: 13,
                        this_month: 1
                    }
                },
                date_closed: {
                    type: 'date_range',
                    'results':
                    {
                        last_quarter: 5,
                        this_quarter: 10,
                        next_quarter: 8
                    }
                }
            };

            collection.facets = data.aggregations;
            this.context.set('facets', data.aggregations, {silent: true});
            this.context.trigger('facets:added', this.context);
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
