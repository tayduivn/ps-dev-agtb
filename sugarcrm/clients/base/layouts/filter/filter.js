({
    className: 'filter-view search',

    initialize: function(opts) {
        app.view.layouts.FilterLayout.loadedModules = app.view.layouts.FilterLayout.loadedModules || {};
        app.view.Layout.prototype.initialize.call(this, opts);

        this.layoutType = this.context.get('layout') || this.context.get('layoutName') || app.controller.context.get('layout');

        this.aclToCheck = (this.layoutType === 'record')? 'view' : 'list';
        this.filters = app.data.createBeanCollection('Filters');

        this.emptyFilter = app.data.createBean('Filters', {
            id: 'all_records',
            name: app.lang.get('LBL_FILTER_ALL_RECORDS'),
            filter_def: {},
            editable: false
        });

        // Can't use getRelevantContextList here, because the context may not
        // have all the children we need.
        if (this.layoutType === 'records' && this.module !== 'Home') {
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
            });
        }

        this.on('filter:change:quicksearch', function(query) {
            var self = this,
                ctxList = this.getRelevantContextList();

            _.each(ctxList, function(ctx) {
                var ctxCollection = ctx.get('collection'),
                    origfilterDef = ctxCollection.filterDef || [],
                    filterDef = self.getFilterDef(origfilterDef, query, ctx),
                    options = {
                        //Show alerts for this request
                        showAlerts: true,
                        success: function() {
                            // Close the preview pane to ensure that the preview
                            // collection is in sync with the list collection.
                            app.events.trigger('preview:close');
                    }};

                ctxCollection.filterDef = filterDef;

                options = _.extend(options, ctx.get('collectionOptions'));

                ctx.resetLoadFlag(false);
                ctx.set('skipFetch', false);
                ctx.loadData(options);
                ctxCollection.filterDef = origfilterDef;
            });
        }, this);

        this.on('filter:create:close', function() {
            this.layout.trigger('filter:create:close');
        }, this);

        this.on('filter:create:open', function(filterModel) {
            this.layout.trigger('filter:create:open', filterModel);
        }, this);

        this.on('subpanel:change', function(linkName) {
            this.layout.trigger('subpanel:change', linkName);
        }, this);

        this.on('filter:get', this.initializeFilterState, this);

        this.on('filter:change:filter', function(id, preventCache) {
            if (id && id != 'create' && !preventCache) {
                app.cache.set("filters:last:" + this.layout.currentModule + ":" + this.layoutType, id);
            }
            var filter = this.filters.get(id) || this.emptyFilter,
                ctxList = this.getRelevantContextList();


            _.each(ctxList, function(ctx) {
                ctx.get('collection').filterDef = filter.get('filter_definition');
            });
            this.trigger('filter:clear:quicksearch');
        }, this);

        this.layout.on('filterpanel:change', function(name) {
            this.showingActivities = name === 'activitystream';
            var module = this.showingActivities ? "Activities" : this.module;
            var link;

            if(this.layoutType === 'record' && !this.showingActivities) {
                module = link = 'all_modules';
            } else {
                link = null;
            }
            this.trigger("filter:render:module");
            this.trigger("filter:change:module", module, link);
        }, this);

        //When a filter is saved, update the cache and set the filter to be the currently used filter
        this.layout.on('filter:add', function(model){
            this.filters.add(model);
            app.cache.set("filters:" + this.layout.currentModule, this.filters.toJSON());
            app.cache.set("filters:last:" + this.layout.currentModule + ":" + this.layoutType, model.get("id"));
            this.initializeFilterState(this.layout.currentModule, this.layout.currentLink);
        }, this);
    },

    getRelevantContextList: function() {
        var contextList = [], context;
        if (this.showingActivities) {
            context = this.layout.getActivityContext();
            if (context) {
                contextList.push(context);
            }
        } else {
            if (this.layoutType === 'records' && this.module !== "Home") {
                contextList.push(this.context);
            } else {
                if (this.context.children.length) {
                    _.each(this.context.children, function(childCtx) {
                        if (childCtx.get('link') && !childCtx.get('hidden')) {
                            contextList.push(childCtx);
                        }
                    });
                } else {
                    contextList.push(this.context.parent);
                }
            }
        }
        return contextList;
    },

    getFilterDef: function(filterDef, searchTerm, context) {
        var searchFilter,
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
                filterDef = {'$and' : [filterDef, searchFilter]};
            }
        }

        if(_.isArray(filterDef)) {
            return filterDef;
        }

        return [filterDef];
    },

    initializeFilterState: function(moduleName, linkName) {
        var self = this,
            callback = function(data) {
                var module = moduleName || (self.showingActivities? "Activities" : self.module),
                    link = linkName || data.link;

                if (!moduleName && self.layoutType === 'record' && link !== 'all_modules' && !self.showingActivities) {
                    module = app.data.getRelatedModule(module, data.link);
                }

                self.trigger('filter:change:module', module, link, true);
                self.getFilters(module, data.filter);
            };

        this.getPreviouslyUsedFilter(moduleName || this.module, callback);
    },

    /**
     * Gets previously used filters for a given module from the endpoint.
     * @param  {string}   moduleName
     * @param  {Function} callback
     */
    getPreviouslyUsedFilter: function(moduleName, callback) {
        var lastFilter = app.cache.get("filters:last:" + moduleName + ":" + this.layoutType);
        if (!(this.filters.get(lastFilter)))
            lastFilter = null;
        // TODO: This is temporary. We need to hook this up to the PreviouslyUsed API.
        if (this.layoutType === 'record' && !this.showingActivities) {
            callback({
                link: lastFilter || 'all_modules',
                filter: lastFilter || 'all_records'
            });
        } else {
            callback({
                filter: lastFilter || null
            });
        }
    },

    /**
     * Retrieves the appropriate list of filters from the server.
     * @param  {string} moduleName
     * @param  {string} defaultId
     */
    getFilters: function(moduleName, defaultId) {
        var lastFilter = app.cache.get("filters:last:" + moduleName + ":" + this.layoutType);
        var filter = [
            {'created_by': app.user.id},
            {'module_name': moduleName}
        ], self = this,
            callback = function() {
                var defaultFilterFromMeta,
                    possibleFilters = [],
                    filterMeta = self.getModuleFilterMeta(moduleName);

                if (filterMeta) {
                    _.each(filterMeta, function(value) {
                        if (_.isObject(value)) {
                            if (_.isObject(value.meta.filters)) {
                                self.filters.add(value.meta.filters);
                            }
                            if (value.meta.default_filter) {
                                defaultFilterFromMeta = value.meta.default_filter;
                            }
                        }
                    });

                    possibleFilters = [defaultId, defaultFilterFromMeta, 'all_records'];
                    possibleFilters = _.filter(possibleFilters, self.filters.get, self.filters);
                }

                if (lastFilter && !(self.filters.get(lastFilter))){
                    app.cache.cut("filters:last:" + moduleName + ":" + self.layoutType);
                }
                self.trigger('filter:render:filter');
                self.trigger('filter:change:filter', app.cache.get("filters:last:" + moduleName + ":" + self.layoutType) ||  _.first(possibleFilters) || 'all_records', true);
            };

        // TODO: Add filtering on subpanel vs. non-subpanel filters here.
        if (app.view.layouts.FilterLayout.loadedModules[moduleName] && !_.isEmpty(app.cache.get("filters:" + moduleName)))
        {
            this.filters.reset();
            var filters = app.cache.get("filters:" + moduleName);
            _.each(filters, function(f){
                self.filters.add(app.data.createBean("Filters", f));
            });
            callback();
        }
        else {
            this.filters.fetch({
                //Don't show alerts for this request
                showAlerts: false,
                filter: filter,
                success:function(){
                    app.view.layouts.FilterLayout.loadedModules[moduleName] = true;
                    app.cache.set("filters:" + moduleName, self.filters.toJSON());
                    callback();
                }
            });
        }
    },

    createPanelIsOpen: function() {
        return !this.layout.$(".filter-options").hasClass("hide");
    },

    /**
     * Determines whether a user can create a filter for the current module.
     * @return {[type]} [description]
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

    _render: function() {
        if (app.acl.hasAccess(this.aclToCheck, this.module)) {
            app.view.Layout.prototype._render.call(this);
            this.initializeFilterState();
        }
    }

})
