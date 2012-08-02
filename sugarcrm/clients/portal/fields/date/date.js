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
        // Rely on the user's browser to do timezone conversions for us
        var jsDate = new Date(value);
        var myUser = this.app.user.getUser();
        var output = this.app.utils.date.format(jsDate, myUser.get('datepref'));
        return output
    },

})