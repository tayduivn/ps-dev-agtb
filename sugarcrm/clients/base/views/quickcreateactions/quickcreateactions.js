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
        this.initiateSave(this.closeModal);
    },

    /**
     * Handle click on cancel button
     */
    cancel: function() {
        this.closeModal();
    },

    /**
     * Handle click on save and create another button
     */
    saveAndCreate: function() {
        var self = this;
        this.initiateSave(function() {
            self.context.trigger('quickcreate:clear');
        });
    },

    /**
     * Handle click on save and view button
     */
    saveAndView: function() {
        var self = this;
        this.initiateSave(function() {
            self.closeModal();
            self.app.navigate(self.context, self.model, 'detail');
        });
    },

    /**
     * Check for possible duplicates before creating a new record
     * @param callback
     */
    initiateSave: function(callback) {
        var self = this;
        async.waterfall([
            _.bind(this.dupeCheckWaterfall, this),
            _.bind(this.createRecordWaterfall, this)
        ], function(error) {
            if (error) {
                //TODO: handle error
            } else {
                callback.apply(self);
            }
        });
    },

    /**
     * Check for possible duplicate records
     * @param callback
     */
    dupeCheckWaterfall: function(callback) {
        var success = function() {
                callback(false);
            },
            error = function() {
                callback(true)
            };

        this.context.trigger('quickcreate:dupecheck', success, error);
    },

    /**
     * Create new record
     * @param callback
     */
    createRecordWaterfall: function(callback) {
        var success = function() {
                callback(false);
            },
            error = function() {
                callback(true)
            };

        this.context.trigger('quickcreate:save', success, error);
    },

    /**
     * Close the modal window
     */
    closeModal: function() {
        this.context.parent.trigger('modal:close');
    }
})
