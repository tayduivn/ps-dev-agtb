({
    'events': {
        'click .icon-favorite': 'toggle'
    },

    /**
     * {@inheritdoc}
     *
     * The favorite is always a non editable field.
     */
    initialize: function(options) {
        app.view.Field.prototype.initialize.call(this, options);
        this.def.noedit = true;
    },

    /**
     * Check first if the module has favoritesEnabled before rendering it.
     *
     * @private
     */
    _render: function() {

        if (!app.metadata.getModule(this.model.module).favoritesEnabled) {
            app.logger.error("Trying to use favorite field on a module that doesn't support it: '" + this.model.module + "'.");
            return null;
        }
        return app.view.Field.prototype._render.call(this);
    },

    /**
     * Function called for each click on the star icon (normally acts as toggle
     * function).
     *
     * If the star is checked, copy all the source fields to target ones
     * based on the mapping definition of this field. Otherwise, restore all the
     * values of the modified fields by this copy widget.
     *
     * @param {Event} evt
     *   The event (expecting click event) that triggered the checkbox status
     *   change.
     */
    toggle: function(evt) {

        var star = $(evt.currentTarget);

        if (this.model.favorite(!this.model.isFavorite()) === false) {
            app.logger.error("Unable to set '" + this.model.module + "' record '" + this.model.id + "' as favorite");
            return;
        }
        if (this.model.isFavorite()) {
            star.addClass('active');
        }
        else {
            star.removeClass('active');
        }
    },

    /**
     * {@inheritdoc}
     *
     * @return {Boolean}
     */
    format: function() {
        return this.model.isFavorite();
    }
})
