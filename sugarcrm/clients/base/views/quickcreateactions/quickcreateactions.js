({
    events: {
        'click [name=save_button]': 'save',
        'click [name=cancel_button]': 'cancel',
        'click [name=save_create_button]': 'saveAndCreate',
        'click [name=save_view_button]': 'saveAndView'
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        this.context.on('quickcreate:actions:setButtonAsIgnoreDuplicate', this.setButtonAsIgnoreDuplicate, this);
        this.context.on('quickcreate:actions:setButtonAsSave', this.setButtonAsSave, this);
    },

    /**
     * Handle click on save button
     */
    save: function() {
        this.context.trigger('quickcreate:save');
    },

    /**
     * Handle click on cancel button
     */
    cancel: function() {
        this.context.trigger('quickcreate:cancel');
    },

    /**
     * Handle click on save and create another button
     */
    saveAndCreate: function() {
        this.context.trigger('quickcreate:saveAndCreate');
    },

    /**
     * Handle click on save and view button
     */
    saveAndView: function() {
        this.context.trigger('quickcreate:saveAndView');
    },

    /**
     * Change button to Ignore Duplicate and Save
     */
    setButtonAsIgnoreDuplicate: function() {
        this.$('[name=save_button]')
            .text(this.app.lang.get('LBL_IGNORE_DUPLICATE_AND_SAVE', this.module));
    },

    /**
     * Change button to Save
     */
    setButtonAsSave: function() {
        this.$('[name=save_button]')
            .text(this.app.lang.get('LBL_SAVE_BUTTON_LABEL', this.module));
    }
})
