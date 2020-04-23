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
 * @class View.Fields.Base.ShiftExceptions.ShiftExceptionsAllDayField
 * @extends View.Fields.Base.BoolField
 */
({
    extendsFrom: 'BoolField',

    /**
     * Defines the start and end times of the day
     *
     * @property {Object}
     */
    _defaultDayStartEnd: {
        start_hour: 0,
        start_minutes: 0,
        end_hour: 23,
        end_minutes: 59,
    },

    /**
     * Object for saving current time values between switches
     *
     * @property {Object}
     */
    _currentDayStartEnd: {},

    /**
     * Start/End fields
     *
     * @property {string}
     */
    _timeFields: '.record-cell[data-name="start_time"], ' +
        '.record-cell[data-name="end_time"]',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.type = 'bool';

        if (this.model && this.model.isNew()) {
            this.view.once('render', this._updateTimeFields, this);
        }
    },

    /**
     * Restore temporary values
     */
    _restoreTime: function() {
        this.model.set(this._currentDayStartEnd);
    },

    /**
     * Set default value for the saving
     */
    _clearTime: function() {
        this._saveTime();
        this.model.set(this._defaultDayStartEnd);
    },

    /**
     * Update the model and show/hide time fields
     */
    _updateTimeFields: function() {
        const isAllDay = this.getValue();

        isAllDay ? this._clearTime() : this._restoreTime();
        $(this.$el).closest('.record').find(this._timeFields).toggle(!isAllDay);
    },

    /**
     * Add temporary values to object
     */
    _saveTime: function() {
        $.each(this._defaultDayStartEnd, function(key) {
            this._currentDayStartEnd[key] = this.model.get(key);
        }.bind(this));
    },

    bindDataChange: function() {
        this._super('bindDataChange');
        this.model.on('change:' + this.name, this._updateTimeFields, this);
    },

    unformat: function(value) {
        return (value && value !== '0') ? 1 : 0;
    },

    getValue: function() {
        const value = this.model.get(this.name);
        return !!(value && value !== '0');
    },
});
