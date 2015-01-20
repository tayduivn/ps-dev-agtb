/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

({
    inlineEditMode: false,

    createMode: false,

    plugins: [
        'SugarLogic',
        'ErrorDecoration',
        'GridBuilder',
        'Editable',
        'Audit',
        'FindDuplicates',
        'ToggleMoreLess'
    ],

    enableHeaderButtons: true,

    enableHeaderPane: true,

    events: {
        'click .record-edit-link-wrapper': 'handleEdit',
        'click a[name=cancel_button]': 'cancelClicked',
        'click [data-action=scroll]': 'paginateRecord',
        'click .record-panel-header': 'togglePanel',
        'click .tab a': 'setActiveTab'
    },

    /**
     * Button fields defined in view definition.
     */
    buttons: null,

    /**
     * Button states.
     */
    STATE: {
        EDIT: 'edit',
        VIEW: 'view'
    },

    // current button states
    currentState: null,

    // fields that should not be editable
    noEditFields: null,

    // width of the layout that contains this view
    _containerWidth: 0,

    initialize: function(options) {
        _.bindAll(this);
        options.meta = _.extend({}, app.metadata.getView(null, 'record'), options.meta);
        app.view.View.prototype.initialize.call(this, options);
        this.buttons = {};
        this.createMode = this.context.get('create') ? true : false;

        // Even in createMode we want it to start in detail so that we, later, respect
        // this.editableFields (the list after pruning out readonly fields, etc.)
        this.action = 'detail';

        this.context.on('change:record_label', this.setLabel, this);
        this.context.set('viewed', true);
        this.model.on('duplicate:before', this.setupDuplicateFields, this);
        this.on('editable:keydown', this.handleKeyDown, this);
        this.on('editable:mousedown', this.handleMouseDown, this);

        this.context.on('approve:case', this.approveCase, this);
        this.context.on('reject:case', this.rejectCase, this);
        this.context.on('cancel:case', this.cancelCase, this);

        //event register for preventing actions
        // when user escapes the page without confirming deleting
        // add a callback to close the alert if users navigate from the page
        app.routing.before('route', this.dismissAlert, this);
        $(window).on('beforeunload.delete' + this.cid, _.bind(this.warnDeleteOnRefresh, this));

        this.delegateButtonEvents();

        if (this.createMode) {
            this.model.isNotEmpty = true;
        }

        this.noEditFields = [];
        // properly namespace SHOW_MORE_KEY key
        this.MORE_LESS_KEY = app.user.lastState.key(this.MORE_LESS_KEY, this);

        this.adjustHeaderpane = _.bind(_.debounce(this.adjustHeaderpane, 50), this);
        $(window).on('resize.' + this.cid, this.adjustHeaderpane);
    },

    approveCase: function(options){
        var self = this;
        var statusApprove = 'approve';
        url = App.api.buildURL('pmse_approve', null, {id: statusApprove});
        App.api.call('update', url, options.attributes, {
            success: function () {
            },
            error: function (err) {
            }
        });
        var redirect = options.module;
        app.router.navigate(redirect , {trigger: true, replace: true });
    },

    rejectCase: function(options){
        var self = this;
        var statusApprove = 'reject';
        url = App.api.buildURL('pmse_approve', null, {id: statusApprove});
        App.api.call('update', url, options.attributes, {
            success: function () {
            },
            error: function (err) {
            }
        });
        var redirect = options.module;
        app.router.navigate(redirect , {trigger: true, replace: true });
    },

    cancelCase: function(options){
        var redirect = options.module;
        app.router.navigate(redirect , {trigger: true, replace: true });
    },
    /**
     * Compare with last fetched data and return true if model contains changes.
     *
     * Check changes for fields that are editable only.
     *
     * @return {Boolean} `true` if current model contains unsaved changes, otherwise `false`.
     * @link {app.plugins.view.editable}
     */
    hasUnsavedChanges: function() {
        var changedAttributes,
            editableFieldNames = [],
            unsavedFields,
            self = this,
            setAsEditable = function(fieldName) {
                if (fieldName && _.indexOf(self.noEditFields, fieldName) === -1) {
                    editableFieldNames.push(fieldName);
                }
            };

        if (this.resavingAfterMetadataSync)
            return false;

        changedAttributes = this.model.changedAttributes(this.model.getSyncedAttributes());

        if (_.isEmpty(changedAttributes)) {
            return false;
        }

        // get names of all editable fields on the page including fields in a fieldset
        _.each(this.meta.panels, function(panel) {
            _.each(panel.fields, function(field) {
                if (!field.readonly) {
                    if (field.fields && _.isArray(field.fields)) {
                        _.each(field.fields, function(field) {
                            setAsEditable(field.name);
                        });
                    } else {
                        setAsEditable(field.name);
                    }
                }
            });
        });

        // check whether the changed attributes are among the editable fields
        unsavedFields = _.intersection(_.keys(changedAttributes), editableFieldNames);

        return !_.isEmpty(unsavedFields);
    },

    /**
     * Called when current record is being duplicated to allow customization of
     * fields that will be copied into new record.
     *
     * Override to setup the fields on this bean prior to being displayed in
     * Create dialog.
     *
     * @param {Object} prefill Bean that will be used for new record.
     * @template
     */
    setupDuplicateFields: function(prefill) {
    },

    setLabel: function(context, value) {
        this.$('.record-label[data-name="' + value.field + '"]').text(value.label);
    },

    /**
     * Called each time a validation pass is completed on the model.
     *
     * @param {boolean} isValid TRUE if model is valid.
     */
    validationComplete: function(isValid) {
        if (isValid) {
            this.setButtonStates(this.STATE.VIEW);
            this.handleSave();
        }
    },

    delegateButtonEvents: function() {
        this.context.on('button:edit_button:click', this.editClicked, this);
        this.context.on('button:save_button:click', this.saveClicked, this);
        this.context.on('button:delete_button:click', this.deleteClicked, this);
        this.context.on('button:duplicate_button:click', this.duplicateClicked, this);
    },

    _render: function() {
        this._buildGridsFromPanelsMetadata(this.meta.panels);
        if (this.meta && this.meta.panels) {
            this._initTabsAndPanels();
        }
        app.view.View.prototype._render.call(this);

        if (this.context.get('record_label')) {
            this.setLabel(this.context, this.context.get('record_label'));
        }

        // Field labels in headerpane should be hidden on view but displayed in edit and create
        _.each(this.fields, function(field) {
            var toggleLabel = _.bind(function() {
                this.toggleLabelByField(field);
            }, this);

            field.off('render', toggleLabel);
            if (field.$el.closest('.headerpane').length > 0) {
                field.on('render', toggleLabel);
            }
            // some fields like 'favorite' is readonly by default, so we need to remove edit-link-wrapper
            if (field.def.readonly && field.name && -1 == _.indexOf(this.noEditFields, field.name)) {
                this.$('.record-edit-link-wrapper[data-name=' + field.name + ']').remove();
            }
        }, this);

        this.toggleHeaderLabels(this.createMode);
        this.initButtons();
        this.setButtonStates(this.STATE.VIEW);
        this.setEditableFields();

        if (this.createMode) {
            // RecordView starts with action as detail; once this.editableFields has been set (e.g.
            // readonly's pruned out), we can call toggleFields - so only fields that should be are editable
            this.toggleFields(this.editableFields, true);
        }

        this.handleActiveTab();
    },

    /**
     * Handles initiation of Tabs and Panels view upon render
     * @private
     */
    _initTabsAndPanels: function() {
        this.meta.firstPanelIsTab = this.checkFirstPanel();
        this.meta.lastPanelIndex = this.meta.panels.length-1;

        _.each(this.meta.panels, function(panel, i) {
            if (panel.header) {
                this.meta.firstNonHeaderPanelIndex = (i + 1);
            }
        }, this);

        // Tell the view to use Tabs and Panels view if either there exists a tab or if the number of panels isn't
        // equivalent to the amount expected for Business Card view (2 panels + possibly 1 if header exists)
        var headerExists = 0;
        if (_.first(this.meta.panels).header) {
            headerExists = 1;
        }

        this.meta.useTabsAndPanels = false;

        //Check if there are any newTabs
        for (i = headerExists; i < this.meta.panels.length; i++) {
            if (this.meta.panels[i].newTab) {
                this.meta.useTabsAndPanels = true;
            }
        }

        //Check for panel number
        if (this.meta.panels.length > (2 + headerExists)) {
            this.meta.useTabsAndPanels = true;
        }

        // set states
        _.each(this.meta.panels, function(panel){

            var panelKey = app.user.lastState.key(panel.name+':tabState', this);
            var panelState = app.user.lastState.get(panelKey);
            if (panelState) {
                panel.panelState = panelState;
            }
        }, this);
    },
    /**
     * handles setting active tab
     */
    handleActiveTab: function() {
        var activeTabHref = app.user.lastState.get(app.user.lastState.key('activeTab', this));
        var activeTab = this.$('ul a[href="'+activeTabHref+'"]');
        if (activeTabHref && activeTab) {
            activeTab.tab('show');
        } else if (this.meta.useTabsAndPanels && this.checkFirstPanel()) {
            // If tabs and no last state set, show first tab on render
            this.$('#recordTab a:first').tab('show');
        }
    },
    /**
     * sets active tab in user last state
     * @param {Event} event
     */
    setActiveTab: function(event) {
        var tabTarget = this.$(event.currentTarget).attr('href');
        var tabKey = app.user.lastState.key('activeTab', this);
        app.user.lastState.set(tabKey, tabTarget);
    },
    /**
     * saves panel state in user last state
     * @param {String} panelID
     * @param {String} state
     */
    savePanelState: function(panelID, state) {
        var panelKey = app.user.lastState.key(panelID+':tabState', this);
        app.user.lastState.set(panelKey, state);
    },
    /**
     * sets editable fields
     */
    setEditableFields: function() {
        delete this.editableFields;
        this.editableFields = [];

        var previousField, firstField;
        _.each(this.fields, function(field) {

            var readonlyField = field.def.readonly ||
                _.indexOf(this.noEditFields, field.def.name) >= 0 ||
                field.parent || (field.name && this.buttons[field.name]);

            if (readonlyField) {
                // exclude read only fields
                return;
            }
            if (previousField) {
                previousField.nextField = field;
                field.prevField = previousField;
            } else {
                firstField = field;
            }
            previousField = field;
            this.editableFields.push(field);

        }, this);

        if (previousField) {
            previousField.nextField = firstField;
            firstField.prevField = previousField;
        }
    },

    initButtons: function() {

        if (this.options.meta && this.options.meta.buttons) {
            _.each(this.options.meta.buttons, function(button) {
                this.registerFieldAsButton(button.name);
                if (button.buttons) {
                    var dropdownButton = this.getField(button.name);
                    if (!dropdownButton) {
                        return;
                    }
                    _.each(dropdownButton.fields, function(ddButton) {
                        this.buttons[ddButton.name] = ddButton;
                    }, this);
                }
            }, this);
        }
    },
    showPreviousNextBtnGroup: function() {
        var listCollection = this.context.get('listCollection') || new app.data.createBeanCollection(this.module);
        var recordIndex = listCollection.indexOf(listCollection.get(this.model.id));
        if (listCollection && listCollection.models && listCollection.models.length <= 1) {
            this.showPrevNextBtnGroup = false;
        } else {
            this.showPrevNextBtnGroup = true;
        }
        if (this.collection && listCollection.length !== 0) {
            this.showPrevious = listCollection.hasPreviousModel(this.model);
            this.showNext = listCollection.hasNextModel(this.model);
        }
    },

    registerFieldAsButton: function(buttonName) {
        var button = this.getField(buttonName);
        if (button) {
            this.buttons[buttonName] = button;
        }
    },

    _renderHtml: function() {
        this.showPreviousNextBtnGroup();
        app.view.View.prototype._renderHtml.call(this);
        this.adjustHeaderpane();
    },

    bindDataChange: function() {
        this.model.on('change', function(fieldType) {
            if (this.inlineEditMode) {
                this.setButtonStates(this.STATE.EDIT);
            }
            if (this.model.isNotEmpty !== true && fieldType !== 'image') {
                this.model.isNotEmpty = true;
                if (!this.disposed) {
                    this.render();
                }
            }
        }, this);
    },

    duplicateClicked: function() {
        var self = this,
            prefill = app.data.createBean(this.model.module);

        prefill.copy(this.model);
        self.model.trigger('duplicate:before', prefill);
        prefill.unset('id');
        app.drawer.open({
            layout: 'create-actions',
            context: {
                create: true,
                model: prefill
            }
        }, function(context, newModel) {
            if (newModel && newModel.id) {
                app.router.navigate(self.model.module + '/' + newModel.id, {trigger: true});
            }
        });

        prefill.trigger('duplicate:field', self.model);
    },

    editClicked: function() {
        this.setButtonStates(this.STATE.EDIT);
        this.toggleEdit(true);
    },

    saveClicked: function() {
        this.model.doValidate(this.getFields(this.module), _.bind(this.validationComplete, this));
    },

    cancelClicked: function() {
        this.handleCancel();
        this.setButtonStates(this.STATE.VIEW);
        this.clearValidationErrors(this.editableFields);
    },

    deleteClicked: function() {
        this.warnDelete();
    },

    /**
     * Render fields into either edit or view mode.
     *
     * @param {Boolean} isEdit `true` to set the field in edit mode, `false`
     *   otherwise.
     */
    toggleEdit: function(isEdit) {
        this.$('.record-edit-link-wrapper').toggle(!isEdit);
        this.$('.headerpane .record-label').toggle(isEdit);
        this.toggleFields(this.editableFields, isEdit);
        this.toggleViewButtons(isEdit);
        this.adjustHeaderpane();
    },

    /**
     * Handler for intent to edit. This handler is called both as a callback
     * from click events, and also triggered as part of tab focus event.
     *
     * @param {Event} e Event object (should be click event).
     * @param {jQuery} cell A jQuery node cell of the target node to edit.
     */
    handleEdit: function(e, cell) {
        var target,
            cellData,
            field;

        if (e) { // If result of click event, extract target and cell.
            target = this.$(e.target);
            cell = target.parents('.record-cell');
        }

        cellData = cell.data();
        field = this.getField(cellData.name);

        // Set Editing mode to on.
        this.inlineEditMode = true;

        this.setButtonStates(this.STATE.EDIT);

        this.toggleField(field);

        if (cell.closest('.headerpane').length > 0) {
            this.toggleViewButtons(true);
            this.adjustHeaderpaneFields();
        }
    },

    /**
     * Hide/show all field labels in headerpane.
     *
     * @param {Boolean} isEdit `true` to show the field labels, `false`
     *   otherwise.
     */
    toggleHeaderLabels: function(isEdit) {
        this.$('.headerpane .record-label').toggle(isEdit);
        this.toggleViewButtons(isEdit);
        this.adjustHeaderpane();
    },

    /**
     * Hide view specific button during edit.
     *
     * @param {Boolean} isEdit `true` to hide some specific buttons, `false`
     *   otherwise.
     *
     * FIXME this should be done in a more generic way (field or metadata
     * property).
     */
    toggleViewButtons: function(isEdit) {
        this.$('.headerpane span[data-type="badge"]').toggleClass('hide', isEdit);
        this.$('.headerpane span[data-type="favorite"]').toggleClass('hide', isEdit);
        this.$('.headerpane span[data-type="follow"]').toggleClass('hide', isEdit);
        this.$('.headerpane .btn-group-previous-next').toggleClass('hide', isEdit);
    },

    /**
     * Hide/show field label given a field.
     *
     * @param {View.Field} field The field to toggle the label based on current
     *   action.
     */
    toggleLabelByField: function(field) {
        if (field.action === 'edit') {
            field.$el.closest('.record-cell')
                .addClass('edit')
                .find('.record-label')
                .show();
        } else {
            field.$el.closest('.record-cell')
                .removeClass('edit')
                .find('.record-label')
                .hide();
        }
    },

    handleSave: function() {
        var self = this;
        self.inlineEditMode = false;

        app.file.checkFileFieldsAndProcessUpload(self, {
                success: function(response) {
                    if (response.record && response.record.date_modified) {
                        self.model.set('date_modified', response.record.date_modified);
                    }
                    self._saveModel();
                }
            }, {
                deleteIfFails: false
            }
        );

        self.$('.record-save-prompt').hide();
        if (!self.disposed) {
            self.render();
        }
    },

    _saveModel: function() {
        var options,
            successCallback = _.bind(function() {
                // Loop through the visible subpanels and have them sync. This is to update any related
                // fields to the record that may have been changed on the server on save.
                _.each(this.context.children, function(child) {
                    if (!_.isUndefined(child.attributes) && !_.isUndefined(child.attributes.isSubpanel)) {
                        if (child.attributes.isSubpanel && !child.attributes.hidden) {
                            child.attributes.collection.fetch();
                        }
                    }
                });
                if (this.createMode) {
                    app.navigate(this.context, this.model);
                } else if (!this.disposed) {
                    this.render();
                }
            }, this);

        //Call editable to turn off key and mouse events before fields are disposed (SP-1873)
        this.turnOffEvents(this.fields);

        options = {
            showAlerts: true,
            success: successCallback,
            error: _.bind(function(error) {
                if (error.status === 412 && !error.request.metadataRetry) {
                    this.handleMetadataSyncError(error);
                } else if (error.status === 409) {
                    app.utils.resolve409Conflict(error, this.model, _.bind(function(model, isDatabaseData) {
                        if (model) {
                            if (isDatabaseData) {
                                successCallback();
                            } else {
                                this._saveModel();
                            }
                        }
                    }, this));
                } else {
                    this.editClicked();
                }
            }, this),
            lastModified: this.model.get('date_modified'),
            viewed: true
        };

        options = _.extend({}, options, this.getCustomSaveOptions(options));

        this.model.save({}, options);
    },

    handleMetadataSyncError: function(error) {
        var self = this;
        //On a metadata sync error, retry the save after the app is synced
        self.resavingAfterMetadataSync = true;
        app.once('app:sync:complete', function() {
            error.request.metadataRetry = true;
            self.model.once('sync', function() {
                self.resavingAfterMetadataSync = false;
                //self.model.changed = {};
                app.router.refresh();
            });
            //add a new success callback to refresh the page after the save completes
            error.request.execute(null, app.api.getMetadataHash());
        });
    },

    getCustomSaveOptions: function(options) {
        return {};
    },

    handleCancel: function() {
        this.model.revertAttributes();
        this.toggleEdit(false);
        this.inlineEditMode = false;
    },

    /**
     * Pre-event handler before current router is changed.
     *
     * @return {Boolean} `true` to continue routing, `false` otherwise.
     */
    beforeRouteDelete: function() {
        if (this._modelToDelete) {
            this.warnDelete();
            return false;
        }
        return true;
    },

    /**
     * Format the message displayed in the alert.
     *
     * @return {Object} Confirmation and success messages.
     */
    getDeleteMessages: function() {
        var messages = {},
            model = this.model,
            name = app.utils.getRecordName(model),
            context = app.lang.get('LBL_MODULE_NAME_SINGULAR', model.module).toLowerCase() + ' ' + name.trim();

        messages.confirmation = app.utils.formatString(app.lang.get('NTC_DELETE_CONFIRMATION_FORMATTED'), [context]);
        messages.success = app.utils.formatString(app.lang.get('NTC_DELETE_SUCCESS'), [context]);
        return messages;
    },

    /**
     * Popup dialog message to confirm delete action
     */
    warnDelete: function() {
        var self = this;
        this._modelToDelete = true;

        self._targetUrl = Backbone.history.getFragment();
        //Replace the url hash back to the current staying page
        if (self._targetUrl !== self._currentUrl) {
            app.router.navigate(self._currentUrl, {trigger: false, replace: true});
        }

        app.alert.show('delete_confirmation', {
            level: 'confirmation',
            messages: self.getDeleteMessages().confirmation,
            onConfirm: _.bind(self.deleteModel, self),
            onCancel: function() {
                self._modelToDelete = false;
            }
        });
    },

    /**
     * Popup browser dialog message to confirm delete action
     *
     * @return {String} The message to be displayed in the browser dialog.
     */
    warnDeleteOnRefresh: function() {
        if (this._modelToDelete) {
            return this.getDeleteMessages().confirmation;
        }
    },

    /**
     * Delete the model once the user confirms the action
     */
    deleteModel: function() {
        var self = this;

        self.model.destroy({
            //Show alerts for this request
            showAlerts: {
                'process': true,
                'success': {
                    messages: self.getDeleteMessages().success
                }
            },
            success: function() {
                var redirect = self._targetUrl !== self._currentUrl;
                self._modelToDelete = false;

                self.context.trigger('record:deleted');
                if (redirect) {
                    self.unbindBeforeRouteDelete();
                    //Replace the url hash back to the current staying page
                    app.router.navigate(self._targetUrl, {trigger: true});
                    return;
                }

                app.router.navigate(self.module, {trigger: true});
            }
        });

    },

    /**
     * Key handlers for inline edit mode.
     *
     * Jump into the next or prev target field if `tab` key is pressed.
     * Calls {@link app.plugins.Editable#nextField} to go to next/prev field.
     *
     * @param {Event} e Event object.
     * @param {View.Field} field Current focused field (field in inline-edit mode).
     */
    handleKeyDown: function(e, field) {
        if (e.which === 9) { // If tab
            e.preventDefault();
            this.nextField(field, e.shiftKey ? 'prevField' : 'nextField');
            this.adjustHeaderpane();
        }
    },

    /**
     * Adjust headerpane fields when they change to view mode
     */
    handleMouseDown: function() {
        this.toggleViewButtons(false);
        this.adjustHeaderpaneFields();
    },

    /**
     * Show/hide buttons depending on the state defined for each buttons in the
     * metadata.
     *
     * @param {String} state The {@link #STATE} of the current view.
     */
    setButtonStates: function(state) {
        this.currentState = state;

        _.each(this.buttons, function(field) {
            var showOn = field.def.showOn;
            if (_.isUndefined(showOn) || (showOn === state)) {
                field.show();
            } else {
                field.hide();
            }
        }, this);
    },

    /**
     * Set the title in the header pane.
     *
     * @param {String} title The new title to set on the headerpane.
     *
     * FIXME this should be done with the header pane view + re-render it.
     */
    setTitle: function(title) {
        var $title = this.$('.headerpane .module-title');
        if ($title.length > 0) {
            $title.text(title);
        } else {
            this.$('.headerpane h1').prepend('<div class="record-cell"><span class="module-title">' + title + '</span></div>');
        }
    },

    /**
     * Detach the event handlers for warning delete
     */
    unbindBeforeRouteDelete: function() {
        app.routing.offBefore('route', this.beforeRouteDelete, this);
        $(window).off('beforeunload.delete' + this.cid);
    },

    _dispose: function() {
        this.unbindBeforeRouteDelete();
        _.each(this.editableFields, function(field) {
            field.nextField = null;
            field.prevField = null;
        });
        this.buttons = null;
        this.editableFields = null;
        this.off('editable:keydown', this.handleKeyDown, this);
        $(window).off('resize.' + this.cid);
        app.view.View.prototype._dispose.call(this);
    },

    _buildGridsFromPanelsMetadata: function(panels) {
        var lastTabIndex = 0;
        this.noEditFields = [];

        _.each(panels, function(panel) {
            // it is assumed that a field is an object but it can also be a string
            // while working with the fields, might as well take the opportunity to check the user's ACLs for the field
            _.each(panel.fields, function(field, index) {
                if (_.isString(field)) {
                    panel.fields[index] = field = {name: field};
                }
                // disable the pencil icon if the user doesn't have ACLs
                if (field.type === 'fieldset') {
                    if (field.readonly || _.every(field.fields, function(field) {
                        return !app.acl.hasAccessToModel('edit', this.model, field.name);
                    }, this)) {
                        this.noEditFields.push(field.name);
                    }
                } else if (field.readonly || !app.acl.hasAccessToModel('edit', this.model, field.name)) {
                    this.noEditFields.push(field.name);
                }
            }, this);

            // Set flag so that show more link can be displayed to show hidden panel.
            if (panel.hide) {
                this.hiddenPanelExists = true;
            }

            // labels: visibility for the label
            if (_.isUndefined(panel.labels)) {
                panel.labels = true;
            }

            if (_.isFunction(this.getGridBuilder)) {
                var options = {
                        fields: panel.fields,
                        columns: panel.columns,
                        labels: panel.labels,
                        labelsOnTop: panel.labelsOnTop,
                        tabIndex: lastTabIndex
                    },
                    gridResults = this.getGridBuilder(options).build();

                panel.grid = gridResults.grid;
                lastTabIndex = gridResults.lastTabIndex;
            }
        }, this);
    },

    /**
     * Handles click event on next/previous button of record.
     * @param {Event} evt
     */
    paginateRecord: function(evt) {
        var el = $(evt.currentTarget),
            data = el.data();
        if (data.id) {
            var list = this.context.get('listCollection'),
                model = list.get(data.id);
            switch (data.actionType) {
                case 'next':
                    list.getNext(model, this.navigateModel);
                    break;
                case 'prev':
                    list.getPrev(model, this.navigateModel);
                    break;
                default:
                    this._disablePagination(el);
            }
        }
    },

    /**
     * Callback for navigate to new model.
     *
     * @param {Data.Bean} model model New model to navigate.
     * @param {String} actionType actionType Side of navigation (prev/next).
     */
    navigateModel: function(model, actionType) {
        if (model && model.id) {
            app.router.navigate(app.router.buildRoute(this.module, model.id), {trigger: true});
        } else {
            var el = this.$el.find('[data-action=scroll][data-action-type=' + actionType + ']');
            this._disablePagination(el);
        }
    },

    /**
     * Disabling pagination if we can't paginate.
     * @param {Object} el Element to disable pagination on.
     */
    _disablePagination: function(el) {
        app.logger.error('Wrong data for record pagination. Pagination is disabled.');
        el.addClass('disabled');
        el.data('id', '');
    },

    /**
     * Adjust headerpane such that certain fields can be shown with ellipsis
     */
    adjustHeaderpane: function() {
        this.setContainerWidth();
        this.adjustHeaderpaneFields();
    },

    /**
     * Get the width of the layout container
     */
    getContainerWidth: function() {
        return this._containerWidth;
    },

    /**
     * Set the width of the layout container
     */
    setContainerWidth: function() {
        this._containerWidth = this._getParentLayoutWidth(this.layout);
    },

    /**
     * Get the width of the parent layout that contains `getPaneWidth()`
     * method.
     *
     * @param {View.Layout} layout The parent layout.
     * @return {Number} The parent layout width.
     * @private
     */
    _getParentLayoutWidth: function(layout) {
        if (!layout) {
            return 0;
        } else if (_.isFunction(layout.getPaneWidth)) {
            return layout.getPaneWidth(this);
        }

        return this._getParentLayoutWidth(layout.layout);
    },

    /**
     * Adjust headerpane fields such that the first field is ellipsified and the last field
     * is set to 100% on view.  On edit, the first field is set to 100%.
     */
    adjustHeaderpaneFields: function() {
        var $ellipsisCell,
            ellipsisCellWidth,
            $recordCells;

        if (this.disposed) {
            return;
        }

        $recordCells = this.$('.headerpane h1').children('.record-cell, .btn-toolbar');

        if (!_.isEmpty($recordCells) && this.getContainerWidth() > 0) {
            $ellipsisCell = $(this._getCellToEllipsify($recordCells));

            if (!_.isEmpty($ellipsisCell)) {
                if ($ellipsisCell.hasClass('edit')) {
                    // make the ellipsis cell widen to 100% on edit
                    $ellipsisCell.css({'width': '100%'});
                } else {
                    ellipsisCellWidth = this._calculateEllipsifiedCellWidth($recordCells, $ellipsisCell);
                    this._setMaxWidthForEllipsifiedCell($ellipsisCell, ellipsisCellWidth);
                    this._widenLastCell($recordCells);
                }
            }
        }
    },

    /**
     * Get the first cell for the field that can be ellipsified.
     * @param {jQuery} $cells
     * @return {jQuery}
     * @private
     */
    _getCellToEllipsify: function($cells) {
        var fieldTypesToEllipsify = ['fullname', 'name', 'text', 'base', 'enum', 'url', 'dashboardtitle'];

        return _.find($cells, function(cell) {
            return (_.indexOf(fieldTypesToEllipsify, $(cell).data('type')) !== -1);
        });
    },

    /**
     * Calculate the width for the cell that needs to be ellipsified.
     * @param {jQuery} $cells
     * @param {jQuery} $ellipsisCell
     * @return {Number}
     * @private
     */
    _calculateEllipsifiedCellWidth: function($cells, $ellipsisCell) {
        var width = this.getContainerWidth();

        _.each($cells, function(cell) {
            var $cell = $(cell);

            if ($cell.is($ellipsisCell)) {
                width -= (parseInt($ellipsisCell.css('padding-left'), 10) +
                    parseInt($ellipsisCell.css('padding-right'), 10));
            } else if ($cell.is(':visible')) {
                $cell.css({'width': 'auto'});
                width -= $cell.outerWidth();
            }
            $cell.css({'width': ''});
        });

        return width;
    },

    /**
     * Set the max-width for the specified cell.
     * @param {jQuery} $ellipsisCell
     * @param {number} width
     * @private
     */
    _setMaxWidthForEllipsifiedCell: function($ellipsisCell, width) {
        var ellipsifiedCell,
            fieldType = $ellipsisCell.data('type');

        if (fieldType === 'fullname' || fieldType === 'dashboardtitle') {
            ellipsifiedCell = this.getField($ellipsisCell.data('name'));
            width -= ellipsifiedCell.getCellPadding();
            ellipsifiedCell.setMaxWidth(width);
        } else {
            $ellipsisCell.css({'max-width': width});
        }
    },

    /**
     * Widen the last cell to 100%.
     * @param {jQuery} $cells
     * @private
     */
    _widenLastCell: function($cells) {
        var $cellToWiden;

        _.each($cells, function(cell) {
            var $cell = $(cell);
            if ($cell.hasClass('record-cell') && (!$cell.hasClass('hide') || $cell.is(':visible'))) {
                $cellToWiden = $cell;
            }
        });

        if ($cellToWiden) {
            $cellToWiden.css({'width': '100%'});
        }
    },

    /**
     * Adds the favorite field to app.view.View.getFieldNames() if `favorite` field is within a panel
     * so my_favorite is part of the field list and is fetched
     */
    getFieldNames: function(module) {
        var fields = app.view.View.prototype.getFieldNames.call(this, module);
        var favorite = _.find(this.meta.panels, function(panel) {
            return _.find(panel.fields, function(field) {
                return field.type === 'favorite';
            });
        });
        var follow = _.find(this.meta.panels, function(panel) {
            return _.find(panel.fields, function(field) {
                return field.type === 'follow';
            });
        });
        if (favorite) {
            fields = _.union(fields, ['my_favorite']);
        }
        if (follow) {
            fields = _.union(fields, ['following']);
        }
        return fields;
    },

    /**
     * Hide or show panel based on click to the panel header
     * @param e - event
     */
    togglePanel: function(e) {
        var $panelHeader = this.$(e.currentTarget);
        if ($panelHeader && $panelHeader.next()) {
            $panelHeader.next().toggle();
        }
        if ($panelHeader && $panelHeader.find('i')) {
            $panelHeader.find('i').toggleClass('fa-chevron-up fa-chevron-down');
        }
        var panelName = this.$(e.currentTarget).parent().data('panelname');
        var state = 'collapsed';
        if (this.$(e.currentTarget).next().is(":visible")) {
            state = 'expanded';
        }
        this.savePanelState(panelName, state);
    },

    /**
     * Returns true if the first non-header panel has useTabs set to true
     */
    checkFirstPanel: function() {
        if (this.meta && this.meta.panels) {
            if (this.meta.panels[0] && this.meta.panels[0].newTab && !this.meta.panels[0].header) {
                return true;
            }
            if (this.meta.panels[1] && this.meta.panels[1].newTab) {
                return true;
            }
        }
        return false;
    },
    /**
     * Returns true if the first non-header panel has useTabs set to true
     */
    checkFirstPanel: function() {
        if (this.meta && this.meta.panels) {
            if (this.meta.panels[0] && this.meta.panels[0].newTab && !this.meta.panels[0].header) {
                return true;
            }
            if (this.meta.panels[1] && this.meta.panels[1].newTab) {
                return true;
            }
        }
        return false;
    },
})
