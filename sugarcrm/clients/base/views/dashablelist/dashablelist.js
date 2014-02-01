/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * Dashablelist is a dashlet representation of a module list view. Users can
 * build dashlets of this type for any accessible and approved module with
 * their choice of columns from the list view for their chosen module.
 *
 * Options:
 * {String}  module             The module from which the records are
 *                              retrieved.
 * {String}  label              The string (i18n or hard-coded) representing
 *                              the dashlet name that the user sees.
 * {Array}   display_columns    The field names of the columns to include in
 *                              the list view.
 * {String}  my_items           Allows for limiting results to only records
 *                              assigned to the user. If '0' or undefined, then
 *                              all records may be returned. If '1', then
 *                              records assigned to the user will be returned.
 * {String}  favorites          Allows for limiting the results to only records
 *                              the user has favorited. If '0' or undefined,
 *                              then all records may be returned. If '1', then
 *                              records the user has favorited will be
 *                              returned.
 * {Integer} limit              The number of records to retrieve for the list
 *                              view.
 * {Integer} auto_refresh       How frequently (in minutes) that the dashlet
 *                              should refresh its data collection.
 *
 * Example:
 * <pre><code>
 * // ...
 * array(
 *     'module'          => 'Accounts',
 *     'label'           => 'LBL_MODULE_NAME',
 *     'display_columns' => array(
 *         'name',
 *         'phone_office',
 *         'billing_address_country',
 *     ),
 *     'my_items'        => '0',
 *     'favorites'       => '1',
 *     'limit'           => 15,
 *     'auto_refresh'    => 5,
 * ),
 * //...
 * </code></pre>
 *
 * @class View.BaseDashablelistView
 * @alias SUGAR.App.view.views.BaseDashablelistView
 * @extends View.BaseListView
 */
({
    extendsFrom: 'ListView',

    /**
     * The plugins used by this view.
     */
    plugins: ['Dashlet', 'Pagination'],

    /**
     * We want to load field `list` templates
     */
    fallbackFieldTemplate: 'list',

    /**
     * The default settings for a list view dashlet.
     *
     * @property {Object}
     */
    _defaultSettings: {
        limit: 5,
        my_items: '1',
        favorites: '0',
        intelligent: '0'
    },

    /**
     * Modules that are permanently blacklisted so users cannot configure a
     * dashlet for these modules.
     *
     * @property {Array}
     */
    moduleBlacklist: ['Home', 'Forecasts', 'ProductCategories', 'ProductTemplates'],

    /**
     * Cache of the modules a user is allowed to see.
     *
     * The keys are the module names and the values are the module names after
     * resolving them against module and/or app strings. The cache logic can be
     * seen in {@link BaseDashablelistView#_getAvailableModules}.
     *
     * @property {Object}
     */
    _availableModules: {},

    /**
     * Cache of the fields found in each module's list view definition.
     *
     * This hash is multi-dimensional. The first set of keys are the module
     * names and the values are objects where the keys are the field names and
     * the values are the field names after resolving them against module
     * and/or app strings. The cache logic can be seen in
     * {@link BaseDashablelistView#_getAvailableColumns}.
     *
     * @property {Object}
     */
    _availableColumns: {},

    /**
     * Flag indicates if dashlet is intelligent.
     */
    intelligent: null,

    /**
     * Flag indicates if a module is available for display.
     */
    moduleIsAvailable: true,

    /**
     * {@inheritDoc}
     *
     * Append lastStateID on metadata in order to active user cache.
     */
    initialize: function(options) {
        options.meta = _.extend({}, options.meta, {
            last_state: {
                id: 'dashable-list'
            }
        });
        this.checkIntelligence();
        this._super('initialize', [options]);
        this._noAccessTemplate = app.template.get(this.name + '.noaccess');
    },

    /**
     * Check if dashlet can be intelligent.
     */
    checkIntelligence: function () {
        if (app.controller.context.get('layout') !== 'record' ||
            _. contains(this.moduleBlacklist, app.controller.context.get('module'))
        ) {
            this.intelligent = '0';
        } else {
            this.intelligent = '1';
        }
    },

    /**
     * Show/hide `linked_fields` field.
     *
     * @param {String} visible Show field if `1` or hide otherwise.
     * @param {String} intelligent Flag displayed if dashlet is in intelligent mode.
     */
    setLinkedFieldVisibility: function(visible, intelligent) {
        var field = this.getField('linked_fields'),
            fieldEl = this.$('[data-name=linked_fields]');
        intelligent = intelligent || '1';
        if (!field) {
            return;
        }
        if (visible === '1' && intelligent === '1' && !_.isEmpty(field.items)) {
            fieldEl.show();
        } else {
            fieldEl.hide();
        }
    },

    /**
     * Must implement this method as a part of the contract with the Dashlet
     * plugin. Kicks off the various paths associated with a dashlet:
     * Configuration, preview, and display.
     *
     * @param {String} view The name of the view as defined by the `oninit`
     *   callback in {@link DashletView#onAttach}.
     */
    initDashlet: function(view) {
        if (this.meta.config) {
            // keep the display_columns and label fields in sync with the selected module when configuring a dashlet
            this.settings.on('change:module', function(model, moduleName) {
                var label = (model.get('my_items') == '1') ? 'TPL_DASHLET_MY_MODULE' : 'LBL_MODULE_NAME';
                model.set('label', app.lang.get(label, moduleName, {
                    module: app.lang.getAppListStrings('moduleList')[moduleName]
                }));

                this._reinitializeFilterPanel(moduleName, null, 'all_records');
                this._updateDisplayColumns();
                this.updateLinkedFields(moduleName);
            }, this);
            this.settings.on('change:intelligent', function(model, intelligent) {
                this.setLinkedFieldVisibility('1', intelligent);
            }, this);
            this.on('render', function() {
                if (_.isEmpty(this.settings.get('linked_fields'))) {
                    this.setLinkedFieldVisibility('0');
                }
            }, this);
            this.listenTo(this.layout, 'render', function() {
                if (this.meta.config) {
                    this._reinitializeFilterPanel(this.context.get('module'), null, this.settings.get('filterId'));
                }
            });
        }
        this._initializeSettings();

        if (this.settings.get('intelligent') === '1') {

            var link = this.settings.get('linked_fields'),
                model = app.controller.context.get('model'),
                module = this.settings.get('module'),
                options = {
                    link: {
                        name: link,
                        bean: model
                    },
                    relate: true
                };
            this.collection = app.data.createBeanCollection(module, null, options);
            this.context.set('collection', this.collection);
        }

        this.before('render', function() {
            if (!this.moduleIsAvailable) {
                this.$el.html(this._noAccessTemplate());
                return false;
            }
        });

        // the pivot point for the various dashlet paths
        if (this.meta.config) {
            this._configureDashlet();
        } else if (this.moduleIsAvailable) {
            var filterId = this.settings.get('filterId');
            if (filterId) {
                var filterDef = this._getFilterDefFromMeta(filterId);
                if (filterDef) {
                    this._displayDashlet(filterDef);
                } else {
                    this._fetchCustomFilter(filterId, {
                        success: _.bind(function(data) {
                            var filterDef = data.filter_definition;
                            this._displayDashlet(filterDef);
                        }, this),
                        error: _.bind(function(err) {
                            this._displayDashlet();
                        }, this)
                    });
                }
            } else {
                this._displayDashlet();
            }
        }
        this.metaFields = this.meta.panels && _.first(this.meta.panels).fields || [];
    },

    /**
     * Fetch the next pagination records.
     */
    showMoreRecords: function() {
        //Show alerts for this request
        // Override default collection options if they exist
        this.getNextPagination(this.context.get('collectionOptions'));
    },

    /**
     * Returns a custom label for this dashlet.
     *
     * @returns {String}
     */
    getLabel: function() {
        var module = this.settings.get('module') || this.context.get('module'),
            moduleName = app.lang.getAppListStrings('moduleList')[module];
        return app.lang.get(this.settings.get('label'), module, {module: moduleName});
    },

    /**
     * Trigger's a reinitialization event on the filterpanel, and sets the
     * current module, link module, and filter ID on the filter layout.
     *
     * @param {String} module The current module.
     * @param {String} link The link module.
     * @param {String} filterId The filter ID to set on the filterpanel.
     * @private
     */
    _reinitializeFilterPanel: function(module, link, filterId) {
        var filterPanelLayout = this.layout.getComponent('filterpanel'),
            filterLayout = filterPanelLayout ? filterPanelLayout.getComponent('filter') : null;

        if (filterLayout) {
            filterLayout.trigger('filter:get', module, link, filterId);
        }
    },

    /**
     * Certain dashlet settings can be defaulted.
     *
     * Builds the available module cache by way of the
     * {@link BaseDashablelistView#_setDefaultModule} call. The module is set
     * after "my_items" because the value of "my_items" could impact the value
     * of "label" when the label is set in response to the module change while
     * in configuration mode (see the "module:change" listener in
     * {@link BaseDashablelistView#initDashlet}).
     *
     * @private
     */
    _initializeSettings: function() {
        if (this.intelligent === '0') {
            _.each(this.dashletConfig.panels, function(panel) {
                panel.fields = panel.fields.filter(function(el) {return el.name !== 'intelligent'; });
            }, this);
            this.settings.set('intelligent', '0');
            this.model.set('intelligent', '0');
        } else {
            if (_.isUndefined(this.settings.get('intelligent'))) {
                this.settings.set('intelligent', this._defaultSettings.intelligent);
            }
        }
        this.setLinkedFieldVisibility('1', this.settings.get('intelligent'));
        if (!this.settings.get('limit')) {
            this.settings.set('limit', this._defaultSettings.limit);
        }
        if (!this.settings.get('my_items')) {
            this.settings.set('my_items', this._defaultSettings.my_items);
        }
        if (!this.settings.get('favorites')) {
            this.settings.set('favorites', this._defaultSettings.favorites);
        }
        this._setDefaultModule();
        if (!this.settings.get('label')) {
            this.settings.set('label', 'LBL_MODULE_NAME');
        }
    },

    /**
     * Sets the default module when a module isn't defined in the dashlet's
     * view definition.
     *
     * If the module was defined but it is not in the list of available modules
     * in config mode, then the view's module will be used.
     * @private
     */
    _setDefaultModule: function() {
        var availableModules = _.keys(this._getAvailableModules()),
            module = this.settings.get('module') || this.context.get('module');

        if (_.contains(availableModules, module)) {
            this.settings.set('module', module);
        } else if (this.meta.config) {
            module = this.context.parent.get('module');
            if (_.contains(this.moduleBlacklist, module)) {
                module = _.first(availableModules);
            }
            this.settings.set('module', module);
        } else {
            this.moduleIsAvailable = false;
        }
    },

    /**
     * Update the display_columns attribute based on the current module defined
     * in settings.
     *
     * This will mark, as selected, all fields in the module's list view
     * definition. Any existing options will be replaced with the new options
     * if the "display_columns" DOM field ({@link EnumField}) exists.
     *
     * @private
     */
    _updateDisplayColumns: function() {
        var availableColumns = this._getAvailableColumns(),
            columnsFieldName = 'display_columns',
            columnsField = this.getField(columnsFieldName);
        if (columnsField) {
            columnsField.items = availableColumns;
        }
        this.settings.set(columnsFieldName, _.keys(availableColumns));
    },

    /**
     * Update options for `linked_fields` based on current selected module.
     * If there are no options field is hidden.
     *
     * @param {String} moduleName Name of selected module.
     */
    updateLinkedFields: function(moduleName) {
        var linked = this.getLinkedFields(moduleName),
            displayColumn = this.getField('linked_fields'),
            intelligent = this.model.get('intelligent');
        if (displayColumn) {
            displayColumn.items = linked;
            this.setLinkedFieldVisibility('1', intelligent);
        } else {
            this.setLinkedFieldVisibility('0', intelligent);
        }
        this.settings.set('linked_fields', _.keys(linked));
    },

    /**
     * Returns object with linked fields.
     *
     * @param {String} moduleName Name of module to find linked fields with.
     * @return {Object} Hash with linked fields labels.
     */
    getLinkedFields: function(moduleName) {
        var fieldDefs  = app.metadata.getModule(this.layout.module).fields;
        var relates =  _.filter(fieldDefs, function(field) {
                if (!_.isUndefined(field.type) && (field.type === 'link')) {
                    if (app.data.getRelatedModule(this.layout.module, field.name) === moduleName) {
                        return true;
                    }
                }
                return false;
            }, this),
            result = {};
        _.each(relates, function(field) {
            result[field.name] = app.lang.get(field.vname || field.name, this.layout.module);
        }, this);
        return result;
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
        var availableModules = this._getAvailableModules(),
            availableColumns = this._getAvailableColumns(),
            relates = this.getLinkedFields(this.module);
        _.each(this.getFieldMetaForView(this.meta), function(field) {
            switch(field.name) {
                case 'module':
                    // load the list of available modules into the metadata
                    field.options = availableModules;
                    break;
                case 'display_columns':
                    // load the list of available columns into the metadata
                    field.options = availableColumns;
                    break;
                case 'linked_fields':
                    field.options = relates;
                    break;
            }
        });
    },

    /**
     * Acquires pre-defined filters that may exist in metadata, for a given
     * module.
     *
     * @param {String} module The module to fetch predefined filters from.
     * @return {Array} An array of pre-defined filter objects.
     * @private
     */
    _getPreDefinedFilters: function(module) {
        var moduleMeta = app.utils.deepCopy(app.metadata.getModule(module));

        if (_.isObject(moduleMeta)) {
            return _.compact(_.flatten(_.pluck(_.compact(_.pluck(moduleMeta.filters, 'meta')), 'filters')));
        }
    },

    /**
     * Fetches the appropriate filter definition from the module metadata,
     * for a given filter ID.
     *
     * @param {String} id The ID of the filter.
     * @return {Array} The filter definition array.
     * @private
     */
    _getFilterDefFromMeta: function(id) {
        var module = this.settings.get('module');

        if (id) {
            var filtersFromMeta = this._getPreDefinedFilters(module),
                filter = _.find(filtersFromMeta, function(filter) {
                    return filter.id === id;
                }, this);

            if (filter) {
                return filter.filter_definition;
            }
        }
    },

    /**
     * Fetches a Filters bean record from the server using the supplied ID.
     *
     * @param {String} id The ID of the filter to fetch.
     * @param {Function} callbacks Callback functions to execute after fetch.
     * @private
     */
    _fetchCustomFilter: function(id, callbacks) {
        var self = this,
            module = this.settings.get('module'),
            url = app.api.buildURL('Filters', 'read', {id: id});

        if (id && callbacks) {
            app.api.call('read', url, null, callbacks);
        }
    },

    /**
     * Gets all of the modules the current user can see.
     *
     * This is used for populating the module select and list view columns
     * fields. Filters any modules that are blacklisted.
     *
     * @return {Object} {@link BaseDashablelistView#_availableModules}
     * @private
     */
    _getAvailableModules: function() {
        if (_.isEmpty(this._availableModules) || !_.isObject(this._availableModules)) {
            this._availableModules = {};
            var allowedModules = _.difference(
                app.metadata.getModuleNames({filter: 'visible', access: 'read'}), this.moduleBlacklist
            );
            _.each(allowedModules, function(module) {
                var hasListView = !_.isEmpty(this.getFieldMetaForView(app.metadata.getView(module, 'list')));
                if (hasListView) {
                    this._availableModules[module] = app.lang.get('LBL_MODULE_NAME', module);
                }
            }, this);
        }
        return this._availableModules;
    },

    /**
     * Gets the correct list view metadata.
     *
     * Returns the correct module list metadata
     *
     * @param  {String} module
     * @return {Object}
     */
    _getListMeta: function(module) {
        return app.metadata.getView(module, 'list');
    },

    /**
     * Gets all of the fields from the list view metadata for the currently
     * chosen module.
     *
     * This is used for the populating the list view columns field and
     * displaying the list.
     *
     * @return {Object} {@link BaseDashablelistView#_availableColumns}
     * @private
     */
    _getAvailableColumns: function() {
        var columns = {},
            module = this.settings.get('module');
        if (!module) {
            return columns;
        }

        _.each(this.getFieldMetaForView(this._getListMeta(module)), function(field) {
            columns[field.name] = app.lang.get(field.label || field.name, module);
        });

        return columns;
    },

    /**
     * Perform any necessary setup before displaying the dashlet.
     *
     * @param {Array} filterDef The filter definition array.
     * @private
     */
    _displayDashlet: function(filterDef) {
        this.context.set('skipFetch', false);
        this.context.set('limit', this.settings.get('limit'));

        if (filterDef) {
            this._applyFilterDef(filterDef);
            this.context.reloadData({'recursive': false});
        }
        // get the columns that are to be displayed and update the panel metadata
        var columns = this._getColumnsForDisplay();
        this.meta.panels = [{fields: columns}];
        this._startAutoRefresh();
    },

    /**
     * Sets the filter definition on the context collection to retrieve records
     * for the list view.
     *
     * @param {Array} filterDef The filter definition array.
     * @private
     */
    _applyFilterDef: function(filterDef) {
        if (filterDef) {
            this.context.get('collection').filterDef = filterDef;
        }
    },

    /**
     * Gets the columns chosen for display for this dashlet list.
     *
     * The display_columns setting might not have been defined when the dashlet
     * is being displayed from a metadata definition, like is the case for
     * preview and the default dashablelist's that are defined. All columns for
     * the selected module are shown in these cases.
     *
     * @returns {Object[]} Array of objects defining the field metadata for
     *                     each column.
     * @private
     */
    _getColumnsForDisplay: function() {
        var columns = [],
            fields = this.getFieldMetaForView(this._getListMeta(this.settings.get('module')));
        if (!this.settings.get('display_columns')) {
            this._updateDisplayColumns();
        }
        if (!this.settings.get('linked_fields')) {
            this.updateLinkedFields(this.model.module);
        }
        _.each(this.settings.get('display_columns'), function(name) {
            var field = _.find(fields, function(field) {
                return field.name === name;
            }, this);
            var column = _.extend({name: name, sortable: true}, field || {});
            columns.push(column);
        }, this);
        //Its possible that a column is on the dashlet and not on the main list view.
        //We need to fix up the columns in that case.
        columns = app.metadata._patchFields(this.module, app.metadata.getModule(this.module), columns);
        return columns;
    },

    /**
     * Starts the automatic refresh of the dashlet.
     *
     * @private
     */
    _startAutoRefresh: function() {
        var refreshRate = parseInt(this.settings.get('auto_refresh'), 10);
        if (refreshRate) {
            this._stopAutoRefresh();
            this._timerId = setInterval(_.bind(function() {
                this.context.resetLoadFlag();
                this.layout.loadData();
            }, this), refreshRate * 1000 * 60);
        }
    },

    /**
     * Cancels the automatic refresh of the dashlet.
     *
     * @private
     */
    _stopAutoRefresh: function() {
        if (this._timerId) {
            clearInterval(this._timerId);
        }
    },

    /**
     * {@inheritDoc}
     *
     * Calls {@link BaseDashablelistView#_stopAutoRefresh} so that the refresh will
     * not continue after the view is disposed.
     *
     * @private
     */
    _dispose: function() {
        this._stopAutoRefresh();
        this._super('_dispose');
    },

    /**
     * Gets the fields metadata from a particular view's metadata.
     *
     * @param {Object} meta The view's metadata.
     * @return {Object[]} The fields metadata or an empty array.
     */
    getFieldMetaForView: function(meta) {
        meta = _.isObject(meta) ? meta : {};
        return !_.isUndefined(meta.panels) ? _.flatten(_.pluck(meta.panels, 'fields')) : [];
    },

    /**
     * ListView sort will close previews, but this is not needed for dashablelists
     * In fact, closing preview causes problem when previewing this list dashlet
     * from dashlet-select
     */
    sort: $.noop
})
