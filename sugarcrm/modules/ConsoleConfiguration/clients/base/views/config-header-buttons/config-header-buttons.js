// FILE SUGARCRM flav=ent ONLY
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
 * @class View.Views.Base.ConsoleConfiguration.ConfigHeaderButtonsView
 * @alias SUGAR.App.view.views.BaseConsoleConfigurationConfigHeaderButtonsView
 * @extends View.Views.Base.ConfigHeaderButtonsView
 */
({
    extendsFrom: 'ConfigHeaderButtonsView',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this._viewAlerts = [];

        this.moduleLangObj = {
            // using "Console Configuration" for config title
            module: app.lang.get('LBL_CONSOLE_CONFIG_TITLE', this.module)
        };
    },

    /**
     * Displays alert message for invalid models
     */
    showInvalidModel: function() {
        var self = this;
        if (!this instanceof app.view.View) {
            app.logger.error('This method should be invoked by Function.prototype.call(), passing in as ' +
                'argument an instance of this view.');
            return;
        }
        var name = 'invalid-data';
        self._viewAlerts.push(name);
        app.alert.show(name, {
            level: 'error',
            messages: 'ERR_RESOLVE_ERRORS'
        });
    },

    /**
     * @inheritdoc
     */
    cancelConfig: function() {
        if (this.triggerBefore('cancel')) {
            if (app.drawer.count()) {
                app.drawer.close(this.context, this.context.get('model'));
            }
        }
    },

    /**
     * Process all the models of the collection and prepares the context
     * bean for save action
     */
    _setupSaveConfig: function() {
        var consoleId = this.context.get('consoleId');
        var ctxModel = this.context.get('model');

        // Get the current settings for the given console ID. If there are no
        // settings for the given console ID, create them
        var enabledModules = ctxModel.get('enabled_modules') || {};
        enabledModules[consoleId] = enabledModules[consoleId] || [];

        var orderByPrimary = ctxModel.get('order_by_primary') || {};
        orderByPrimary[consoleId] = orderByPrimary[consoleId] || {};

        var orderBySecondary = ctxModel.get('order_by_secondary') || {};
        orderBySecondary[consoleId] = orderBySecondary[consoleId] || {};

        var filterDef = ctxModel.get('filter_def') || {};
        filterDef[consoleId] = filterDef[consoleId] || {};

        // Update the variables holding the field values for the given console ID
        _.each(this.collection.models, function(model) {
            var moduleName = model.get('enabled_module');
            orderByPrimary[consoleId][moduleName] = model.get('order_by_primary');
            orderBySecondary[consoleId][moduleName] = model.get('order_by_secondary');
            filterDef[consoleId][moduleName] = model.get('filter_def');
        }, this);

        ctxModel.set({
            is_setup: true,
            enabled_modules: enabledModules,
            order_by_primary: orderByPrimary,
            order_by_secondary: orderBySecondary,
            filter_def: filterDef
        }, {silent: true});
    },

    /**
     * Calls the context model save and saves the config model in case
     * the default model save needs to be overwritten
     *
     * @protected
     */
    _saveConfig: function() {
        this.validatedModels = [];
        this.getField('save_button').setDisabled(true);

        if (this.collection.models.length === 0) {
            this._setupSaveConfig();
            this._super('_saveConfig');
        } else {
            async.waterfall([
                _.bind(this.validateCollection, this)
            ], _.bind(function(result) {
                this.validatedModels.push(result);

                // doValidate() has finished on all models.
                if (this.collection.models.length === this.validatedModels.length) {

                    var found = _.find(this.validatedModels, function(details) {
                        return details.isValid === false;
                    });

                    if (found) {
                        this.showInvalidModel();
                        this.getField('save_button').setDisabled(false);
                    } else {
                        this._setupSaveConfig();
                        this._super('_saveConfig');
                    }
                }
            }, this));
        }
    },

    /**
     * Validates all the models in the collection using the validation tasks
     */
    validateCollection: function(callback) {
        var fieldsToValidate = {};
        var allFields = this.getFields(this.module, this.model);

        for (var fieldKey in allFields) {
            if (app.acl.hasAccessToModel('edit', this.model, fieldKey)) {
                _.extend(fieldsToValidate, _.pick(allFields, fieldKey));
            }
        }

        _.each(this.collection.models, function(model) {
            model.doValidate(fieldsToValidate, function(isValid) {
                callback({modelId: model.id, isValid: isValid});
            });
        }, this);
    }
})
