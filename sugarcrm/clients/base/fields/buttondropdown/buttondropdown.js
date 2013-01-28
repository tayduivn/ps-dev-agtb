({
    events: {
        'click a[name]': 'handleActions'
    },

    /**
     * Set all fields under button dropdown as buttons if not specified
     * @param options
     */
    initialize: function(options) {
        app.view.Field.prototype.initialize.call(this, options);
        _.each(this.def.buttons, function(definition) {
            definition.type = _.isUndefined(definition.type) ? 'button' : definition.type;
        });
    },

    _render: function() {
        if (app.acl.hasAccessToModel(this.def.value, this.model, this)) {
            app.view.Field.prototype._render.call(this);
            this.renderButtons();
        }
    },

    /**
     * Render all buttons in the dropdown.
     */
    renderButtons: function() {
        var primaryPlaceholder = this.$('.primary-button'),
            dropdownOptions = this.$('.dropdown-menu');

        _.each(this.def.buttons, function(definition) {
            var button = this.createButton(definition);
            if (definition.primary === true) {
                primaryPlaceholder.append(button.el);
                button.render();
                button.$('a.btn').addClass('btn-primary');
            } else {
                dropdownOptions.append(button.el);
                button.render();
                button.$('a.btn')
                    .removeClass('btn')
                    .wrap('<li></li>');
            }
        }, this);
    },

    /**
     * Create button field.
     * @param definition
     * @return {object} Field
     */
    createButton: function(definition) {
        var button = app.view.createField({
            def: definition,
            view: this.view,
            viewName: this.options.viewName,
            model: this.model
        });

        _.extend(button, {
            getFieldElement: function() {
                return this.$el;
            }
        })

        this.view.fields[button.sfId] = button;
        return button;
    },

    /**
     * Trigger button event when any of the buttons are clicked on.
     * @param event
     */
    handleActions: function(event) {
        event.preventDefault();
        this.context.trigger('button:' + $(event.currentTarget).prop('name') + ':click');
    }
})