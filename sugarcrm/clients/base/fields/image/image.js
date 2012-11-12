({
    extendsFrom: 'FileField',
    events: {
        "mouseenter img" : "showButton",
        "mouseenter a" : "showButton",
        "mouseleave a" : "hideButton",
        "mouseleave img" : "hideButton",
        "click .delete" : "delete",
        "focus input[type=file]": "onUploadFocus",
        "blur input[type=file]": "onUploadBlur",
        "change input[type=file]": "onUploadChange"
    },
    fileUrl : "",
    _render: function() {
        app.view.Field.prototype._render.call(this);
        this.model.fileField = this.name;
        this.fileURL = (this.value) ? this.buildUrl() + "&" + this.value : "";
        app.view.Field.prototype._render.call(this);
        this.resizeInput();
        return this;
    },
    buildUrl: function(options) {
        return app.api.buildFileURL({
                    module: this.module,
                    id: this.model.id,
                    field: this.name
                }, options);
    },
    showButton: function() {
        this.$(".delete").removeClass("hide");
    },
    hideButton: function() {
        this.$(".delete").addClass("hide");
    },
    delete: function() {
        var self = this;
        App.api.call('delete', self.buildUrl({htmlJsonFormat: false}), {}, {
                success: function(data) {
                    self.model.set(self.name, null);
                    self._render();
                },
                error: function(data) {
                    // refresh token if it has expired
                    app.error.handleHttpError(data, {});
                }}
        );
    }
})