({
    extendsFrom: 'RecordView',
    editAllMode: false,

    SAVEACTIONS: {
        SAVE_AND_CREATE: 'saveAndCreate',
        SAVE_AND_VIEW: 'saveAndView'
    },

    enableDuplicateCheck: false,
    dupecheckList: null, //duplicate list layout

    saveButtonName: 'save_button',
    cancelButtonName: 'cancel_button',
    saveAndCreateButtonName: 'save_create_button',
    saveAndViewButtonName: 'save_view_button',
    restoreButtonName: 'restore_button',

    /**
     * Initialize the view and prepare the model with default button metadata
     * for the current layout.
     */
    initialize: function(options) {
        var createViewEvents = {};
        createViewEvents['click a[name=' + this.saveButtonName + ']'] = 'save';
        createViewEvents['click a[name=' + this.cancelButtonName + ']'] = 'cancel';
        createViewEvents['click a[name=' + this.saveAndCreateButtonName + ']'] = 'saveAndCreate';
        createViewEvents['click a[name=' + this.saveAndViewButtonName + ']'] = 'saveAndView';
        createViewEvents['click a[name=' + this.restoreButtonName + ']'] = 'restoreModel';
        this.events = _.extend({}, this.events, createViewEvents);

        //add states for create view
        this.STATE = _.extend({}, this.STATE, {
            CREATE: 'create',
            SELECT: 'select',
            DUPLICATE: 'duplicate'
        });

        app.view.views.RecordView.prototype.initialize.call(this, options);

        this.model.off("change", null, this);

        //keep track of what post-save action was chosen in case user chooses to ignore dupes
        this.context.lastSaveAction = null;

        //listen for the select and edit button
        this.context.on('list:dupecheck-list-select-edit:fire', this.editExisting, this);

        //extend the record view definition
        this.meta = _.extend({}, app.metadata.getView(this.module, 'record'), this.meta);

        //enable or disable duplicate check?
        var moduleMetadata = app.metadata.getModule(this.module);
        this.enableDuplicateCheck = (moduleMetadata && moduleMetadata.dupCheckEnabled) || false;

        var fields = (moduleMetadata && moduleMetadata.fields) ? moduleMetadata.fields : [];

        _.each(fields, function(field){
            if(((field.name && field.name==='assigned_user_id') || (field.id_name && field.id_name==='assigned_user_id')) &&
               (field.type && field.type==='relate')) {
                    this.model.set('assigned_user_id', app.user.id);
                    this.model.set('assigned_user_name', app.user.attributes.full_name);
            }
        }, this);

    },

    handleSync: function() {
        //override handleSync since there is no need to save the previous model state
    },

    delegateButtonEvents: function() {
        //override record view's button delegation
    },

    _render: function() {
        app.view.views.RecordView.prototype._render.call(this);
        this.setButtonStates(this.STATE.CREATE);

        this.renderDupeCheckList();
    },

    /**
     * Determine appropriate save action and execute it
     * Default to saveAndClose
     */
    save: function() {
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
    },

    /**
     * Save and close drawer
     */
    saveAndClose: function() {
        this.initiateSave(_.bind(function() {
            this.alerts.showSuccess();
            app.drawer.close(this.model);
        }, this));
    },

    /**
     * Handle click on the cancel link
     */
    cancel: function() {
        app.drawer.close();
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
        this.render();
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
        this.$('.inline-error').removeClass('inline-error');
        async.waterfall([
            _.bind(this.validateModelWaterfall, this),
            _.bind(this.dupeCheckWaterfall, this),
            _.bind(this.createRecordWaterfall, this)
        ], _.bind(function(error) {
            if (!error) {
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
            this.alerts.showInvalidModel();
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
                this.alerts.showServerError();
                callback(true);
            }, this);
        if (this.skipDupeCheck() || !this.enableDuplicateCheck) {
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
            success: success,
            error: error
        };

        this.context.trigger("dupecheck:fetch:fire", this.model, options);
    },

    /**
     * Duplicate found: display duplicates and change buttons
     * @param {object} Collection of sugar beans
     * @param {array} List of user key fields
     */
    handleDuplicateFound: function(collection) {
        this.setButtonStates(this.STATE.DUPLICATE);
        this.dupecheckList.show();
        this.skipDupeCheck(true);
    },

    /**
     * Clear out all things related to duplicate checks
     */
    resetDuplicateState: function() {
        this.setButtonStates(this.STATE.CREATE);
        this.hideDuplicates();
        this.skipDupeCheck(false);
    },

    /**
     * Called when current record is being saved to allow customization of options and params
     * during save
     *
     * Override to return set of custom options
     */
    getCustomSaveOptions: function(){

    },

    /**
     * Create a new record
     * @param success
     * @param error
     */
    saveModel: function(success, error) {
        var self = this,
            options;
        success = _.wrap(success, function (func) {
            app.file.checkFileFieldsAndProcessUpload(self.model, {
                    success:function () {
                        func();
                    }
                },
                { deleteIfFails:true});
        });

        options = {
            fieldsToValidate: self.getFields(self.module),
            success: success,
            error: error,
            viewed: true
        };

        options = _.extend({}, options, self.getCustomSaveOptions() || {});
        self.model.save(null, options);
    },

    /**
     * Check to see if we should skip duplicate check. If param specified, set duplicate check
     * to either true or false.
     * @param skip (boolean)
     * @return {*}
     */
    skipDupeCheck: function(skip) {
        var skipDupeCheck,
            saveButton = this.buttons[this.saveButtonName].getFieldElement();

        if (_.isUndefined(skip)) {
            skipDupeCheck = saveButton.data('skipDupeCheck');
            if (_.isUndefined(skipDupeCheck)) {
                skipDupeCheck = false;
            }
            return skipDupeCheck;
        } else {
            if (skip) {
                saveButton.data('skipDupeCheck', true);
            } else {
                saveButton.data('skipDupeCheck', false);
            }
        }
    },

    /**
     * Clears out field values
     */
    clear: function() {
        this.model.clear();
        this.model.set(this.model._defaults);
        this.render();
    },

    /**
     * Make the specified record as the data to be edited, and merge the existing data.
     * @param model
     */
    editExisting: function(model) {
        var origAttributes = this.saveFormData(),
            skipDupeCheck = this.skipDupeCheck();

        this.model.clear();
        this.model.set(this.extendModel(model, origAttributes));

        this.createMode = false;
        this.render();
        this.toggleEdit(true);

        this.hideDuplicates();
        this.skipDupeCheck(skipDupeCheck);
        this.setButtonStates(this.STATE.SELECT);
    },

    /**
     * Merge the selected record with the data entered in the form
     * @param newModel
     * @param origAttributes
     * @return {*}
     */
    extendModel: function(newModel, origAttributes) {
        var modelAttributes = _.clone(newModel.attributes);

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
        this._origAttributes = _.clone(this.model.attributes);
        return this._origAttributes;
    },

    /**
     * Render duplicate check list table
     */
    renderDupeCheckList: function() {
        this.context.set('dupelisttype', 'dupecheck-list-edit');

        if (_.isNull(this.dupecheckList)) {
            this.dupecheckList = app.view.createLayout({
                context: this.context,
                name: 'dupecheck',
                module: this.module
            });
           this.addToLayoutComponents(this.dupecheckList);
        }

        this.$('.headerpane').after(this.dupecheckList.$el);
        this.dupecheckList.hide();
        this.dupecheckList.render();
    },

    /**
     * Add component to layout's component list so it gets cleaned up properly on dispose
     *
     * @param component
     */
    addToLayoutComponents: function(component) {
        this.layout._components.push(component);
    },

    /**
     * Clear out duplicate list
     */
    hideDuplicates: function() {
        this.dupecheckList.hide();
    },

    /**
     * Change the behavior of buttons depending on the state that they are in
     * @param state
     */
    setButtonStates: function(state) {
        app.view.views.RecordView.prototype.setButtonStates.call(this, state);

        var $saveButtonEl = this.buttons[this.saveButtonName];
        if ($saveButtonEl) {
            switch (state) {
                case this.STATE.CREATE:
                case this.STATE.SELECT:
                    $saveButtonEl.getFieldElement().text(app.lang.get('LBL_SAVE_BUTTON_LABEL', this.module));
                    break;

                case this.STATE.DUPLICATE:
                    $saveButtonEl.getFieldElement().text(app.lang.get('LBL_IGNORE_DUPLICATE_AND_SAVE', this.module));
                    break;
            }
        }
    },

    alerts: {
        showSuccess: function() {
            //TODO: Need correct error message
            app.alert.show('record-saved', {
                level: 'success',
                messages: app.lang.get('LBL_SAVED', this.module),
                autoClose: true
            });
        },
        showInvalidModel: function() {
            //TODO: Need correct error message
            app.alert.show('invalid-data', {
                level: 'error',
                messages: 'Please resolve invalid field values before saving.',
                autoClose: true
            });
        },
        showServerError: function() {
            //TODO: Need correct error message
            app.alert.show('server-error', {
                level: 'error',
                messages: 'Error occurred while connecting to the server. Please try again.',
                autoClose: false
            });
        }
    }

})
