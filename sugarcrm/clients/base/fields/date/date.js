({
    // date
    _render: function(value) {
        var self = this;
        // Although the server serves up iso date string with Z and all .. for date types going back it wants this
        self.serverDateFormat = 'Y-m-d';
        app.view.Field.prototype._render.call(this);//call proto render
        var viewName = self.view.meta && self.view.meta.type ? self.view.meta.type : self.view.name;
        $(function() {
            if(self.options.def.view === 'edit' || self.options.viewName === 'edit' ||
                viewName === 'edit') {
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

        // In case ISO 8601 get it back to js native date which date.format understands
        jsDate = new Date(value);
        return app.date.format(jsDate, this.serverDateFormat);
    },

    format:function(value) {
        var jsDate, parts,
            usersDateFormatPreference = app.user.get('datepref');

        // If there is a default 'string' value like "yesterday", format it as a date
        if(!value && this.def.display_default) {
            value = app.date.parseDisplayDefault(this.def.display_default);
            parts = value.match(/(\d+)/g);
            jsDate = new Date(parts[0], parts[1]-1, parts[2]); //months are 0-based
            this.model.set(this.name, app.date.format(jsDate, this.serverDateFormat));
        } else {
            // Bug 56249 .. Date constructor doesn't reliably handle yyyy-mm-dd
            // e.g. new Date("2011-10-10" ) // in my version of chrome browser returns
            // Sun Oct 09 2011 17:00:00 GMT-0700 (PDT)
            parts = value.match(/(\d+)/g);
            jsDate = new Date(parts[0], parts[1]-1, parts[2]); //months are 0-based
            value  = app.date.format(jsDate, usersDateFormatPreference);
        }

        jsDate = app.date.parse(value);
        return app.date.format(jsDate, usersDateFormatPreference);
    }

})
