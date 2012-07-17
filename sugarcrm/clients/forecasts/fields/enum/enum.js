({
    fieldTag: "select",
    _render: function() {
        this.app.view.Field.prototype._render.call(this);
        var field = this.$(this.fieldTag).chosen();
        if (this.view.name == "forecastsWorksheet") {
            field.change({field: this}, this._save);
        }
        return this;
    },

    _save: function(event, input) {
        var field = event.data.field;

        console.log("save");
    }

})