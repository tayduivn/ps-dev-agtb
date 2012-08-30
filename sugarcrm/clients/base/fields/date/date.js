({
    _render:function(value) {
        this.app.view.Field.prototype._render.call(this);//call proto render
        $(function() {
            $(".datepicker").datepicker({
                showOn: "button",
                buttonImage: "../lib/jquery-ui/css/smoothness/images/calendar.gif",
                buttonImageOnly: true
            });
        });
    },

    unformat:function(value) {
        var jsDate = this.app.date.parse(value,this.app.user.get('datepref'));
        return this.app.date.format(jsDate, 'Y-m-d');
    },

    format:function(value) {
        var splitValue = /^(\d{4}-\d{2}-\d{2})/.exec(value);
        if ( typeof(splitValue) == 'undefined' || typeof(splitValue[1]) == 'undefined' ) {
            // Could not figure this string out.
            return '';
        }
        var jsDate = new Date(splitValue[1]);
        var output = this.app.date.format(jsDate, this.app.user.get('datepref'));
        return output
    },

})