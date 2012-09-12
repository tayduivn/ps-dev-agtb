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
        return jsDate.toISOString();
    },

    format:function(value) {
        var jsDate, output, 
            usersDateFormatPreference, usersTimeFormatPreference, 
            myUser = app.user;
        usersDateFormatPreference = myUser.get('datepref');
        usersTimeFormatPreference = myUser.get('timepref');

        // If there is a default 'string' value like "yesterday", format it as a date
        if(!value && this.def.display_default) {
            value  = app.date.parseDisplayDefault(this.def.display_default);
            // Preset the model with display default in case user doesn't change anything
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
        var self  = this, date, model, fieldName, hour, minute, amPm;
        date      = this.$('input');
        model     = this.model;
        fieldName = this.name;
        hour      = this.$('.date_time_hours');
        minute    = this.$('.date_time_minutes');
        amPm      = this.$('.date_time_ampm');

        date.on('change', function(ev) {
            model.set(fieldName, self.unformat(date.val() + ' ' + hour.val() + ':' + minute.val() + ':00' +':'+ amPm.val()));
        });
        hour.on('change', function(ev) {
            model.set(fieldName, self.unformat(date.val() + ' ' + hour.val() + ':' + minute.val() + ':00' +':'+ amPm.val()));
        });
        minute.on('change', function(ev) {
            model.set(fieldName, self.unformat(date.val() + ' ' + hour.val() + ':' + minute.val() + ':00' +':'+ amPm.val()));
        });
        amPm.on('change', function(ev) {
            model.set(fieldName, self.unformat(date.val() + ' ' + hour.val() + ':' + minute.val() + ':00' +':'+ amPm.val()));
        });
    }
})
