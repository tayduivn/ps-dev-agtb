({
    // datetime
    _render: function(value) {
        var self = this, usersDateFormatPreference;
        app.view.Field.prototype._render.call(this);//call proto render
        usersDateFormatPreference = app.user.get('datepref');

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
            usersDateFormatPreference = app.user.get('datepref');
        jsDate = this.app.date.parse(value, usersDateFormatPreference);
        return this.app.date.format(jsDate, usersDateFormatPreference);
    },

    format:function(value) {
        var jsDate, now, usersDateFormatPreference;

        // If there is a default 'string' value like "yesterday", format it as a date
        if(!value && this.def.display_default) {
            value = app.date.parseDisplayDefault(this.def.display_default);
        }
        jsDate = this.app.date.parse(value);
        usersDateFormatPreference = app.user.get('datepref');
        return this.app.date.format(jsDate, usersDateFormatPreference);
    }

})
