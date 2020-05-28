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
    _timeFields: [
        'start_time',
        'end_time',
    ],

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.type = 'bool';
        this._currentDayStartEnd = {};

        if (this.model.isNew()) {
            this._currentDayStartEnd = {
                start_hour: 0,
                start_minutes: 0,
                end_hour: 0,
                end_minutes: 0,
            };

            this.view.once('render', function() {
                this._updateTimeFields(false);
            }, this);
        }

        if (_.contains(['preview', 'dashablerecord'], this.view.name)) {
            this.view.once('render', function() {
                this._toggleTimeFields();
            }, this);
        }
    },

    /**
     * Restore temporary values
     */
    _restoreTime: function() {
        if (!_.isEmpty(this._currentDayStartEnd)) {
            this.model.set(this._currentDayStartEnd);
        }
    },

    /**
     * Set default value for the saving
     * @param {boolean} save It shows if it needs to save current values
     */
    _clearTime: function(save) {
        if (save) {
            this._saveTime();
        }

        this.model.set(this._defaultDayStartEnd);
    },

    /**
     * Update the model and show/hide time fields
     * @param {boolean} save It shows if it needs to save current values
     */
    _updateTimeFields: function(save) {
        const isAllDay = this.getValue();

        isAllDay ? this._clearTime(save) : this._restoreTime();

        this._toggleTimeFields();
    },

    /**
     * Toggle (show/hide) time fields
     */
    _toggleTimeFields: function() {
        const isAllDay = this.getValue();

        $.each(this._timeFields, function(key, item) {
            const field = this.view.getField(item);
            field.$el.closest('.record-cell').toggle(!isAllDay);
        }.bind(this));
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
        this.model.on('change:' + this.name, function() {
            this._updateTimeFields(true);
        }, this);
    },

    unformat: function(value) {
        return (value && value !== '0') ? 1 : 0;
    },

    getValue: function() {
        const value = this.model.get(this.name);
        return !!(value && value !== '0');
    },
});
