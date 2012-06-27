({
    fileUrl : "",
    _render: function() {
        app.view.Field.prototype._render.call(this);
        this.fileURL = (this.value) ? app.api.buildFileURL({
            module: this.module,
            id: this.model.id,
            field: this.name
        }) : "";
        app.view.Field.prototype._render.call(this);
        return this;
    },
    bindDataChange:function() {
        //Keep empty because you cannot set a value of a type `file` input
    }
})