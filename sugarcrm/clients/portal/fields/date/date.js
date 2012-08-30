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
        // Rely on the user's browser to do timezone conversions for us
        var jsDate = new Date(value);
        var output = this.app.date.format(jsDate, this.app.user.get('datepref'));
        return output
    },

})