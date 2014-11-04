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
 * RepeatCount field is a special int field for Meetings & Calls that adds
 * max validation (which can't be done via metadata due to config value)
 *
 * @class View.Fields.Base.RepeatCountField
 * @alias SUGAR.App.view.fields.BaseRepeatCountField
 * @extends View.Fields.Base.IntField
 */
({
    extendsFrom: 'IntField',

    /**
     * @property {int} defaultCount
     *
     * The number of occurrences to use as a default in the UI when creating a
     * new record.
     */
    defaultCount: 10,

    /**
     * @inheritdoc
     *
     * Add custom max value validation. The value of the field is defaulted in
     * the UI when creating a new record.
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        // setting type & def.type so number validator will run
        this.type = this.def.type = 'int';

        if (this.model.isNew()) {
            this.model.set(this.name, this.defaultCount);
        }

        this.model.addValidationTask(
            'repeat_count_max_validator_' + this.cid,
            _.bind(this._doValidateRepeatCountMax, this)
        );
    },

    /**
     * @inheritdoc
     *
     * Always returns an empty string if the value is 0, '0', null, or
     * undefined.
     */
    format: function(value) {
        value = this._super('format', [value]);

        return (value === '0' || value == null) ? '' : value;
    },

    /**
     * @inheritdoc
     *
     * Converts the value to an integer so that the integer representation is
     * always used in the model.
     */
    unformat: function(value) {
        if (!_.isString(value)) {
            return value;
        }

        if (value.trim() === '') {
            value = 0;
        } else {
            value = parseInt(this._super('unformat', [value]), 10);
        }

        return value;
    },

    /**
     * Custom required validator for the `repeat_count` field.
     *
     * This validates `repeat_count` is not above the max allowed value
     * Since max value is in a config, cannot use sidecar maxValue validator.
     *
     * @param {Object} fields The list of field names and their definitions.
     * @param {Object} errors The list of field names and their errors.
     * @param {Function} callback Async.js waterfall callback.
     * @private
     */
    _doValidateRepeatCountMax: function(fields, errors, callback) {
        var repeatCount = parseInt(this.model.get(this.name), 10),
            maxRepeatCount = app.config.calendar.maxRepeatCount;

        if (repeatCount > maxRepeatCount) {
            errors[this.name] = {'maxValue': maxRepeatCount};
        }
        callback(null, fields, errors);
    },

    /**
     * @inheritdoc
     */
    _dispose: function() {
        this.model._validationTasks = _.omit(
            this.model._validationTasks,
            'repeat_count_max_validator_' + this.cid
        );
        this._super('_dispose');
    }
})
