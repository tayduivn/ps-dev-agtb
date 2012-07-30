({
    fieldTag: "select",
    _render: function() {
        this.app.view.Field.prototype._render.call(this);
        this.$(this.fieldTag).chosen();
        this.$(".chzn-container").addClass("tleft");
        return this;
    }

})