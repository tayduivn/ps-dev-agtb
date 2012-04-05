({
    fieldType: "select",
    render: function() {
        var result = this.app.sugarField.base.prototype.render.call(this);
        $(this.fieldType + "[name=" + this.name + "]").chosen();
        $('select').chosen();
        return result;
    }

})