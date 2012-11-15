({
    events: {
        "click .delete" : "delete"
    },
    fileUrl : "",
    _render: function() {
        this.model.fileField = this.name;
        app.view.Field.prototype._render.call(this);
        return this;
    },
    format: function(value){
        if (value) {
            value = this.buildUrl() + "&" + value;
        }
        return value;
    },
    buildUrl: function(options) {
        return app.api.buildFileURL({
                    module: this.module,
                    id: this.model.id,
                    field: this.name
                }, options);
    },
    delete: function() {
        var self = this;
        app.api.call('delete', self.buildUrl({htmlJsonFormat: false}), {}, {
                success: function(data) {
                    self.model.set(self.name, null);
                    self._render();
                },
                error: function(data) {
                    // refresh token if it has expired
                    app.error.handleHttpError(data, {});
                }}
        );
    },
    bindDataChange: function() {
        if (this.view.name != "edit" && this.view.fallbackFieldTemplate != "edit") {
            //Keep empty because you cannot set a value of a type `file` input
            app.view.Field.prototype.bindDataChange.call(this);
        }
    }
})