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
({
    extendsFrom:'GlobalsearchView',
    _renderHtml: function() {
        if (!app.api.isAuthenticated() || app.config.appStatus == 'offline') return;

        app.view.View.prototype._renderHtml.call(this);

        // Search ahead drop down menu stuff
        var self = this,
            menuTemplate = app.template.getView(this.name + '.result');

        this.$('.search-query').searchahead({
            context: self,
            request: function(term) {
                self.fireSearchRequest(term);
            },
            compiler: menuTemplate,
            throttleMillis: (app.config.requiredElapsed || 500),
            throttle: function(callback, millis) {
                if(!self.debounceFunction) {
                    self.debounceFunction = _.debounce(function(){
                        callback();
                    }, millis || 500);
                }
                self.debounceFunction();
            },
            onEnterFn: function(hrefOrTerm, isHref) {
                if(isHref) {
                   window.location = hrefOrTerm;
                } else {
                    // It's the term only (user didn't select from drop down
                    // so this is essentially the term typed
                    var term = $.trim(self.$('.search-query').attr('value'));
                    if (!_.isEmpty(term)) {
                        self.fireSearchRequest(term, this);
                    }
                }
            }
        });
        // Prevent the form from being submitted
        this.$('.navbar-search').submit(function() {
            return false;
        });
    },
    /**
     * Populates search modules from displayable modules, taking acls and globalSearchEnabled in to account
     */
    populateModules: function() {
        if (this.disposed) {
            return;
        }
        var modules = app.metadata.getModules() || {};
        var moduleNames = app.metadata.getModuleNames({filter: 'display_tab'}); // visible
        this.searchModules = this.populateSearchableModules({
            modules: modules,
            moduleNames: moduleNames,
            acl: app.acl,
            // Unlike sugar7, today, portal doesn't use ftsEnabled but instead any visible modules that
            // are also globalSearchEnabled (e.g. Home should have not be global search enabled)
            checkFtsEnabled: false,
            checkGlobalSearchEnabled: true
        });
        this.render();
    },
    // TODO if we are extending this, why are we duplicating almost everything on this code?
    fireSearchRequest: function (term) {
        var self = this,
            searchModuleNames = this._getSearchModuleNames(),
            maxNum = app.config && app.config.maxSearchQueryResult ? app.config.maxSearchQueryResult : 5,
            params = {
                q: term,
                fields: 'name, id',
                module_list: searchModuleNames.join(","),
                max_num: maxNum
            };


        app.api.search(params, {
            success:function(data) {
                var formattedRecords = [],
                    modList = app.metadata.getModuleNames({filter: 'quick_create', action: 'create'}),
                    moduleIntersection = _.intersection(modList, self.searchModules);
                _.each(data.records, function(record) {
                    if (!record.id) {
                        return; // Elastic Search may return records without id and record names.
                    }
                    var formattedRecord = {
                        id: record.id,
                        name: record.name,
                        module: record._module,
                        link: '#' + app.router.buildRoute(record._module, record.id)
                    };

                    if ((record._search.highlighted)) { // full text search
                        _.each(record._search.highlighted, function(val, key) {
                            if (key !== 'name') { // found in a related field
                               formattedRecord.field_name = app.lang.get(val.label, val.module);
                               formattedRecord.field_value = val.text;
                            }
                        });
                    }
                    formattedRecords.push(formattedRecord);
                });
                self.$('.search-query').searchahead('provide', {module_list: moduleIntersection, next_offset: data.next_offset, records: formattedRecords});
            },
            error:function(error) {
                app.error.handleHttpError(error, plugin);
                app.logger.error("Failed to fetch search results in search ahead. " + error);
            }
        });
    },
    /**
     * Show full search results when the search button is clicked
     * (Show searchahead results for sugarcon because we don't have full results page yet)
     */
    gotoFullSearchResultsPage: function(evt) {
        var term;
        // Force navigation to full results page and don't let plugin get control
        evt.preventDefault();
        evt.stopPropagation();
        // URI encode search query string so that it can be safely
        // decoded by search handler (bug55572)
        term = encodeURIComponent(this.$('.search-query').val());
        if(term && term.length) {
            // Bug 57853 Shouldn't show the search result pop up window after click the global search button.
            // This prevents anymore dropdowns (note we re-init if/when _renderHtml gets called again)
            this.$('.search-query').searchahead('disable', 1000);
            app.router.navigate('#search/'+term, {trigger: true});
        }
    }
})
