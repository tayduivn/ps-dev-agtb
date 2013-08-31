({
    /**
     * {@inheritDoc}
     * Convert the raw field type name
     * into the label of the field of the parent model.
     */
    format: function(value) {
        if (this.context && this.context.parent) {
            var parentModel = this.context.parent.get('model'),
                field = parentModel.fields[value];
            if (field) {
                value = app.lang.get(field.label || field.vname, parentModel.module);
            }
        }
        return value;
    }
})
