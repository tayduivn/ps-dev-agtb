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
 * Recurrence is a field for Calls & Meetings module used to set attributes
 * about a recurring record.
 *
 * @class View.Fields.Base.RecurrenceField
 * @alias SUGAR.App.view.fields.BaseRecurrenceField
 * @extends View.Fields.Base.FieldsetWithLabelsField
 */
({
    extendsFrom: 'FieldsetWithLabelsField',

    /**
     * @inheritdoc
     *
     * This field doesn't support `showNoData`.
     */
    showNoData: false,

    /**
     * @inheritdoc
     *
     * Add validator to ensure that either `repeat_count` or `repeat_until`
     * has a value set
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.model.addValidationTask(
            'repeat_count_or_until_required_validator_' + this.cid,
            _.bind(this._doValidateRepeatCountOrUntilRequired, this)
        );
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this._super('bindDataChange');
        this.model.on('change:repeat_type', this.repeatTypeChanged, this);
        this.model.on('change:repeat_count', this.repeatCountChanged, this);
        this.model.on('change:repeat_until', this.repeatUntilChanged, this);
    },

    /**
     * @inheritdoc
     *
     * Prepare the recurrence fields based on the value of `repeat_type`
     */
    _render: function() {
        var repeatType = this.model.get('repeat_type');

        this._super('_render');

        switch (repeatType) {
            case 'Daily':
            case 'Weekly':
            case 'Monthly':
            case 'Yearly':
                this.show();
                break;
            default:
                this.hide();
                break;
        }

        this.prepareView();
    },

    /**
     * Set up the recurrence fields based on `repeat_type` and the action
     *
     * * `repeat_dow` - show when repeat_type is weekly, hide otherwise
     * * `repeat_count` & `repeat_until` - always show both fields on edit view
     * and only show the field which as a value on detail view
     */
    prepareView: function() {
        var repeatType = this.model.get('repeat_type'),
            repeatUntil = this.model.get('repeat_until');

        if (repeatType === 'Weekly') {
            this._showField('repeat_dow');
        } else {
            this._hideField('repeat_dow');
        }

        if (this.action === 'edit') {
            this._showField('repeat_count');
            this._showField('repeat_until');
        } else {
            if (repeatUntil && (repeatUntil.length > 0)) {
                this._hideField('repeat_count');
                this._showField('repeat_until');
            } else {
                this._showField('repeat_count');
                this._hideField('repeat_until');
            }
        }
    },

    /**
     * Reset recurrence fields when `repeat_type` changes & then re-render so the
     * hide/show logic is applied
     */
    repeatTypeChanged: function() {
        _.each(this.fields, function(field) {
            if (!this._isPopulated(this.model.get(field.name))) {
                this.model.set(field.name, field.def['default']);
            }
        }, this);

        this.render();
    },

    /**
     * Clear out `repeat_until` field when `repeat_count` is set
     */
    repeatCountChanged: function() {
        if (this._isPopulated(this.model.get('repeat_count'))) {
            this.model.set('repeat_until', '');
        }
    },

    /**
     * Clear out `repeat_count` field when `repeat_until` is set
     */
    repeatUntilChanged: function() {
        if (this._isPopulated(this.model.get('repeat_until'))) {
            this.model.set('repeat_count', '');
        }
    },

    /**
     * Show the given field
     *
     * @param {String} fieldName Name of the field to show
     * @private
     */
    _showField: function(fieldName) {
        this._getFieldRecordCell(fieldName).removeClass('hide');
    },

    /**
     * Hide the given field
     *
     * @param {String} fieldName Name of the field to hide
     * @private
     */
    _hideField: function(fieldName) {
        this._getFieldRecordCell(fieldName).addClass('hide');
    },

    /**
     * Returns the field cell for a given field name
     *
     * @param {String} fieldName Name of the field to select
     * @returns {Object} jQuery selected record cell
     * @private
     */
    _getFieldRecordCell: function(fieldName) {
        var selector = '.record-cell[data-name="' + fieldName + '"]';
        return this.$(selector);
    },

    /**
     * Check if a particular field is populated
     *
     * @param {String|Number} value The value to check if it is populated
     * @returns {Boolean} Returns true if the field is populated
     * @private
     */
    _isPopulated: function(value) {
        return !_.isUndefined(value) && !_.isNull(value) && value !== '';
    },

    /**
     * Custom required validator for the `repeat_count`/`repeat_until` field.
     * This validates `repeat_count` is required if `repeat_until` is not
     * specified.
     *
     * @param {Object} fields The list of field names and their definitions.
     * @param {Object} errors The list of field names and their errors.
     * @param {Function} callback Async.js waterfall callback.
     * @private
     */
    _doValidateRepeatCountOrUntilRequired: function(fields, errors, callback) {
        var repeatType = this.model.get('repeat_type'),
            repeatCount = this.model.get('repeat_count'),
            repeatUntil = this.model.get('repeat_until');

        if (this._isPopulated(repeatType) &&
            !this._isPopulated(repeatCount) &&
            !this._isPopulated(repeatUntil)
        ) {
            errors.repeat_count = {'required': true};
        }
        callback(null, fields, errors);
    },

    /**
     * @inheritdoc
     */
    _dispose: function() {
        this.model._validationTasks = _.omit(
            this.model._validationTasks,
            'repeat_count_or_until_required_validator_' + this.cid
        );
        this._super('_dispose');
    }
})
