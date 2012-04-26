({
    fieldTag: "select",
    render: function() {
        var result = this.app.view.Field.prototype.render.call(this);
        $(this.fieldTag + "[name=" + this.name + "]").chosen();
        return result;
    }

})