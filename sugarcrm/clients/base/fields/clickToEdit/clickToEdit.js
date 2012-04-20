({
    render:function() {
        var result = this.app.view.Field.prototype.render.call(this);
        $('.' + this.class).editable(function(value) {
                console.log('value:' + value);
                return value;
            });
        return result;
    }

})
