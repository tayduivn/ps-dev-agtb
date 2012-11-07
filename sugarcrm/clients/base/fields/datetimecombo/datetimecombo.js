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
    extendsFrom:'BasedateField',

    // This is dynamically detected and represents whether meridien is appropriate
    showAmPm: false,

    // used by hbt template (note we inherit dateValue from basedate)
    timeValue: '', 


    /**
     * Renders widget, sets up date and time pickers, etc.
     * @param  {String} value  
     */
    _render:function(value) {
        var self = this, viewName;

        // Set our internal time and date values so hbt picks up
        self._presetDateValues();

        app.view.Field.prototype._render.call(self);

        viewName = self._getViewName();

        $(function() {
            if (self._isEditView(viewName)) {
                self._setupDatepicker();
                self._setupTimepicker();
            }
        });
    },

    /**
     * Strips out the 'T' and, either the 'Z' or +00:00 by, essentially, taking just the first 19 characters
     * @param  {String} value The value 
     * @return {String} stripped value 
     */
    _stripIsoTimeDelimterAndTZ: function(value) {
        if(!_.isUndefined(value) && value) {
            // Since s.replace('T', ' ').replace('Z','') assumes we have Z it's better to do:
            // str.replace('T', ' ').substr(0, 19) ... which works for both of following formats:
            // '2012-11-07T04:28:52+00:00'.replace('T', ' ').substr(0, 19)
            // "2012-11-06 20:00:06.651Z".replace('T', ' ').substring(0, 19)
            return value.replace("T", " ").substr(0, 19);
        }
    },
    /**
     * Formats value
     * @param  {String} value The value
     * @return {String} formatted value
     */
    format:function(value) {
        var jsDate, output, myUser = app.user, d, parts, before24Hours;

        if (this.stripIsoTZ) {
            value = this._stripIsoTimeDelimterAndTZ(value);
        }

        if (this._isNewEditViewWithNoValue(value)) {
            jsDate = this._setDateIfDefaultValue();
            if (!jsDate) {
                return value;
            }
        } else if (!value) {
            return value;
        } else {
            // In case ISO 8601 get it back to js native date which date.format understands
            // Note that if stripIsoTZ is true, time zone won't matter since it was stripped out.
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

        value = {
            date: app.date.format(jsDate, this.usersDatePrefs),
            time: app.date.format(jsDate, this.userTimePrefs),
            hours: app.date.format(jsDate, (this.showAmPm ? 'h' : 'H')),
            minutes: app.date.format(jsDate, 'i'),
            seconds: app.date.format(jsDate, 's'),
            amPm: this.showAmPm ? (before24Hours < 12 ? 'am' : 'pm') : ''
        };

        // 0am must be shown as 12am if we're on a 12 hour based time format
        if (value.time.toLowerCase().indexOf('am') !== -1 && value.hours == 0) {

            // Now for 00 to 12 since we want 12am
            value.hours = '12';
            d = new Date();
            d.setHours(12); 
            value.time = '12' + value.time.substr(2);
            d.setMinutes(value.minutes); 
            this.model.set(this.name, this._buildUnformatted(value.date, '00', value.minutes), {silent: true});
        }
        this.timeValue = value['time'];
        this.dateValue = value['date'];
        this.$(".datepicker").datepicker('update', this.dateValue);

        return value;
    },

    unformat:function(value) {
        var jsDate;
        if (value) {
            jsDate = app.date.parse(value);

            if (jsDate) {
                return this._setServerDateString(jsDate);
            } else {
                app.logger.error("Issue setting the server date string for value: " + value);
                return value;
            }
        }
        return value;
    },
        
    /**
     * Sets up the timepicker.
     */
    _setupTimepicker: function() {
        this.$(".ui-timepicker-input").attr('placeholder', this.userTimePrefs);
        this.$(".ui-timepicker-input").timepicker({
            // TODO: 'lang' is only used on time "durations" (e.g. 3 horas, etc.) We can later pull 
            // this from meta, but, this only makes sense if we implement durations. To my mind, this
            // is really a client specific customization for which they can set this themselves.
            // "lang": {"decimal": '.', "mins": 'minutos', "hr": 'hora', "hrs": 'horas'},
            'timeFormat': this.userTimePrefs, // And this will localize their time format anyway ;-)
            'scrollDefaultNow': true,         // detects user's time (e.g if 1pm, dropdown jumps to 1:00)
            'step': 15                        // consistent w/Sugar proper ... may need to be dynamic later
        });

        // Bind Timepicker to proxy functions
        this.$('.ui-timepicker-input').on({
            changeTime: _.bind(this.changeTime, this),
            blur: _.bind(this._handleTimepickerBlur, this)
        });

    }, 
    /**
     * Main hook to update model when timepicker selected
     * @param {event} ev The event 
     */
    changeTime: function(ev) {
        var model     = this.model,
            fieldName = this.name,
            timeValue = '',
            hrsMins= {},
            dateValue = '', timeParts, hour, hours, minutes;

        // Get hours, minutes, and date peices, then set our model
        hrsMins    = this._getHoursMinutes($(ev.currentTarget));
        dateValue  = this._getDatepickerValue();
        this._setDatepickerValue(dateValue);
        model.set(fieldName, this._buildUnformatted(dateValue, hrsMins.hours, hrsMins.minutes), {silent: true});
    },
    /**
     * Precondition: timeAsDate must be date and hrsMins must be object literal.
     * No checking done!
     * 
     * @param  {HTMLElement} The timepicker element 
     * @param  {Date} timeAsDate Date representing time portion
     * @param  {Ojbect} hrsMins Object literal for hours and minutes
     * @return {Object} Object representing hours and minutes. If not edit view 
     * this will simply return hrsMins passed in
     */
    _forceRoundedTime: function(timepicker, timeAsDate, hrsMins) {
        var minutes, hours;

        // If edit view we force time to our 15 minutes blocks
        if (this.view.name === 'edit') {
            timeAsDate = app.date.roundTime(timeAsDate);
            minutes    = this._forceTwoDigits(timeAsDate.getMinutes().toString());
            hours      = this._forceTwoDigits(timeAsDate.getHours().toString());

            // Update timepicker element's value with rounded time
            this._setTimepickerValue($(timepicker), hours, minutes);
            hrsMins.hours   = hours;
            hrsMins.minutes = minutes;
        }
        return hrsMins;
    },
    /**
     * The timepicker plugin doesn't provide blur hook for when the user types in
     * time and then focuses out. Essentially, we update our this.timeValue and model. 
     * @param {event} ev The event 
     */
    _handleTimepickerBlur: function(ev) {
        var dateValue, hrsMins, timeAsDate, hours, minutes, 
            timepicker = ev.currentTarget;

        // First get current hours/minutes, then round to blocks (if edit view)
        hrsMins    = this._getHoursMinutes($(ev.currentTarget));
        timeAsDate = this._getTimepickerValueAsDate($(timepicker));
        hrsMins    = this._forceRoundedTime(timepicker, timeAsDate, hrsMins);
        this._setTimeValue();

        // Get datepicker value and finally set our model
        dateValue  = this._getDatepickerValue();
        this.model.set(this.name, this._buildUnformatted(dateValue, hrsMins.hours, hrsMins.minutes), {silent: true});
    },
    _setTimeValue: function() {
        this.timeValue = this.$('.ui-timepicker-input').val();
        this.timeValue = (this.timeValue) ? this.timeValue : '';
    },
    /**
     * Returns the current value in the timepicker element. 
     * @param {String} $timepickerElement the element. 
     * @return {String} timeValue The time value.
     */
    _getTimepickerValue: function($timepickerElement) {
        var timeValue  = $timepickerElement.val();
        this.timeValue = timeValue; // so hbt template will pick up on next render

        return timeValue;
    },
    /**
     * Get the time using a Javascript Date object, relative to today's date.
     * @param {HTMLElement} $timepickerElement the element. 
     * @return {Date} time relative to today's date in date object 
     */
    _getTimepickerValueAsDate: function($timepickerElement) {
        return this.$($timepickerElement).timepicker('getTime');
    },
    /**
     * Sets the timepicker element and plugin to hours and minutes passed in. 
     * If neither hours or minutes provided defaults to midnight.
     * @param {Object} jQuery wrapped timepicker element. 
     * @param {String} hours optional hours 
     * @param {String} minutes optional minutes
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
     * @return {Date} jsDate The date.
     */
    _setDateIfDefaultValue: function() {
        var value, jsDate; 

        // If there's a display default 'string' value like "yesterday", format it as a date
        if (this.def.display_default) {
            jsDate = app.date.parseDisplayDefault(this.def.display_default);
            this.model.set(this.name, this._setServerDateString(jsDate), {silent: true}); 
        } else if (this.def.required) {
            return this._setDateNow();
        } else {
            return null;  
        }
        return jsDate;
    },

    /**
     * Takes the time value and returns hours and minutes parts.
     * @param {HTMLElement} $timepickerElement the element. 
     * @return {Object} An object literal like: {hours: <hours>, minutes: <minutes>}
     */
    _getHoursMinutes: function(el) {
        var timeParts, hour, hours, minutes, timeValue, amPm = null;
        timeValue  = this._getTimepickerValue(el) || '';
        timeParts = timeValue.toLowerCase().match(/(\d+)(?::(\d\d?))?\s*([pa]?)/);

        // If timeValue is empty we may get back null for regex. If so, set to default.
        if (!timeParts) {
            return this._setIfNoTime(null, null);
        }

        hour = parseInt(timeParts[1]*1, 10);

        // We have am/pm part (ostensibly 12 hour format)
        if (!_.isUndefined(timeParts[3])) {

            amPm = (timeParts[3] === 'a') ? 'am' : 'pm';

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

        return this._setIfNoTime(hours, minutes, amPm);
    },
    /**
     * Helper to set hours and minutes with 12am and 00pm edge cases in mind.
     * @param {String} h hours 
     * @param {String} m minutes
     * @param {String} ampm optional 'am' or 'pm'  
     * @return {Object} object literal with hours, minutes, amPm properties
     */
    _setIfNoTime: function(h, m, ampm) {
        var o = {};

        o.amPm = ampm ? ampm : 'am';

        // Essentially, if we have no time parts, we're going to default to 12:00am
        if (!h && !m) {
            o.amPm = 'am';
        }
        o.hours = h ? h : '00'; // will downstream turn to 12am but internally needs to be 00
        o.minutes = m ? m : '00';
        
        //Convert 12am to 00 and also 00pm to 12
        o.hours = o.hours === '12' && o.amPm==='am' ? '00' : o.hours;
        o.hours = o.hours === '00' && o.amPm==='pm' ? '12' : o.hours;

        return o;
    }

})
