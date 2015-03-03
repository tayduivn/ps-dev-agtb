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
 * @class View.Layouts.Base.GlobalSearchResultsLayout
 * @alias SUGAR.App.view.layouts.BaseGlobalSearchResultsListLayout
 * @extends View.Layout
 */
({
    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        var collection = this.collection = app.data.createMixedBeanCollection();
        this.context.set('collection', collection);
    },

    /**
     * @inheritDoc
     */
    loadData: function() {
        var searchTerm = this.context.get('searchTerm');
        this.fireSearchRequest(searchTerm);
    },

    /**
     * Fires the search request.
     *
     * @param {string} term The string to search for.
     */
    fireSearchRequest: function(term) {
        var self = this;
        var maxNum = app.config && app.config.maxSearchQueryResult ? parseInt(app.config.maxSearchQueryResult, 10) : 20;
        var params = {
            q: term,
            max_num: maxNum
        };

        app.api.search(params, {
            success: function(data) {
                if (self.disposed) {
                    return;
                }
                var formattedRecords = self.formatRecords(data.records);
                self.collection.reset(formattedRecords);
            },
            error: function(error) {
                app.error.handleHttpError(error);
                app.logger.error('Failed to fetch search results in search ahead. ' + error);
            }
        });
    },

    /**
     * Formats records sent by the globalsearch api.
     *
     * @param {Object[]} records The records to format.
     * @return {Object[]} formattedRecords The array of formatted records.
     */
    formatRecords: function(records) {
        var formattedRecords = [];
        _.each(records, function(record) {
            var module = app.metadata.getModule(record.data._module);

            record.highlights = _.map(record.highlights, function(val, key) {
                return {name: key, value: new Handlebars.SafeString(val), label: module.fields[key].vname};
            });
            model.set('_highlights', highlights);

            //FIXME: We shouldn't do that because it only applies for person
            // object, SC-4196 will fix it.
            if (!record.data.name) {
                record.data.name = record.data.first_name + ' ' + record.data.last_name;
            }

            var formattedRecord = {
                id: record.data.id,
                name: record.data.name,
                link: true,
                first_name: record.data.first_name,
                last_name: record.data.last_name,
                module: record.data._module,
                _module: record.data._module,
                route: '#' + app.router.buildRoute(record.data._module, record.data.id),
                highlights: record.highlights
            };
            formattedRecords.push(formattedRecord);
        });

        return formattedRecords;
    }
})
