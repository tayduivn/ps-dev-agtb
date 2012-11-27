({
    extendsFrom: 'RecordView',

    SAVEACTIONS: {
        SAVE_AND_CREATE: 'saveAndCreate',
        SAVE_AND_VIEW: 'saveAndView'
    },

    enableDuplicateCheck: true,

    /**
     * Initialize the view and prepare the model with default button metadata
     * for the current layout.
     */
    initialize: function(options) {
        _.extend(this.events, {
            'click [name=save_button]': 'save',
            'click [name=cancel_button]': 'cancel',
            'click [name=save_create_button]': 'saveAndCreate',
            'click [name=save_view_button]': 'saveAndView',
            'click [name=restore_button]': 'restoreModel'
        });

        app.view.views.RecordView.prototype.initialize.call(this, options);

        //keep track of what post-save action was chosen in case user chooses to ignore dupes
        this.context.lastSaveAction = null;

        //listen for the edit button
        this.context.on('quickcreate:edit', this.editExisting, this);

        //listen for the save link click on the alert
        this.context.on('create:alert:save', this.save, this);

        //initialize buttons
        this.buttons.initialize(this);
    },

    render: function() {
        app.view.views.RecordView.prototype.render.call(this);
        this.buttons.setButtonAsCreate();
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
            //TODO: close pushdown modal instead
            app.navigate(this.context, this.model, 'record');
            this.alerts.showSuccess();
        }, this));
    },

    /**
     * Handle click on the cancel link
     */
    cancel: function() {
        //TODO: close pushdown modal
        window.history.back();
    },

    /**
     * Enable button to save if the model is valid.
     */
    bindDataChange: function() {
        app.view.views.RecordView.prototype.bindDataChange.call(this);

        this.model.on("change", function() {
            if (this.model.isValid(undefined, true)) {
                this.buttons.enable();
            } else {
                this.buttons.disable();
            }
        }, this);
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
            //TODO: close pushdown modal
            this.alerts.showSuccess();
            app.navigate(this.context, this.model);
        }, this));
    },

    /**
     * Handle click on restore to original link
     */
    restoreModel: function() {
        this.model.clear();
        this.createMode = true;
        this.render();

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
        this.buttons.setButtonAsIgnoreDuplicate();
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
        this.buttons.setButtonAsCreate();
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
        this.buttons.setButtonAsEdit();
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

    buttons: {
        /**
         * Button states
         */
        STATE: {
            CREATE: 'create',
            SAVE: 'save',
            EDIT: 'edit',
            DUPLICATE: 'duplicate'
        },
        /**
         * Current button state
         */
        currentState: null,
        view: null,
        /**
         * Need to know the view scope
         * @param view
         */
        initialize: function(view) {
            this.view = view;
        },
        /**
         * Enable buttons to save
         */
        enable: function() {
            if (this.currentState === this.STATE.CREATE) {
                this.setButtonAsSave();
            }
            this.enableSave(true);
        },
        /**
         * Disable buttons from saving
         */
        disable: function() {
            if (this.currentState === this.STATE.SAVE) {
                this.setButtonAsCreate();
            }
            this.enableSave(false);
        },
        /**
         * Change button to Create
         */
        setButtonAsCreate: function() {
            this.setButtonStates(this.STATE.CREATE);
        },
        /**
         * Change button to Ignore Duplicate and Save
         */
        setButtonAsIgnoreDuplicate: function() {
            this.setButtonStates(this.STATE.DUPLICATE);
        },
        /**
         * Change button to Edit
         */
        setButtonAsEdit: function() {
            this.setButtonStates(this.STATE.EDIT);
        },
        /**
         * Change button to Save
         */
        setButtonAsSave: function() {
            this.setButtonStates(this.STATE.SAVE);
        },
        /**
         * Change the behavior of buttons depending on the state that they should be in
         * @param state
         */
        setButtonStates: function(state) {
            var $buttons = {
                save:        this.view.$("[name=save_button]"),
                saveAndNew:  this.view.$("[name=save_create_button]"),
                saveAndView: this.view.$("[name=save_view_button]"),
                cancel:      this.view.$("[name=cancel]"),
                undo:        this.view.$("[name=restore_button]")
            };

            switch (state) {
                case this.STATE.CREATE:
                    $buttons.save
                        .text(app.lang.get('LBL_SAVE_BUTTON_LABEL', this.module));
                    this.enableSave(false);
                    $buttons.saveAndNew.toggleClass('hide', true);
                    $buttons.saveAndView.toggleClass('hide', true);
                    $buttons.undo.toggleClass('hide', true);
                    break;
                case this.STATE.SAVE:
                    $buttons.save
                        .text(app.lang.get('LBL_SAVE_BUTTON_LABEL', this.module));
                    this.enableSave(true);
                    $buttons.saveAndNew.toggleClass('hide', false);
                    $buttons.saveAndView.toggleClass('hide', false);
                    $buttons.undo.toggleClass('hide', true);
                    break;
                case this.STATE.EDIT:
                    $buttons.save
                        .text(app.lang.get('LBL_SAVE_BUTTON_LABEL', this.module));
                    this.enableSave(true);
                    $buttons.saveAndNew.toggleClass('hide', true);
                    $buttons.saveAndView.toggleClass('hide', true);
                    $buttons.undo.toggleClass('hide', false);
                    break;
                case this.STATE.DUPLICATE:
                    $buttons.save
                        .text(app.lang.get('LBL_IGNORE_DUPLICATE_AND_SAVE', this.module));
                    this.enableSave(true);
                    $buttons.saveAndNew.toggleClass('hide', true);
                    $buttons.saveAndView.toggleClass('hide', true);
                    $buttons.undo.toggleClass('hide', true);
                    break;
                default:
                    break;
            }

            this.currentState = state;
        },
        /**
         * Enable or disable the save button
         * @param enable
         */
        enableSave: function(enable) {
            var $saveButton = this.view.$("[name=save_button]");
            if (enable) {
                $saveButton
                    .removeClass("disabled")
                    .addClass('btn-primary');
            } else {
                $saveButton
                    .addClass("disabled")
                    .removeClass('btn-primary');
            }
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

            app.alert.show('record-saved', {
                level: 'warning',
                title: numOfDupes + ' Duplicate Records.',
                messages: 'You can <a class="alert-action-save">ignore duplicates and save</a> or select to edit one of the duplicates.',
                autoClose: true
            });

            alert = app.alert.get('record-saved');
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