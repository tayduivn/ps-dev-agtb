({
    // datetimecombo
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
        if(jsDate && typeof jsDate.toISOString === 'function') {
            return jsDate.toISOString();
        } else {
            app.logger.error("Issue converting date to iso string; no toISOString available for date created for value: "+value);
            return value;
        }
    },

    format:function(value) {
        var jsDate, output, usersDateFormatPreference, usersTimeFormatPreference, myUser = app.user, d, parts;
        usersDateFormatPreference = myUser.get('datepref');
        usersTimeFormatPreference = myUser.get('timepref');

        // If there is a default 'string' value like "yesterday", format it as a date
        if(!value && this.def.display_default) {
            value  = app.date.parseDisplayDefault(this.def.display_default);
            dt = app.date.dateFromDisplayDefaultString(value);
            this.model.set(this.name, dt.toISOString()); 
        } else if(!value) {
            return value;
        } else {
            // In case ISO 8601 get it back to js native date which date.format understands
            jsDate = new Date(value);
            value  = app.date.format(jsDate, usersDateFormatPreference)+' '+app.date.format(jsDate, usersTimeFormatPreference);
        }
        jsDate = app.date.parse(value);
        jsDate = app.date.roundTime(jsDate);
        
        value = {
            dateTime: app.date.format(jsDate, usersDateFormatPreference)+' '+app.date.format(jsDate, usersTimeFormatPreference),
            date: app.date.format(jsDate, usersDateFormatPreference),
            time: app.date.format(jsDate, usersTimeFormatPreference),
            hours: app.date.format(jsDate, 'H'),
            minutes: app.date.format(jsDate, 'i'),
            seconds: app.date.format(jsDate, 's'),
            amPm: app.date.format(jsDate, 'H') < 12 ? 'am' : 'pm'
        };
        //0am must be shown as 12am
        if(typeof value.amPm != "undefined" && value.amPm == 'am' && value.hours == 0) {
            value.hours = '12';
            d = new Date();
            d.setHours(12); 
            value.time = app.date.format(d, usersTimeFormatPreference);
            this.model.set(this.name, this.buildUnformatted(value.date, '00', value.minutes, value.amPm), {silent: true});
        }
        return value;
    },

    timeOptions:{  //TODO set this via a call to userPrefs in a overloaded initalize
        hours:[
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
            {key: "23", value: "23"},
            {key: "24", value: "24"}
        ],
            minutes: [
            {key: "00", value: "00"},
            {key: "15", value: "15"},
            {key: "30", value: "30"},
            {key: "45", value: "45"}
        ],
            amPm: [
            {key: "am", value: "am"},
            {key: "pm", value: "pm"}
        ]
    },
    bindDomChange: function() {
        $('select').css({'width': 50});
        var self  = this, date, model, fieldName, hour, minute, amPm, hr;
        date      = this.$('input');
        model     = this.model;
        fieldName = this.name;
        hour      = this.$('.date_time_hours');
        minute    = this.$('.date_time_minutes');
        amPm      = this.$('.date_time_ampm');

        date.on('change', function(ev) {
            model.set(fieldName, self.buildUnformatted(date.val(), hour.val(), minute.val(), amPm.val()));
        });
        hour.on('change', function(ev) {
            // If no date (display default set to none), user may select hrs, minutes, etc., so defaults to now date in this case
            date.val(self.setIfNoDate(date.val()));
            hr = self.patched12AmHour(amPm, hour);
            model.set(fieldName, self.buildUnformatted(date.val(), hr, minute.val(), amPm.val()), {silent: true});
        });
        minute.on('change', function(ev) {
            date.val(self.setIfNoDate(date.val()));
            hr = self.patched12AmHour(amPm, hour);
            model.set(fieldName, self.buildUnformatted(date.val(), hr, minute.val(), amPm.val()), {silent: true});
        });
        amPm.on('change', function(ev) {
            var isWrongAmPm = false;

            date.val(self.setIfNoDate(date.val()));
            //am only be allowed 0-12; pm 12-23
            if(parseInt(hour.val(), 10) >= 13 && amPm.val()==='am') {
                amPm.val('pm');
                isWrongAmPm = true;
            }
            else if(parseInt(hour.val(), 10) < 12 && amPm.val()==='pm') {
                amPm.val('am');
                isWrongAmPm = true;
            }
            if(isWrongAmPm) {
                app.alert.show('wrong_ampm', {
                    level: 'warning',
                    title: app.lang.get('LBL_PORTAL_WRONG_AMPM'),
                    messages: app.lang.get('LBL_PORTAL_WRONG_AMPM_MSG'),
                    autoClose: true});
            }
            model.set(fieldName, self.buildUnformatted(date.val(), hour.val(), minute.val(), amPm.val()));
        });
    },
    buildUnformatted: function(d, h, m, amPm) {
        return this.unformat(d + ' ' + h + ':' + m + ':00' +':'+ amPm);
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
    patched12AmHour: function (amPm, hour) {
        // Patch 12am to 00am as we need it this way internally though we present 12am
        if(amPm.val() && amPm.val() === 'am' && hour.val() === '12') {
            return '00';
        } else {
            return hour.val();
        }
    }
    
})
