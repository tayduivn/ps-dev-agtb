({
    fields: null,

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
    }
})
