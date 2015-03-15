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
 * @class View.Views.Base.QuicksearchBarView
 * @alias SUGAR.App.view.views.BaseQuicksearchBarView
 * @extends View.View
*/
({
    plugins: ['Dropdown'],

    /**
     * The minimum number of characters before the search bar attempts to
     * retrieve results.
     *
     * @property {number}
     */
    minChars: 1,

    /**
     * Used by Dropdown plugin to determine which items to select when using the arrow keys.
     *
     * @property {string}
     */
    dropdownItemSelector: '[data-action="select-module"]',

    searchModules: [],
    events: {
        'click .typeahead a': 'clearSearch',
        'click [data-action=search]': 'showResults',
        'click [data-advanced=options]': 'persistMenu',
        'click [data-action=select-module]': 'selectModule',
        'focus input.search-query': 'requestFocus'
    },

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        /**
         * The collection for executing searches and passing results.
         * This could be shared and used by other components.
         */
        // FIXME Sidecar should be modified to allow multiple top level contexts. When this happens, quick search
        // should use that context instead of layout.collection.
        this.collection = this.layout.collection || app.data.createMixedBeanCollection();

        /**
         * Used for keyboard up/down arrow navigation between components of `globalsearch` layout
         *
         * @property {string}
         */
        this.isFocusable = true;


        /**
         * The current search term.
         * When a search term is typed, the term is immediately stored to this variable. After the 500ms debounce, the
         * term is used to execute a search.
         * @type {string}
         * @private
         */
        this._searchTerm = '';

        /**
         * The previous search term.
         * This is stored to check against `this._searchTerm`. If `this._searchTerm === this._oldSearchTerm`, we do
         * not need to retrieve new results. This protects us against keystrokes that do not change the search term.
         * @type {string}
         * @private
         */
        this._oldSearchTerm = '';

        /**
         * The previous query term.
         * This is the last search term used to get results, and as such, is the term that produced the currently
         * displayed results. If `this._searchTerm === this._currentQueryTerm` when the search is executed (after
         * the 500ms debounce), we do not need to execute a new search.
         * @type {string}
         * @private
         */
        this._currentQueryTerm = '';

        app.events.on('app:sync:complete', this.populateModules, this);

        // shortcut keys
        // Focus the search bar
        app.shortcuts.register(app.shortcuts.GLOBAL + 'Search', ['s', 'ctrl+alt+0'], function() {
            this.$('input.search-query').focus();
        }, this);

        // Exit the search bar
        app.shortcuts.register(app.shortcuts.GLOBAL + 'SearchBlur', ['esc', 'ctrl+alt+l'], function() {
            this.layout.trigger('quicksearch:clear');
        }, this, true);

        // Listener for receiving focus for up/down arrow navigation:
        this.on('navigate:focus:receive', function() {
            // if the input doesn't have focus, give it focus.
            var inputBox = this.$('input.search-query')[0];
            if (inputBox !== $(document.activeElement)[0]) {
                inputBox.focus();
            }
            this.attachKeyEvents();
        }, this);

        // Listener for losing focus for up/down arrow navigation:
        this.on('navigate:focus:lost', function() {
            this.disposeKeyEvents();
        }, this);

        // Listener for `quicksearch:clear`. This clears the old search terms, aborts in progress
        // searches, and disposes key listeners
        this.layout.on('quicksearch:clear', function() {
            this._searchTerm = '';
            this._oldSearchTerm = '';
            this._currentQueryTerm = '';
            this.collection.abortFetchRequest();
            this.disposeKeyEvents();
        }, this);

    },

    /**
     * Request focus from the layout. This is used primarily for mouse clicks.
     */
    requestFocus: function() {
        this.layout.trigger('navigate:to:component', this.name);
    },

    /**
     * Function to attach the keydown and keyup events.
     */
    attachKeyEvents: function() {
        // for arrow key navigation
        this.$('input.search-query').on('keydown', _.bind(this.keydownHandler, this));

        // for searchbar typeahead
        this.$('input.search-query').on('keyup', _.bind(this.keyupHandler, this));
    },

    /**
     * Function to dispose the keydown and keyup events.
     */
    disposeKeyEvents: function() {
        this.$('input.search-query').off('keydown keyup');
    },

    /**
     * Handle module 'select/unselect' event.
     *
     * @param {Event} event
     */
    selectModule: function(event) {
        var module = this.$(event.currentTarget).data('module'),
            searchAll = this.$('input:checkbox[data-module="all"]'),
            searchAllLabel = searchAll.closest('label'),
            checkedModules = this.$('input:checkbox:checked[data-module!="all"]');

        if (module === 'all') {
            searchAll.attr('checked', true);
            searchAllLabel.addClass('active');
            checkedModules.removeAttr('checked');
            checkedModules.closest('label').removeClass('active');
        } else {
            var currentTarget = this.$(event.currentTarget);
            currentTarget.toggleClass('active', currentTarget.attr('checked'));

            if (checkedModules.length) {
                searchAll.removeAttr('checked');
                searchAllLabel.removeClass('active');
            }
            else {
                searchAll.attr('checked', true);
                searchAllLabel.addClass('active');
            }
        }
        // This will prevent the module selection dropdown from disappearing.
        event.stopPropagation();
    },
    /**
     * Create the dropdown for the user to select which modules to search.
     */
    populateModules: function() {
        if (this.disposed) {
            return;
        }
        this.searchModules = [];
        var modules = app.metadata.getModules() || {};
        this.searchModules = this._populateSearchableModules({
            modules: modules,
            acl: app.acl,
            checkFtsEnabled: true,
            checkGlobalSearchEnabled: true
        });
        this.render();
    },
    /**
     * Helper that can be called from here in base, or, from derived quicksearch views. Called internally,
     * so please ensure that you have passed in any required options or results may be undefined
     *
     * @param {object} options An object literal with the following properties:
     * - modules: our current modules (required)
     * - acl: app.acl that has the hasAccess function (required) (we DI this for testability)
     * - moduleNames: displayed modules; an array of white listed string names. If used, only modules within
     * this white list will be added (optional)
     * - checkFtsEnabled: whether we should check meta.ftsEnabled (optional defaults to false)
     * - checkGlobalSearchEnabled: whether we should check meta.globalSearchEnabled (optional defaults to false)
     * @return {array} An array of searchable modules
     * @protected
     */
    _populateSearchableModules: function(options) {
        var modules = options.modules,
            moduleNames = options.moduleNames || null,
            acl = options.acl,
            searchModules = [];

        _.each(modules, function(meta, module) {
            var goodToAdd = true;
            // First check if we have a "white list" of displayed module names (e.g. portal)
            // If so, check if it contains the current module we're checking
            if (moduleNames && !_.contains(moduleNames, module)) {
                goodToAdd = false;
            }
            // First check access the, conditionally, check fts and global search enabled properties
            if (goodToAdd && acl.hasAccess.call(acl, 'view', module)) {
                // Check global search enabled if relevant to caller
                if (options.checkGlobalSearchEnabled && !meta.globalSearchEnabled) {
                    goodToAdd = false;
                }
                // Check global search enabled if relevant to caller
                if (goodToAdd && options.checkFtsEnabled && !meta.ftsEnabled) {
                    goodToAdd = false;
                }
                // If we got here we've passed all checks so push module to search modules
                if (goodToAdd) {
                    searchModules.push(module);
                }
            }
        }, this);
        return searchModules;
    },

    /**
     * Handles the keydown event for up, down, and ignores tab.
     *
     * @param {Event} e The `keydown` event
     * @private
     */
    keydownHandler: function(e) {
        switch (e.keyCode) {
            case 40: // down arrow
                this.moveForward();
                e.preventDefault();
                break;
            case 38: // up arrow
                this.moveBackward();
                e.preventDefault();
                break;
            case 9:  // tab
        }
    },

    keyupHandler: function(e) {
        switch (e.keyCode) {
            case 40: // down arrow
                break;
            case 38: // up arrow
                break;
            case 9: //tab
                break;
            default:
                this._validateAndSearch();
        }
    },

    /**
     * Navigate to the next component
     */
    moveForward: function() {
        if (this.layout.triggerBefore('navigate:next:component')) {
            this.disposeKeyEvents();
            this.layout.trigger('navigate:next:component');
        }
    },

    /**
     * Navigate to the previous component
     */
    moveBackward: function() {
        if (this.layout.triggerBefore('navigate:previous:component')) {
            this.disposeKeyEvents();
            this.layout.trigger('navigate:previous:component');
        }
    },

    /**
     * Waits & debounces for 0.5 seconds before firing a search. This is primarily used on the
     * keydown event for the typeahead functionality.
     *
     * @param {string} term The search term.
     * @private
     * @method
     */
    _debounceSearch: _.debounce(function() {
        // Check if the search term is falsy (empty string)
        // or the search term is the same as the previously searched term
        // If either of those conditions are met, we do not need to execute a new search.
        if (!this._searchTerm || this._searchTerm === this._currentQueryTerm) {
            return;
        }
        this._currentQueryTerm = this._searchTerm;
        this.fireSearchRequest();
    }, 500),

    /**
     * Collects the search term, validates that a search is appropriate, and executes a debounced search.
     * First, it checks the search term length, to ensure it meets the minimum length requirements.
     * Second, it checks the search term against the previously typed search term. If the search term hasn't changed
     * (for example, for keyboard shortcuts) then there is no need to rerun the search.
     * If the above conditions are met, `_validateAndSearch` runs a debounced search.
     *
     * @private
     */
    _validateAndSearch: function() {
        var term = this.$('input').val();
        this._searchTerm = term;

        // if the term is too short, close the search
        if (term.length < this.minChars) {
            this.layout.trigger('quicksearch:dropdown:close');
            this._currentQueryTerm = '';
            this._oldSearchTerm = '';
            return;
        }

        // shortcuts might trigger multiple `keydown` events, to do some actions like blurring the input, but since the
        // input value didn't change we don't want to trigger a new search.
        var hasInputChanged = (this._searchTerm !== this._oldSearchTerm);
        if (hasInputChanged) {
            this._oldSearchTerm = term;
            this._debounceSearch();
        }
    },

    /**
     * @inheritDoc
     */
    _renderHtml: function() {
        if (!app.api.isAuthenticated() || app.config.appStatus == 'offline') return;
        this._super('_renderHtml', this);

        // Prevent the form from being submitted
        this.$('.navbar-search').submit(function() {
            return false;
        });
    },

    /**
     * Get the modules that current user selected for search.
     * Empty array for all.
     *
     * @return {array}
     * @private
     */
    _getSearchModuleNames: function() {
        if (this.$('input:checkbox[data-module="all"]').attr('checked')) {
            return [];
        }
        else {
            var searchModuleNames = [],
                checkedModules = this.$('input:checkbox:checked[data-module!="all"]');
            _.each(checkedModules, function(val, index) {
                searchModuleNames.push(val.getAttribute('data-module'));
            }, this);
            return searchModuleNames;
        }
    },
    /**
     * Executes a search using `this._searchTerm`.
     */
    fireSearchRequest: function() {
        var term = this._searchTerm;
        // FIXME: SC-4254 Remove this.layout.v2
        var moduleList = this._getSearchModuleNames(),
            defaultMaxNum = this.layout.v2 ? 3 : 5,
            maxNum = app.config && app.config.maxSearchQueryResult ? app.config.maxSearchQueryResult : defaultMaxNum,
            options = {
                query: term,
                module_list: moduleList
            };
        // FIXME: SC-4254 Remove this.layout.v2
        if (this.layout.v2) {
            options.max_num = maxNum;
        } else {
            options.limit = maxNum;
            options.fields = ['name', 'id'];
        }
        this.collection.fetch(options);
    },

    /**
     * Show results when the search button is clicked.
     */
    showResults: function(evt) {
        this._validateAndSearch();
    },

    /**
     * Clears out search upon user following search result link in menu
     */
    clearSearch: function() {
        this.$('.search-query').val('');
    },

    /**
     * This will prevent the dropup menu from closing when clicking anywhere on it
     */
    persistMenu: function(e) {
        e.stopPropagation();
    },
    /**
     * @inheritDoc
     */
    unbind: function() {
        this.disposeKeyEvents();
        this._super('unbind');
    }
})
