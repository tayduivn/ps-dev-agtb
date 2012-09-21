({
    initialize: function(options) {
        app.view.Layout.prototype.initialize.call(this, options);

        this.context.on('quickcreate:cancel', this.cancel, this);
        this.context.on('quickcreate:close', this.closeModal, this);
        this.context.on('quickcreate:save', this.save, this);
        this.context.on('quickcreate:saveAndCreate', this.saveAndCreate, this);
        this.context.on('quickcreate:saveAndView', this.saveAndView, this);
    },

    /**
     * Save and close quickcreate modal window
     */
    save: function() {
        var self = this;
        this.initiateSave(function() {
            self.closeModal()
        });
    },

    /**
     * Close quickcreate modal window
     */
    cancel: function() {
        this.closeModal();
    },

    /**
     * Save and reset the form
     */
    saveAndCreate: function() {
        var self = this;
        this.initiateSave(function() {
            self.context.trigger('quickcreate:clear');
        });
    },

    /**
     * Save and view the record
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
        this.context.trigger('quickcreate:list:toggle', false);
        async.waterfall([
            _.bind(this.validateModelWaterfall, this),
            _.bind(this.dupeCheckWaterfall, this),
            _.bind(this.createRecordWaterfall, this)
        ], function(error) {
            if (error) {
                console.log("Saving failed.");
                //TODO: handle error
            } else {
                callback();
            }
        });
    },

    /**
     * Validate model
     * @param callback
     */
    validateModelWaterfall: function(callback) {
        var result = function(isValid) {
                if (isValid) {
                    callback(false);
                } else {
                    callback(true);
                }
            };

        this.context.trigger('quickcreate:validateModel', result);
    },

    /**
     * Check for possible duplicate records
     * @param callback
     */
    dupeCheckWaterfall: function(callback) {
        var self = this,
            success = function(collection) {
                var keys = self.getFieldValuesForUserKeys(self.getUserKeys());
                if (collection.models.length > 0) {
                    self.handleDuplicateFound(collection);
                    callback(true);
                } else {
                    self.handleDuplicateNotFound();
                    callback(false);
                }
            },
            error = function() {
                console.log('dupe check failed on server').
                callback(true);
            };

        if (this.skipDupCheck()) {
            callback(false);
        } else {
            this.checkForDuplicate(success, error);
        }
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
                callback(true);
            };

        this.saveModel(success,error);
    },

    /**
     * Check the server to see if there are possible duplicate records.
     * @param success
     * @param error
     */
    checkForDuplicate: function(success, error) {
        var options = {
            limit: this.limit || null,
            params: {
                q: 'w'
            },
            fields: this.collection.fields || {},
            success: success,
            error: error
        };

        this.collection.fetch(options);
    },

    /**
     * Duplicate found: display duplicates and change buttons
     */
    handleDuplicateFound: function(collection) {
        this.context.trigger('quickcreate:list:toggle', true);
        // self.showDuplicateAlertMessage();
        this.skipDupCheck(true);
        this.context.trigger('quickcreate:actions:setButtonAsIgnoreDuplicate');
        this.context.trigger('quickcreate:alert', {
            level: 'warning',
            messages: this.getAlertMessage(collection.models.length),
            autoClose: false});
    },

    /**
     * No duplicates found
     */
    handleDuplicateNotFound: function() {
        this.skipDupCheck(false);
        this.context.trigger('quickcreate:actions:setButtonAsSave');
    },

    /**
     * Create a new record
     * @param success
     * @param error
     */
    saveModel: function(success, error) {
        this.model.save(null, {
            fieldsToValidate: this.getFields(this.module),
            success: success,
            error: error
        });
    },

    /**
     * Check to see if we should skip duplicate check. If param specified, set duplicate check
     * to either true or false.
     * @param skip (boolean)
     * @return {*}
     */
    skipDupCheck: function(skip) {
        var skipDupCheck,
            saveButton = this.$('[name=save_button]');

        if (_.isUndefined(skip)) {
            skipDupCheck = saveButton.data('skipDupCheck');
            if (_.isUndefined(skipDupCheck)) {
                skipDupCheck = false;
            }
            return skipDupCheck;
        } else {
            if (skip) {
                saveButton.data('skipDupCheck', true);
            } else {
                saveButton.data('skipDupCheck', false);
            }
        }
    },

    getFieldValuesForUserKeys: function(keys) {
        var data = [],
            self = this;

        _.each(keys, function (key) {
            data.push(self.model.get(self.formatFieldName(key)));
        });

        return data;
    },

    getUserKeys:function () {
        var keys = [],
            fields = this.getFields(this.module);

        _.each(fields, function (field) {
            if (field.duplicate_merge && field.duplicate_merge === 'default') {
                keys.push(field.name);
            }
        });
        
        return keys;
    },

    getAlertMessage: function(dupCount) {
        return "<span class=\"alert-message\">" +
            "<strong>" + dupCount + " Duplicate Records.</strong>  You can " +
            "<a>ignore duplicates and save</a> or select to edit one of the duplicates." +
            "</span>";
    },
    /**
     * Close the modal window
     */
    closeModal: function() {
        this.context.parent.trigger('modal:close');
    }
})
