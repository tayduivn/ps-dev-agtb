({
    // datetime
    _render: function(value) {
        var self = this;
        app.view.Field.prototype._render.call(this);//call proto render
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
        jsDate = app.date.parse(value, usersDateFormatPreference);
        return jsDate.toISOString();
    },

    format:function(value) {
        var jsDate, 
            usersDateFormatPreference = app.user.get('datepref');

        // If there is a default 'string' value like "yesterday", format it as a date
        if(!value && this.def.display_default) {
            value = app.date.parseDisplayDefault(this.def.display_default);
            this.model.set(this.name, new Date(value).toISOString()); 
        } else {
            // In case ISO 8601 get it back to js native date which date.format understands
            jsDate = new Date(value);
        }
        jsDate = app.date.parse(value);
        return app.date.format(jsDate, usersDateFormatPreference);
    }

})
