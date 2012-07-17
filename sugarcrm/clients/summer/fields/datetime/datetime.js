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
        return value;
    },
    format:function(value) {
        return value;
    }
})