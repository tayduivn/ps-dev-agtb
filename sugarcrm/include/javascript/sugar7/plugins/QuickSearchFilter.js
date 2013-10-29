/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
(function(app) {
    app.events.on("app:init", function() {
        app.plugins.register('QuickSearchFilter', ['layout', 'view', 'field'], {
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
                    //See SP-1093.
                    //For Person Type modules, need to split the terms and build a smart filter definition
                    if (fieldNames.length === 2) {
                        terms = searchTerm.split(' ');
                        var firstTerm = _.first(terms.splice(0, 1));
                        var otherTerms = terms.join(' ');
                        //First field starts with first term, second field starts with other terms
                        //If only one term, use $or and search for the term on both fields
                        terms = otherTerms ? [firstTerm, otherTerms] : null;
                    } else if (fieldNames.length > 2) {
                        app.logger.fatal('Filtering by 3 quicksearch fields is not yet supported.');
                    }
                    _.each(fieldNames, function(name, index) {
                        var o = {};
                        if (terms) {
                            o[name] = {'$starts': terms[index]};
                        } else {
                            o[name] = {'$starts': searchTerm};
                        }
                        searchFilter.push(o);
                    });
                    if (terms) {
                        returnFilter.push(searchFilter.length > 1 ? {'$and': searchFilter} : searchFilter[0]);
                    } else {
                        returnFilter.push(searchFilter.length > 1 ? {'$or': searchFilter} : searchFilter[0]);
                    }

                    // See MAR-1362 for details.
                    if (searchModule === 'Users' || searchModule === 'Employees') {
                        returnFilter[0] = ({
                            '$and': [
                                {'status': {'$not_equals': 'Inactive'}},
                                returnFilter[0]
                            ]
                        });
                    }
                }
                return returnFilter;
            }
        });
    });
})(SUGAR.App);
