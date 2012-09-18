({
    events: {
        'click [name=save_button]': 'save',
        'click [name=cancel_button]': 'cancel',
        'click [name=save_create_button]': 'saveAndCreate'
    },

    /**
     * Handle click on save button
     */
    save: function() {
        this.context.trigger('quickcreate:save');
        this.context.parent.trigger('modal:close');
    },

    /**
     * Handle click on cancel button
     */
    cancel: function() {
        this.context.parent.trigger('modal:close');
    },

    /**
     * Handle click on save and create button
     */
    saveAndCreate: function() {
        var self = this;
        this.context.trigger('quickcreate:save', function() {
            self.context.trigger('quickcreate:clear');
        });
    }
})
