({
    /**
     * Creates a new field using field name viewdef defined by vardef metadata. Field is hidden by default.
     *
     * Viewdef Metadata Example:
     * {
     *   name: 'field_name',
     *   type: 'secondary',
     *   primary: {
     *     field: 'primary_field_name'
     *     value: 'dependent_value' OR ['dependent_value1','dependent_value2','dependent_value3']
     *   }
     * }
     *
     * Currently, secondary field type only supports 'enum' primary field.
     *
     * @private
     */
    _render: function() {
        this.$el.hide();

        if (this.app.metadata.data.modules[this.module].fields[this.def.primary.field].type.toLowerCase() !== 'enum') {
            throw Error('Secondary fields only work with enum primary field type.');
        }

        var field = this.app.view.createField({
            def: this.app.metadata.data.modules[this.module].fields[this.def.name],
            view: this.view,
            model: this.model
        });
        field.setElement(this.$el);
        field.render();
    },

    /**
     * The field is only shown when the primary field has the value defined in the viewdef metadata.
     */
    bindDataChange: function() {
        var self = this;
        this.model.on('change:'+this.def.primary.field, function(model) {
            var primaryFieldValue = model.get(self.def.primary.field),
                matchesWithPrimary = false;

            if (_.isArray(self.def.primary.value)) {
                _.each(self.def.primary.value, function(value) {
                    if (primaryFieldValue == value) {
                        matchesWithPrimary = true;
                    }
                });
            } else if (primaryFieldValue == self.def.primary.value) {
                matchesWithPrimary = true;
            }

            if (matchesWithPrimary) {
                self.$el.show();
            } else {
                self.$el.hide();
                model.set(self.name, '');
            }
        });
    }
})