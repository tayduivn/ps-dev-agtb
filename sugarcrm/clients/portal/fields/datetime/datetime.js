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
        var output = this.app.utils.date.parse(value,myUser.get('datepref')+' '+myUser.get('timepref')).toISOString();

        return output;
    },
    format:function(value) {
        var jsDate = new Date(value);
        var myUser = this.app.user.getUser();
        var output = this.app.utils.date.format(jsDate,myUser.get('datepref')+' '+myUser.get('timepref'));
        return output;
    }
})