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
 * Repeat Days of Month is a custom field for Meetings/Calls used to set
 * day(s) of the month for a Monthly recurring record.
 *
 * FIXME: This component will be moved out of clients/base folder as part of MAR-2274 and SC-3593
 *
 * @class View.Fields.Base.RepeatDaysField
 * @alias SUGAR.App.view.fields.BaseRepeatDaysField
 * @extends View.Fields.Base.EnumField
 */
({
    extendsFrom: 'EnumField',

    /**
     * @inheritdoc
     *
     * Set default enum options for this field and add validation (required if
     * `repeat_selector` is "Each")
     */
    initialize: function(options) {
        options = options || {};
        options.def = options.def || {};
        options.def.options = options.def.options || this._getEnumOptions();

        this._super('initialize', [options]);
        this.type = 'enum';

        this.model.addValidationTask(
            'repeat_days_validator_' + this.cid,
            _.bind(this._doValidateRepeatDays, this)
        );
    },

    /**
     * Build an array of options representing days from 1 to 31
     * @return {Array} The array of options
     * @private
     */
    _getEnumOptions: function() {
        var options = {};

        for (var i = 1; i <= 31; i++) {
            options[i] = i.toString();
        }

        return options;
    },

    /**
     * @inheritdoc
     *
     * Model days format is a string of comma separated numbers (1-31)
     * Select2 needs an array of these values
     */
    format: function(value) {
        if (!_.isString(value)) {
            return value;
        } else if (value === '') {
            return [];
        } else {
            return _.sortBy(value.split(','), function(num) {
                return parseInt(num);
            });
        }
    },

    /**
     * @inheritdoc
     *
     * Select2 array of numeric strings to model comma separated number format
     */
    unformat: function(value) {
        return (_.isArray(value)) ? value.join(',') : value;
    },

    /**
     * Custom required validator for the `repeat_days` field.
     *
     * This validates `repeat_days` based on the value of `repeat_selector` -
     * if "Each", repeat days must be specified
     *
     * @param {Object} fields The list of field names and their definitions.
     * @param {Object} errors The list of field names and their errors.
     * @param {Function} callback Async.js waterfall callback.
     * @private
     */
    _doValidateRepeatDays: function(fields, errors, callback) {
        var repeatSelector = this.model.get('repeat_selector'),
            repeatDays = this.model.get(this.name);

        if (repeatSelector === 'Each' && (!_.isString(repeatDays) || repeatDays.length < 1)) {
            errors[this.name] = {'required': true};
        }
        callback(null, fields, errors);
    },

    /**
     * @inheritdoc
     */
    _dispose: function() {
        this.model.removeValidationTask('repeat_days_validator_' + this.cid);
        this._super('_dispose');
    }
})
