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
        model.on('validation:success', _.bind(this._validationSuccess, this));
        model.on('error:validation', _.bind(this._handleValidationError, this));

        app.error.errorName2Keys['lang_empty'] = 'ERR_CONFIG_LANGUAGES_EMPTY';
        app.error.errorName2Keys['lang_duplicate']= 'ERR_CONFIG_LANGUAGES_DUPLICATE';
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
                errors['lang'] = errors['lang'] || {};
                errors['lang']['lang_empty'] = true;
            }
            languagesToSave.push(key.trim().toLowerCase());
        }, this);

        if (_.indexOf(languagesToSave, '') !== -1) {
            errors['lang'] = errors['lang'] || {};
            errors['lang']['lang_empty'] = true;
        }
        if (languagesToSave.length !== _.uniq(languagesToSave).length) {
            errors['lang'] = errors['lang'] || {};
            errors['lang']['lang_duplicate'] = true;
        }

        callback(null, fields, errors);
    },

    /**
     * On success validation, trim language keys and labels
     */
    _validationSuccess: function () {
        var model = this.context.get('model'),
            languages = this.model.get('languages');

        // trim keys
        var buf = _.map(languages, function(lang) {
            var prim = lang['primary'],
                lng = _.omit(lang, 'primary'),
                key = _.first(_.keys(lng)),
                val = lang[key].trim();

            key = key.trim();
            var res = {primary: prim};
            res[key] = val;

            return res;
        }, this);

        model.set('languages', buf);
    },

    /**
     * Show validation alert
     * @param {Object} errors
     */
    _handleValidationError: function (errors) {
        if (!errors['lang']) {
            return;
        }

        var key = _.first(_.keys(errors['lang']));
        app.alert.dismiss('languages');
        app.alert.show('languages', {
            level: 'error',
            messages: app.lang.get(app.error.errorName2Keys[key], 'KBContents')
        });
    }
})
