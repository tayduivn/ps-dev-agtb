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
 * @class View.Fields.Base.Quotes.QuoteFooterCurrency
 * @alias SUGAR.App.view.fields.BaseQuotesQuoteFooterCurrency
 * @extends View.Fields.Base.CurrencyField
 */
({
    extendsFrom: 'CurrencyField',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.model.addValidationTask(
            'isNumeric_validator_' + this.cid,
            _.bind(this._doValidateIsNumeric, this)
        );
    },

    /**
     * @inheritdoc
     */
    bindDomChange: function() {
        if (!(this.model instanceof Backbone.Model)) {
            return;
        }

        var $el = this.$(this.fieldTag);
        $el.on('change', _.bind(this._onModelChanged, this));
    },

    /**
     * callback for when the shipping field changes.
     * @param {Object} evt the JS event object passed into the change event.
     * @private
     */
    _onModelChanged: function(evt) {
        var value =  evt.currentTarget.value;
        this.model.set(this.name, value);
        this.model.doValidate(this.name, _.bind(this._validationComplete, this));
    },

    /**
     * Callback for after validation runs.
     * @param {bool} isValid flag determining if the validation is correct
     * @private
     */
    _validationComplete: function(isValid) {
        if (isValid) {
            app.alert.dismiss('invalid-data');
            if (!this.context.isCreate()) {
                this.model.save();
            }
        }
    },

    /**
     * Validation function to check to see if a value is numeric.
     * @param {Array} fields
     * @param {Array} errors
     * @param {Function} callback
     * @private
     */
    _doValidateIsNumeric: function(fields, errors, callback) {
        var value = this.model.get(this.name);
        if (!$.isNumeric(value)) {
            errors[this.name] = app.lang.get('ERROR_NUMBER');
        }
        callback(null, fields, errors);
    },

    /**
     * Formats number
     * @param {integer|string} value Currency value to be formatted
     * @override
     */
    format: function(value) {
        return app.utils.formatNumberLocale(value);
    },

    /**
      * Extending to remove the custom validation task for this field
      *
      * @inheritdoc
      * @private
      */
    _dispose: function() {
        this.model.removeValidationTask('isNumeric_validator_' + this.cid);
        this._super('_dispose');
    }
});
