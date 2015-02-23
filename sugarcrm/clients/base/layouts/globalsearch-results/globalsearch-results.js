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
    initialize: function(options) {
        this._super('initialize', [options]);
        var collection = this.collection = app.data.createMixedBeanCollection();
        this.context.set('collection', collection);
    },

    loadData: function() {
        var searchTerm = this.context.get('searchTerm');
        this.fireSearchRequest(searchTerm);
    },

    fireSearchRequest: function(term) {
           var self = this;
           var maxNum = app.config && app.config.maxSearchQueryResult ? app.config.maxSearchQueryResult : 20,
            params = {
                q: term,
                fields: 'name, id',
//                module_list: moduleList,
                max_num: maxNum
            };
        app.api.search(params, {
            success: function(data) {
                var formattedRecords = [];
                _.each(data.records, function(record) {
                    if (!record.id) {
                        return; // Elastic Search may return records without id and record names.
                    }
                    var formattedRecord = {
                        id: record.id,
                        name: record.name,
                        module: record._module,
                        _module: record._module,
                        route: '#' + app.router.buildRoute(record._module, record.id),
                        date_modified: record.date_modified
                    };

//                    if ((record._search.highlighted)) { // full text search
//                        _.each(record._search.highlighted, function(val, key) {
//                            var safeString = self._escapeSearchResults(val.text);
//                            if (key !== 'name') { // found in a related field
//                                formattedRecord.field_name = app.lang.get(val.label, val.module);
//                                formattedRecord.field_value = safeString;
//                            // if it is a name that is found, we need to replace the name with the highlighted text
//                            } else {
//                                formattedRecord.name = safeString;
//                            }
//                        });
//                    }
                    formattedRecords.push(formattedRecord);
                });
                self.collection.reset(formattedRecords);
            },
            error: function(error) {
                app.error.handleHttpError(error);
                app.logger.error('Failed to fetch search results in search ahead. ' + error);
            }
        });
    }
})
