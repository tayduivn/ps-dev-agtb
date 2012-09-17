({
    // datetimecombo
    initialize: function(options) {
        var userTimePrefs;
        this.lastHourSelected   = null;
        this.lastMinuteSelected = null;
        this.lastAmPmSelected   = null;

        // Determines if we're using 12 or 24 hour clock conventions
        userTimePrefs = app.user.get('timepref');
        // h specifies 12 hour format (TODO: refactor date.js to support g/G options and add here)
        this.showAmPm = userTimePrefs.match(/h/)!==null ? true : false; // TODO: date.js doesn't yet support g/G options 
        this.timeOptions.hours = this.getHours();
        this.app.view.Field.prototype.initialize.call(this, options);
    },
    _render:function(value) {
        var self = this;
        app.view.Field.prototype._render.call(self);
        $(function() {
            if(self.view.name === 'edit') {
                $(".datepicker").datepicker({
                    showOn: "button",
                    buttonImage: app.config.siteUrl + "/sidecar/lib/jquery-ui/css/smoothness/images/calendar.gif",
                    buttonImageOnly: true
                });
            }
        });
    },
    unformat:function(value) {
        var jsDate, 
            myUser = app.user;
        jsDate = app.date.parse(value, myUser.get('datepref')+' '+ myUser.get('timepref'));
        if(jsDate && _.isFunction(jsDate.toISOString)) {
            return jsDate.toISOString();
        } else {
            app.logger.error("Issue converting date to iso string; no toISOString available for date created for value: "+value);
            return value;
        }
    },
    format:function(value) {
        var jsDate, output, 
            usersDateFormatPreference, usersTimeFormatPreference, 
            myUser = app.user;

    format:function(value) {
        var jsDate, output, usersDateFormatPreference, usersTimeFormatPreference, myUser = app.user, d, parts, before24Hours;
        usersDateFormatPreference = myUser.get('datepref');
        usersTimeFormatPreference = myUser.get('timepref');

        // If there is a default 'string' value like "yesterday", format it as a date
        if(!value && this.def.display_default) {
            value  = app.date.parseDisplayDefault(this.def.display_default);
            jsDate = app.date.dateFromDisplayDefaultString(value);
            this.model.set(this.name, jsDate.toISOString(), {silent: true}); 
        } else if(!value) {
            return value;
        } else {
            // In case ISO 8601 get it back to js native date which date.format understands
            jsDate = new Date(value);
        }

        // Save the 24 hour based hours in case we're using ampm to determine if am or pm later 
        before24Hours = jsDate.getHours();
        value  = app.date.format(jsDate, usersDateFormatPreference)+' '+app.date.format(jsDate, usersTimeFormatPreference);
        jsDate = app.date.parse(value);
        jsDate = app.date.roundTime(jsDate);
        
        value = {
            dateTime: app.date.format(jsDate, usersDateFormatPreference)+' '+app.date.format(jsDate, usersTimeFormatPreference),
            date: app.date.format(jsDate, usersDateFormatPreference),
            time: app.date.format(jsDate, usersTimeFormatPreference),
            hours: app.date.format(jsDate, (this.showAmPm ? 'h' : 'H')),
            minutes: app.date.format(jsDate, 'i'),
            seconds: app.date.format(jsDate, 's'),
            amPm: this.showAmPm ? (before24Hours < 12 ? 'am' : 'pm') : ''
        };
        this.lastHourSelected   = value.hours;
        this.lastMinuteSelected = value.minutes;
        this.lastAmPmSelected   = value.amPm;

        //0am must be shown as 12am if we're on a 12 hour based time format
        if(!_.isUndefined(value.amPm) && value.amPm === 'am' && value.hours == 0) {
            value.hours = '12';
            d = new Date();
            d.setHours(12); 
            d.setMinutes(value.minutes); 
            value.time = app.date.format(d, usersTimeFormatPreference);
            this.model.set(this.name, this.buildUnformatted(value.date, '00', value.minutes), {silent: true});
            this.lastHourSelected  = '00';
        }
        return value;
    },
    getHours: function() {
        if(this.showAmPm) {
            return  [
                {key: "", value: ""},
                {key: "01", value: "01"},
                {key: "02", value: "02"},
                {key: "03", value: "03"},
                {key: "04", value: "04"},
                {key: "05", value: "05"},
                {key: "06", value: "06"},
                {key: "07", value: "07"},
                {key: "08", value: "08"},
                {key: "09", value: "09"},
                {key: "10", value: "10"},
                {key: "11", value: "11"},
                {key: "12", value: "12"}
            ];
        } else {
            return  [
                {key: "", value: ""},
                {key: "00", value: "00"},
                {key: "01", value: "01"},
                {key: "02", value: "02"},
                {key: "03", value: "03"},
                {key: "04", value: "04"},
                {key: "05", value: "05"},
                {key: "06", value: "06"},
                {key: "07", value: "07"},
                {key: "08", value: "08"},
                {key: "09", value: "09"},
                {key: "10", value: "10"},
                {key: "11", value: "11"},
                {key: "12", value: "12"},
                {key: "13", value: "13"},
                {key: "14", value: "14"},
                {key: "15", value: "15"},
                {key: "16", value: "16"},
                {key: "17", value: "17"},
                {key: "18", value: "18"},
                {key: "19", value: "19"},
                {key: "20", value: "20"},
                {key: "21", value: "21"},
                {key: "22", value: "22"},
                {key: "23", value: "23"}
            ];
        }
    },
    timeOptions: { 
        /* hours: set dynamically */
        minutes: [
            {key: "", value: ""},
            {key: "00", value: "00"},
            {key: "15", value: "15"},
            {key: "30", value: "30"},
            {key: "45", value: "45"}
        ],
        amPm: [
            {key: "", value: ""},
            {key: "am", value: "am"},
            {key: "pm", value: "pm"}
        ]
    },
    bindDomChange: function() {
        $('select').css({'width': 50});
        var self  = this, date, model, fieldName, hour, minute, amPm, hr, min;
        date      = this.$('input');
        model     = this.model;
        fieldName = this.name;
        hour      = this.$('.date_time_hours');
        minute    = this.$('.date_time_minutes');
        amPm      = this.$('.date_time_ampm');

        date.on('change', function(ev) {
            var timeObj;
            // If user selects date but no hour/minutes selected yet, prepopulate with 12:00am
            hr = self.patchHour(amPm.val(), hour.val());
            timeObj = self.setIfNoTime(hr, minute.val());
            model.set(fieldName, self.buildUnformatted(date.val(), timeObj.hour, timeObj.minutes));
        });
        hour.on('change', function(ev) {
            // If no date (display default set to none), user may select hrs, minutes, etc., so defaults to today's date 
            date.val(self.setIfNoDate(date.val()));
            // if user attempts to select first "blank" option, set back to previous hour selected
            hr = hour.val() ? hour.val() : self.lastHourSelected; 
            hr = self.patchHour(amPm.val(), hr);
            self.lastHourSelected = hr;
            // if selecting hour and no minutes yet put a default value to make it meaningful
            min  = minute.val() ? minute.val() : '00'; 
            // Force trigger since internally 00/12 are both represented as 00 so won't always trigger
            model.set(fieldName, self.buildUnformatted(date.val(), hr, min), {silent: true});
            model.trigger('change:'+fieldName);
        });
        minute.on('change', function(ev) {
            date.val(self.setIfNoDate(date.val()));
            // if selecting minutes and no hours yet put a default value to make it meaningful
            hr = self.patchHour(amPm.val(), hour.val());
            min  = minute.val() ? minute.val() : self.lastMinuteSelected;
            self.lastMinuteSelected = min;
            model.set(fieldName, self.buildUnformatted(date.val(), hr, min), {silent: true});
            model.trigger('change:'+fieldName);
        });
        amPm.on('change', function(ev) {
            var ampm, timeObj;
            date.val(self.setIfNoDate(date.val()));

            // If valid am or pm select fine, but also force blank selection to "bounce back" to previously selected
            if(!amPm.val()) {
                amPm.find("option").attr("selected",false);
                ampm = self.lastAmPmSelected;
                amPm.find("option[value='"+ampm+"']").attr("selected",true);
            } else {
                ampm = amPm.val();
            }
            self.lastAmPmSelected = ampm; 
            hr = self.patchHour(amPm.val(), hour.val());
            timeObj = self.setIfNoTime(hr, minute.val(), ampm);
            model.set(fieldName, self.buildUnformatted(date.val(), timeObj.hour, timeObj.minutes));
        });
    },
    buildUnformatted: function(d, h, m) {
        return this.unformat(d + ' ' + h + ':' + m + ':00');
    },
    
    // Checks if h and/or m are falsy, if so, sets to '00'
    setIfNoTime: function(h, m, ampm) {
        var o = {};
        // Essentially, if we have no time parts, we're going to default to 12:00am
        if(!h && !m) {
            o.amPm = ampm ? ampm : 'am';
        }
        o.hour = h ? h : '00'; // will downstream turn to 12am but internally needs to be 00
        o.minutes = m ? m : '00';
        
        //Convert 12am to 00 and also 00pm to 12
        o.hour = o.hour==='12' && o.amPm==='am' ? '00' : o.hour;
        o.hour = o.hour==='00' && o.amPm==='pm' ? '12' : o.hour;
        return o;
    },
    // Checks if dateToCheck is falsy .. if so returns today date formatted by user's prefs
    setIfNoDate: function(dateToCheck) {
        var usersDateFormatPreference = app.user.get('datepref');
        if(!dateToCheck) {
            var d = new Date();
            return app.date.format(d, usersDateFormatPreference);
        } 
        return dateToCheck;
    },

    // If we have 00am we patch the displayed value to 12 am but we still want internally to represent as 00am
    // if on 12 hour time format .. if not this function will just return hour val anyway. 
    patchHour: function (ampm, hour) {
        var hr = hour ? parseInt(hour, 10) : 0;
        if(this.showAmPm) {
            // Patch 12am to 00am as we need it this way internally though we present 12am (if on 12 hr time format)
            if(ampm && ampm === 'am' && hr === 12) {
                return '00';
            } else if(hr < 12 && ampm === 'pm') {
                // add 12 e.g. 4pm becomes 16 - again for internal iso representation
                return hr+12+'';
            }
        }
        return hour;
    }
    
})
