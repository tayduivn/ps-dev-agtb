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
    showAmPm: false,
    timeValue: '', // used by hbt template (note we inherit dateValue from basedate)
    _setTimeValue: function() {
        this.timeValue = this.$('.ui-timepicker-input').val();
        this.timeValue = (this.timeValue) ? this.timeValue : '';
    },
    _render:function(value) {
        var self = this, viewName;

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
    format:function(value) {
        var jsDate, output, myUser = app.user, d, parts, before24Hours;

        if (this._isNewEditViewWithNoValue(value)) {
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
 
    /**
     * Main hook to update model when timepicker selected
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
            return this._setDateNow();
        } else {
            return null;  
        }
        return jsDate;
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
            changeTime: _.bind(this.changeTime, this)
        });
    },
    /**
     * Takes the time value and returns hours and minutes parts.
     * @param String timeValue time value within timepicker field. 
     * @return Object An object literal like: {hours: <hours>, minutes: <minutes>}
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
     * @param String h hours 
     * @param String m minutes
     * @param String ampm optional 'am' or 'pm'  
     * @return Object object literal with hours, minutes, amPm properties
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
