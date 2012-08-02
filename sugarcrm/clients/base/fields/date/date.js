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
        var myUser = this.app.user.getUser();
        var jsDate = this.app.utils.date.parse(value,myUser.get('datepref'));
        return this.app.utils.date.format(jsDate, 'Y-m-d');
    },

    format:function(value) {
        var splitValue = /^(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2}:\d{2})(.*[+-].*)$/.exec(value);
        if ( typeof(splitValue) == 'undefined' || typeof(splitValue[1]) == 'undefined' ) {
            // Could not figure this string out.
            return '';
        }
        var jsDate = new Date(splitValue[1]+' '+splitValue[2]);
        var myUser = this.app.user.getUser();
        var output = this.app.utils.date.format(jsDate, myUser.get('datepref'));
        return output
    },

})