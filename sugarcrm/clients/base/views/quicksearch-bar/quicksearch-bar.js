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

    className: 'table-cell quicksearch-bar-wrapper',
    /**
     * The minimum number of characters before the search bar attempts to
     * retrieve results.
     *
     * @property {number}
     */
    minChars: 1,

    searchModules: [],
    events: {
        'focus input[data-action=search_bar]': 'requestFocus',
        'click input[data-action=search_bar]': 'searchBarClickHandler'
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
         * The default number of maximum results to display.
         *
         * @type {number}
         * @property
         */
        this.limit = 3;
        if (this.meta && this.meta.limit) {
            this.limit = this.meta.limit;
        }

        /**
         * Used for keyboard up/down arrow navigation between components of `globalsearch` layout
         *
         * @property {boolean}
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

        // Listener for receiving focus for up/down arrow navigation:
        this.on('navigate:focus:receive', function() {
            // if the input doesn't have focus, give it focus.
            var inputBox = this.$('input[data-action=search_bar]')[0];
            if (inputBox !== $(document.activeElement)[0]) {
                inputBox.focus();
            } else {
                this.attachKeyEvents();
            }
        }, this);

        // Listener for losing focus for up/down arrow navigation:
        this.on('navigate:focus:lost', function() {
            this.disposeKeyEvents();
        }, this);

        // Listener for `quicksearch:close`. This aborts in progress
        // searches
        this.layout.on('quicksearch:close', function() {
            this._searchTerm = '';
            this._currentQueryTerm = '';
            this._oldSearchTerm = '';
            this.collection.abortFetchRequest();
            this.$('input[data-action=search_bar]').blur();
        }, this);

        this.layout.on('quicksearch:bar:clear', this.clearSearch, this);

        this.layout.on('quicksearch:bar:search', this.goToSearchPage, this);
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
        var searchBarEl = this.$('input[data-action=search_bar]');
        // for arrow key navigation
        searchBarEl.on('keydown', _.bind(this.keydownHandler, this));

        // for searchbar typeahead
        searchBarEl.on('keyup', _.bind(this.keyupHandler, this));
    },

    /**
     * Function to dispose the keydown and keyup events.
     */
    disposeKeyEvents: function() {
        this.$('input[data-action=search_bar]').off('keydown keyup');
    },

    /**
     * Toggles the search icon between the magnifying glass and x, if available.
     *
     * @param {boolean} searchButtonIcon Indicates the state of the search button icon
     * - `true` means set to magnifying glass.
     * - `false` means set to X icon.
     */
    toggleSearchIcon: function(searchButtonIcon) {
        if (this.context.get('search')) {
            searchButtonIcon = !this.$('input[data-action=search_bar]').val();
        }
        this.layout.trigger('quicksearch:button:toggle', searchButtonIcon);
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
        }
    },

    /**
     * Handles the keyup event for typing, and ignores tab
     *
     * @param {Event} e The `keyup event
     */
    keyupHandler: function(e) {
        switch (e.keyCode) {
            case 40: // down arrow
                break;
            case 38: // up arrow
                break;
            case 9: // tab
                break;
            case 16: // shift
                break;
            case 13: // enter
                this.goToSearchPage();
                break;
            default:
                this._validateAndSearch();
        }
    },

    /**
     * Goes to the search page and displays results.
     */
    goToSearchPage: function() {
        // navigate to the search results page
        var term = this.$('input[data-action=search_bar]').val();
        var route = '';
        this._searchTerm === this._currentQueryTerm;
        this._currentQueryTerm = term;
        if (this.layout.v2) {
            route = app.utils.GlobalSearch.buildSearchRoute(term, {
               modules: this.collection.selectedModules
            });
        } else {
            var moduleString = this.collection.selectedModules.join(',');
            route = 'bwc/index.php?module=Home&append_wildcard=true&action=spot&full=true' +
                '&q=' + term +
                '&m=' + moduleString;
        }
        this.collection.abortFetchRequest();
        app.router.navigate(route, {trigger: true});
    },
    /**
     * Handler for clicks on the search bar.
     *
     * Expands the bar and toggles the search icon.
     */
    searchBarClickHandler: function() {
        this.requestFocus();
        _.defer(_.bind(this.layout.expand, this.layout));
        this.toggleSearchIcon(false);
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
        var term = this.$('input[data-action=search_bar]').val();
        this._searchTerm = term;

        // if the term is too short, don't search
        if (term.length < this.minChars) {
            this._searchTerm = '';
            this._currentQueryTerm = '';
            this._oldSearchTerm = '';
            // We trigger `quicksearch:results:close` instead of
            // `quicksearch:close` because we only want to close the dropdown
            // and keep the bar expanded. That means we only want the listener
            // in `quicksearch-results.js` to be called, not the other ones.
            this.collection.abortFetchRequest();
            this.layout.trigger('quicksearch:results:close');
            this.collection.abortFetchRequest();
            // If on the search page, reset the search button.
            this.toggleSearchIcon(!term);
            return;
        }

        // shortcuts might trigger multiple `keydown` events, to do some actions like blurring the input, but since the
        // input value didn't change we don't want to trigger a new search.
        var hasInputChanged = (this._searchTerm !== this._oldSearchTerm);
        if (hasInputChanged) {
            this.collection.dataFetched = false;
            this.layout.trigger('quicksearch:search:underway');
            this.layout.expand();
            this.toggleSearchIcon(false);
            this._oldSearchTerm = term;
            this._debounceSearch();
        }
    },

    /**
     * Executes a search using `this._searchTerm`.
     */
    fireSearchRequest: function() {
        var term = this._searchTerm;
        // FIXME: SC-4254 Remove this.layout.v2
        var limit = this.layout.v2 ? this.limit : 5;
        limit = app.config && app.config.maxSearchQueryResult || limit;
        var options = {
            query: term,
            module_list: this.collection.selectedModules,
            limit: limit
        };
        // FIXME: SC-4254 Remove this.layout.v2
        if (!this.layout.v2) {
            options.fields = ['name', 'id'];
        }
        this.collection.query = term;
        this.collection.fetch(options);
    },

    /**
     * Clears out search upon user following search result link in menu
     */
    clearSearch: function() {
        this.$('.search-query').val('');
        this._searchTerm = '';
        this._oldSearchTerm = '';
        this._currentQueryTerm = '';
        this.disposeKeyEvents();

        if (this.context.get('search')) {
            this.toggleSearchIcon(true);
        }
    },

    /**
     * @inheritDoc
     */
    unbind: function() {
        this.disposeKeyEvents();
        this._super('unbind');
    }
})
