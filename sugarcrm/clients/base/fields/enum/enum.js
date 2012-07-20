({
    fieldTag: "select",
    _render: function() {
        this.app.view.Field.prototype._render.call(this);
        this.$(this.fieldTag).chosen({disable_search_threshold: 5});
        return this;
    }

})