({
    fieldType: "select",
    render: function() {
        var result = this.app.view.Field.prototype.render.call(this);
        $(this.fieldType + "[name=" + this.name + "]").chosen();
        return result;
    }

})