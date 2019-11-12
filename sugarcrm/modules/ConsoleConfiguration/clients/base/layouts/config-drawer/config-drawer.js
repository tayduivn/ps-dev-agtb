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
 * @class View.Layouts.Base.ConsoleConfigurationConfigDrawerLayout
 * @alias SUGAR.App.view.layouts.BaseConsoleConfigurationConfigDrawerLayout
 * @extends View.Layouts.Base.ConfigDrawerLayout
 */
({
    extendsFrom: 'BaseConfigDrawerLayout',

    plugins: ['ErrorDecoration'],

    /**
     * Holds a list of all modules with multi-line list views that can be
     * configured using the Console Configurator
     */
    supportedModules: ['Accounts', 'Cases', 'Opportunities'],

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.setAllowedModules();
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this.context.on('consoleconfiguration:config:model:add', this.addModelToCollection, this);
        this.context.on('consoleconfiguration:config:model:remove', this.removeModelFromCollection, this);
    },

    /**
     * Returns the list of modules the user has access to
     * and are supported.
     *
     * @return {Array} The list of module names.
     */
    getAvailableModules: function() {
        var moduleNames = app.metadata.getModuleNames();
        var selectedModules = this.model.get('enabled_modules')[this.context.get('consoleId')];

        return _.filter(selectedModules, function(module) {
            return _.contains(moduleNames, module);
        });
    },

    /**
     * Sets up the models for each of the enabled modules from the configs
     */
    loadData: function(options) {
        if (!this.checkAccess()) {
            this.blockModule();
            return;
        }
        var availableModules = this.getAvailableModules();
        var orderByPrimary = this.model.get('order_by_primary')[this.context.get('consoleId')];
        var orderBySecondary = this.model.get('order_by_secondary')[this.context.get('consoleId')];
        var filterDef = this.model.get('filter_def')[this.context.get('consoleId')];

        _.each(availableModules, function(moduleName) {
            var data = {
                enabled: true,
                enabled_module: moduleName,
                order_by_primary: orderByPrimary[moduleName],
                order_by_secondary: orderBySecondary[moduleName],
                filter_def: filterDef[moduleName],
            };
            this.addModelToCollection(moduleName, data);
        }, this);
        this.setActiveTabIndex(0);
    },

    /**
     * Checks ConsoleConfiguration ACLs to see if the User is a system admin
     * or if the user has a developer role for the ConsoleConfiguration module
     *
     * @inheritdoc
     */
    _checkModuleAccess: function() {
        var acls = app.user.getAcls().ConsoleConfiguration;
        var isSysAdmin = (app.user.get('type') == 'admin');
        var isDev = (!_.has(acls, 'developer'));

        return (isSysAdmin || isDev);
    },

    /**
     * Sets the allowed modules that the admin are allowed to configure
     */
    setAllowedModules: function() {
        var moduleDetails = {};
        var allowedModules = this.supportedModules;
        var modules = {};
        _.each(allowedModules, function(module) {
            moduleDetails = app.metadata.getModule(module);
            if (moduleDetails &&
                !moduleDetails.isBwcEnabled &&
                !_.isEmpty(moduleDetails.fields)) {
                modules[module] = app.lang.getAppListStrings('moduleList')[module];
            }
        });

        this.context.set('allowedModules', modules);
    },

    /**
     * Sets the active tab
     */
    setActiveTabIndex: function(index) {
        if (this.collection.length >= 1 || !_.isUndefined(index)) {
            var activeIndex = !_.isUndefined(index) ? index : this.collection.length - 1;
            this.context.set('activeTabIndex', activeIndex);
        }
    },

    /**
     * Removes a model from the collection and triggers events
     * to re-render the components
     * @param {string} module Module Name
     */
    removeModelFromCollection: function(module) {
        var modelToDelete = _.find(this.collection.models, function(model) {
            return model.get('enabled_module') === module;
        });

        if (!_.isEmpty(modelToDelete)) {
            this.collection.remove(modelToDelete);
            this.setActiveTabIndex();
        }
    },

    /**
     * Adds a model from the collection and triggers events
     * to re-render the components
     * @param {string} module Module Name
     * @param {Object} data Model data to add to the collection
     */
    addModelToCollection: function(module, data) {
        var data = data || {};
        var existingBean = _.find(this.collection.models, function(model) {
            if (_.contains(_.keys(this.context.get('allowedModules')), module)) {
                return model.get('enabled_module') === module;
            }
        }, this);

        if (_.isEmpty(existingBean)) {
            var bean = app.data.createBean(this.module, {
                enabled: data.enabled || true,
                enabled_module: data.module || module,
                order_by_primary: data.order_by_primary || '',
                order_by_secondary: data.order_by_secondary || '',
                filter_def: data.filter_def || '',
            });

            this.getModuleFields(bean);
            this.addValidationTasks(bean);
            this.collection.add(bean);
        }

        this.setActiveTabIndex();
    },

    /**
     * Set the fields for the module on the bean
     *
     * @param {Object} bean ta Model data to add to the collection
     */
    getModuleFields: function(bean) {
        var module = bean.get('enabled_module');
        var content = {};
        var dropdownFields = {};
        var allFields = {};

        var fields = _.flatten(_.pluck(app.metadata.getView(module, 'multi-line-list').panels, 'fields'));

        _.each(app.metadata.getModule(module, 'fields'), function(field) {
            if (field.type == 'enum' && app.acl.hasAccess('read', module, null, field.name)) {
                dropdownFields[field.name] = app.lang.get(field.label || field.vname, module);
            }
        }, this);

        _.each(fields, function(field) {
            if (_.isObject(field) && app.acl.hasAccess('read', module, null, field.name)) {
                var label = app.lang.get(field.label || field.vname, module);
                if (!_.isEmpty(label)) {
                    allFields[field.name] = label;
                }
            }
        }, this);

        content.dropdownFields = dropdownFields;
        content.fields = allFields;

        bean.set('tabContent', content);
    },

    /**
     * Adds validation tasks to the fields in the layout for the enabled modules
     */
    addValidationTasks: function(bean) {
        if (bean !== undefined) {
            bean.addValidationTask('check_order_by_primary', _.bind(this._validatePrimaryOrderBy, bean));
        } else {
            _.each(this.collection.models, function(model) {
                model.addValidationTask('check_order_by_primary', _.bind(this._validatePrimaryOrderBy, model));
            }, this);
        }
    },

    /**
     * Validates table header values for the enabled module
     *
     * @protected
     */
    _validatePrimaryOrderBy: function(fields, errors, callback) {
        if (_.isEmpty(this.get('order_by_primary'))) {
            errors.order_by_primary = errors.order_by_primary || {};
            errors.order_by_primary.required = true;
        }

        callback(null, fields, errors);
    },

    /**
     * @inheritdoc
     */
    _dispose: function() {
        this.context.off('consoleconfiguration:config:model:add', null, this);
        this.context.off('consoleconfiguration:config:model:remove', null, this);

        this._super('_dispose');
    }
})
