({
    extendsFrom: 'EditableView',
    buttons: null,
    editableFields: null,
    events: {
        'click [name=edit_button]' : 'editClicked',
        'click [name=cancel_button]' : 'cancelClicked',
        'click [name=save_button]' : 'saveClicked',
        'click [name=create_button]' : 'saveClicked',
        'click [name=create_cancel_button]' : 'createCancelClicked',
        'click [name=delete_button]' : 'deleteClicked',
        'click [name=add_button]': 'addClicked',
        'click [name=collapse_button]': 'collapseClicked',
        'click [name=expand_button]': 'expandClicked'
    },
    initialize: function(options) {
        if(options.context.parent) {
            options.meta = app.metadata.getView(options.context.parent.get("module"), options.name);
            options.template = app.template.getView(options.name);
        }
        // TODO: Calling "across controllers" considered harmful .. please consider using a plugin instead.
        app.view.invokeParent(this, {type: 'view', name: 'headerpane', method: 'initialize', args:[options]});
        this.model.on("change change:layout change:metadata", function() {
            if (this.inlineEditMode) {
                this.changed = true;
            }
        }, this);
        this.model.on("error:validation", this.handleValidationError, this);

        if(this.context.get("create")) {
            this.changed = true;
            this.action = 'edit';
            this.inlineEditMode = true;
        } else {
            this.action = 'detail';
        }
        this.buttons = {};
    },
    editClicked: function(evt) {
        this.previousModelState = app.utils.deepCopy(this.model.attributes);
        this.inlineEditMode = true;
        this.setButtonStates('edit');
        this.toggleEdit(true);
        this.model.trigger("setMode", "edit");
    },
    cancelClicked: function(evt) {
        this.changed = false;
        this.setButtonStates('view');
        this.handleCancel();
        this.model.trigger("setMode", "view");
    },
    saveClicked: function(evt) {
        this.handleSave();
    },
    createCancelClicked: function(evt) {
        if(this.context.parent) {
            this.model.dashboardLayout.navigateLayout('list');
        } else {
            app.navigate(this.context);
        }
    },
    deleteClicked: function(evt) {
        this.handleDelete();
    },
    addClicked: function(evt) {
        if(this.context.parent) {
            this.model.dashboardLayout.navigateLayout('create');
        } else {
            var route = app.router.buildRoute(this.module, null, 'create');
            app.router.navigate(route, {trigger: true});
        }
    },
    collapseClicked: function(evt) {
        this.context.trigger("dashboard:collapse:fire", true);
    },
    expandClicked: function(evt) {
        this.context.trigger("dashboard:collapse:fire", false);
    },
    _render: function() {
        app.view.View.prototype._render.call(this);

        this.initButtons();
        this.setButtonStates(this.context.get("create") ? 'create' : 'view');
        this.setEditableFields();
    },
    handleSave: function() {
        this.inlineEditMode = false;
        var self = this;
        if(this.changed) {
            this.model.save({}, {
                //Show alerts for this request
                showAlerts: true,
                fieldsToValidate: {
                    'name' : {
                        required: true
                    },
                    'metadata' : {
                        required: true
                    }
                },
                success: function() {
                    if(self.context.get("create")) {
                        if(self.context.parent) {
                            self.model.dashboardLayout.navigateLayout(self.model.id);
                        } else {
                            app.navigate(self.context, self.model);
                        }
                    } else {
                        self.changed = false;
                        self.setButtonStates('view');
                        self.model.trigger("setMode", "view");
                        self.toggleEdit(false);
                    }
                },
                error: function() {
                    app.alert.show('error_while_save', {level:'error', title: app.lang.getAppString('ERR_INTERNAL_ERR_MSG'), messages: app.lang.getAppString('ERR_HTTP_500_TEXT'), autoClose: true});
                }
            });
        } else {
            this.setButtonStates('view');
            this.toggleEdit(false);
        }
    },
    handleCancel: function() {
        this.inlineEditMode = false;
        if (!_.isEmpty(this.previousModelState)) {
            this.model.set(this.previousModelState);
        }
        this.toggleEdit(false);
    },
    handleDelete: function() {
        var self = this;
        app.alert.show('delete_confirmation', {
            level: 'confirmation',
            messages: app.lang.get('LBL_DELETE_DASHBOARD_CONFIRM', this.module),
            onConfirm: function() {
                var message = app.lang.get('LBL_DELETE_DASHBOARD_SUCCESS', self.module, {name: self.model.get("name")});

                self.model.destroy({
                    success: function() {
                        if(self.context.parent) {
                            self.model.dashboardLayout.navigateLayout('list');
                        } else {
                            var route = app.router.buildRoute(self.module);
                            app.router.navigate(route, {trigger: true});
                        }
                    },
                    error: function() {
                        app.alert.show('error_while_save', {level:'error', title: app.lang.getAppString('ERR_INTERNAL_ERR_MSG'), messages: app.lang.getAppString('ERR_HTTP_500_TEXT'), autoClose: true});
                    },
                    //Show alerts for this request
                    showAlerts: {
                        'process': true,
                        'success': {
                            messages: message
                        }
                    }
                });
            }
        });
    },
    toggleEdit: function(isEdit) {
        this.toggleFields(this.editableFields, isEdit);
    },
    initButtons: function() {
        // TODO: Calling "across controllers" considered harmful .. please consider using a plugin instead.
        app.view.invokeParent(this, {type: 'view', name: 'record', method: 'initButtons'});
    },
    registerFieldAsButton: function(buttonName) {
        // TODO: Calling "across controllers" considered harmful .. please consider using a plugin instead.
        app.view.invokeParent(this, {type: 'view', name: 'record', method: 'registerFieldAsButton', args: [buttonName]});
    },
    setButtonStates: function(state) {
        // TODO: Calling "across controllers" considered harmful .. please consider using a plugin instead.
        app.view.invokeParent(this, {type: 'view', name: 'record', method: 'setButtonStates', args: [state]});
    },
    setEditableFields: function() {
        // TODO: Calling "across controllers" considered harmful .. please consider using a plugin instead.
        app.view.invokeParent(this, {type: 'view', name: 'record', method: 'setEditableFields'});
    },
    _dispose: function() {
        _.each(this.editableFields, function(field) {
            field.nextField = null;
        });
        this.buttons = null;
        this.editableFields = null;
        app.view.invokeParent(this, {type: 'view', name: 'editable', method: '_dispose'});
    }

})
