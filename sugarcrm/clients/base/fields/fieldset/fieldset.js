({
    fields: null,
    focusIndex: 0,
    length: 0,

    /**
     * {@inheritdoc}
     */
    getPlaceholder: function() {

        var placeholder = app.view.Field.prototype.getPlaceholder.call(this);
        var $container = $(placeholder.toString());

        var self = this;

        if (!this.fields) {
            this.fields = [];
            _.each(this.def.fields, function(fieldDef) {
                var field = app.view.createField({
                    def: fieldDef,
                    view: self.view,
                    viewName: self.options.viewName,
                    model: self.model
                });
                self.fields.push(field);
                field.parent = self;
                $container.append(field.getPlaceholder().toString());
            });
        }

        this.length = this.fields.length;

        return new Handlebars.SafeString($container.get(0).outerHTML);
    },

    /**
     * {@inheritdoc}
     *
     * We only render the child fields for this fieldset and for now there is no
     * support for templates on fieldset widgets.
     */
    _render: function() {
        if (this.options.viewName == "detail") {
            this.focusIndex = 0;
        }

        _.each(this.fields, function(field) {
            field.render();
        }, this);

        // Adds classes to the component based on the metadata.
        if(this.def && this.def.css_class) {
            this.getFieldElement().addClass(this.def.css_class);
        }

        return this;
    },

    /**
     * {@inheritdoc}
     *
     * We need this empty so it won't affect the nested fields that have the
     * same `fieldTag` of this fieldset due the usage of `find()` method.
     */
    bindDomChange: function() {
    },

    /**
     * {@inheritdoc}
     *
     * Keep empty because you cannot set a value of a type `fieldset`.
     */
    bindDataChange: function() {
    },

    /**
     * {@inheritdoc}
     *
     * We need this empty so it won't affect the nested fields that have the
     * same `fieldTag` of this fieldset due the usage of `find()` method.
     */
    unbindDom: function() {
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
