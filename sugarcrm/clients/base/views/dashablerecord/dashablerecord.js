/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * Dashablerecord is a dashlet representation of a module record view. Users
 * can build dashlets of this type for any accessible and approved module.
 *
 * The specific record is not configured in advance. Rather, only the
 * module to which the record belongs is. Records are loaded into the
 * dashlet via the `change:model` event.
 *
 * @class View.Views.Base.DashablerecordView
 * @alias SUGAR.App.view.views.BaseDashablerecordView
 * @extends View.Views.Base.RecordView
 */
({
    extendsFrom: 'RecordView',

    /**
     * The plugins used by this view.
     *
     * This list is the same as that of the normal record view, with the
     * following exceptions:
     *
     * * Dashlet plugin added (because this is a dashlet)
     * * Pii removed (no PII drawer)
     * * Audit removed (no Audit Log drawer)
     * * FindDuplicates removed (no find-duplicate button)
     * * Pagination added (used by the list view tabs)
     */
    plugins: [
        'SugarLogic',
        'ErrorDecoration',
        'GridBuilder',
        'Editable',
        'ToggleMoreLess',
        'Dashlet',
        'Pagination',
    ],

    /**
     * We want to load field `record` templates
     */
    fallbackFieldTemplate: 'record',

    /**
     * Modules that are permanently blacklisted so users cannot configure a
     * dashlet for these modules.
     *
     * @property {string[]}
     */
    moduleBlacklist: [
        'Campaigns',
        'Home',
        'Forecasts',
        'ProductCategories',
        'ProductTemplates',
        'ProductTypes',
        'Project',
        'ProjectTask',
        'UserSignatures',
        'OutboundEmail',
    ],

    /**
     * List of modules that should not be available as tabs
     *
     * @property {string[]}
     */
    tabBlacklist: [
        'Tags',
    ],

    /**
     * Flag indicates if a module is available for display.
     *
     * @property {boolean}
     */
    moduleIsAvailable: true,

    /**
     * Cache of the modules a user is allowed to see.
     *
     * The keys are the module names and the values are the module names after
     * resolving them against module and/or app strings. The cache logic can be
     * seen in {@link BaseDashablerecordView#_getAvailableModules}.
     *
     * @property {Object}
     */
    _availableModules: {},

    /**
     * The default settings for a record view dashlet.
     *
     * @property {Object}
     */
    _defaultSettings: {
        limit: 5, // for tabs with list view
    },

    /**
     * Denotes the mode of operation for the dashlet:
     * 'main' during normal use, 'config' during configuration.
     *
     * @property {string}
     */
    _mode: 'main',

    /**
     * List of fields we wish to banish from the header.
     *
     * @property {string[]}
     */
    _noshowFields: ['favorite', 'follow', 'badge'],

    /**
     * Size of avatars within the dashlet toolbar.
     *
     * @property {number}
     */
    _avatarSize: 28,

    /**
     * Cap on the maximum number of tabs allowed.
     *
     * @property {Object}
     */
    _tabLimit: {
        number: 6,
        label: 'LBL_SIX'
    },

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        // bolt record view metadata for the given module onto the dashlet
        options.meta = _.extend(
            {},
            options.meta,
            app.metadata.getView(options.meta.module, 'recorddashlet') ||
            app.metadata.getView(options.meta.module, 'record')
        );

        // split out the headerpane if necessary, and remove unwanted fields
        // we need to inject these into the toolbar
        if (options.meta.panels) {
            this._prepareHeader(options.meta.panels);
        }

        this._super('initialize', [options]);

        this._noAccessTemplate = app.template.get(this.name + '.noaccess');
        this._pickARecordTemplate = app.template.get(this.name + '.pick-a-record');
        this._recordsTemplate = app.template.get(this.name + '.records');
        this._recordTemplate = app.template.get(this.name + '.record');
        this._tabsTemplate = app.template.get(this.name + '.tabs');

        // listen to tab events
        this.events = _.extend(this.events || {}, {
            'click [class*="orderBy"]': 'setOrderBy',
            'click [data-action=tab-switcher]': 'tabSwitcher'
        });

        // FIXME CS-55: disable this code
        this.toggleFields(this.editableFields, false);
        this.action = 'detail'; // don't allow editing
    },

    /**
     * Must implement this method as a part of the contract with the Dashlet
     * plugin. Kicks off the various paths associated with a dashlet:
     * Configuration, preview, and display.
     *
     * @param {string} viewName The name of the view as defined by the `oninit`
     *   callback in {@link DashletView#onAttach}.
     */
    initDashlet: function(viewName) {
        this._mode = viewName;
        this._initializeSettings();

        // always save the host dashboard's module
        // (this might be different from the "base record type")
        this._baseModule = this.settings.get('base_module') || this.model.module || this.model.get('module');

        if (this._mode === 'config') {
            this._configureDashlet();
        } else if (this.meta.pseudo) {
            // re-render the pseudo-dashlet in the configuration whenever the set of selected tabs or its order changes
            this.layout.context.on('dashablerecord:config:tabs:change', function(newTabs) {
                this.meta.tabs = newTabs;
                this._initTabs(newTabs);
                this._updateViewToCurrentTab();
                this.render();
            }, this);

            this.layout.context.trigger('dashablerecord:config:tabs:change', this.meta.tabs);
        }

        this.before('render', function() {
            // ACL check
            if (!this.moduleIsAvailable || !this.model) {
                return this._noAccess();
            }

            if (this._mode === 'main' && this.model && this.model.module === this.module && this.model.dataFetched) {
                // ensure the header is populated with the relevant data if we already have it
                this._injectRecordHeader(this.model);
            }
        });

        // set model if rowModel exists, eg, in multi-line dashboard
        if (this.context.parent && this.context.parent.parent) {
            var model = this.context.parent.parent.get('rowModel');
            if (model && model.get('_module') === this.module) {
                model = app.data.createBean(this.module, {id: model.get('id')});
                model.fetch({
                    showAlerts: true,
                    success: _.bind(function(model) {
                        model.module = this.module;
                        this.switchModel(model);
                        this._injectRecordHeader(model);
                        this.render();
                    }, this)
                });
            }
        }

        this._initTabs();
    },

    /**
     * Inject the pseudo dashlet (a configuration pane) into the
     * dashletconfiguration layout.
     *
     * @private
     */
    _addPseudoDashlet: function() {
        var pseudoDashlet = this.layout.getComponent('dashlet');
        if (pseudoDashlet && pseudoDashlet.meta && pseudoDashlet.meta.pseudo) {
            return;
        }

        var metadata = {
            component: this.type,
            name: this.type,
            type: this.type,
            module: this._baseModule,
            config: [],
            preview: []
        };

        var newContext = this.context.getChildContext();
        newContext.prepare(); // try to avoid the pagination plugin complaining about collection not being defined
        var component = {
            name: metadata.component,
            type: metadata.type,
            preview: true,
            context: newContext,
            module: metadata.module,
            custom_toolbar: 'no'
        };
        component.view = _.extend({module: metadata.module}, metadata.preview, component);
        component.view.tabs = [];
        component.view.pseudo = true;

        var settingsTabs = this.settings.get('tabs');
        _.each(settingsTabs, function(tab) {
            var configTab = {};

            // handle full-object tabs
            if (!_.isUndefined(tab.link) && !_.isFunction(tab.link)) {
                tab = link;
            }

            if (tab === this._baseModule) {
                configTab.link = '';
                configTab.module = this._baseModule;
            } else if (this._getTabType(tab) === 'record') {
                configTab.link = tab;
                configTab.module = this._getRelatedModule(tab);
            } else {
                // FIXME CS-63: LIST VIEW CHANGES HERE
                configTab.module = this._getRelatedModule(tab);
                configTab.limit = this._defaultSettings.limit;
                configTab.collection = app.data.createBeanCollection(configTab.module);
                configTab.skipFetch = true;
            }

            component.view.tabs.push(configTab);
        }, this);

        var layout = {
            type: 'dashlet',
            css_class: 'dashlets',
            config: false,
            preview: false,
            module: metadata.module,
            context: this.context,
            components: [
                component
            ],
            pseudo: true
        };
        this.layout.initComponents([{layout: layout}], this.context);
    },

    /**
     * Event handler for tab switcher.
     *
     * @param {Event} event Click event.
     */
    tabSwitcher: function(event) {
        var index = this.$(event.currentTarget).data('index');

        if (this._mode === 'config') {
            if (index === this.activeConfigTab) {
                return;
            }

            this.activeConfigTab = index;
            this.render();
        } else {
            if (index === this.settings.get('activeTab')) {
                return;
            }

            this.settings.set('activeTab', index);
            var tab = this.tabs[index];
            this.currentTab = tab;
            this.collection = this.tabs[index].collection || null;
            this.context.set('collection', this.collection);
            this._updateViewToCurrentTab();

            if (this.collection && !this.collection.dataFetched && !tab.skipFetch && !this.meta.pseudo) {
                this._loadDataForTabs([tab]);
            } else {
                this.render();
            }
        }
    },

    /**
     * Update the view with tab data at the view level so that the
     * templates can reference the correct meta, model and module
     *
     * @private
     */
    _updateViewToCurrentTab: function() {
        var tab = this.currentTab;
        if (!tab) {
            return;
        }
        this.model = tab.model;
        var linkName = tab.link;
        if (linkName) {
            this.module = linkName === this._baseModule ? tab.module : tab.relatedModule;
        } else {
            this.module = tab.module;
        }
        this.meta = _.extend(this.meta, tab.meta);
        this._buildGridsFromPanelsMetadata();
        this.collection = tab.collection || null;
        this.context.set('model', this.model, {silent: true});
        this._prepareHeader(this.meta.panels);
        // TODO update this.collection as well as part of CS-63?
    },

    /**
     * Set order by on collection.
     * The event is canceled if an element being dragged is found.
     *
     * @param {Event} event jQuery event object.
     */
    setOrderBy: function(event) {
        var $target = $(event.currentTarget);

        if ($target.find('ui-draggable-dragging').length) {
            return;
        }

        var tab = this.tabs[this.settings.get('activeTab')];
        var collection = tab.collection;
        // first check if alternate orderby is set for column
        var orderBy = $target.data('orderby');
        // if no alternate orderby, use the field name
        if (!orderBy) {
            orderBy = $target.data('fieldname');
        }
        if (!_.isEmpty(orderBy) && !app.acl.hasAccess('read', tab.module, app.user.get('id'), orderBy)) {
            // no read access to the orderBy field, don't bother to reload data
            return;
        }
        // if same field just flip
        if (orderBy === tab.order_by.field) {
            tab.order_by.direction = tab.order_by.direction === 'desc' ? 'asc' : 'desc';
        } else {
            tab.order_by.field = orderBy;
            tab.order_by.direction = 'desc';
        }

        collection.orderBy = tab.order_by;
        collection.resetPagination();
        this._loadDataForTabs([tab]);
    },

    /**
     * Create collection based on tab properties and current context.
     *
     * @param {Object} tab Tab properties.
     * @return {Data.BeanCollection|null} A new instance of bean collection or `null`
     *   if we cannot access module metadata.
     * @private
     */
    _createCollection: function(tab) {
        var meta = app.metadata.getModule(this.module);
        if (_.isUndefined(meta)) {
            return null;
        }
        var options = {};
        if (meta.fields[tab.link] && meta.fields[tab.link].type === 'link') {
            options = {
                link: {
                    name: tab.link,
                    bean: this.model
                }
            };
        }
        var collection = app.data.createBeanCollection(tab.module, [], options);
        return collection;
    },

    /**
     * Retrieve collection options for a specific tab.
     *
     * @param {Object} tab The tab.
     * @return {Object} Collection options.
     * @return {number} return.limit The number of records to retrieve.
     * @return {Object} return.params Additional parameters to the API call.
     * @return {Object|null} return.fields Specifies the fields on each
     * requested model.
     * @private
     */
    _getCollectionOptions: function(tab) {
        var options = {
            limit: tab.limit || this.settings.get('limit'),
            relate: tab.relate,
            params: {
                order_by: !_.isEmpty(tab.order_by) ? tab.order_by.field + ':' + tab.order_by.direction : null,
                include_child_items: tab.include_child_items || null
            },
            fields: tab.fields || null
        };

        return options;
    },

    /**
     * Retrieve pagination options for current tab. Called by 'Pagination' plugin.
     *
     * @return {Object} Pagination options.
     */
    getPaginationOptions: function() {
        return this._getCollectionOptions(this.tabs[this.settings.get('activeTab')]);
    },

    /**
     * Fetch data for tabs.
     *
     * @param {Object} [options={}] Options that are passed to collection/model's
     *   fetch method.
     */
    loadData: function(options) {
        if (this.disposed || this._mode === 'config' || this._mode === 'preview') {
            return;
        }
        this._super('loadData', [options]);
        this._loadDataForTabs(this.tabs, options);
    },

    /**
     * Load data for passed set of tabs.
     *
     * @param {Object[]} tabs Set of tabs to update.
     * @param {Object} [options={}] load options.
     * @private
     */
    _loadDataForTabs: function(tabs, options) {
        // don't load data on the pseudo config  or preview dashlet
        if (this.meta.pseudo || this._mode === 'preview') {
            return;
        }

        options = options || {};
        var self = this;
        var loadDataRequests = [];
        _.each(tabs, function(tab, index) {
            if (!tab.collection || tab.skipFetch) {
                return;
            }
            loadDataRequests.push(function(callback) {
                tab.collection.setOption(self._getCollectionOptions(tab));
                tab.collection.fetch({
                    complete: function() {
                        tab.collection.dataFetched = true;
                        callback(null);
                    }
                });
            });
        }, this);
        if (!_.isEmpty(loadDataRequests)) {
            async.parallel(loadDataRequests, function() {
                if (self.disposed) {
                    return;
                }
                self.collection = self.tabs[self.settings.get('activeTab')].collection;
                self.context.set('collection', self.collection);
                self.render();

                if (_.isFunction(options.complete)) {
                    options.complete.call(self);
                }
            });
        }
    },

    /**
     * Get the fields metadata for a tab.
     *
     * @param {Object} tab The tab.
     * @return {Object[]} The fields metadata or an empty array.
     * @private
     */
    _getFieldMetaForTab: function(tab) {
        var meta = app.metadata.getView(tab.module, 'list') || {};
        return this._getFieldMetaForView(meta);
    },

    /**
     * Get the columns to display for a tab.
     *
     * @param tab {Object} Tab to display.
     * @return {Object[]} Array of objects defining the field metadata for
     *   each column.
     * @private
     */
    _getColumnsForTab: function(tab) {
        var columns = [];
        var fields = this._getFieldMetaForTab(tab);
        var moduleMeta = app.metadata.getModule(tab.module);

        _.each(tab.fields, function(name) {
            var field = _.find(fields, function(field) {
                return field.name === name;
            }, this);

            // field may not be in module's list view metadata
            field = field || app.metadata._patchFields(tab.module, moduleMeta, [name]);

            // handle setting of the sortable flag on the list
            // this will not always be true
            var sortableFlag;
            var fieldDef = moduleMeta.fields[field.name];

            // if the module's field def says nothing about the sortability, then
            // assume it's ok to sort
            if (_.isUndefined(fieldDef) || _.isUndefined(fieldDef.sortable)) {
                sortableFlag = true;
            } else {
                // Get what the field def says it is supposed to do
                sortableFlag = !!fieldDef.sortable;
            }

            var column = _.extend({sortable: sortableFlag}, field);
            columns.push(column);
        }, this);

        return columns;
    },

    /**
     * @inheritdoc
     *
     * New model related properties are injected into each model.
     */
    _renderHtml: function() {
        if (this.meta.config) {
            this._super('_renderHtml');
            return;
        }

        this.tabsHtml = this._tabsTemplate(this);

        var tab = this.currentTab;

        var tabType = tab && tab.type;

        this.tabContentHtml = tabType === 'list' ? this._recordsTemplate(this) : this._recordTemplate(this);

        // Link to studio if showing a single record
        if (this.meta.pseudo && tabType === 'record') {
            this.showStudioText = true;
            this.linkToStudio = '#bwc/index.php?module=ModuleBuilder&action=index&type=studio';
        } else {
            this.showStudioText = false;
        }
        this._showHideListBottom(tab);

        this._super('_renderHtml');
    },

    /**
     * @override
     *
     * Listen to change:model event to populate this dashlet with a new bean.
     */
    bindDataChange: function() {
        this.context.on('change:model', function(ctx, model) {
            this.switchModel(model);

            this._injectRecordHeader(model);

            this.render();
        }, this);
    },

    /**
     * @override
     */
    delegateButtonEvents: function() {
        // don't do anything, we don't have any of the buttons from the regular record view
        // For CS-55 we may want to change that.
    },

    /**
     * @override
     */
    editClicked: function() {
        // the dashlet toolbar is triggering record view's editClicked, so override it here
        this.layout.getComponent('dashlet-toolbar').editClicked();
    },

    /**
     * Use the given model to render this dashlet.
     * This transfers any events from the existing model to the new one.
     *
     * @param {Data.Bean} model Model to render.
     */
    switchModel: function(model) {
        this.model && this.model.abortFetchRequest();
        this.stopListening(this.model);
        this.model = model;
        this._initTabs();
    },

    /**
     * @inheritdoc
     *
     * Show the "pick-a-record" template if our current model is from the
     * wrong module.
     */
    _render: function() {
        if (this.model.module !== this.module && this._mode !== 'config') {
            this.$el.html(this._pickARecordTemplate());
            return;
        }
        this._super('_render');
    },

    /**
     * Certain dashlet settings can be defaulted.
     *
     * Builds the available module cache by way of the
     * {@link BaseDashablerecordView#_setDefaultModule} call.
     *
     * @private
     */
    _initializeSettings: function() {
        var settings = _.extend(
            {},
            this._defaultSettings,
            this.settings.attributes
        );
        this.settings.set(settings);
        this._setDefaultModule();
        if (!this.settings.get('label')) {
            this.settings.set('label', 'LBL_MODULE_NAME');
        }
    },

    /**
     * Sets the default module when a module isn't defined in this dashlet's
     * view definition.
     *
     * If the module was defined but it is not in the list of available modules
     * in config mode, then the view's module will be used.
     *
     * @private
     */
    _setDefaultModule: function() {
        var availableModules = this._getAvailableModules();
        var metadata = app.metadata.getModule(this.model.module);
        var fields = metadata && metadata.fields;
        var module = this.settings.get('module') || this.context.get('module');

        // note: the module in the settings might actually be the name of a link field
        if (fields && this._isALink(module, fields)) {
            module = fields[module].module;
        }

        if (module in availableModules) {
            this.settings.set('module', module);
        } else if (this._mode === 'config') {
            module = this.context.parent.get('module');
            if (_.contains(this.moduleBlacklist, module)) {
                module = _.first(_.keys(availableModules));
                // On 'initialize' model is set to context's model - that model can have no access at all
                // and we'll result in 'no-access' template after render. So we change it to default model.
                this.model = app.data.createBean(module);
            }
            this.settings.set('module', module);
        } else {
            this.moduleIsAvailable = false;
        }
    },

    /**
     * Perform any necessary setup before the user can configure the dashlet.
     *
     * Modifies the dashlet configuration panel metadata to allow it to be
     * dynamically primed prior to rendering.
     *
     * @private
     */
    _configureDashlet: function() {
        var availableModules = this._getAvailableModules();
        var validTabs = this._getValidTabs(_.keys(availableModules));

        _.each(this._getFieldMetaForView(this.meta), function(field) {
            if (field.name === 'module' || field.name === 'tabs') {
                // load the list of available modules into the metadata
                field.options = validTabs;
                field.default = this.module;
            }
        }, this);

        this.listenTo(this.layout, 'init', this._addPseudoDashlet);

        // load the previously selected tabs by default
        var initialTabs = this.settings.get('tabs') || [this.settings.get('module')];
        this.settings.set('tabs', initialTabs);

        this._bindSettingsEvents();
        this._bindSaveEvents();
    },

    /**
     * Gets all of the modules the current user can see.
     *
     * This is used for populating the module select field.
     * Filters out any modules that are blacklisted.
     *
     * @return {Object} {@link BaseDashablerecordView#_availableModules}
     * @private
     */
    _getAvailableModules: function() {
        if (_.isEmpty(this._availableModules) || !_.isObject(this._availableModules)) {
            this._availableModules = {};
            var visibleModules = app.metadata.getModuleNames({filter: 'visible', access: 'read'});
            var allowedModules = _.difference(visibleModules, this.moduleBlacklist);

            _.each(allowedModules, function(module) {
                var recordMeta = this._getRecordMeta(module);
                var hasRecordView = !_.isEmpty(this._getFieldMetaForView(recordMeta));
                if (hasRecordView) {
                    this._availableModules[module] = app.lang.getModuleName(module, {plural: true});
                }
            }, this);
        }
        return this._availableModules;
    },

    /**
     * Gets the fields metadata from a particular view's metadata.
     *
     * @param {Object} meta The view's metadata.
     * @return {Object[]} The fields metadata or an empty array.
     * @private
     */
    _getFieldMetaForView: function(meta) {
        meta = _.isObject(meta) ? meta : {};
        return _.compact(!_.isUndefined(meta.panels) ? _.flatten(_.pluck(meta.panels, 'fields')) : []);
    },

    /**
     * Gets the correct record view metadata.
     *
     * @param {string} module
     * @return {Object} The correct module record metadata.
     * @private
     */
    _getRecordMeta: function(module) {
        return app.metadata.getView(module, 'record');
    },

    /**
     * Renders the no-access template, then aborts further rendering.
     *
     * @return {boolean} Always returns `false`.
     * @private
     */
    _noAccess: function() {
        this.$el.html(this._noAccessTemplate());
        return false;
    },

    /**
     * Prepare the header fields from the given panels.
     *
     * @param {Object[]} panels Record view panel metadata.
     * @private
     */
    _prepareHeader: function(panels) {
        var headerIndex = _.findIndex(panels, function(panel) {
            return panel.header === true;
        });
        if (headerIndex !== -1) {
            var header = panels.splice(headerIndex, 1)[0];
            var fields = _.filter(header.fields, _.bind(function(field) {
                return !field.type || !_.includes(this._noshowFields, field.type);
            }, this));

            // shrink certain header fields down for the toolbar
            _.each(fields, function(field) {
                if (field.size) {
                    field.size = 'button';
                }
                if (field.type === 'avatar') {
                    field.height = field.height ? Math.min(field.height, this._avatarSize) : this._avatarSize;
                    field.width = field.width ? Math.min(field.width, this._avatarSize) : this._avatarSize;
                }
            }, this);
        }
        this._headerFields = fields || [];
        this._injectRecordHeader();
    },

    /**
     * Send header fielddefs and model data to the dashlet toolbar.
     *
     * @param {Data.Bean} [model] Model to send to the toolbar. If undefined,
     *   the header fielddefs will be sent to the toolbar but the model data
     *   will not.
     * @private
     */
    _injectRecordHeader: function(model) {
        if (this.meta && this.meta.pseudo) {
            return;
        }
        // inject header content into dashlet toolbar
        var dashletToolbar = this.layout && this.layout.getComponent('dashlet-toolbar');
        if (dashletToolbar) {
            var toolbarCtx = dashletToolbar.context;
            toolbarCtx.trigger('dashlet:toolbar:change', this._headerFields, model);
        }
    },

    /**
     * Reset the configuration tabs after a base record type switch.
     *
     * @private
     */
    _resetConfigTabs: function() {
        this.activeConfigTab = 0;
    },

    /**
     * Gets the dashletconfiguration layout.
     *
     * @return {View.Layout} The dashletconfiguration layout.
     * @private
     */
    _getDashletConfiguration: function() {
        return this.closestComponent('dashletconfiguration');
    },

    /**
     * Turn the dashlet configuration save button on or off.
     *
     * @param {bool} enabled true to enable and false to disable.
     * @private
     */
    _toggleDashletSaveButton: function(enabled) {
        this._getDashletConfiguration().trigger('dashletconfig:save:toggle', enabled);
    },

    /**
     * Initialize tabs.
     *
     * @param {Object[]} [newTabs] List of new tabs.
     * @private
     */
    _initTabs: function(newTabs) {
        this.tabs = [];

        var dashletTabs;
        if (this._mode === 'config') {
            dashletTabs = this.meta.tabs;
        } else {
            dashletTabs = newTabs || this._getTabsFromSettings() || this.meta.tabs;
        }

        _.each(dashletTabs, function(tab, index) {
            if (tab.active) {
                this.settings.set('activeTab', index);
                this.currentTab = tab;
            }

            tab.type = tab.type || this._getTabType(tab.link);
            if (tab.type === 'list') {
                var collection = this._createCollection(tab);
                if (_.isNull(collection)) {
                    return;
                }

                tab.collection = collection;
                tab.relate = _.isObject(collection.link);
                tab.include_child_items = tab.include_child_items || false;
                tab.collection.display_columns = [{
                    fields: this._getColumnsForTab(tab),
                    module: tab.module
                }];
                tab.collection.orderBy = tab.order_by || {};
                this.tabs[index] = tab;
            } else if (tab.type === 'record') {
                // Single record (record view tab)
                var module = tab.link && tab.link === this._baseModule ? tab.module : tab.relatedModule;
                var linkName = tab.link;
                if (linkName) {
                    module = linkName === this._baseModule ? tab.module : tab.relatedModule;
                } else {
                    module = tab.module;
                }
                tab.meta = app.metadata.getView(module, 'recorddashlet') || app.metadata.getView(module, 'record');
                // TODO need the correct ID here
                tab.model = app.data.createBean(module);
                this.tabs[index] = tab;
            }
        }, this);

        if (this.tabs.length === 1) {
            this.currentTab = this.tabs[0];
            // don't show tabs if there is only one
            this.tabs = [];
        }
    },

    /**
     * Given a list of tab names (either base module or a link field name),
     * return a list of tabs suitable for rendering.
     *
     * @param {string[]} tablist List of tabs (base module or link name).
     * @param {Object} [options] Additional tab options (to be applied to every
     *   tab.)
     * @return {Object[]} The list of tab objects.
     * @private
     */
    _generateConfigTabs: function(tablist, options) {
        if (_.isEmpty(tablist)) {
            return [];
        }

        options = options || {};

        return _.map(tablist, function(tab) {
            var link = tab;
            if (_.isObject(tab)) {
                link = tab.link || tab.module;
            }

            var tabType = this._getTabType(link);
            var relatedModule = this._getRelatedModule(link);

            var baseOptions = {
                type: tabType,
                label: this._getLinkLabel(link),
                module: this._baseModule,
                relatedModule: relatedModule,
                link: link
            };

            // FIXME CS-63: we'll probably want to do this differently later
            if (tabType === 'list') {
                baseOptions.fields = _.pluck(app.metadata.getView(relatedModule, 'list').panels[0].fields, 'name');
                baseOptions.limit = this._defaultSettings.limit;
                baseOptions.skipFetch = true;
            }

            return _.extend(baseOptions, options);
        }, this);
    },

    /**
     * Determine whether a tab for the given link name is a list type or a
     * record type.
     *
     * @param {string} linkName The link name.
     * @return {string} "list" if this tab should be a list view and "record"
     *   otherwise.
     * @private
     */
    _getTabType: function(linkName) {
        return app.data.canHaveMany(this._baseModule, linkName) ? 'list' : 'record';
    },

    /**
     * Get the link label for the given link field name.
     *
     * @param {string} linkName Name of the link field.
     * @return {string}
     * @private
     */
    _getLinkLabel: function(linkName) {
        if (linkName === this._baseModule) {
            return this._getBaseRecordLabel();
        }

        var fields = this._getBaseModuleFields();
        var linkField = fields[linkName];
        var module = this._getModuleFromLinkField(linkField);

        return app.lang.get(
            linkField.vname,
            [this._baseModule, module]
        );
    },

    /**
     * Get the related module given a link name,
     * relative to the base module.
     *
     * If given the base module, just return that.
     *
     * @param {string} linkName Link field name.
     * @return {string} Name of the related module.
     * @private
     */
    _getRelatedModule: function(linkName) {
        if (linkName === this._baseModule) {
            return this._getBaseRecordLabel();
        }

        return this._getModuleFromLinkField(this._getBaseModuleFields()[linkName]);
    },

    /**
     * Get the module from a link field.
     *
     * @param {Object} linkField The link field vardef.
     * @return {string} The name of the module.
     * @private
     */
    _getModuleFromLinkField: function(linkField) {
        if (!linkField || !linkField.name) {
            return '';
        }

        if (linkField.module) {
            return linkField.module;
        }

        // a lot of link fields don't actually have a module on them (look at the Accounts vardef for proof)
        // in that case, determine the module from the relationship rather than the link
        return app.data.getRelatedModule(this._baseModule, linkField.name);
    },

    /**
     * For the pseudo dashlet, get a list of tabs to show from the config
     * settings.
     *
     * @return {Object[]} List of dashlet tabs as retrieved from settings.
     * @private
     */
    _getTabsFromSettings: function() {
        var tabs = [];
        var tabSettings = this.settings.get('tabs');

        _.each(tabSettings, function(tab) {
            var tabType;
            var relatedModule;

            // convert from full tab object to just the name
            if (_.isObject(tab)) {
                if (!_.isUndefined(tab.link) && tab.module && tab.module === this._baseModule) {
                    tab = this._baseModule;
                } else if (tab.link) {
                    tab = tab.link;
                }
            }

            // determine the related module and tab type (record/list)
            if (tab === this._baseModule) {
                relatedModule = this._baseModule;
                tabType = 'record';
            } else {
                relatedModule = this._getRelatedModule(tab);
                tabType = this._getTabType(tab);
            }

            // set up the dashlet based on its type
            var dashletTab;
            if (tabType === 'list') {
                // FIXME CS-63: This is obviously mostly wrong
                dashletTab = {
                    fields: ['name'],
                    limit: this.settings.get('limit'),
                    link: tab,
                    module: relatedModule,
                    order_by: {},
                };
            } else if (tabType === 'record') {
                dashletTab = {
                    label: 'LBL_MODULE_NAME_SINGULAR',
                    module: relatedModule
                };
            }
            dashletTab.type = tabType;

            tabs.push(dashletTab);
        }, this);

        // default to making the first tab active
        if (tabs.length >= 1) {
            tabs[0].active = true;
        }

        return tabs;
    },

    /**
     * Check if the given string corresponds to a link field.
     *
     * @param {string} str The name to check.
     * @param {Object} fields The fielddefs for the module to check.
     * @return {boolean} true if the given name corresponds to a link field,
     *   false otherwise.
     * @private
     */
    _isALink: function(str, fields) {
        return !!(fields[str] && fields[str].type === 'link');
    },

    /**
     * Get a translated label of the form "This <module>"
     * for the base module.
     *
     * @return {string}
     * @private
     */
    _getBaseRecordLabel: function() {
        return app.lang.get(
            'TPL_DASHLET_RECORDVIEW_THIS_RECORD_TYPE',
            null,
            {moduleSingular: app.lang.getModuleName(this._baseModule)}
        );
    },

    /**
     * Get the list of acceptable tabs filtered across available modules,
     * which are related to the base module.
     *
     * @param {string[]} availableModules The list of available modules.
     * @return {Object} A mapping of link field names to display link labels.
     * @private
     */
    _getValidTabs: function(availableModules) {
        var baseRecordTypes = {};

        // get the "This Account" label
        baseRecordTypes[this._baseModule] = this._getBaseRecordLabel();

        // find the related module for each link field and make sure we can use it
        var linkFields = this._getBaseModuleLinks();
        _.each(linkFields, function(linkField) {
            var relatedModule = app.data.getRelatedModule(this._baseModule, linkField.name);

            if (!_.contains(availableModules, relatedModule) || _.contains(this.tabBlacklist, relatedModule)) {
                return;
            }

            baseRecordTypes[linkField.name] = this._getLinkLabel(linkField.name);
        }, this);

        return baseRecordTypes;
    },

    /**
     * In config mode, bind events that occur when the configuration options
     * are (about to be) saved.
     *
     * @private
     */
    _bindSaveEvents: function() {
        this.layout.before('dashletconfig:save', function() {
            // save the dashlet tabs settings in full, not the strings-only version
            var tabNames = this.settings.get('tabs');

            var settings = {
                activeTab: 0, // reset to initial tab
                base_module: this._baseModule,
                label: this.settings.get('label'),
                tabs: this._generateConfigTabs(tabNames)
            };

            // don't save unwanted record view metadata into the dashlet. Just whitelist what we need.
            this.settings.clear({silent: true});

            // FIXME CS-63: when we do the list view config we'll probably want to do this differently
            this.settings.set(settings, {silent: true});
        }, this);
    },

    /**
     * In config mode, bind events that occur when the configuration options
     * change.
     *
     * @private
     */
    _bindSettingsEvents: function() {
        this.settings.on('change:module', function(model, moduleName) {
            this.dashModel.set('module', moduleName);

            // clear out any previously selected tabs, except for the new base record type
            this._resetConfigTabs();
            this.settings.set('tabs', [moduleName]);
        }, this);

        this.settings.on('change:tabs', function(model, tabs) {
            // show warning message if too many tabs
            if (tabs && tabs.length > this._tabLimit.number) {
                this._showTooManyTabsWarning();
            }

            // disable save button on both 0 and too many tabs
            var enableSaveButton = tabs && tabs.length && tabs.length <= this._tabLimit.number;
            this._toggleDashletSaveButton(enableSaveButton);

            // for rendering the tabs in the config
            var configTabs = this._generateConfigTabs(tabs || [], {skipFetch: true});
            var configDashletLayout = this.layout.getComponent('dashlet');
            configDashletLayout.context.trigger('dashablerecord:config:tabs:change', configTabs);
        }, this);
    },

    /**
     * Show a warning that there are too many tabs selected.
     *
     * @private
     */
    _showTooManyTabsWarning: function() {
        app.alert.show('too_many_tabs', {
            level: 'warning',
            messages: app.lang.get(
                'TPL_DASHLET_RECORDVIEW_TOO_MANY_TABS',
                null,
                {num: this._tabLimit.number, numWord: app.lang.get(this._tabLimit.label)}
            )
        });
    },

    /**
     * Return the fielddefs from the base module.
     *
     * @return {Object} Fielddefs from the base module.
     * @private
     */
    _getBaseModuleFields: function() {
        if (this._baseModuleFields) {
            return this._baseModuleFields;
        }

        this._baseModuleFields = app.metadata.getModule(this._baseModule).fields;
        return this._baseModuleFields;
    },

    /**
     * Get all fields of type link from the base module.
     *
     * @return {Object[]} List of base module fields of type link.
     * @private
     */
    _getBaseModuleLinks: function() {
        return _.filter(this._getBaseModuleFields(), function(field) {
            return field.type && field.type === 'link';
        });
    },

    /**
     * Show or hide the list-bottom component depending on the tab type.
     *
     * @param {Object} tab Tab to be shown.
     * @private
     */
    _showHideListBottom: function(tab) {
        var listBottom = this.layout.getComponent('list-bottom');
        if (listBottom) {
            tab && tab.type === 'list' ? listBottom.show() : listBottom.hide();
        }
    }
})
