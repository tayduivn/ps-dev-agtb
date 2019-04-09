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
     */
    plugins: [
        'SugarLogic',
        'ErrorDecoration',
        'GridBuilder',
        'Editable',
        'ToggleMoreLess',
        'Dashlet',
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
     * Flag indicates if a module is available for display.
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
    _defaultSettings: {},

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
     * @inheritdoc
     */
    initialize: function(options) {
        // bolt record view metadata for the given module onto the dashlet
        options.meta = _.extend(
            {},
            options.meta,
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
        if (this._mode === 'config') {
            this._configureDashlet();
            this.settings.on('change:module', function(model, moduleName) {
                this.dashModel.set('module', moduleName);
            }, this);
        }

        this.before('render', function() {
            if (!this.moduleIsAvailable || !this.model) {
                return this._noAccess();
            }

            if (this._mode === 'main' && this.model && this.model.module === this.module && this.model.dataFetched) {
                // ensure the header is populated with the relevant data if we already have it
                this._injectRecordHeader(this.model);
            }
        });
    },

    /**
     * @override
     *
     * Listen to change:model event to populate this dashlet with a new bean.
     */
    bindDataChange: function() {
        this.context.on('change:model', function(model) {
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
        var module = this.settings.get('module') || this.context.get('module');

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
        _.each(this._getFieldMetaForView(this.meta), function(field) {
            if (field.name === 'module') {
                // load the list of available modules into the metadata
                field.options = availableModules;
            }
        });
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
        return !_.isUndefined(meta.panels) ? _.flatten(_.pluck(meta.panels, 'fields')) : [];
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
        // inject header content into dashlet toolbar
        var dashletToolbar = this.layout && this.layout.getComponent('dashlet-toolbar');
        if (dashletToolbar) {
            var toolbarCtx = dashletToolbar.context;
            toolbarCtx.trigger('dashlet:toolbar:change', this._headerFields, model);
        }
    },
})
