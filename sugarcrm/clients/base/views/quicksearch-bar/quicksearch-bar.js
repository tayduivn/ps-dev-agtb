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
        'click [data-action=search_icon]' : 'searchIconClickHandler',
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

        /**
         * Indicates if the search bar is expanded
         * @type {boolean}
         */
        this.expanded = false;

        /**
         * Indicates the state of the search button icon:
         *
         * - `true` means magnifying glass.
         * - `false` means X icon.
         *
         * @type {boolean}
         */
        this.searchButtonIcon = true;

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
            if (!this.expanded) {
                return;
            }
            this.collection.abortFetchRequest();
            // Don't collapse on the search page
            if (!this.context.get('search')) {
                this.collapse();
            }
            this.searchButtonIcon = true;
            this.toggleSearchIcon();
        }, this);

        // Listener for app:view:change to expand or collapse the search bar
        app.events.on('app:view:change', function() {
            if (this.context.get('search')) {
                _.defer(_.bind(this.expand, this, true));
            } else {
                _.bind(this.collapse, this);
            }
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
     * Expands the search input box.
     *
     * @param {boolean} update `true` means the expansion is to update the width.
     *                  `false` means the expansion is new and needs animation.
     */
    expand: function(update) {
        // if the search bar is already expanded and it is not an update,
        // do nothing.
        if (this.expanded && !update) {
            return;
        }

        // Calculate the target searchbox width
        var newWidth = this._calculateExpansion();

        // if the newWidth is not defined, then the menu hasn't completely
        // loaded, and we should do nothing.
        if (_.isUndefined(newWidth)) {
            return;
        }

        // For new expansions, we need to clear out the modules.
        var headerLayout = this.layout.closestComponent('header');
        headerLayout.trigger('view:resize', headerLayout.getModuleListMinWidth());

        // Now that there is space for the search bar to expand, animate the
        // expansion.
        var $inputEl = this.$('input[data-action=search_bar]');
        if (update) {
            $inputEl.width(newWidth);
            $inputEl.val(this._searchTerm);
            this.expanded = true;
        } else {
            $inputEl.animate({width: newWidth},
                {
                    duration: 100,
                    complete: _.bind(function() {
                        this.expanded = true;
                    }, this)
                }
            );
        }

        // On route, call the router handler.
        app.router
            .off('route', this.routerHandler)
            .on('route', this.routerHandler, this);

        // Turn off the default header resize listener
        this.layout.closestComponent('header').setModuleListResize(false);

        // On window resize, if expanded, recalculate expansion
        $(window)
            .off('resize.quicksearch')
            .on('resize.quicksearch', _.debounce(_.bind(this.resizeHandler, this), 10));
    },

    /**
     * Resizes the expanded search bar when the window is resized.
     * @private
     */
    resizeHandler: function() {
        if (this.expanded) {
            _.defer(_.bind(this.expand, this, true));
        }
    },

    /**
     * Handles the route event on the router.
     *
     * This simple function allows us to reuse a function pointer to the router
     * handler. The router does not allow namespaced events such as
     * `route.quicksearch`. So, this function pointer is necessary to
     * properly dispose the event handler.
     */
    routerHandler: function() {
        this.layout.trigger('quicksearch:close');
        this.$('input[data-action=search_bar]').blur();
        this.toggleSearchIcon();
    },

    /**
     * Calculates the target width for the search bar expansion based off the current state of the megamenu.
     *
     * @return {number} The target width for expansion.
     * @private
     */
    _calculateExpansion: function() {
        var headerLayout = this.layout.closestComponent('header');

        // The starting width of the input box
        var searchbarStartingWidth = this.$('input[data-action=search_bar]').outerWidth();

        // The total width of the module list header
        var totalModuleWidth = headerLayout.getModuleListWidth();

        // The minimum width necessary for module list header
        var minimumModuleWidth = headerLayout.getModuleListMinWidth();

        // The target width is most of the module list, saving room for the
        // minimum module list width.
        return searchbarStartingWidth +
               totalModuleWidth -
               minimumModuleWidth;
    },

    /**
     * Collapses the search input box.
     */
    collapse: function() {
        // if on the search page
        if (this.context.get('search')) {
            return;
        }

        this.expanded = false;

        // Turn off the quicksearch resize listener
        $(window).off('resize.quicksearch');

        // Turn on the default header resize listener
        this.layout.closestComponent('header').setModuleListResize(true);

        // jQuery `width` function with no arguments (or null arguments) only
        // returns the current width. Calling `width('')` with the empty string
        // sets the width to an empty value, which the browser ignores and
        // uses the css width.
        this.$('input[data-action=search_bar]').width('');
        var headerLayout = this.layout.closestComponent('header');
        headerLayout.resize();
    },

    /**
     * Toggles the search icon between the magnifying glass and x.
     */
    toggleSearchIcon: function() {
        var iconEl = this.$('[data-action="search_icon"] .fa').first();
        // In the search context, the icon needs special handling.
        // he icon is an 'x' if there is text in the input.
        // Otherwise, show a magnifying glass.
        if (this.context.get('search')) {
            this.searchButtonIcon = !this.$('input[data-action=search_bar]').val();
        }
        if (this.searchButtonIcon) {
            iconEl.removeClass('fa-times');
            iconEl.addClass('fa-search');
        } else {
            iconEl.removeClass('fa-search');
            iconEl.addClass('fa-times');
        }
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
            case 13: // enter
                this.goToSearchPage();
                break;
            default:
                this._validateAndSearch();
        }
    },

    /**
     * Handler for clicks on the search icon (or x, depending on state).
     *
     * If the search bar is expanded, collapse. If the search bar is collapsed,
     * expand.
     */
    searchIconClickHandler: function() {
        if (this.expanded) {
            this.clearSearch();
            this.layout.trigger('quicksearch:close');
        } else {
            this.goToSearchPage();
        }
    },

    /**
     * Goes to the search page and displays results.
     */
    goToSearchPage: function() {
        // navigate to the search results page
        var term = this.$('input[data-action=search_bar]').val();
        var route = '';
        var moduleString = this._getSearchModuleNames().join(',');
        this._searchTerm === this._currentQueryTerm;
        this._currentQueryTerm = term;
        if (this.layout.v2) {
            route = app.router.buildRoute('search', term + '&m=' + moduleString);
        } else {
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
        _.defer(_.bind(this.expand, this));
        this.searchButtonIcon = false;
        this.toggleSearchIcon();
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
            this._currentQueryTerm = '';
            this._oldSearchTerm = '';
            // We trigger `quicksearch:results:close` instead of
            // `quicksearch:close` because we only want to close the dropdown
            // and keep the bar expanded. That means we only want the listener
            // in `quicksearch-results.js` to be called, not the other ones.
            this.collection.abortFetchRequest();
            this.layout.trigger('quicksearch:results:close');
            this.toggleSearchIcon();
        }

        // shortcuts might trigger multiple `keydown` events, to do some actions like blurring the input, but since the
        // input value didn't change we don't want to trigger a new search.
        var hasInputChanged = (this._searchTerm !== this._oldSearchTerm);
        if (hasInputChanged) {
            this.layout.trigger('quicksearch:search:underway');
            this.expand();
            this.searchButtonIcon = false;
            this.toggleSearchIcon();
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

        this._searchTerm = this.context.get('searchTerm');
        // if on search page, expand
        if (this.context.get('search')) {
            this.expand(true);
            this.toggleSearchIcon();
        }
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
        var moduleList = this._getSearchModuleNames();
        var limit = this.layout.v2 ? this.limit : 5;
        limit = app.config && app.config.maxSearchQueryResult || limit;
        var options = {
            query: term,
            module_list: moduleList,
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
    },

    /**
     * @inheritDoc
     */
    unbind: function() {
        this.disposeKeyEvents();
        this._super('unbind');
    }
})
