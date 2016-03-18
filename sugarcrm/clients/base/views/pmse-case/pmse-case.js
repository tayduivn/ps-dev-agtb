/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
({
    extendsFrom: 'RecordView',

    events: {
        'click .record-edit-link-wrapper': 'handleEdit'
    },

    initialize: function(options) {
        this.case = this.options.context.get('case');
        this.plugins = _.union(this.plugins || [], ["ProcessAuthorActions"]);
        options.meta = _.extend({}, app.metadata.getView(this.options.module, 'record'), options.meta);
        options.meta.hashSync = _.isUndefined(options.meta.hashSync) ? true : options.meta.hashSync;
        options.meta.buttons = this.case.buttons;
        this._super('initialize', [options]);
        this.context.set("layout", "record");
        this.buttons = {};
        this.createMode = this.context.get('create') ? true : false;
        this.action = 'detail';
        this.context.on('change:record_label', this.setLabel, this);
        this.context.set('viewed', true);
        this.context.set('dataView', 'record');
        this.model.on('duplicate:before', this.setupDuplicateFields, this);
        this.on('editable:keydown', this.handleKeyDown, this);
        this.on('editable:mousedown', this.handleMouseDown, this);
        this.on('field:error', this.handleFieldError, this);
        this.context.on('button:cancel_button:click', this.cancelClicked, this);
        //event register for preventing actions
        // when user escapes the page without confirming deleting
        app.routing.before('route', this.beforeRouteDelete, this);
        $(window).on('beforeunload.delete' + this.cid, _.bind(this.warnDeleteOnRefresh, this));

        if (this.createMode) {
            this.model.isNotEmpty = true;
        }

        this.noEditFields = [];
        // properly namespace SHOW_MORE_KEY key
        this.MORE_LESS_KEY = app.user.lastState.key(this.MORE_LESS_KEY, this);
        this.adjustHeaderpane = _.bind(_.debounce(this.adjustHeaderpane, 50), this);
        $(window).on('resize.' + this.cid, this.adjustHeaderpane);

        $(window).on('resize.' + this.cid, _.bind(this.overflowTabs, this));

        // initialize tab view after the component is attached to DOM
        this.on('append', function() {
            this.overflowTabs();
            this.handleActiveTab();
        }, this);

    },

    validationComplete: function(isValid) {
        if (isValid) {
            this.setButtonStates(this.STATE.VIEW);
            this.handleSave();
        }
    },

    delegateButtonEvents: function() {
        this.context.on('button:edit_button:click', this.editClicked, this);

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
        this.model.doValidate(this.getFields(this.module), _.bind(this.validationCompleteApprove, this));
    },

    validationCompleteApprove: function (isValid) {
        if (isValid) {
            app.alert.show('confirm_approve', {
                level: 'confirmation',
                messages: app.lang.get('LBL_PA_PROCESS_APPROVE_QUESTION', 'pmse_Inbox'),
                onConfirm: _.bind(function () {
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
                    var self = this;
                    var pmseInboxUrl = app.api.buildURL('pmse_Inbox/engine_route', '', {}, {});
                    app.api.call('update', pmseInboxUrl, value, {
                        success: function () {
                            app.alert.show('success_approve', {
                                level: 'success',
                                messages: app.lang.get('LBL_PA_PROCESS_APPROVED_SUCCESS', 'pmse_Inbox'),
                                autoClose: true
                            });
                            self.model.setSyncedAttributes(self.model.attributes);
                            self.redirectCase();
                        }
                    });
                }, this),
                onCancel: $.noop
            });
        }
    },

    caseReject: function () {
        this.model.doValidate(this.getFields(this.module), _.bind(this.validationCompleteReject, this));
    },

    validationCompleteReject: function (isValid) {
        if (isValid) {
            app.alert.show('confirm_reject', {
                level: 'confirmation',
                messages: app.lang.get('LBL_PA_PROCESS_REJECT_QUESTION', 'pmse_Inbox'),
                onConfirm: _.bind(function () {
                    app.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});
                    var value = this.model.attributes;
                    value.frm_action = 'Reject';
                    value.idFlow = this.case.flowId;
                    value.idInbox = this.case.inboxId;
                    value.cas_id = this.case.flow.cas_id;
                    value.cas_index = this.case.flow.cas_index;
                    value.moduleName = this.case.flow.cas_sugar_module;
                    value.beanId = this.case.flow.cas_sugar_object_id;
                    value.taskName = this.case.title.activity;
                    var self = this;
                    var pmseInboxUrl = app.api.buildURL('pmse_Inbox/engine_route', '', {}, {});
                    app.api.call('update', pmseInboxUrl, value, {
                        success: function () {
                            app.alert.show('success_reject', {
                                level: 'success',
                                messages: app.lang.get('LBL_PA_PROCESS_REJECTED_SUCCESS', 'pmse_Inbox'),
                                autoClose: true
                            });
                            self.model.setSyncedAttributes(self.model.attributes);
                            self.redirectCase();
                        }
                    });
                }, this),
                onCancel: $.noop
            });
        }
    },

    caseRoute: function () {
        this.model.doValidate(this.getFields(this.module), _.bind(this.validationCompleteRoute, this));
    },

    validationCompleteRoute: function (isValid) {
        if (isValid) {
            app.alert.show('confirm_route', {
                level: 'confirmation',
                messages: app.lang.get('LBL_PA_PROCESS_ROUTE_QUESTION', 'pmse_Inbox'),
                onConfirm: _.bind(function () {
                    var value = this.model.attributes;
                    value.frm_action = 'Route';
                    value.idFlow = this.case.flowId;
                    value.idInbox = this.case.inboxId;
                    value.cas_id = this.case.flow.cas_id;
                    value.cas_index = this.case.flow.cas_index;
                    value.moduleName = this.case.flow.cas_sugar_module;
                    value.beanId = this.case.flow.cas_sugar_object_id;
                    value.taskName = this.case.title.activity;
                    if (this.case.taskContinue) {
                        value.taskContinue = true;
                    }
                    var self = this;
                    var pmseInboxUrl = app.api.buildURL('pmse_Inbox/engine_route', '', {}, {});
                    app.api.call('update', pmseInboxUrl, value, {
                        success: function () {
                            app.alert.show('success_route', {
                                level: 'success',
                                messages: app.lang.get('LBL_PA_PROCESS_ROUTED_SUCCESS', 'pmse_Inbox'),
                                autoClose: true
                            });
                            self.redirectCase();
                        }
                    });
                }, this),
                onCancel: $.noop
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
                app.router.list("Home");
                break;
        };
    },

    /**
     * Shows a window with current history of the record
     *
     */
    caseHistory: function () {
        this.getHistory(this.case.flow.cas_id);
    },

    /**
     * Shows window with picture of current status of the process
     *
     */
    caseStatus: function() {
        this.showStatus(this.case.flow.cas_id);
    },

    /**
     * Shows window with notes of current process
     *
     */
    caseAddNotes: function () {
        this.showNotes(this.case.flow.cas_id, this.case.flow.cas_index);
    },

    caseChangeOwner: function () {
        var value = this.model.attributes;
        value.moduleName = this.case.flow.cas_sugar_module;
        value.beanId = this.case.flow.cas_sugar_object_id;
        this.showForm(this.case.flow.cas_id, this.case.flow.cas_index, 'adhoc', this.case.flowId, this.case.inboxId, this.case.title.activity, value);
    },

    caseReassign: function () {
        var value = this.model.attributes;
        value.moduleName = this.case.flow.cas_sugar_module;
        value.beanId = this.case.flow.cas_sugar_object_id;
        this.showForm(this.case.flow.cas_id, this.case.flow.cas_index, 'reassign', this.case.flowId, this.case.inboxId, this.case.title.activity, value);
    },

    /**
     * Helper for select2 field of showForm
     *
     * @param {string} url
     * @param {id} flowId
     */
    getUserSearchURL: function (url, flowId) {
        return url + '/users/' + flowId + '?filter={%TERM%}&max_num={%PAGESIZE%}&offset={%OFFSET%}';
    },

    /**
     * Returns a new HiddenField object with the desired name and value.
     * @param name
     * @param value
     * @private
     */
    _getHiddenFieldObject: function (name, value) {
        return new HiddenField({name: name, value: value});
    },

    /**
     * Show form to reassign user or change assigned user
     *
     * @param {id} casId
     * @param {id} casIndex
     * @param {string} wtype
     * @param {id} flowId
     * @param {id} pmseInboxId
     * @param {string} taskName
     * @param {Object} [values]
     */
    showForm: function (casId, casIndex, wtype, flowId, pmseInboxId, taskName, values) {
        var f,
            w,
            combo_users,
            items,
            proxy,
            textArea,
            url,
            wtitle,
            wWidth,
            wHeight,
            casIdField,
            casIndexField,
            combo_type,
            casFlowId,
            casInboxId,
            task_Name,
            user_Name,
            module_Name,
            bean_Id,
            full_Name,
            valAux,
            reassignForm;

        module_Name = this._getHiddenFieldObject('moduleName', values.moduleName);
        bean_Id = this._getHiddenFieldObject('beanId', values.beanId);
        if (values.name) {
            valAux = values.name;
        } else {
            valAux = values.full_name;
        }
        full_Name = this._getHiddenFieldObject('full_name', valAux);
        task_Name = this._getHiddenFieldObject('taskName', taskName);

        casIdField = this._getHiddenFieldObject('cas_id', casId);

        casIndexField = this._getHiddenFieldObject('cas_index', casIndex);
        casFlowId = this._getHiddenFieldObject('idFlow', flowId);

        casInboxId = this._getHiddenFieldObject('idInbox', pmseInboxId);
        combo_type = new ComboboxField({
            name: 'adhoc_type',
            label: app.lang.get('LBL_PMSE_FORM_LABEL_TYPE', 'pmse_Inbox'),
            options: [
                {text: 'Round Trip', value: 'ROUND_TRIP'},
                {text: 'One Way', value: 'ONE_WAY'}
            ],
            initialValue: 'ROUND_TRIP',
            required: true
        });

        textArea = new TextareaField({
            name: 'adhoc_comment',
            label: app.lang.get('LBL_PMSE_FORM_LABEL_NOTE', 'pmse_Inbox'),
            fieldWidth: '300px',
            fieldHeight: '100px'
        });
        user_Name = this._getHiddenFieldObject('user_name', '');

        reassignForm = this._getHiddenFieldObject('reassign_form', true);

        if (wtype === 'reassign') {
            url = 'pmse_Inbox/AdhocReassign';
            wtitle = app.lang.get('LBL_PMSE_TITLE_AD_HOC', 'pmse_Inbox');
            wWidth = 550;
            wHeight = 300;

            combo_users = new SearchableCombobox({
                label: app.lang.get('LBL_PMSE_FORM_LABEL_USER', 'pmse_Inbox'),
                name: 'adhoc_user',
                submit: true,
                required: true,
                searchMore: {
                    module: "Users",
                    fields: ["id"]
                },
                searchURL: this.getUserSearchURL(url, flowId),
                searchValue: 'id',
                searchLabel: 'full_name',
                placeholder: app.lang.get('LBL_PA_FORM_COMBO_ASSIGN_TO_USER_HELP_TEXT', 'pmse_Project'),
                helpTooltip: {
                    message: app.lang.get('LBL_PMSE_FORM_TOOLTIP_SELECT_USER', 'pmse_Inbox')
                }
            });

            items = [
                casIdField,
                casIndexField,
                casFlowId,
                casInboxId,
                combo_users,
                combo_type,
                textArea,
                task_Name,
                user_Name,
                module_Name,
                bean_Id,
                full_Name,
                reassignForm
            ];
            combo_users.setName('adhoc_user');
            textArea.setName('not_content');
        } else {
            // If wtype is set to user selection, change the tooltip msg
            url = 'pmse_Inbox/ReassignForm';
            wtitle = app.lang.get('LBL_PMSE_TITLE_REASSIGN', 'pmse_Inbox');
            wWidth = 500;
            wHeight = 250;

            combo_users = new SearchableCombobox({
                label: app.lang.get('LBL_PMSE_FORM_LABEL_USER', 'pmse_Inbox'),
                name: 'adhoc_user',
                submit: true,
                required: true,
                searchMore: {
                    module: "Users",
                    fields: ["id"]
                },
                searchURL: this.getUserSearchURL(url, flowId),
                searchValue: 'id',
                searchLabel: 'full_name',
                placeholder: app.lang.get('LBL_PA_FORM_COMBO_ASSIGN_TO_USER_HELP_TEXT', 'pmse_Project'),
                helpTooltip: {
                    message: app.lang.get('LBL_PMSE_FORM_TOOLTIP_CHANGE_USER', 'pmse_Inbox')
                }
            });

            items = [
                casIdField,
                casIndexField,
                casFlowId,
                casInboxId,
                combo_users,
                textArea,
                task_Name,
                user_Name,
                module_Name,
                bean_Id,
                full_Name
            ];
            combo_users.setName('reassign_user');
            textArea.setName('reassign_comment');
        }
        flowId = (flowId) ? flowId : urlCase.id;
        proxy = new SugarProxy({
            url: url,
            uid: '',
            callback: null
        });
        f = new Form({
            items: items,
            closeContainerOnSubmit: true,
            buttons: [
                {
                    jtype: 'normal',
                    caption: app.lang.get('LBL_PMSE_BUTTON_SAVE', 'pmse_Inbox'),
                    cssClasses: ['btn', 'btn-primary'],
                    handler: function () {
                        if (f.validate()) {
                            app.alert.show('upload', {level: 'process', title: 'LBL_SAVING', autoClose: false});
                            var cbDate = combo_users.getSelectedText();
                            if (combo_users.name == 'reassign_user') {
                                items[6].setValue(cbDate);
                            } else {
                                items[7].setValue(cbDate);
                            }
                            var urlIni = app.api.buildURL(url, null, null);
                            attributes = {
                                data: f.getData()
                            };
                            $(w.html).remove();
                            app.api.call('update', urlIni, attributes, {
                                success: function (response) {
                                    app.alert.show('pmse_reassign_success', {
                                        autoClose: true,
                                        level: 'success',
                                        messages: app.lang.get('LBL_PMSE_ALERT_REASSIGN_SUCCESS', 'pmse_Inbox')
                                    });
                                    if (wtype == 'reassign') {
                                        w.close();
                                        app.router.redirect('Home');
                                    }
                                    else if (wtype == 'adhoc') {
                                        if ($('#assigned_user_name').length) {
                                            $("#assigned_user_name").val(cbDate);
                                            w.close();
                                        }
                                        else {
                                            w.close();
                                            if (!app.router.refresh()) {
                                                window.location.reload();
                                            }
                                        }
                                    }
                                    app.alert.dismiss('upload');
                                }
                            });
                        }
                    }
                },
                {
                    jtype: 'normal',
                    caption: app.lang.get('LBL_PMSE_BUTTON_CANCEL', 'pmse_Inbox'),
                    cssClasses: ['btn btn-invisible btn-link'],
                    handler: function () {
                        w.close();
                    }
                }
            ],
            labelWidth: 300,
            callback: {
                'loaded': function (data) {
                    casIdField.setValue(casId);
                    casIndexField.setValue(casIndex);
                    f.setProxy(proxy);
                }
            }
        });
        w = new Window({
            width: wWidth,
            height: wHeight,
            modal: true,
            title: wtitle
        });
        w.addPanel(f);
        w.show();
    },

    setEditableFields: function() {
        delete this.editableFields;
        this.editableFields = [];
        var previousField, firstField;
        _.each(this.fields, function(field) {
            if(this.checkReadonly(field)){
                field.def.readonly = true;
            }
            if (field.fields && _.isArray(field.fields)) {
                var that = this;
                var basefield = field;
                _.each(field.fields, function (field) {
                    if (that.checkReadonly(field)) {
                        field.action = "disabled";
                        // Some fields use shouldDisable to enable readonly property,
                        // like 'body' in KBContents
                        if (!_.isUndefined(field.shouldDisable)) {
                            field.setDisabled(true);
                            basefield.def.readonly = true;
                        }
                        return;
                    }
                    // If the field is not readonly, verify if it's required
                    if (that.checkRequired(field)) {
                        field.def.required = true;
                    }
                });
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

    handleSave: function() {
        this.inlineEditMode = false;

        this._saveModel();
        self.$('.record-save-prompt').hide();
        if (!self.disposed) {
            self.render();
        }
    },
    toggleViewButtons: function(isEdit) {
        this.$('.headerpane span[data-type="badge"]').toggleClass('hide', isEdit);
        this.$('.headerpane span[data-type="favorite"]').toggleClass('hide', isEdit);
        this.$('.headerpane span[data-type="follow"]').toggleClass('hide', isEdit);
        this.$('.headerpane .btn-group-previous-next').toggleClass('hide', isEdit);
    },

    _saveModel: function() {
        var options,
            successCallback = _.bind(function() {
                // Loop through the visible subpanels and have them sync. This is to update any related
                // fields to the record that may have been changed on the server on save.
                _.each(this.context.children, function(child) {
                    if (child.get('isSubpanel') && !child.get('hidden')) {
                        child.get('collapsed') ? child.resetLoadFlag(false) : child.reloadData({recursive: false});
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
    },

    /**
     * @override
     */
    bindDataChange: function() {
        this.model.on('change', function(fieldType) {
            if (this.model.isNotEmpty !== true && fieldType !== 'image') {
                this.model.isNotEmpty = true;
                if (!this.disposed) {
                    this.render();
                }
            }
        }, this);
    }

})
