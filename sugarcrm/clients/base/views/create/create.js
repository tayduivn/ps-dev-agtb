({
    extendsFrom: 'RecordView',
    editAllMode: false,

    SAVEACTIONS: {
        SAVE_AND_CREATE: 'saveAndCreate',
        SAVE_AND_VIEW: 'saveAndView'
    },

    enableDuplicateCheck: true,

    STATE: {
        CREATE: 'create',
        SAVE: 'save',
        EDIT: 'edit',
        DUPLICATE: 'duplicate'
    },

    saveButtonName: 'save_button',

    /**
     * Initialize the view and prepare the model with default button metadata
     * for the current layout.
     */
    initialize: function(options) {
        this.events = _.extend({}, this.events, {
            'click a[name=save_button]': 'save',
            'click a[name=cancel_button]': 'cancel',
            'click a[name=save_create_button]': 'saveAndCreate',
            'click a[name=save_view_button]': 'saveAndView',
            'click a[name=restore_button]': 'restoreModel'
        });

        app.view.views.RecordView.prototype.initialize.call(this, options);

        this.model.off("change", null, this);

        //duplicate a record
        if(app.cache.has("duplicate"+this.module)) {
            _.each(app.cache.get("duplicate"+this.module), function(value, key) {
                if(key != 'id') this.model.set(key, value);
            }, this);  
            app.cache.cut("duplicate"+this.module);
        }  
        
        //keep track of what post-save action was chosen in case user chooses to ignore dupes
        this.context.lastSaveAction = null;

        //listen for the edit button
        this.context.on('quickcreate:edit', this.editExisting, this);

        //listen for the save link click on the alert
        this.context.on('create:alert:save', this.save, this);

        //extend the record view definition
        this.meta = _.extend({}, app.metadata.getView(this.module, 'record'), this.meta);
    },

    delegateButtonEvents: function() {
        //override record view's button delegation
    },

    render: function() {
        app.view.views.RecordView.prototype.render.call(this);
        this.setButtonStates(this.STATE.CREATE);

        this.showDuplicates();

        if (this.createMode) {
            this.setTitle(app.lang.get('LBL_CREATE_BUTTON_LABEL', this.module) + ' ' + this.moduleSingular);
        }
    },

    /**
     * Determine appropriate save action and execute it
     * Default to saveAndClose
     */
    save: function() {
        if (!this.$('[name=save_button]').hasClass('disabled')) {
            switch(this.context.lastSaveAction) {
                case this.SAVEACTIONS.SAVE_AND_CREATE:
                    this.saveAndCreate();
                    break;
                case this.SAVEACTIONS.SAVE_AND_VIEW:
                    this.saveAndView();
                    break;
                default:
                    this.saveAndClose();
            }
        }
    },

    /**
     * Save and close quickcreate modal window
     */
    saveAndClose: function() {
        this.initiateSave(_.bind(function() {
            this.cancel();
            this.alerts.showSuccess();
        }, this));
    },

    /**
     * Handle click on the cancel link
     */
    cancel: function() {
        this.context.trigger("drawer:hide");
        if (this.context.parent)
            this.context.parent.trigger("drawer:hide");
    },

    /**
     * Enable button to save if the model is valid.
     */
    bindDataChange: function() {
        app.view.views.RecordView.prototype.bindDataChange.call(this);
        if(this.model) {
            this.model.on("change", function() {
                if (this.model.isValid(undefined, true)) {
                    if (this.currentState === this.STATE.CREATE) {
                        this.currentState = this.STATE.SAVE;
                        this.setButtonStates(this.currentState);
                    }
                    this.enableSave();
                } else {
                    if (this.currentState === this.STATE.SAVE) {
                        this.currentState = this.STATE.CREATE;
                        this.setButtonStates(this.currentState);
                    }
                    this.disableSave();
                }
            }, this);
        }
    },

    /**
     * Handle click on save and create another link
     */
    saveAndCreate: function() {
        this.context.lastSaveAction = this.SAVEACTIONS.SAVE_AND_CREATE;
        this.initiateSave(_.bind(function() {
            this.clear();
            this.resetDuplicateState();
            this.alerts.showSuccess();
        }, this));
    },

    /**
     * Handle click on save and view link
     */
    saveAndView: function() {
        this.context.lastSaveAction = this.SAVEACTIONS.SAVE_AND_VIEW;
        this.initiateSave(_.bind(function() {
            app.navigate(this.context, this.model);
            this.alerts.showSuccess();
        }, this));
    },

    /**
     * Handle click on restore to original link
     */
    restoreModel: function() {
        this.model.clear();
        this.createMode = true;
        this.setButtonStates(this.STATE.CREATE);

        if (this._origAttributes) {
            this.model.set(this._origAttributes);
        }
    },

    /**
     * Check for possible duplicates before creating a new record
     * @param callback
     */
    initiateSave: function(callback) {
        async.waterfall([
            _.bind(this.validateModelWaterfall, this),
            _.bind(this.dupeCheckWaterfall, this),
            _.bind(this.createRecordWaterfall, this)
        ], _.bind(function(error) {
            if (error) {
                console.log("Saving failed.");
                //TODO: handle error
            } else {
                this.context.lastSaveAction = null;
                callback();
            }
        }, this));
    },

    /**
     * Check to see if all fields are valid
     * @param callback
     */
    validateModelWaterfall: function(callback) {
        if (this.model.isValid(this.getFields(this.module))) {
            callback(false);
        } else {
            callback(true);
        }
    },

    /**
     * Check for possible duplicate records
     * @param callback
     */
    dupeCheckWaterfall: function(callback) {
        var success = _.bind(function(collection) {
                if (collection.models.length > 0) {
                    this.handleDuplicateFound(collection);
                    callback(true);
                } else {
                    this.resetDuplicateState();
                    callback(false);
                }
            }, this),
            error = _.bind(function() {
                this.showServerError();
                callback(true);
            }, this);

        if (this.skipDupCheck() || !this.enableDuplicateCheck) {
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
            error = _.bind(function() {
                this.alerts.showServerError();
                callback(true);
            }, this);

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
            fields: this.getFieldNames() || {},
            success: success,
            error: error
        };

        this.collection.fetch(options);
    },

    /**
     * Duplicate found: display duplicates and change buttons
     * @param {object} Collection of sugar beans
     * @param {array} List of user key fields
     */
    handleDuplicateFound: function(collection) {
        this.setButtonStates(this.STATE.DUPLICATE);
        this.context.trigger('quickcreate:list:toggle', true);
        this.skipDupCheck(true);

        this.alerts.showDuplicateFound(collection.models.length, _.bind(function() {
            this.context.trigger('create:alert:save');
        }, this));
    },

    /**
     * Clear out all things related to duplicate checks
     */
    resetDuplicateState: function() {
        this.setButtonStates(this.STATE.CREATE);
        this.context.trigger('quickcreate:list:close');
        this.skipDupCheck(false);
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
            saveButton = this.buttons[this.saveButtonName].getFieldElement();

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

    /**
     * Clears out field values
     */
    clear: function() {
        this.model.clear();
        this.model.set(this.model._defaults);
    },

    /**
     * Make the specified record as the data to be edited, and merge the existing data.
     * @param model
     */
    editExisting: function(model) {
        var origAttributes = this.saveFormData();

        this.model.clear();
        this.model.set(this.extendModel(model, origAttributes));

        this.createMode = false;
        this.render();
        this.toggleEdit(true);

        this.hideDuplicates();
        this.setButtonStates(this.STATE.EDIT);
    },

    /**
     * Merge the selected record with the data entered in the form
     * @param newModel
     * @param origAttributes
     * @return {*}
     */
    extendModel: function(newModel, origAttributes) {
        var modelAttributes = newModel.previousAttributes();

        _.each(modelAttributes, function(value, key, list) {
            if ( _.isUndefined(value)|| _.isEmpty(value)) {
                delete modelAttributes[key];
            }
        });

        return _.extend({}, origAttributes, modelAttributes);
    },

    /**
     * Save the data entered in the form
     * @return {*}
     */
    saveFormData: function() {
        this._origAttributes = this.model.previousAttributes();
        return this._origAttributes;
    },

    /**
     * Show list of duplicates
     */
    showDuplicates: function() {
        var view = app.view.createView({
            context: this.context,
            name: 'quickcreate-list',
            module: this.module,
            layout: this.layout
        });

        this.$('.headerpane').after(view.$el);
        view.render();
    },

    /**
     * Clear out duplicate list
     */
    hideDuplicates: function() {
        this.collection.reset();
    },

    setButtonStates: function(state) {
        app.view.views.RecordView.prototype.setButtonStates.call(this, state);
        if(this.buttons[this.saveButtonName]) {

            var $saveButtonEl = this.buttons[this.saveButtonName];
            switch (state) {
                case this.STATE.CREATE:
                    $saveButtonEl.label = app.lang.get('LBL_SAVE_BUTTON_LABEL', this.module);
                    this.disableSave();
                    break;

                case this.STATE.SAVE:
                case this.STATE.EDIT:
                    $saveButtonEl.label = app.lang.get('LBL_SAVE_BUTTON_LABEL', this.module);
                    this.enableSave();
                    break;

                case this.STATE.DUPLICATE:
                    $saveButtonEl.label = app.lang.get('LBL_IGNORE_DUPLICATE_AND_SAVE', this.module);
                    this.enableSave();
                    break;
            }
        }
        this.currentState = state;
    },

    enableSave: function() {
        if(this.buttons[this.saveButtonName]) {
            this.buttons[this.saveButtonName].setDisabled(false);
        }
    },

    disableSave: function() {

        if(this.buttons[this.saveButtonName]) {
            this.buttons[this.saveButtonName].setDisabled(true);
        }
    },


    alerts: {
        showSuccess: function() {
            app.alert.show('record-saved', {
                level: 'success',
                messages: 'Record saved.',
                autoClose: true
            });
        },
        showDuplicateFound: function(numOfDupes, clickHandler) {
            var alert;

            app.alert.show('record-duplicate', {
                level: 'warning',
                title: numOfDupes + ' Duplicate Records.',
                messages: 'You can <a class="alert-action-save">ignore duplicates and save</a> or select to edit one of the duplicates.',
                autoClose: true
            });

            alert = app.alert.get('record-duplicate');
            alert.$('.alert-action-save').one('click', function() {
                clickHandler();
                alert.close();
            });
        },
        showServerError: function() {
            app.alert.show('server-error', {
                level: 'error',
                messages: 'Error occurred while connecting to the server. Please try again.',
                autoClose: false
            });
        }
    }

})