({
    fields: null,
    focusIndex: 0,
    length: 0,

    getPlaceholder: function() {
        var ret = "",
            self = this;

        if (!this.fields) {
            this.fields = [];
            _.each(this.def.fields, function(fieldDef) {
                var field = app.view.createField({
                    def: fieldDef,
                    view: self.view,
                    model: self.model
                });
                self.fields.push(field);
                field.parent = self;
                ret += field.getPlaceholder();
            });
        }

        this.length = this.fields.length;

        return new Handlebars.SafeString(app.view.Field.prototype.getPlaceholder.call(this) + ret);
    },

    render: function() {
        if (this.options.viewName == "detail") {
            this.focusIndex = 0;
        }

        _.each(this.fields, function(field) {
            field.render();
        }, this);
    },

    /**
     * Toggles focus between "internal" fields
     * TODO: FIX THE LOGIC, unintuitive
     * @param last {Boolean} Soft focus to check if last element or no
     * @return {*}
     */
    focus: function(last) {
        // If this fieldset has no fields, return.
        if (!this.length) {
            return false;
        }

        // If last flag set then only do check if next focus will still be within this field.
        if (last) {
            return !!(this.focusIndex == this.length);
        }

        if (this.focusIndex < this.length) {
            this.focusIndex++; // focusIndex is offset 1 from the actual index

            // Assuming input field
            this.fields[this.focusIndex - 1].$el.find("input").focus().val(this.fields[this.focusIndex - 1].$el.find("input").val());
            return this.fields[this.focusIndex - 1];
        } else {
            this.unfocus();
            return false
        }
    },

    unfocus: function() {
        this.focusIndex = 0;
    }
})