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
        'ToggleMoreLess',
        'Tooltip'
    ],

    enableHeaderButtons: true,

    enableHeaderPane: true,

    events: {
        'click .record-edit-link-wrapper': 'handleEdit',
        'click a[name=cancel_button]': 'cancelClicked'
//        'click [data-action=scroll]': 'paginateRecord',
//        'click .record-panel-header': 'togglePanel',
//        'click #recordTab > .tab > a:not(.dropdown-toggle)': 'setActiveTab',
//        'click .tab .dropdown-menu a': 'triggerNavTab'
    },

    buttons: null,

    STATE: {
        EDIT: 'edit',
        VIEW: 'view'
    },

    currentState: null,

    noEditFields: null,

    _containerWidth: 0,

    initialize: function(options) {
        this.case = this.options.context.get('case');
        //console.log(this);
        _.bindAll(this);
        options.meta = _.extend({}, app.metadata.getView(this.options.module, 'record'), options.meta);
        options.meta.buttons = this.case.buttons;
        app.view.View.prototype.initialize.call(this, options);
        this.buttons = {};
        this.createMode = this.context.get('create') ? true : false;
        this.action = 'detail';
        this.context.on('change:record_label', this.setLabel, this);
        this.context.set('viewed', true);
        this.model.on('duplicate:before', this.setupDuplicateFields, this);
        this.on('editable:keydown', this.handleKeyDown, this);
        this.on('editable:mousedown', this.handleMouseDown, this);
        this.on('field:error', this.handleFieldError, this);

        //event register for preventing actions
        // when user escapes the page without confirming deleting
        app.routing.before('route', this.beforeRouteDelete, this, true);
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

        $(window).on('resize.' + this.cid, this.overflowTabs);
    },

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

    setupDuplicateFields: function(prefill) {
    },

    setLabel: function(context, value) {
        this.$('.record-label[data-name="' + value.field + '"]').text(value.label);
    },

    validationComplete: function(isValid) {
        if (isValid) {
            this.setButtonStates(this.STATE.VIEW);
            this.handleSave();
        }
    },

    delegateButtonEvents: function() {
        this.context.on('button:edit_button:click', this.editClicked, this);
//        this.context.on('button:save_button:click', this.saveClicked, this);
//        this.context.on('button:delete_button:click', this.deleteClicked, this);
//        this.context.on('button:duplicate_button:click', this.duplicateClicked, this);

        this.context.on('case:cancel', this.cancelCase, this);
        this.context.on('case:claim', this.caseClaim, this);
        this.context.on('case:approve', this.caseApprove, this);
        this.context.on('case:reject', this.caseReject, this);
        this.context.on('case:route', this.caseRoute, this);

        this.context.on('case:history', this.caseHistory, this);
        this.context.on('case:status', this.caseStatus, this);
        this.context.on('case:add:notes', this.caseAddNotes, this);
        this.context.on('case:change:owner', this.caseChangeOwner, this);
        this.context.on('case:reassign', this.caseReassign, this);
    },

    cancelCase: function () {
        this.redirectCase();
    },

    caseClaim: function () {
        app.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});
        var frm_action = 'Claim';
//            value = {};
        var value = this.model.attributes;
        value.moduleName = this.case.flow.cas_sugar_module;
        value.beanId = this.case.flow.cas_sugar_object_id;
        value.cas_id = this.case.flow.cas_id;
        value.cas_index = this.case.flow.cas_index;
        value.taskName = this.case.title.activity;
        var self = this;
        var pmseInboxUrl = app.api.buildURL('pmse_Inbox/engine_claim','',{},{});
        app.api.call('update', pmseInboxUrl, value,{
            success: function (){
                app.alert.dismiss('upload');
                self.redirectCase(frm_action);
            }
        });
    },
    caseApprove: function () {
//        app.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});
        this.model.doValidate(this.getFields(this.module), _.bind(this.validationCompleteApprove, this));

    },

    validationCompleteApprove: function(isValid) {
        if (isValid) {
            app.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});
            var value = this.model.attributes;
            value.frm_action = 'Approve';
            value.idFlow = this.case.flowId;
            value.idInbox = this.case.inboxId;
            value.cas_id = this.case.flow.cas_id;
            value.cas_index = this.case.flow.cas_index;
            value.moduleName = this.case.flow.cas_sugar_module;
            value.beanId = this.case.flow.cas_sugar_object_id;
            value.taskName = this.case.title.activity;
            //this.setButtonStates(this.STATE.VIEW);
            //this.handleSave();
            var self = this;
            var pmseInboxUrl = app.api.buildURL('pmse_Inbox/engine_route','',{},{});
            app.api.call('update', pmseInboxUrl, value,{
                success: function (){
                    self.redirectCase();
                }
            });
        }
    },

    caseReject: function () {
        app.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});
        this.model.doValidate(this.getFields(this.module), _.bind(this.validationCompleteReject, this));
    },

    validationCompleteReject: function(isValid) {
        if (isValid) {
            var value = this.model.attributes;
            value.frm_action = 'Reject';
            value.idFlow = this.case.flowId;
            value.idInbox = this.case.inboxId;
            value.cas_id = this.case.flow.cas_id;
            value.cas_index = this.case.flow.cas_index;
            value.moduleName = this.case.flow.cas_sugar_module;
            value.beanId = this.case.flow.cas_sugar_object_id;
            value.taskName = this.case.title.activity;
            //this.setButtonStates(this.STATE.VIEW);
            //this.handleSave();
            var self = this;
            var pmseInboxUrl = app.api.buildURL('pmse_Inbox/engine_route','',{},{});
            app.api.call('update', pmseInboxUrl, value,{
                success: function (){
                    self.redirectCase();
                }
            });
        }
    },

    caseRoute: function () {
        this.model.doValidate(this.getFields(this.module), _.bind(this.validationCompleteRoute, this));
    },

    validationCompleteRoute: function(isValid) {
        if (isValid) {
            var value = this.model.attributes;
            value.frm_action = 'Route';
            value.idFlow = this.case.flowId;
            value.idInbox = this.case.inboxId;
            value.cas_id = this.case.flow.cas_id;
            value.cas_index = this.case.flow.cas_index;
            value.moduleName = this.case.flow.cas_sugar_module;
            value.beanId = this.case.flow.cas_sugar_object_id;
            value.taskName = this.case.title.activity;
            //this.setButtonStates(this.STATE.VIEW);
            //this.handleSave();
            var self = this;
            var pmseInboxUrl = app.api.buildURL('pmse_Inbox/engine_route','',{},{});
            app.api.call('update', pmseInboxUrl, value,{
                success: function (){
                    self.redirectCase();
                }
            });
        }
    },

    redirectCase: function(isRoute){
        app.alert.dismiss('upload');
        switch(isRoute){
            case 'Claim':
                window.location.reload();
                break;
            default:
                //app.router.navigate("Home" , {trigger: true, replace: true });
                //app.router.record("Home", null);
                app.router.list("Home");
                break;
        };
    },

    caseHistory: function(){
        showHistory(this.case.flow.cas_id, this.case.flow.cas_index);
    },

    caseStatus: function(){
        showImage(this.case.flow.cas_id);
    },

    caseAddNotes: function(){
        showNotes(this.case.flow.cas_id, this.case.flow.cas_index);
    },

    caseChangeOwner: function () {
        var value = this.model.attributes;
        value.moduleName = this.case.flow.cas_sugar_module;
        value.beanId = this.case.flow.cas_sugar_object_id;
        adhocForm(this.case.flow.cas_id, this.case.flow.cas_index, this.case.flowId, this.case.inboxId,this.case.title.activity,value);
    },

    caseReassign: function () {
        var value = this.model.attributes;
        value.moduleName = this.case.flow.cas_sugar_module;
        value.beanId = this.case.flow.cas_sugar_object_id;
        reassignForm(this.case.flow.cas_id, this.case.flow.cas_index, this.case.flowId, this.case.inboxId,this.case.title.activity,value);
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
        this.overflowTabs();
    },

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
            panel.panelState = panelState || panel.panelDefault;
        }, this);
    },

    handleActiveTab: function() {
        var activeTabHref = this.getActiveTab(),
            activeTab = this.$('#recordTab > .tab > a[href="'+activeTabHref+'"]');

        if (activeTabHref && activeTab) {
            activeTab.tab('show');
        } else if (this.meta.useTabsAndPanels && this.checkFirstPanel()) {
            // If tabs and no last state set, show first tab on render
            this.$('#recordTab a:first').tab('show');
        }
    },

    getActiveTab: function() {
        var activeTabHref = app.user.lastState.get(app.user.lastState.key('activeTab', this));

        // Set to first tab by default
        if (!activeTabHref) {
            activeTabHref = this.$('#recordTab > .tab:first-child > a').attr('href') || '';
            app.user.lastState.set(
                app.user.lastState.key('activeTab', this),
                activeTabHref.substring(0, activeTabHref.indexOf(this.cid))
            );
        }
        else {
            activeTabHref += this.cid;
        }
        return activeTabHref;
    },

    setActiveTab: function(event) {
        var tabTarget = this.$(event.currentTarget).attr('href'),
            tabKey = app.user.lastState.key('activeTab', this),
            cidIndex = tabTarget.indexOf(this.cid);

        tabTarget = tabTarget.substring(0, cidIndex);
        app.user.lastState.set(tabKey, tabTarget);
    },

    savePanelState: function(panelID, state) {
        var panelKey = app.user.lastState.key(panelID+':tabState', this);
        app.user.lastState.set(panelKey, state);
    },

    setEditableFields: function() {
        delete this.editableFields;
        this.editableFields = [];
        var previousField, firstField;
        _.each(this.fields, function(field) {
            if(this.checkReadonly(field)){
                field.def.readonly = true;
            }

            var readonlyField = field.def.readonly ||
                _.indexOf(this.noEditFields, field.def.name) >= 0 ||
                field.parent || (field.name && this.buttons[field.name]);

            if (readonlyField) {
                // exclude read only fields
                return;
            }
            if(this.checkRequired(field)){
                field.def.required = true;
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
                model: prefill,
                copiedFromModelId: this.model.get('id')
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

    toggleEdit: function(isEdit) {
        this.$('.record-edit-link-wrapper').toggle(!isEdit);
        this.$('.headerpane .record-label').toggle(isEdit);
        this.toggleFields(this.editableFields, isEdit);
        this.toggleViewButtons(isEdit);
        this.adjustHeaderpane();
    },

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

    toggleHeaderLabels: function(isEdit) {
        this.$('.headerpane .record-label').toggle(isEdit);
        this.toggleViewButtons(isEdit);
        this.adjustHeaderpane();
    },

    toggleViewButtons: function(isEdit) {
        this.$('.headerpane span[data-type="badge"]').toggleClass('hide', isEdit);
        this.$('.headerpane span[data-type="favorite"]').toggleClass('hide', isEdit);
        this.$('.headerpane span[data-type="follow"]').toggleClass('hide', isEdit);
        this.$('.headerpane .btn-group-previous-next').toggleClass('hide', isEdit);
    },

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

    beforeRouteDelete: function() {
        if (this._modelToDelete) {
            this.warnDelete();
            return false;
        }
        return true;
    },

    getDeleteMessages: function() {
        var messages = {},
            model = this.model,
            name = app.utils.getRecordName(model),
            context = app.lang.get('LBL_MODULE_NAME_SINGULAR', model.module).toLowerCase() + ' ' + name.trim();

        messages.confirmation = app.utils.formatString(app.lang.get('NTC_DELETE_CONFIRMATION_FORMATTED'), [context]);
        messages.success = app.utils.formatString(app.lang.get('NTC_DELETE_SUCCESS'), [context]);
        return messages;
    },

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

    warnDeleteOnRefresh: function() {
        if (this._modelToDelete) {
            return this.getDeleteMessages().confirmation;
        }
    },

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

    handleKeyDown: function(e, field) {
        if (e.which === 9) { // If tab
            e.preventDefault();
            this.nextField(field, e.shiftKey ? 'prevField' : 'nextField');
            this.adjustHeaderpane();
        }
    },

    handleMouseDown: function() {
        this.toggleViewButtons(false);
        this.adjustHeaderpaneFields();
    },

    handleFieldError: function(field, hasError) {
        if(!hasError) {
            return;
        }
        var tabLink,
            fieldTab   = field.$el.closest('.tab-pane'),
            fieldPanel = field.$el.closest('.record-panel-content');

        if (field.view.meta && field.view.meta.useTabsAndPanels) {
            // If field's panel is a tab, switch to the tab that contains the field with the error
            if (fieldTab.length > 0) {
                tabLink = this.$('[href="#'+fieldTab.attr('id')+'"].[data-toggle="tab"]');
                tabLink.tab('show');
                // Put a ! next to the tab if one doesn't already exist
                if (tabLink.find('.fa-exclamation-circle').length === 0) {
                    tabLink.append(' <i class="fa fa-exclamation-circle tab-warning"></i>');
                }
            }

            // If field's panel is a panel that is closed, open it and change arrow
            if (fieldPanel && fieldPanel.is(':hidden')) {
                fieldPanel.toggle();
                var fieldPanelArrow = fieldPanel.prev().find('i');
                fieldPanelArrow.toggleClass('fa-chevron-up fa-chevron-down');
            }
        }
    },

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

    setTitle: function(title) {
        var $title = this.$('.headerpane .module-title');
        if ($title.length > 0) {
            $title.text(title);
        } else {
            this.$('.headerpane h1').prepend('<div class="record-cell"><span class="module-title">' + title + '</span></div>');
        }
    },

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
                if(this.checkReadonly(field)){
                    field.readonly = true;
                }
                if (_.isString(field)) {
                    panel.fields[index] = field = {name: field};
                }

                var keys = _.keys(field);

                // Make filler fields readonly
                if (keys.length === 1 && keys[0] === 'span')  {
                    field.readonly = true;
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

    navigateModel: function(model, actionType) {
        if (model && model.id) {
            app.router.navigate(app.router.buildRoute(this.module, model.id), {trigger: true});
        } else {
            var el = this.$el.find('[data-action=scroll][data-action-type=' + actionType + ']');
            this._disablePagination(el);
        }
    },

    _disablePagination: function(el) {
        app.logger.error('Wrong data for record pagination. Pagination is disabled.');
        el.addClass('disabled');
        el.data('id', '');
    },

    adjustHeaderpane: function() {
        this.setContainerWidth();
        this.adjustHeaderpaneFields();
    },

    getContainerWidth: function() {
        return this._containerWidth;
    },

    setContainerWidth: function() {
        this._containerWidth = this._getParentLayoutWidth(this.layout);
    },

    _getParentLayoutWidth: function(layout) {
        if (!layout) {
            return 0;
        } else if (_.isFunction(layout.getPaneWidth)) {
            return layout.getPaneWidth(this);
        }

        return this._getParentLayoutWidth(layout.layout);
    },

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

    _getCellToEllipsify: function($cells) {
        var fieldTypesToEllipsify = ['fullname', 'name', 'text', 'base', 'enum', 'url', 'dashboardtitle'];

        return _.find($cells, function(cell) {
            return (_.indexOf(fieldTypesToEllipsify, $(cell).data('type')) !== -1);
        });
    },

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

    togglePanel: function(e) {
        var $panelHeader = this.$(e.currentTarget);
        if ($panelHeader && $panelHeader.next()) {
            $panelHeader.next().toggle();
            $panelHeader.toggleClass('panel-inactive panel-active');
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

    overflowTabs: function() {
        var $tabs = this.$('#recordTab > .tab:not(.dropdown)'),
            $dropdownList = this.$('#recordTab .dropdown'),
            $dropdownTabs = this.$('#recordTab .dropdown-menu li'),
            navWidth = this.$('#recordTab').width(),
            activeTabHref = this.getActiveTab(),
            $activeTab = this.$('#recordTab > .tab > a[href="'+activeTabHref+'"]').parent(),
            // Calculate available width for items in navbar
            // Includes the activetab to ensure it is displayed
            width = $activeTab.outerWidth() + $dropdownList.outerWidth();

        $tabs.each(_.bind(function (index, elem) {
            var $tab = $(elem),
                overflow;

            // Always include the active tab
            if ($tab.hasClass('active')) {
                overflow = false;
            }
            else {
                width += $tab.outerWidth();
                // Check if the tab fits in the navbar
                overflow = width >= navWidth;
            }

            // Toggle tabs in the navbar
            $tab.toggleClass('hidden', overflow);
            // Toggle items in the dropdown
            this.$($dropdownTabs[index]).toggleClass('hidden', !overflow);
        }, this));
        // Toggle the dropdown arrow
        $dropdownList.toggleClass('hidden', !$tabs.is(':hidden'));
    },

    triggerNavTab: function(e) {
        var tabTarget = e.currentTarget.hash,
            activeTab = this.$('#recordTab > .tab > a[href="'+tabTarget+'"]');

        e.preventDefault();
        activeTab.trigger('click');
        this.overflowTabs();
    },

    checkReadonly: function(field){
        var isReadonly = false;
        _.each(this.case.readonly, function(caseField){
            if(field.name=== caseField){
                isReadonly = true;
            }
        }, this);
        return isReadonly;
    },

    checkRequired: function(field){
        var isRequired = false;
        _.each(this.case.required, function(caseField){
            if(field.name=== caseField){
                isRequired = true;
            }
        }, this);
        return isRequired;
    }

})
