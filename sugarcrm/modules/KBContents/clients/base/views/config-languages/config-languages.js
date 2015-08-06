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
            var lng = _.omit(lang, 'primary'),
                key = _.first(_.keys(lng)),
                val = lang[key].trim();
            if (val.length === 0) {
                message = app.lang.get('ERR_CONFIG_LANGUAGES_EMPTY', 'KBContents');
                errors['lang'] = {'required': true};
            }
            lang[key] = val;
            languagesToSave.push(key.trim().toLowerCase());
        }, this);

        if (_.indexOf(languagesToSave, '') !== -1) {
            message = app.lang.get('ERR_CONFIG_LANGUAGES_EMPTY', 'KBContents');
            errors['lang'] = {'required': true};
        }
        if (languagesToSave.length !== _.uniq(languagesToSave).length) {
            message = app.lang.get('ERR_CONFIG_LANGUAGES_DUPLICATE', 'KBContents');
            errors['lang'] = {'required': true};
        }

        if (!_.isEmpty(errors)) {
            app.alert.show('languages', {
                level: 'error',
                messages: message
            });
        } else {
            this.model.set('languages', languages);
        }
        callback(null, fields, errors);
    }
})
