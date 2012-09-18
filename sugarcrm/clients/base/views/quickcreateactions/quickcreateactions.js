({
    events: {
        'click [name=save_button]': 'save',
        'click [name=cancel_button]': 'cancel',
        'click [name=save_create_button]': 'saveAndCreate',
        'click [name=save_view_button]': 'saveAndView'
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
     * Handle click on save and create another button
     */
    saveAndCreate: function() {
        var self = this;
        this.context.trigger('quickcreate:save', function() {
            self.context.trigger('quickcreate:clear');
        });
    },

    /**
     * Handle click on save and view button
     */
    saveAndView: function() {
        var self = this;
        this.context.trigger('quickcreate:save', function() {
            self.context.parent.trigger('modal:close');
            self.app.navigate(self.context, self.model, 'detail');
        });
    }
})
