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
 * @class View.Fields.Base.DurationField
 * @alias SUGAR.App.view.fields.BaseDurationField
 */
({
    extendsFrom: 'FieldsetWithLabelsField',

    /**
     * @inheritdoc
     *
     * Adds a custom validation task to the model.
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.model.addValidationTask('duration_validator_' + this.cid, _.bind(this.doValidateDuration, this));
    },

    /**
     * @inheritdoc
     *
     * Removes the custom validator from the model.
     */
    _dispose: function() {
        this.model._validationTasks = _.omit(this.model._validationTasks, 'duration_validator_' + this.cid);
        this._super('_dispose');
    },

    /**
     * Custom required validator for the `duration` field.
     *
     * The `duration_hours` and `duration_minutes` fields must have integer-
     * like values that are greater than or equal to 0.
     *
     * @param {Object} fields The list of field names and their definitions.
     * @param {Object} errors The list of field names and their errors.
     * @param {Function} callback Async.js waterfall callback.
     */
    doValidateDuration: function(fields, errors, callback) {
        var hours, isPositiveInteger, minutes;

        /**
         * The duration_hours and duration_minutes fields must be positive
         * integers; either actual integers or strings that are integers.
         *
         * @param {*} value
         * @return {boolean}
         */
        isPositiveInteger = function(value) {
            return /^\d+$/.test(String(value));
        };

        hours = this.model.get('duration_hours');
        minutes = this.model.get('duration_minutes');

        if (!isPositiveInteger(hours) || !isPositiveInteger(minutes)) {
            errors[this.name] = app.lang.get('NOTICE_DURATION_TIME', this.module);
        }

        callback(null, fields, errors);
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        // Change the end date when start date changes.
        this.model.on('change:date_start', this.modifyEndDateToRetainDuration, this);

        // Check for valid date range on edit. If not valid, show a validation error.
        // In detail mode, re-render the field if either start or end date changes.
        this.model.on('change:date_start change:date_end', function(model) {
            var dateStartField,
                dateEndField,
                diff;

            diff = moment(model.get('date_end')).diff(model.get('date_start'));
            model.set('duration_hours', moment.duration(diff).hours());
            model.set(
                'duration_minutes',
                moment.duration(diff).subtract(
                    moment.duration(model.get('duration_hours'), 'h')
                ).minutes()
            );

            if (this.action === 'edit') {
                dateStartField = this.view.getField('date_start');
                dateEndField = this.view.getField('date_end');

                if (dateStartField && !dateStartField.disposed && dateEndField && !dateEndField.disposed) {
                    dateStartField.clearErrorDecoration();

                    if (!this.isDateRangeValid()) {
                        dateStartField.decorateError({
                            isBefore: app.lang.get(dateEndField.label || dateEndField.vname || dateEndField.name, model.module)
                        });
                    }
                }
            } else {
                this.render();
            }
        }, this);

        this._super('bindDataChange');
    },

    /**
     * Render start date and end date fields on edit. In detail mode, render
     * date range as a display string.
     * @inheritdoc
     * @private
     */
    _render: function() {
        if (this.action === 'edit') {
            this._super('_render');
            if (!this.model.get('date_start')) {
                this.setDefaultStartDateTime();
            }
        } else {
            this._disposeOldFields();
            //hate doing this but not sure what the better option is
            app.view.Field.prototype._render.call(this);
        }
    },

    /**
     * Dispose and remove start date and end date fields from the view.
     * @private
     */
    _disposeOldFields: function() {
        _.each(this.fields, function(field) {
            if (this.view.fields[field.sfId]) {
                this.view.fields[field.sfId].dispose();
                delete this.view.fields[field.sfId];
            }
        }, this);
        this.fields = [];
    },

    /**
     * Return the display string for the start and date, along with the duration.
     * @returns {string}
     */
    getFormattedValue: function() {
        var displayString = '',
            startDateString = this.model.get('date_start'),
            endDateString = this.model.get('date_end'),
            startDate,
            endDate,
            duration,
            durationString;

        if (startDateString && endDateString) {
            startDate = app.date(startDateString);
            endDate = app.date(endDateString);
            duration = app.date.duration(endDate - startDate);
            durationString = duration.format() || ('0 ' + app.lang.get('LBL_DURATION_MINUTES'));

            if ((duration.days() === 0) && (duration.months() === 0) && (duration.years() === 0)) {
                // Should not display the date twice when the start and the end dates are the same.
                displayString = app.lang.get('LBL_START_AND_END_DATE_SAME_DAY', this.module, {
                    date: startDate.formatUser(true),
                    start: startDate.format(app.date.getUserTimeFormat()),
                    end: endDate.format(app.date.getUserTimeFormat()),
                    duration: durationString
                });
            } else {
                displayString = app.lang.get('LBL_START_AND_END_DATE', this.module, {
                    start: startDate.formatUser(false),
                    end: endDate.formatUser(false),
                    duration: durationString
                });
            }
        }

        return displayString;
    },

    /**
     * Set the default start date time to the upcoming hour or half hour,
     * whichever is closest.
     * @param {Utils.Date} currentDateTime (optional) - current date time
     */
    setDefaultStartDateTime: function(currentDateTime) {
        var defaultDateTime = currentDateTime || app.date().seconds(0);

        if (defaultDateTime.minutes() > 30) {
            defaultDateTime
                .add('h', 1)
                .minutes(0);
        } else if (defaultDateTime.minutes() > 0) {
            defaultDateTime.minutes(30);
        }

        this.model.set('date_start', defaultDateTime.formatServer());
    },

    /**
     * If the start and end date has been set and the start date changes,
     * automatically change the end date to maintain duration.
     */
    modifyEndDateToRetainDuration: function() {
        var startDateString = this.model.get('date_start'),
            originalStartDateString = this.model.previous('date_start'),
            originalStartDate,
            endDateString = this.model.get('date_end'),
            endDate,
            duration,
            changedAttributes = this.model.changedAttributes();

        // Do not change the end date if the start date has not been set or if the start date
        // and the end date have been changed at the same time.
        if (!startDateString || (changedAttributes.date_start && changedAttributes.date_end)) {
            return;
        }

        if (endDateString && originalStartDateString) {
            // If end date has been set, maintain duration when the start
            // date changes.
            originalStartDate = app.date(originalStartDateString);
            duration = app.date(endDateString).diff(originalStartDate);

            // Only set the end date if start date is before the end date.
            if (duration >= 0) {
                endDate = app.date(startDateString).add(duration).formatServer();
                this.model.set('date_end', endDate);
            }
        } else {
            // Set the end date to be an hour from the start date if the end
            // date has not been set yet.
            endDate = app.date(startDateString).add('h', 1).formatServer();
            this.model.set('date_end', endDate);
        }
    },

    /**
     * Is this date range valid? It returns true when start date is before end date.
     * @returns {boolean}
     */
    isDateRangeValid: function() {
        var start = this.model.get('date_start'),
            end = this.model.get('date_end'),
            isValid = false;

        if (start && end) {
            if (app.date.compare(start, end) < 1) {
                isValid = true;
            }
        }

        return isValid;
    }
})
