({
    _render: function(value) {
        app.view.Field.prototype._render.call(this);//call proto render
        $(function() {
            $(".datepicker").datepicker({
                showOn: "button",
                buttonImage: "../lib/jquery-ui/css/smoothness/images/calendar.gif",
                buttonImageOnly: true,
                dateFormat: "yy-mm-dd"
            });
        });
    },
    unformat:function(value) {
        var myUser = this.app.user.getUser();
        var jsDate = this.app.utils.date.parse(value,myUser.get('datepref')+' '+myUser.get('timepref'));
        var output = this.app.utils.date.format(value,'Y-m-dTH:i:s');

        return output;
    },
    format:function(value) {
        // The API has gone to the trouble of getting the date in the user's timezone, so we should
        // display it using the timezone that the API sent it to us.
        // This should split the date/time into date, time and offset, if we don't pass the offset in the Date class assumes it to be local time.
        var splitValue = /^(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2}:\d{2})(.*[+-].*)$/.exec(value);
        if ( typeof(splitValue) == 'undefined' || typeof(splitValue[1]) == 'undefined' ) {
            // Could not figure this string out.
            return '';
        }
        var jsDate = new Date(splitValue[1]+' '+splitValue[2]);
        var myUser = this.app.user.getUser();
        var output = this.app.utils.date.format(jsDate,myUser.get('datepref')+' '+myUser.get('timepref'));
        return output;
    }
})