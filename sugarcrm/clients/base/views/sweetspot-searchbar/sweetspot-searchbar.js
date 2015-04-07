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
 * @class View.Views.Base.SweetspotSearchbarView
 * @alias SUGAR.App.view.views.BaseSweetspotSearchbarView
 * @extends View.View
 */
({
    className: 'sweetspot-searchbar',
    events: {
        'keyup input': 'throttledSearch',
        'click [data-action=configure]': 'initConfig'
    },

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        app.events.on('app:sync:complete sweetspot:reset', this.initLibrary, this);
        this.lastTerm = '';
    },

    initConfig: function(evt) {
        this.layout.toggle();
        this.layout.trigger('sweetspot:config');
    },

    /**
     * Initializes the libraries.
     *
     * - Adds all the mega menu actions to {@link #internalLibrary}.
     */
    initLibrary: function() {
        /**
         * Static library.
         *
         * Contains mega menu actions and system commands.
         *
         * @type {Array}
         */
        this.internalLibrary = [];

        /**
         * Temporary library.
         *
         * Contains records from the search API, but these are only kept for
         * 5 minutes (for better user experience).
         *
         * @type {Object}
         */
        this.temporaryLibrary = {};

        this.addToInternalLibrary(this.getSweetspotActions());

        this.internalLibrary = this._formatInternalLib();
    },

    /**
     * Formats the {@link #internalLibrary internal library} by parsing it to
     * remove duplicate results.
     *
     * @return {Array} The formatted library of actions/commands.
     */
    _formatInternalLib: function() {
        var lib = _.chain(this.internalLibrary)
            .map(function(item) {
                return JSON.stringify(item);
            })
            .uniq()
            .map(function(item) {
                return JSON.parse(item)
            })
            .value();

        return lib;
    },

    getSweetspotActions: function() {
        var actionsById = app.metadata.getSweetspotActions();
        var data = app.user.getPreference('sweetspot');

        _.each(data, function(customSetting) {
            if (!actionsById[customSetting.action]) {
                return;
            }
            actionsById[customSetting.action].keyword = customSetting.keyword;
        });
        return _.flatten(actionsById);
    },

    /**
     * Adds some items to the {@link #internalLibrary}.
     *
     * @param {Array} items Items to add.
     */
    addToInternalLibrary: function(items) {
        this.internalLibrary = this.internalLibrary.concat(items);
    },

    /**
     * Adds some records to the {@link #temporaryLibrary}
     *
     * @param {Array} items Records to add.
     */
    addToTemporaryLibrary: function(items) {
        _.each(items, function(item) {
            this.temporaryLibrary[item.id] = item;
        }, this);
    },

    /**
     * Gets the records from the {@link #temporaryLibrary}.
     *
     * Records that are here for more than 5 minutes are removed.
     *
     * @return {Array} The list of records.
     */
    getTemporaryLibrary: function() {
        var now = new Date().getTime();
        var tooOld = now - 300000;
        var updatedLibrary = {};
        var recordList = [];
        _.each(this.temporaryLibrary, function(item) {
            if (item.timestamp > tooOld) {
                updatedLibrary[item.id] = item;
                recordList.push(item);
            }
        });
        this.temporaryLibrary = updatedLibrary;
        return recordList;
    },

    /**
     * Gets the library to perform the search.
     *
     * Concats {@link #internalLibrary} and {@link #temporaryLibrary}.
     *
     * @returns {Array} The list of items to perform the search.
     */
    getLibrary: function() {
        return this.internalLibrary.concat(this.getTemporaryLibrary());
    },



    /**
     * Triggers the search and send results.
     *
     * @param {boolean} later `true` if triggered from the search API callback.
     */
    applyQuickSearch: function(later) {
        var term = this.$('input').val();
        if (!later && term === this.lastTerm) {
            return;
        }
        var results = [];
        if (!later && !_.isEmpty(term)) {
            this.fireSearchRequest(term);
        }
        if (!_.isEmpty(term)) {
            results = this.doSearch(term);
        }
        this.sendResults(results);
        this.lastTerm = term;
    },

    /**
     * Performs the actual search in the library.
     *
     * @param {string} term The term to search
     * @return {Array} Hopefully a list of results.
     */
    doSearch: function(term) {
        var options = {
            keys: ['keyword', 'module', 'name'],
            threshold: '0.1'
        };
        this.fuse = new Fuse(this.getLibrary(), options);
        var results = this.fuse.search(term);
        results = results.slice(0, 6);
        results = _.sortBy(results, 'weight');
        return results;
    },

    /**
     * Triggers `sweetspot:results` with the results of the search.
     *
     * @param {Array} Hopefully a list of results.
     */
    sendResults: function(results) {
        this.layout.trigger('sweetspot:results', results);
    },

    /**
     * Calls {@link #applyQuickSearch} with a debounce of 200ms.
     */
    throttledSearch: _.debounce(function(event) {
        this.applyQuickSearch();
    }, 200),

    /**
     * Makes a request to the search API to find records.
     *
     * On success it calls {@link #addToTemporaryLibrary} to add the records
     * to the temporary library and calls {@link #applyQuickSearch} to re-apply
     * the search.
     *
     * @param {string} term The search term.
     */
    fireSearchRequest: function(term) {
        var self = this;
        var params = {
            q: term,
            fields: 'name, id',
            max_num: 5
        };
        app.api.search(params, {
            success: function(data) {
                var now = new Date().getTime();
                var formattedRecords = [];
                _.each(data.records, function(record) {
                    if (!record.id) {
                        return; // Elastic Search may return records without id and record names.
                    }
                    var formattedRecord = {
                        id: record.id,
                        name: record.name,
                        module: record._module,
                        label: record._module.substr(0, 2),
                        route: '#' + app.router.buildRoute(record._module, record.id),
                        timestamp: now,
                        weight: 40
                    };

                    formattedRecords.push(formattedRecord);
                });
                self.addToTemporaryLibrary(formattedRecords);
                self.applyQuickSearch(true);
            },
            error: function(error) {
                app.logger.error("Failed to fetch search results in search ahead. " + error);
            }
        });
    }

})
