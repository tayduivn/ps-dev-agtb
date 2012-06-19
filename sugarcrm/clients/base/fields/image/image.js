({
    fileUrl : "",
    _render: function() {
        app.view.Field.prototype._render.call(this);
        this.model.fileField = this.name;
        this.fileURL = (this.value) ? app.api.buildAttachmentURL(this.model) : "";
        app.view.Field.prototype._render.call(this);
        return this;
    },
    bindDataChange:function() {
        //Keep empty because you cannot set a value of a type `file` input
    }
})