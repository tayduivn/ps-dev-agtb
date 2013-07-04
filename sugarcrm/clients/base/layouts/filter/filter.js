({
    /**
     * Layout for filtering a collection.
     * Composed of a module dropdown(optional), a filter dropdown and an input
     *
     * @class BaseFilterLayout
     * @extends Layout
     */
    className: 'filter-view search',

    /**
     * @override
     * @param {Object} opts
     */
    initialize: function(opts) {
        var filterLayout = app.view._getController({type:'layout',name:'filter'});
        filterLayout.loadedModules = filterLayout.loadedModules || {};
        app.view.Layout.prototype.initialize.call(this, opts);

        this.layoutType = this.context.get('layout') || this.context.get('layoutName') || app.controller.context.get('layout');

        this.aclToCheck = (this.layoutType === 'record')? 'view' : 'list';
        this.filters = app.data.createBeanCollection('Filters');

        this.emptyFilter = app.data.createBean('Filters', {
            id: 'all_records',
            name: app.lang.get('LBL_FILTER_ALL_RECORDS'),
            filter_definition: {},
            editable: false
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
            this.layout.editingFilter = null;
            this.layout.trigger('filter:create:close');
        }, this);

        this.on('filter:create:open', function(filterModel) {
            this.layout.editingFilter = filterModel;
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
        this.layout.on('filter:add', this.addFilter, this);

        // When a filter is deleted, update the cache and set the default filter
        // to be the currently used filter.
        this.layout.on('filter:remove', this.removeFilter, this);

        this.layout.on('filter:reinitialize', function() {
            this.initializeFilterState(this.layout.currentModule, this.layout.currentLink);
        }, this);
    },
    /**
     * handles filter removal
     * @param model
     */
    removeFilter: function(model){
        this.filters.remove(model);
        app.cache.set("filters:" + this.layout.currentModule, this.filters.toJSON());
        this.layout.trigger('filter:reinitialize');
    },
    /**
     * handles filter addition
     * @param model
     */
    addFilter: function(model){
        this.filters.add(model);
        app.cache.set("filters:" + this.layout.currentModule, this.filters.toJSON());
        app.cache.set("filters:last:" + this.layout.currentModule + ":" + this.layoutType, model.get("id"));
        this.layout.trigger('filter:reinitialize');
    },

    /**
     * Enables or disables a filter toggle button (e.g. activity or subpanel toggle buttons)
     * @param {String} toggleDataView the string used in `data-view` attribute for that toggle element (e.g. 'subpanels', 'activitystream')
     * @param {Boolean} on pass true to enable, false to disable
     */
    toggleFilterButton: function (toggleDataView, on) {
        var toggleButtons = this.layout.$('.toggle-actions button');
        // Loops toggle buttons for 'data-view' that corresponds to `toggleDataView` and enables/disables per `on`
        _.each(toggleButtons, function(btn) {
            if($(btn).data('view') === toggleDataView) {
                if(on) {
                    $(btn).removeAttr('disabled');
                } else {
                    $(btn).attr('disabled', 'disabled');
                }
            }
        });
    },

    /**
     * handles filter panel changes between actvity and subpanels
     * @param name
     * @param silent
     */
    handleFilterPanelChange: function(name, silent) {
        this.showingActivities = name === 'activitystream';
        var module = this.showingActivities ? "Activities" : this.module;
        var link;

        if(this.layoutType === 'record' && !this.showingActivities) {
            module = link = app.cache.get("subpanels:last:" + module) || 'all_modules';
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
    },
    /**
     * handles filter change
     * @param id
     * @param preventCache
     */
    handleFilterChange: function(id, preventCache) {
        if (id && id != 'create' && !preventCache) {
            app.cache.set("filters:last:" + this.layout.currentModule + ":" + this.layoutType, id);
        }
        var filter = this.filters.get(id) || this.emptyFilter,
            ctxList = this.getRelevantContextList();


        _.each(ctxList, function(ctx) {
            ctx.get('collection').origFilterDef = filter.get('filter_definition');
            ctx.get('collection').resetPagination();
            ctx.get('collection').reset();
        });
        this.trigger('filter:clear:quicksearch');
    },
    /**
     * Applies filter on current contexts
     * @param {String} query search string
     * @param {Object} dynamicFilterDef(optional)
     */
    applyFilter: function(query, dynamicFilterDef) {
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

                        // reset the collection with what we fetched to trigger rerender
                        if (!self.disposed && collection) collection.reset(response);
                    }};

            ctxCollection.filterDef = filterDef;
            ctxCollection.origFilterDef = origFilterDef;

            options = _.extend(options, ctx.get('collectionOptions'));

            ctx.resetLoadFlag(false);
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
        var contextList = [], context;
        if (this.showingActivities) {
            _.each(this.layout._components, function(component) {
               if (component.name == 'activitystream') {
                   contextList.push(component.context);
               }
            });
        } else {
            if (this.layoutType === 'records') {
                if (this.context.parent) {
                    contextList.push(this.context.parent);
                } else {
                    contextList.push(this.context);
                }
            } else {
                //Locate and add subpanel contexts
                _.each(this.context.children, function(childCtx) {
                    if (childCtx.get('link') && !childCtx.get('hidden')) {
                        contextList.push(childCtx);
                    }
                });
            }
        }
        return contextList;
    },

    /**
     * Builds the filter definition based on preselected filter and module quick search fields
     * @param {Object} origfilterDef
     * @param {String} searchTerm
     * @param {Context} context
     * @returns {Array} array containing filter def
     */
    buildFilterDef: function(origfilterDef, searchTerm, context) {
        var searchFilter,
            filterDef = app.utils.deepCopy(origfilterDef),
            moduleQuickSearchFields = this.getModuleQuickSearchFields(context.get('module'));

        if (searchTerm) {
            searchFilter = [];
            _.each(moduleQuickSearchFields, function(fieldName) {
                var obj = {};
                obj[fieldName] = {'$starts': searchTerm};
                searchFilter.push(obj);
            });

            if (searchFilter.length > 1) {
                searchFilter = {'$or' : searchFilter};
            } else {
                searchFilter = searchFilter[0];
            }

            if (_.size(filterDef) === 0) {
                // Searching on 'all records'.
                filterDef = [searchFilter];
            } else {
                // We have some filter being applied already.
                // If it's an array, push the searchFilter into the $and filterDef.
                if (_.isArray(filterDef)) {
                    filterDef.push(searchFilter);
                } else {
                    filterDef = [filterDef, searchFilter];
                }
                filterDef = {'$and': filterDef};
            }
        }

        if(_.isArray(filterDef)) {
            return filterDef;
        }

        return [filterDef];
    },

    /**
     * Reset the filter to the previous state
     * @param {String} moduleName
     * @param {String} linkName
     */
    initializeFilterState: function(moduleName, linkName) {
        moduleName = moduleName || this.module;
        var lastFilter = app.cache.get("filters:last:" + moduleName + ":" + this.layoutType),
            filterData;
        if (!(this.filters.get(lastFilter)))
            lastFilter = null;
        // TODO: This is temporary. We need to hook this up to the PreviouslyUsed API.
        if (this.layoutType === 'record' && !this.showingActivities) {
            filterData = {
                link: lastFilter || 'all_modules',
                filter: lastFilter || 'all_records'
            };
        } else {
            filterData = {
                filter: lastFilter || null
            };
        }
        this.applyPreviousFilter(moduleName, linkName, filterData);
    },
    /**
     * applies previous filter
     * @param {String} moduleName
     * @param {String} linkName
     * @param {Object} data
     */
    applyPreviousFilter: function (moduleName, linkName, data) {
        var module = moduleName || (this.showingActivities ? "Activities" : this.module),
            link = linkName || data.link;

        if (!moduleName && this.layoutType === 'record' && link !== 'all_modules' && !this.showingActivities) {
            module = app.data.getRelatedModule(module, data.link);
        }

        this.trigger('filter:change:module', module, link, true);
        this.getFilters(module, data.filter);
    },

    /**
     * Retrieves the appropriate list of filters from the server.
     * @param  {String} moduleName
     * @param  {String} defaultId
     */
    getFilters: function(moduleName, defaultId) {
        var filter = [
            {'created_by': app.user.id},
            {'module_name': moduleName}
        ], self = this;
        // TODO: Add filtering on subpanel vs. non-subpanel filters here.
        var filterLayout = app.view._getController({type:'layout',name:'filter'});
        if (filterLayout.loadedModules[moduleName] && !_.isEmpty(app.cache.get("filters:" + moduleName)))
        {
            this.filters.reset();
            var filters = app.cache.get("filters:" + moduleName);
            _.each(filters, function(f){
                self.filters.add(app.data.createBean("Filters", f));
            });
            self.handleFilterRetrieve(moduleName, defaultId);
        }
        else {
            this.filters.fetch({
                //Don't show alerts for this request
                showAlerts: false,
                filter: filter,
                success:function(){
                    if (self.disposed) return;
                    filterLayout.loadedModules[moduleName] = true;
                    app.cache.set("filters:" + moduleName, self.filters.toJSON());
                    self.handleFilterRetrieve(moduleName, defaultId);
                }
            });
        }
    },
    /**
     * handles return from filter retrieve per module
     * @param moduleName
     * @param defaultId
     */
    handleFilterRetrieve: function(moduleName, defaultId) {
        var lastFilter = app.cache.get("filters:last:" + moduleName + ":" + this.layoutType);
        var defaultFilterFromMeta,
            possibleFilters = [],
            filterMeta = this.getModuleFilterMeta(moduleName);

        if (filterMeta) {
            _.each(filterMeta, function(value) {
                if (_.isObject(value)) {
                    if (_.isObject(value.meta.filters)) {
                        this.filters.add(value.meta.filters);
                    }
                    if (value.meta.default_filter) {
                        defaultFilterFromMeta = value.meta.default_filter;
                    }
                }
            }, this);

            possibleFilters = [defaultId, defaultFilterFromMeta, 'all_records'];
            possibleFilters = _.filter(possibleFilters, this.filters.get, this.filters);
        }

        if (lastFilter && !(this.filters.get(lastFilter))){
            app.cache.cut("filters:last:" + moduleName + ":" + this.layoutType);
        }
        this.trigger('filter:render:filter');
        this.trigger('filter:change:filter', app.cache.get("filters:last:" + moduleName + ":" + this.layoutType) ||  _.first(possibleFilters) || 'all_records', true);
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
            if (_.isObject(meta.filters)) {
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
     * Get filters metadata from module metadata for a module
     * @param {String} moduleName
     * @returns {Object} filters metadata
     */
    getModuleFilterMeta: function(moduleName) {
        var meta;
        if (moduleName !== 'all_modules') {
            meta = app.metadata.getModule(moduleName);
            if (_.isObject(meta)) {
                meta = meta.filters;
            }
        }

        return meta;
    },

    /**
     * Get list of quick search fields from filters metadata.
     * @param {String} moduleName
     * @returns {Array} array of field names
     */
    getModuleQuickSearchFields: function(moduleName) {
        var meta = this.getModuleFilterMeta(moduleName),
            fields,
            priority = 0;

        if (moduleName !== 'all_modules') {
            _.each(meta, function(value, key) {
                if (_.isObject(value) && value.meta.quicksearch_field && priority < value.meta.quicksearch_priority) {
                    fields = value.meta.quicksearch_field;
                    priority = value.meta.quicksearch_priority;
                }
            });
        }

        return fields;
    },

    /**
     * @override
     * @private
     */
    _render: function() {
        if (app.acl.hasAccess(this.aclToCheck, this.module)) {
            app.view.Layout.prototype._render.call(this);
            this.initializeFilterState();
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
