({
    fieldTag: "select",
    render: function() {
        this.app.view.Field.prototype.render.call(this);
        this.$(this.fieldTag).chosen();
        return this;
    }

})