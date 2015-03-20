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
 * @class View.Views.Base.KBContentsConfigLanguagesView
 * @alias SUGAR.App.view.layouts.BaseKBContentsConfigLanguages
 * @extends View.Views.Base.ConfigPanelView
 */
({
    extendsFrom: 'ConfigPanelView',

    /**
     * {@inheritdoc}
     */
    initialize: function (options) {
        this._super('initialize', [options]);
        var model = this.context.get('model');
        model.addValidationTask('validate_config_languages', _.bind(this._validateLanguages, this));
    },

    /**
     * Validate languages duplicates.
     * @param {Object} fields
     * @param {Object} errors
     * @param {Function} callback
     */
    _validateLanguages: function (fields, errors, callback) {
        var model = this.context.get('model'),
            languages = this.model.get('languages'),
            languagesToSave = [];

        errors = {};

        _.each(languages, function(lang) {
            languagesToSave.push(JSON.stringify(_.omit(lang, 'primary')));
        }, this);

        if (languagesToSave.length != _.uniq(languagesToSave).length) {
            app.alert.show('languages', {
                level: 'error',
                messages: app.lang.get('ERR_CONFIG_LANGUAGES_DUPLICATE', 'KBContents')
            });
            errors['lang'] = {'required': true};
        }

        callback(null, fields, errors);
    }
})
