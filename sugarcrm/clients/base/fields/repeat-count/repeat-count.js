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
 * RepeatCount field is a special int field for Meetings/Calls that adds
 * max validation (which can't be done via metadata due to config value)
 *
 * FIXME: This component will be moved out of clients/base folder as part of MAR-2274 and SC-3593
 *
 * @class View.Fields.Base.RepeatCountField
 * @alias SUGAR.App.view.fields.BaseRepeatCountField
 * @extends View.Fields.Base.IntField
 */
({
    extendsFrom: 'IntField',

    /**
     * @inheritdoc
     *
     * Add custom max value validation
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        // setting type & def.type so number validator will run
        this.type = this.def.type = 'int';

        this.model.addValidationTask(
            'repeat_count_max_validator_' + this.cid,
            _.bind(this._doValidateRepeatCountMax, this)
        );
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
        var repeatCount = parseInt(this.model.get('repeat_count'), 10),
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
