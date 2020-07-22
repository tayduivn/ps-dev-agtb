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
 * @class View.Views.Base.AdministrationAwsConnectView
 * @alias SUGAR.App.view.views.BaseAwsConnectView
 * @extends View.Views.Base.RecordView
 */
({
    extendsFrom: 'RecordView',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.addValidationTask();
        this.loadSettings();
        this.boundSaveHandler = _.bind(this.validateModel, this);
        this.context.on('save:awsconnect', this.boundSaveHandler);
    },

    /**
     * Add validation tasks to the current model so any aws related fields could be validated.
     */
    addValidationTask: function() {
        this.model.addValidationTask('aws_required', _.bind(this.validateRequiredFields, this.model));
    },

    /**
     * Set the aws connect settings on the model.
     *
     * @param {Object} settings The AWS config details.
     */
    copySettingsToModel: function(settings) {
        _.each(settings, function(value, key) {
            this.model.set(key, value);
        }, this);
        this.boundChangeHandler = _.bind(this.toggleHeaderButton, this);
        this.model.on('change', this.boundChangeHandler);
    },

    /**
     * It will load the aws configuration options.
     */
    loadSettings: function() {
        var options = {
            success: _.bind(this.copySettingsToModel, this)
        };
        app.api.call('get', app.api.buildURL(this.module, 'aws'), [], options, {context: this});
    },

    /**
     * Render the view in edit mode.
     * @inheritdoc
     */
    render: function() {
        this._super('render');
        this.action = 'edit';
        this.toggleEdit(true);
    },

    /**
     * It will validate required fields.
     *
     * @param {Array} fields The list of fields to be validated.
     * @param {Object} errors A list of error messages.
     * @param {Function} callback Callback to be called at the end of the validation.
     */
    validateRequiredFields: function(fields, errors, callback) {
        var requiredFields = [];
        var instanceName = this.get('aws_connect_instance_name');
        if (instanceName) {
            requiredFields.push('aws_connect_region');
        }
        _.each(requiredFields, function(key) {
            if (!this.get(key)) {
                errors[key] = errors[key] || {};
                errors[key].required = true;
            }
        }, this);
        callback(null, fields, errors);
    },

    /**
     * It will change the Save button enabled/disabled state.
     *
     * @param {boolean} state The state to be set.
     */
    toggleHeaderButton: function(state) {
        this.layout.getComponent('aws-connect-header').enableButton(state);
    },

    /**
     * Triggers the field validation through the model.
     */
    validateModel: function() {
        var fieldsToValidate = this.options.meta.panels[0].fields;
        this.model.doValidate(fieldsToValidate, _.bind(this.validationComplete, this));
    },

    /**
     * It triggers the save process if all fields are valid.
     *
     * @param {boolean} isValid If all the fields are valid.
     */
    validationComplete: function(isValid) {
        if (isValid) {
            this.save();
        }
    },

    /**
     * On a successful save return to the Administration page.
     */
    closeView: function() {
        if (app.drawer && app.drawer.count()) {
            // close the drawer and return to Opportunities
            app.drawer.close(this.context, this.context.get('model'));
            // Config changed... reload metadata
            app.sync();
        } else {
            app.router.navigate(this.module, {trigger: true});
        }
    },

    /**
     * On a successful save the Save button has to be disabled and
     * a message will be shown indicating that the settings have been saved.
     *
     * @param {Object} settings The aws connect settings.
     */
    saveSuccessHandler: function(settings) {
        this.updateConfig(settings);
        this.toggleHeaderButton(false);
        this.closeView();
        app.alert.show('aws-info', {
            autoClose: true,
            level: 'success',
            messages: app.lang.get('LBL_SAVED')
        });
    },

    /**
     * Show an error message if the settings could not be saved.
     */
    saveErrorHandler: function() {
        app.alert.show('aws-warning', {
            level: 'error',
            title: app.lang.get('LBL_ERROR')
        });
    },

    /**
     * Save the settings.
     */
    save: function() {
        var options = {
            error: _.bind(this.saveSuccessHandler, this),
            success: _.bind(this.saveSuccessHandler, this)
        };
        app.api.call('create', app.api.buildURL(this.module, 'aws'), this.model.toJSON(), options);
    },

    /**
     * Update the settings stored in the front-end.
     *
     * @param {Object} settings The aws connect settings.
     */
    updateConfig: function(settings) {
        _.each(settings, function(value, key) {
            app.config[app.utils.getUnderscoreToCamelCaseString(key)] = value;
        });
    },

    /**
     * Unbind save handler.
     * @inheritdoc
     */
    dispose: function() {
        if (!this.disposed) {
            this.context.off('save:awsconnect', this.boundSaveHandler);
            this.model.off('change', this.boundChangeHandler);
            this._super('dispose');
        }
    }
})
