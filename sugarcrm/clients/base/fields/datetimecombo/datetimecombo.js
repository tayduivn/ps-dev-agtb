/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    // datetimecombo
    events: {
        'click .icon-calendar': '_toggleDatepicker'
    },
    datepickerVisible: false,
    timeValue: '', // used by hbt template
    dateValue: '', // same 
    initialize: function(options) {

        /**
         * TODO: Are we still using these?
         */
        this.lastHourSelected   = null;
        this.lastMinuteSelected = null;
        this.lastAmPmSelected   = null;


        this.userTimePrefs  = app.user.get('timepref');
        this.usersDatePrefs = app.user.get('datepref');

        // Note that Sugar will always have an 'h' if ends with [aA] ('h:iA'=>'11:00PM', 'h.ia'=>'11.00pm', 'h.iA'=>'11.00PM')
        this.showAmPm = this.userTimePrefs.match(/[aA]$/)==null ? true : false; // TODO: date.js doesn't yet support g/G options
        app.view.Field.prototype.initialize.call(this, options);
    },
    _render:function(value) {
        var self = this;

        // Set our internal time and date values so hbt picks up
        self.dateValue = self.$('.datepicker').val();
        self.dateValue = (self.dateValue) ? self.dateValue : '';
        self.timeValue = self.$('.ui-timepicker-input').val();
        self.timeValue = (self.timeValue) ? self.timeValue : '';

        app.view.Field.prototype._render.call(self);

        $(function() {
            var prettyNow;

            if (self.view.name === 'edit') {

                /**
                 * Set up the the Datepicker
                 */
                self.$(".datepicker").attr('placeholder', app.date.toDatepickerFormat(self.usersDatePrefs));
                self.$(".datepicker").datepicker({
                    format: (self.usersDatePrefs) ? app.date.toDatepickerFormat(self.usersDatePrefs) : 'mm-dd-yyyy'
                });

                // Bind Datepicker to our proxy functions
                self.$(".datepicker").datepicker().on({
                    show: _.bind(self.showDatepicker, self),
                    hide: _.bind(self.hideDatepicker, self)
                });

                /**
                 * Set up the the Timepicker
                 */
                self.$(".ui-timepicker-input").attr('placeholder', self.userTimePrefs);
                self.$(".ui-timepicker-input").timepicker({
                    'timeFormat': self.userTimePrefs,
                    'scrollDefaultNow': true, // detects user's time (e.g if 1pm drodown jumps to 1:00 location)
                    'step': 15 // 15 minute intervals consistent w/Sugar proper
                });

                // Bind Timepicker to proxy functions
                self.$('.ui-timepicker-input').on({
                    changeTime: _.bind(self.changeTime, self)
                });
            }
        });
    },
    unformat:function(value) {
        var jsDate, 
            myUser = app.user;

        if (value) {
            jsDate = app.date.parse(value);
            if (jsDate && _.isFunction(jsDate.toISOString)) {
                return jsDate.toISOString();
            } else {
                app.logger.error("Issue converting date to iso string; no toISOString available for date created for value: "+value);
                return value;
            }
        }
        return value;
    },
    format:function(value) {
        var jsDate, output, myUser = app.user, d, parts, before24Hours;

        if (this.model.isNew() && !value) {
            jsDate = this._setDateIfDefaultValue();
            if (!jsDate) {
                return value;
            }
        } else if (!value) {
            return value;
        } else {
            // In case ISO 8601 get it back to js native date which date.format understands
            jsDate = new Date(value);
        }

        // Save the 24 hour based hours in case we're using ampm to determine if am or pm later 
        before24Hours = jsDate.getHours();
        value  = app.date.format(jsDate, this.usersDatePrefs)+' '+app.date.format(jsDate, this.userTimePrefs);
        jsDate = app.date.parse(value);
        // round time to the nearest 15th if this is a edit which is consitent with rest of app
        if (this.view.name === 'edit') {
            jsDate = app.date.roundTime(jsDate);
        }

        /**
         * TODO: Are we still using all of these properties? Prune unused.
         */
        value = {
            date: app.date.format(jsDate, this.usersDatePrefs),
            time: app.date.format(jsDate, this.userTimePrefs),
            hours: app.date.format(jsDate, (this.showAmPm ? 'h' : 'H')),
            minutes: app.date.format(jsDate, 'i'),
            seconds: app.date.format(jsDate, 's'),
            amPm: this.showAmPm ? (before24Hours < 12 ? 'am' : 'pm') : ''
        };

        /**
         * TODO: Are we still using these???
         */
        this.lastHourSelected   = value.hours;
        this.lastMinuteSelected = value.minutes;
        this.lastAmPmSelected   = value.amPm;

        // 0am must be shown as 12am if we're on a 12 hour based time format
        if (value.time.toLowerCase().indexOf('am') !== -1 && value.hours == 0) {

            // Now for 00 to 12 since we want 12am
            value.hours = '12';
            d = new Date();
            d.setHours(12); 
            value.time = '12' + value.time.substr(2);
            d.setMinutes(value.minutes); 
            this.model.set(this.name, this.buildUnformatted(value.date, '00', value.minutes), {silent: true});
            this.lastHourSelected  = '00';
        }
        this.timeValue = value['time'];
        this.dateValue = value['date'];
        this.$(".datepicker").datepicker('update', this.dateValue);

        return value;
    },
    buildUnformatted: function(d, h, m) {
        return this.unformat(d + ' ' + h + ':' + m + ':00');
    },
    /**
     * NOP - jquery.timepicker (the plugin we use for time part of datetimecombo widget),
     * triggers both a 'change', and, a 'changeTime' event. If we let base Field's bindDomChange
     * handle, it will result in our format method getting called and we do NOT want that. The
     * reason is that we've already bound this.changeTime to handle changeTime events (and do all 
     * time related handling there). Also, the datepicker plugin events are being handled as well.
     */
    bindDomChange: function() {
        // NOP -- pass through to prevent base Field's bindDomChange to handle
    },
    showDatepicker: function(ev) {
        this.datepickerVisible = true;
    },
    /**
     * This is the main hook we use to update model when datepicker selected
     */
    hideDatepicker: function(ev) {
        var model     = this.model,
            fieldName = this.name, 
            timeValue = '',
            hrsMins   = {},
            dateValue = '',
            $timepicker;

        this.datepickerVisible = false;
        model      = this.model;
        fieldName  = this.name;
        $timepicker= this.$('.ui-timepicker-input');

        // Get time values. If none, set to default of midnight; also get date, set model, etc.
        timeValue  = this._getTimepickerValue($timepicker);
        hrsMins    = this._getHoursMinutes(timeValue);
        this._setTimepickerValue($timepicker, hrsMins.hours, hrsMins.minutes);
        dateValue  = this._getDatepickerValue();
        model.set(fieldName, this.buildUnformatted(dateValue, hrsMins.hours, hrsMins.minutes), {silent: true});
    },
    /**
     * This is the main hook we use to update model when timepicker selected
     */
    changeTime: function(ev) {
        var model     = this.model,
            fieldName = this.name,
            timeValue = '',
            hrsMins= {},
            dateValue = '', timeParts, hour, hours, minutes;

        // Get hours, minutes, and date peices, then set our model
        timeValue  = this._getTimepickerValue($(ev.currentTarget));
        hrsMins    = this._getHoursMinutes(timeValue);
        dateValue  = this._getDatepickerValue();
        this._setDatepickerValue(dateValue);
        model.set(fieldName, this.buildUnformatted(dateValue, hrsMins.hours, hrsMins.minutes), {silent: true});
    },
    /**
     * Toggles datepicker hidden or shown
     */
    _toggleDatepicker: function() {
        var action = (this.datepickerVisible) ? 'hide' : 'show';
        this.$(".datepicker").datepicker(action);
    },
    /**
     * Gets the current datepicker value.
     * 
     * Note: If we have no date (e.g. display default was set to none), when the 
     * user selects time part, date part will be pre-filled with today's date.
     */
    _getDatepickerValue: function() {
        var date  = this.$('input.datepicker'), dateValue;

        dateValue = this._getTodayDateStringIfNoDate(date.prop('value'));
        this.dateValue = dateValue; // so hbt template will pick up on next render
        return dateValue;
    },
    /**
     * Sets the current datepicker value.
     * @param String dateValue date value 
     */
    _setDatepickerValue: function(dateValue) {
        var date = this.$('input.datepicker');
        dateValue = this._getTodayDateStringIfNoDate(dateValue);
        date.prop('value', dateValue); 
    },
    /**
     * Returns the current value in the timepicker element. 
     * @param String $timepickerElement the element. 
     * @return String timeValue The time value.
     */
    _getTimepickerValue: function($timepickerElement) {
        var timeValue  = $timepickerElement.val();
        this.timeValue = timeValue; // so hbt template will pick up on next render
        return timeValue;
    },
    /**
     * Sets the timepicker element and plugin to hours and minutes passed in. 
     * If neither hours or minutes provided defaults to midnight.
     * @param String $timepickerElement the element. 
     * @param String hours optional hours 
     * @param String minutes optional minutes
     */
    _setTimepickerValue: function($timepickerElement, hours, minutes) {
        var date = new Date();

        // If no time value set to midnight
        if (!hours && !minutes) {
            // Shorthand allows us to set mins, secs, ms all at once
            date.setHours(0, 0, 0, 0);
        }
        else {
            // If we have minutes or hours set each one conditionally
            if (minutes) {
                date.setMinutes(minutes);
            }
            if (hours) {
                date.setHours(hours);
            }
        }
        $timepickerElement.timepicker('setTime', date);
        this.timeValue = $timepickerElement.val();// so hbt template will pick up on next render
    },
    /**
     * If the field def has a display_default property, or, is required, this
     * will set the model with corresponding date time.
     */
    _setDateIfDefaultValue: function() {
        var value, jsDate; 

        // If there's a display default 'string' value like "yesterday", format it as a date
        if (this.def.display_default) {
            value  = app.date.parseDisplayDefault(this.def.display_default);
            jsDate = app.date.dateFromDisplayDefaultString(value);
            this.model.set(this.name, jsDate.toISOString(), {silent: true}); 
        } else if (this.def.required) {
            // Per the FDD: When the Datetime field is mandatory, the default value
            // should be SYSDATE and all zeros for Time value
            jsDate = new Date();
            jsDate.setHours(0, 0, 0, 0);
            this.model.set(this.name, jsDate.toISOString(), {silent: true}); 
        } else {
            return null;  
        }
        return jsDate;
    },
    /**
     * Takes the time value and returns hours and minutes parts.
     * @param String timeValue time value within timepicker field. 
     * @return Object An object literal like: {hours: <hours>, minutes: <minutes>}
     */
    _getHoursMinutes: function(timeValue) {
        var timeParts, hour, hours, minutes;

        timeValue = timeValue || '';
        timeParts = timeValue.toLowerCase().match(/(\d+)(?::(\d\d?))?\s*([pa]?)/);

        // If timeValue is empty we may get back null for regex. If so, set to default.
        if (!timeParts) {
            return this._setIfNoTime(null, null);
        }

        hour = parseInt(timeParts[1]*1, 10);

        // We have am/pm part (ostensibly 12 hour format)
        if (!_.isUndefined(timeParts[3])) {
            if (hour == 12) {
                // If 12 and am force 12 to 0, otherwise leave alone
                hours = (timeParts[3] == 'a') ? 0 : hour;
            } else {
                // If pm add 12 to hour e.g. 2 becomes 14, etc.
                hours = (hour + (timeParts[3] == 'p' ? 12 : 0));
            }
        } 
        // Otherwise, we don't have am/pm part (ostensibly 24 hr format)
        else {
            hours = hour;
        }
        minutes = ( timeParts[2]*1 || 0 );

        // Convert above to two character strings
        minutes = this._forceTwoDigits(minutes.toString());
        hours = this._forceTwoDigits(hours.toString());

        return this._setIfNoTime(hours, minutes);
    },
    /**
     * Helper to set hours and minutes with 12am and 00pm edge cases in mind.
     * @param String h hours 
     * @param String m minutes
     * @param String ampm optional 'am' or 'pm'  
     * @return Object object literal with hours, minutes, amPm properties
     */
    _setIfNoTime: function(h, m, ampm) {
        var o = {};
        // Essentially, if we have no time parts, we're going to default to 12:00am
        if (!h && !m) {
            o.amPm = ampm ? ampm : 'am';
        }
        o.hours = h ? h : '00'; // will downstream turn to 12am but internally needs to be 00
        o.minutes = m ? m : '00';
        
        //Convert 12am to 00 and also 00pm to 12
        o.hours = o.hours === '12' && o.amPm==='am' ? '00' : o.hours;
        o.hours = o.hours === '00' && o.amPm==='pm' ? '12' : o.hours;

        return o;
    },
    /**
     * Checks if dateStringToCheck is falsy..if so, returns today's date as string formatted by
     * user's prefs. Otherwise, just returns dateStringToCheck.
     */
    _getTodayDateStringIfNoDate: function(dateStringToCheck) {
        if (!dateStringToCheck) {
            var d = new Date();
            return app.date.format(d, this.usersDatePrefs);
        } 
        return dateStringToCheck;
    },
    /**
     * If we have 00am we patch the displayed value to 12 am but we still want internally to represent as 00am
     * if on 12 hour time format .. if not this function will just return hour val anyway. 
     */
    _patchHour: function (ampm, hour) {
        var hr = hour ? parseInt(hour, 10) : 0;
        if (this.showAmPm) {
            // Patch 12am to 00am as we need it this way internally though we present 12am (if on 12 hr time format)
            if (ampm && ampm === 'am' && hr === 12) {
                return '00';
            } else if (hr < 12 && ampm === 'pm') {
                // add 12 e.g. 4pm becomes 16 - again for internal iso representation
                return hr+12+'';
            }
        }
        return hour;
    },
    /**
     * Pads digits
     */
    _forceTwoDigits: function(numstr) {
        return numstr.length === 1 ? '0' + numstr: numstr;
    }
})
