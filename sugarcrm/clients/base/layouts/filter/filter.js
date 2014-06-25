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
 * Layout for filtering a collection.
 *
 * Composed of a module dropdown(optional), a filter dropdown and an input.
 *
 * @class View.Layouts.Base.FilterLayout
 * @alias SUGAR.App.view.layouts.BaseFilterLayout
 * @extends View.Layout
 */
({
    className: 'filter-view search',

    plugins: ['QuickSearchFilter'],

    events: {
        'click .add-on.icon-remove': function() { this.trigger('filter:clear:quicksearch'); }
    },

    /**
     * @override
     * @param {Object} opts
     */
    initialize: function(opts) {
        var filterLayout = app.view._getController({type:'layout',name:'filter'});
        filterLayout.loadedModules = filterLayout.loadedModules || {};
        app.view.Layout.prototype.initialize.call(this, opts);

        this.layoutType = app.utils.deepCopy(this.options.meta.layoutType) || this.context.get('layout') || this.context.get('layoutName') || app.controller.context.get('layout');

        this.aclToCheck = (this.layoutType === 'record')? 'view' : 'list';
        this.filters = app.data.createBeanCollection('Filters');
        this.filters.comparator = _.bind(this.filterCollectionSorting, this);

        this.emptyFilter = app.data.createBean('Filters', {
            name: '',
            filter_definition: [],
            editable: true
        });

        // Can't use getRelevantContextList here, because the context may not
        // have all the children we need.
        if (this.layoutType === 'records') {
            // filters will handle data fetching so we skip the standard data fetch
            this.context.set('skipFetch', true);
        } else {
            if(this.context.parent) {
                this.context.parent.set('skipFetch', true);
            }
            this.context.on('context:child:add', function(childCtx) {
                if (childCtx.get('link')) {
                    // We're in a subpanel.
                    childCtx.set('skipFetch', true);
                }
            }, this);
        }

        /**
         * bind events
         */
        this.on('filter:apply', this.applyFilter, this);

        this.on('filter:create:close', function() {
            if (!this.createPanelIsOpen()) {
                return;
            }
            this.layout.trigger('filter:create:close');
            // When canceling creating a new filter, we want to go back to the `all_records` filter
            if (this.getLastFilter(this.layout.currentModule, this.layoutType) === 'create') {
                // For that we need to remove the last state key and trigger reinitialize
                this.clearLastFilter(this.layout.currentModule, this.layoutType);
                this.layout.trigger("filter:reinitialize");
            }
            this.context.editingFilter = null;
        }, this);

        this.on('filter:create:open', function(filterModel) {
            this.context.editingFilter = filterModel;
            this.layout.trigger('filter:create:open', filterModel);
        }, this);

        this.on('subpanel:change', function(linkName) {
            this.layout.trigger('subpanel:change', linkName);
        }, this);

        this.on('filter:get', this.initializeFilterState, this);

        this.on('filter:change:filter', this.handleFilterChange, this);

        this.layout.on('filter:apply', function(query, def) {
            this.trigger('filter:apply', query, def);
        }, this);

        this.layout.on('filterpanel:change', this.handleFilterPanelChange, this);
        this.layout.on('filterpanel:toggle:button', this.toggleFilterButton, this);

        //When a filter is saved, update the cache and set the filter to be the currently used filter
        this.context.on('filter:add', this.addFilter, this);

        // When a filter is deleted, update the cache and set the default filter
        // to be the currently used filter.
        this.layout.on('filter:remove', this.removeFilter, this);

        this.layout.on('filter:reinitialize', function() {
            this.initializeFilterState(this.layout.currentModule, this.layout.currentLink);
        }, this);

        this.listenTo(app.events, 'dashlet:filter:save', this.refreshDropdown);
    },

    /**
     * This function refreshes the list of filters in the filter dropdown, and
     * is invoked when a filter is saved on a dashlet (`dashlet:filter:save`).
     * It triggers a `filter:reinitialize` event and resets the cached
     * module in `loadedModules` on the filter layout if the dashlet module
     * matches the `currentModule` on the filter layout.
     *
     * @param {String} module
     */
    refreshDropdown: function(module) {
        if (module === this.layout.currentModule) {
            var filterLayout = app.view._getController({type:'layout', name:'filter'});
            filterLayout.loadedModules[module] = false;
            this.layout.trigger('filter:reinitialize');
        }
    },

    /**
     * handles filter removal
     * @param model
     */
    removeFilter: function(model) {
        this.filters.remove(model);
        this.context.set('currentFilterId', null);
        this.clearLastFilter(this.layout.currentModule, this.layoutType);
        this.saveFilterCollection(this.layout.currentModule);
        this.layout.trigger('filter:reinitialize');
    },

    /**
     * Saves last filter id to app cache.
     *
     * @param {String} filterModule The name of the filtered module.
     * @param {String} layoutName The name of the current layout.
     * @param {String} filterId The filter id.
     */
    setLastFilter: function(filterModule, layoutName, filterId) {
        var filterOptions = this.context.get('filterOptions') || {};
        this.context.set('currentFilterId', filterId);
        if (filterOptions.stickiness !== false) {
            var key = app.user.lastState.key('last-' + filterModule + '-' + layoutName, this);
            app.user.lastState.set(key, filterId);
        }
    },

    /**
     * Gets last filter id from cache.
     *
     * @param {String} filterModule The name of the filtered module.
     * @param {String} layoutName The name of the current layout.
     * @return {String} The filter id.
     */
    getLastFilter: function(filterModule, layoutName) {
        // Check if we've already loaded it.
        var filter = this.context.get('currentFilterId');
        if (!_.isEmpty(filter)) {
            return filter;
        }

        var filterOptions = this.context.get('filterOptions') || {};
        // Load from cache.
        if (filterOptions.stickiness !== false) {
            var key = app.user.lastState.key('last-' + filterModule + '-' + layoutName, this);
            filter = app.user.lastState.get(key);
        }

        // Check if there is an initial filter defined that we should use instead.
        if (_.isEmpty(filter) && filterOptions.initial_filter) {
            filter = filterOptions.initial_filter;
        }

        this.context.set('currentFilterId', filter);
        return filter;
    },

    /**
     * Clears last filter id from cache.
     *
     * @param {String} filterModule The name of the filtered module.
     * @param {String} layoutName The name of the current layout.
     */
    clearLastFilter: function(filterModule, layoutName) {
        var filterOptions = this.context.get('filterOptions') || {};
        if (filterOptions.stickiness !== false) {
            var key = app.user.lastState.key('last-' + filterModule + '-' + layoutName, this);
            app.user.lastState.remove(key);
        }
        this.clearFilterEditState();
    },

    /**
     * Retrieves the current edit state from cache.
     *
     * @return {Object} The filter attributes if found.
     */
    retrieveFilterEditState: function() {
        var filterOptions = this.context.get('filterOptions') || {};
        if (filterOptions.stickiness !== false) {
            var key = app.user.lastState.key('edit-' + this.layout.currentModule + '-' + this.layoutType, this);
            return app.user.lastState.get(key);
        }
    },

    /**
     * Saves the current edit state into the cache
     *
     * @param {Object} filter
     */
    saveFilterEditState: function(filter) {
        var filterOptions = this.context.get('filterOptions') || {};
        if (filterOptions.stickiness !== false) {
            var key = app.user.lastState.key('edit-' + this.layout.currentModule + '-' + this.layoutType, this);
            app.user.lastState.set(key, filter);
        }
    },

    /**
     * Removes the edit state from the cache
     */
    clearFilterEditState: function() {
        var filterOptions = this.context.get('filterOptions') || {};
        if (filterOptions.stickiness !== false) {
            var key = app.user.lastState.key('edit-' + this.layout.currentModule + '-' + this.layoutType, this);
            app.user.lastState.remove(key);
        }
    },

    /**
     * Get the filters of one module from the cache.
     *
     * @param {String} module The module name.
     * @return {String} The BeanCollection at the JSON format.
     */
    getFilterCollection: function(module) {
        var layoutModule = this.module;
        this.module = module;
        var filters = app.user.lastState.get(app.user.lastState.key('saved-filters', this));
        this.module = layoutModule;
        return filters;
    },

    /**
     * Save the filters of one module to the cache.
     *
     * Previously, the filters were stored in the local storage with a key
     * looking like this:
     * ```
     * this.module + ':filter:saved-' + this.layout.currentModule
     * ```
     * where `this.module` is the main context module, and `currentModule` is
     * the filtered module.
     *
     * As a consequence, the collection of filters of the filtered module,
     * saying `Accounts`, was stored multiple times like that:
     * ```
     * Home:filter:saved-Accounts
     * Accounts:filter:saved-Accounts
     * Contacts:filter:saved-Accounts
     * ```
     * And all the copies were not in sync.
     *
     * To fix this before a major refactor where `this.module` would be the
     * filtered module, we need to fake it. We also need to clean old entries in
     * the local storage.
     *
     * @param {String} module The module name.
     */
    saveFilterCollection: function(module) {
        app.user.lastState.remove(app.user.lastState.key('saved-' + module, this));

        var layoutModule = this.module;
        this.module = module;
        app.user.lastState.set(app.user.lastState.key('saved-filters', this), this.filters.toJSON());
        this.module = layoutModule;
    },

    /**
     * Handle filter addition or update.
     *
     * @param {Data.Bean} model The filter model that is created or updated.
     */
    addFilter: function(model) {
        var id = model.get('id');
        this.filters.add(model, { merge: true });
        this.saveFilterCollection(this.layout.currentModule);
        this.setLastFilter(this.layout.currentModule, this.layoutType, id);
        this.context.set('currentFilterId', id);
        this.clearFilterEditState();
        this.layout.trigger('filter:reinitialize');
    },

    /**
     * Enables or disables a filter toggle button (e.g. activity or subpanel toggle buttons)
     * @param {String} toggleDataView the string used in `data-view` attribute for that toggle element (e.g. 'subpanels', 'activitystream')
     * @param {Boolean} on pass true to enable, false to disable
     */
    toggleFilterButton: function (toggleDataView, on) {
        var toggleButtons = this.layout.$('.toggle-actions a.btn');

        // Loops toggle buttons for 'data-view' that corresponds to `toggleDataView` and enables/disables per `on`
        _.each(toggleButtons, function(btn) {
            if($(btn).data('view') === toggleDataView) {
                if(on) {
                    $(btn).removeAttr('disabled').removeClass('disabled');
                } else {
                    $(btn).attr('disabled', 'disabled').addClass('disabled');
                    $(btn).attr('title', app.lang.get('LBL_NO_DATA_AVAILABLE'));
                }
            }
        });
    },

    /**
     * Handles filter panel changes between activity and subpanels
     * @param {String} name Name of panel
     * @param {Boolean} silent Whether to trigger filter events
     * @param {Boolean} setLastViewed Whether to set last viewed to `name` panel
     */
    handleFilterPanelChange: function(name, silent, setLastViewed) {
        this.showingActivities = name === 'activitystream';
        var module = this.showingActivities ? "Activities" : this.module;
        var link;

        this.$el.css('visibility', app.acl.hasAccess(this.aclToCheck, module) ? 'visible' : 'hidden');
        if(this.layoutType === 'record' && !this.showingActivities) {
            module = link = app.user.lastState.get(app.user.lastState.key("subpanels-last", this)) || 'all_modules';
            if (link !== 'all_modules') {
                module = app.data.getRelatedModule(this.module, link);
            }
        } else {
            link = null;
        }
        if (!silent) {
            this.trigger("filter:render:module");
            this.trigger("filter:change:module", module, link);
        }
        if (setLastViewed) {
            // Asks filterpanel to update user.lastState with new panel name as last viewed
            this.layout.trigger('filterpanel:lastviewed:set', name);
        }
    },

    /**
     * Handles filter change.
     *
     * @param {String} id The filter id.
     */
    handleFilterChange: function(id) {
        this.setLastFilter(this.layout.currentModule, this.layoutType, id);

        var filter, editState = this.retrieveFilterEditState();
        // Figure out if we have an edit state. This would mean user was editing the filter so we want him to retrieve
        // the filter form in the state he left it.
        if (editState && (editState.id === id || (id==='create' && !editState.id))) {
            filter = app.data.createBean('Filters');
            filter.set(editState);
        } else {
            editState = false;
            filter = this.filters.get(id) || this.emptyFilter.clone();
        }

        this.context.set('currentFilterId', filter.get('id'));

        // If the user selects a filter that has an incomplete filter
        // definition (i.e. filter definition != filter_template), open the
        // filterpanel to indicate it is ready for further editing.
        var isIncompleteFilter = filter.get('filter_template') &&
            JSON.stringify(filter.get('filter_definition')) !== JSON.stringify(filter.get('filter_template'));

        // If the user selects a filter template that gets populated by values
        // passed in the context/metadata, open the filterpanel to show the
        // actual search.
        var isTemplateFilter = filter.get('is_template');

        var modelHasChanged = !_.isEmpty(filter.changedAttributes(filter.getSyncedAttributes()));

        if (isIncompleteFilter || isTemplateFilter ||
            editState || id === 'create' || modelHasChanged
        ) {
            this.layout.trigger('filter:set:name', '');
            this.trigger('filter:create:open', filter);
            this.layout.trigger('filter:toggle:savestate', true);
        } else {
            this.layout.trigger('filter:create:close');
        }

        var ctxList = this.getRelevantContextList();
        var clear = false;
        //Determine if we need to clear the collections
        _.each(ctxList, function(ctx) {
            var filterDef = filter.get('filter_definition');
            var orig = ctx.get('collection').origFilterDef;
            ctx.get('collection').origFilterDef = filterDef;  //Set new filter def on each collection
            if (_.isUndefined(orig) || !_.isEqual(orig, filterDef)) {
                clear = true;
            }
        });
        //If so, reset collections and trigger quicksearch to repopulate
        if (clear) {
            _.each(ctxList, function(ctx) {
                ctx.get('collection').resetPagination();
                // Silently reset the collection otherwise the view is re-rendered.
                // It will be re-rendered on request response.
                ctx.get('collection').reset(null, { silent: true });
            });
            this.trigger('filter:clear:quicksearch');
        }
    },
    /**
     * Applies filter on current contexts
     * @param {String} query search string
     * @param {Object} dynamicFilterDef(optional)
     */
    applyFilter: function(query, dynamicFilterDef) {
        // TODO: getRelevantContextList needs to be refactored to handle filterpanels in drawer layouts,
        // as it will return the global context instead of filtering a list view within the drawer context.
        // As a result, this flag is needed to prevent filtering on the global context.
        var filterOptions = this.context.get('filterOptions') || {};
        if (filterOptions.auto_apply === false) {
            return;
        }

        //If the quicksearch field is not empty, append a remove icon so the user can clear the search easily
        this._toggleClearQuickSearchIcon(!_.isEmpty(query));
        // reset the selected on filter apply
        var massCollection = this.context.get('mass_collection');
        if (massCollection && massCollection.models && massCollection.models.length > 0) {
            massCollection.reset([],{silent: true});
        }
        var self = this,
            ctxList = this.getRelevantContextList();
        _.each(ctxList, function(ctx) {
            var ctxCollection = ctx.get('collection'),
                origFilterDef = dynamicFilterDef || ctxCollection.origFilterDef || [],
                filterDef = self.buildFilterDef(origFilterDef, query, ctx),
                options = {
                    //Show alerts for this request
                    showAlerts: true,
                    success: function(collection, response, options) {
                        // Close the preview pane to ensure that the preview
                        // collection is in sync with the list collection.
                        app.events.trigger('preview:close');
                    }};

            filterDef = self.applyRequiredFilters(filterDef, ctx);

            ctxCollection.filterDef = filterDef;
            ctxCollection.origFilterDef = origFilterDef;
            ctxCollection.resetPagination();

            options = _.extend(options, ctx.get('collectionOptions'));

            ctx.resetLoadFlag(false);
            if (!_.isEmpty(ctx._recordListFields)) {
                ctx.set('fields', ctx._recordListFields);
            }
            ctx.set('skipFetch', false);
            ctx.loadData(options);
        });
    },

    /**
     * Look for the relevant contexts. It can be
     * - the activity stream context
     * - the list view context on records layout
     * - the selection list view context on records layout
     * - the contexts of the subpanels on record layout
     * @returns {Array} array of contexts
     */
    getRelevantContextList: function() {
        var contextList = [];
        if (this.showingActivities) {
            _.each(this.layout._components, function(component) {
                var ctx = component.context;
                if (component.name == 'activitystream' && !ctx.get('modelId') && ctx.get('collection')) {
                    //FIXME: filter layout's _components array has multiple references to same activitystreams layout object
                    contextList.push(ctx);

                }
            });
        } else {
            if (this.layoutType === 'records') {
                var ctx = this.context;
                if (!ctx.get('modelId') && ctx.get('collection')) {
                    contextList.push(ctx);
                }
            } else {
                //Locate and add subpanel contexts
                _.each(this.context.children, function(ctx) {
                    if (ctx.get('isSubpanel') && !ctx.get('hidden') && !ctx.get('modelId') && ctx.get('collection')) {
                        contextList.push(ctx);
                    }
                });
            }
        }
        return _.uniq(contextList);
    },

    /**
     * Builds the filter definition based on preselected filter and module quick search fields
     * @param {Object} oSelectedFilter
     * @param {String} searchTerm
     * @param {Context} context
     * @returns {Array} array containing filter def
     */
    buildFilterDef: function(oSelectedFilter, searchTerm, context) {
        var selectedFilter = app.utils.deepCopy(oSelectedFilter),
            isSelectedFilter = _.size(selectedFilter) > 0,
            module = context.get('module'),
            searchFilter = this.getFilterDef(module, searchTerm),
            isSearchFilter = _.size(searchFilter) > 0;

        selectedFilter = _.isArray(selectedFilter) ? selectedFilter : [selectedFilter];
        /**
         * Filter fields that don't exist either on vardefs or search definition.
         *
         * Special fields (fields that start with `$`) like `$favorite` aren't
         * cleared.
         *
         * TODO move this to a plugin method when refactoring the code (see SC-2555)
         * TODO we should support cleanup on all levels (currently made on 1st
         * level only).
         */
        var specialField = /^\$/,
            meta = app.metadata.getModule(module);
        selectedFilter = _.filter(selectedFilter, function(def) {
            var fieldName = _.keys(def).pop();
            return specialField.test(fieldName) || meta.fields[fieldName];
        }, this);

        if (isSelectedFilter && isSearchFilter) {
            selectedFilter.push(searchFilter[0]);
            return [{'$and': selectedFilter }];
        } else if (isSelectedFilter) {
            return selectedFilter;
        } else if (isSearchFilter) {
            return searchFilter;
        }

        return [];
    },

    /**
     * Apply required filters.
     * @param {Array|string} filterDef Preselected filters defs.
     * @param {Context} context Context object.
     * @return {Array} Filter defs.
     */
    applyRequiredFilters: function(filterDef, context) {
        var specialField = /^\$/,
            meta = App.metadata.getModule(context.get('module')),
            filtersMeta = meta.filters || null,
            filtersMetaSection = context.get('layout') || context.get('link');

        if (context.has('requiredFilter')) {
            filtersMetaSection = context.get('requiredFilter');
        }

        if (!filtersMetaSection || !filtersMeta) {
            return filterDef;
        }

        filtersMeta = filtersMeta.required && filtersMeta.required.meta;

        if (filtersMeta && filtersMeta[filtersMetaSection]) {
            filterDef = _.isArray(filterDef) ? filterDef : [filterDef];

            filtersMeta = _.filter(filtersMeta[filtersMetaSection], function(def) {
                var fieldName = _.keys(def).pop();
                return specialField.test(fieldName) || meta.fields[fieldName];
            }, this);

            _.each(filtersMeta, function(filter) {
                filterDef.push(filter);
            });
        }

        return filterDef;
    },

    /**
     * Loads the full filter panel for a module.
     *
     * @param {String} moduleName The module name.
     * @param {String} [linkName] The related module link name, by default it
     *   will load the last selected filter,
     * @param {String} [filterId] The filter ID to initialize with. By default
     *   it will load the last selected filter or the default filter from
     *   metadata.
     */
    initializeFilterState: function(moduleName, linkName, filterId) {

        if (this.showingActivities) {
            moduleName = 'Activities';
            linkName = null;
        } else {
            moduleName = moduleName || this.module;

            if (this.layoutType === 'record') {
                linkName = app.user.lastState.get(app.user.lastState.key('subpanels-last', this)) ||
                    linkName ||
                    'all_modules';

                if (linkName !== 'all_modules') {
                    moduleName = app.data.getRelatedModule(moduleName, linkName) || moduleName;
                }
            }
        }

        filterId = filterId || this.getLastFilter(moduleName, this.layoutType) || 'all_records';

        this.layout.trigger('filterpanel:change:module', moduleName);
        this.trigger('filter:change:module', moduleName, linkName, true);
        this.getFilters(moduleName, filterId);
    },

    /**
     * Retrieve the appropriate list of filters from cache if found, otherwise
     * from the server.
     *
     * @param {String} moduleName The module name.
     * @param {String} defaultId The filter `id` to select once loaded.
     */
    getFilters: function(moduleName, defaultId) {
        if (moduleName === 'all_modules') {
            this.selectFilter('all_records');
            return;
        }
        var filterLayout = app.view._getController({type: 'layout', name: 'filter'}),
            cachedFilters = this.getFilterCollection(moduleName);

        if (filterLayout.loadedModules[moduleName] && !_.isUndefined(cachedFilters)) {
            this.filters.reset();
            _.each(cachedFilters, function(filter) {
                this.filters.add(app.data.createBean('Filters', filter));
            }, this);
            this.loadPredefinedFilters(moduleName);
            this.selectFilter(defaultId);

        } else {
            this.filters.fetch({
                //Don't show alerts for this request
                showAlerts: false,
                filter: [
                    {'created_by': app.user.id},
                    {'module_name': moduleName}
                ],
                success: _.bind(function() {
                    if (this.disposed) return;

                    filterLayout.loadedModules[moduleName] = true;
                    this.saveFilterCollection(moduleName);
                    this.loadPredefinedFilters(moduleName);
                    this.selectFilter(defaultId);
                }, this)
            });
        }
    },

    /**
     * Loads predefined filters from metadata and determines the default filter.
     *
     * Template predefined filters are skipped unless they are assigned as the
     * initial filter.
     * The default filter will be the last `default_filter` property found
     * in the filters metadata.
     *
     * @param {String} moduleName The name of the filtered module.
     */
    loadPredefinedFilters: function(moduleName) {
        var filterOptions = this.context.get('filterOptions') || {};

        this.filters.defaultFilterFromMeta = null;

        _.each(this.getModuleFilterMeta(moduleName), function(value) {
            if (!value || !value.meta) {
                return;
            }
            if (_.isArray(value.meta.filters)) {
                this.filters.add(
                    // Skip specific template predefined filters.
                    _.reject(value.meta.filters, function(filter) {
                        return filter.is_template && filterOptions.initial_filter !== filter.id;
                    })
                );
            }
            if (value.meta.default_filter) {
                this.filters.defaultFilterFromMeta = value.meta.default_filter;
            }
        }, this);

        if (filterOptions.initial_filter === '$relate') {
            var filterDef = {};
            _.each(filterOptions.filter_populate, function(value, key) {
                filterDef[key] = '';
            });
            this.filters.add(this.emptyFilter.clone().set({
                'id': '$relate',
                'editable': true,
                'is_template': true,
                'filter_definition': [filterDef]
            }));
        }
    },

    /**
     * Selects a filter.
     *
     * @triggers filter:select:filter to select the filter in the dropdown.
     *
     * @param {String} filterId The filter id to select.
     * @return {String} The selected filter id.
     */
    selectFilter: function(filterId) {
        var possibleFilters,
            selectedFilterId = filterId;

        if (selectedFilterId !== 'create') {
            possibleFilters = [selectedFilterId, this.filters.defaultFilterFromMeta, 'all_records'];
            possibleFilters = _.filter(possibleFilters, this.filters.get, this.filters);
            selectedFilterId = _.first(possibleFilters);
        }
        this.trigger('filter:render:filter');
        this.trigger('filter:select:filter', selectedFilterId);
        return selectedFilterId;
    },

    /**
     * Utility function to know if the create filter panel is opened.
     * @returns {Boolean} true if opened
     */
    createPanelIsOpen: function() {
        return !this.layout.$(".filter-options").is(":hidden");
    },

    /**
     * Determines whether a user can create a filter for the current module.
     * @return {Boolean} true if creatable
     */
    canCreateFilter: function() {
        // Check for create in meta and make sure that we're only showing one
        // module, then return false if any is false.
        var contexts = this.getRelevantContextList(),
            creatable = app.acl.hasAccess("create", "Filters"),
            meta;
        // Short circuit if we don't have the ACLs to create Filter beans.

        if (creatable && contexts.length === 1) {
            meta = app.metadata.getModule(contexts[0].get("module"));
            if (meta && _.isObject(meta.filters)) {
                _.each(meta.filters, function(value) {
                    if (_.isObject(value)) {
                        creatable = creatable && value.meta.create !== false;
                    }
                });
            }
        }

        return creatable;
    },

    /**
     * Gets filters metadata from the module metadata.
     *
     * @param {String} moduleName The name of the filtered module.
     * @return {Object} The filters metadata for this module.
     */
    getModuleFilterMeta: function(moduleName) {
        var module = app.metadata.getModule(moduleName) || {};
        return module.filters || {};
    },

    /**
     * Append or remove an icon to the quicksearch input so the user can clear the search easily
     * @param {Boolean} addIt TRUE if you want to add it, FALSO to remove
     */
    _toggleClearQuickSearchIcon: function(addIt) {
        if (addIt && !this.$('.add-on.icon-remove')[0]) {
            this.$el.append('<i class="add-on icon-remove"></i>');
        } else if (!addIt) {
            this.$('.add-on.icon-remove').remove();
        }
    },

    /**
     * "sort" comparator functions take two models, and return -1 if the first model should come before the second,
     * 0 if they are of the same rank and 1 if the first model should come after.
     *
     * @param {Bean} model1
     * @param {Bean} model2
     */
    filterCollectionSorting: function(model1, model2) {
        if (model1.get('editable') === false && model2.get('editable') !== false) {
            return +1;
        }
        if (model1.get('editable') !== false && model2.get('editable') === false) {
            return -1;
        }
        if (this._getTranslatedFilterName(model1).toLowerCase() < this._getTranslatedFilterName(model2).toLowerCase()) {
            return -1;
        }
        return +1;
    },

    /**
     * Gets (translated) name of a filter.
     *
     * If the model is not editable or is a template, the filter name must be
     * defined as a label that is internationalized.
     * We allow injecting the translated module name into filter names.
     *
     * @param {Data.Bean} model The filter model.
     * @return {String} The (translated) filter name.
     * @private
     */
    _getTranslatedFilterName: function(model) {
        var filterOptions = this.context.get('filterOptions') || {},
            name = model.get('name');

        if (model.id === filterOptions.initial_filter && filterOptions.initial_filter_label) {
            name = filterOptions.initial_filter_label;
        }
        if (model.get('editable') === false || model.get('is_template')) {
            var fallbackLangModules = [this.layout.currentModule, 'Filters'];
            var parentContextModule = this.context.parent && this.context.parent.get('module');
            if (parentContextModule && parentContextModule !== this.layout.currentModule) {
                fallbackLangModules.unshift(parentContextModule);
            }
            var moduleName = app.lang.get('LBL_MODULE_NAME', this.layout.currentModule);
            var text = app.lang.get(name, fallbackLangModules) || '';
            return app.utils.formatString(text, [moduleName]);
        }
        return model.get('name') || '';
    },

    /**
     * @override
     * @private
     */
    _render: function() {
        if (app.acl.hasAccess(this.aclToCheck, this.module)) {
            app.view.Layout.prototype._render.call(this);
        }
    },

    /**
     * @override
     */
    unbind: function() {
        this.filters.off();
        this.filters = null;
        app.view.Layout.prototype.unbind.call(this);
    }

})
