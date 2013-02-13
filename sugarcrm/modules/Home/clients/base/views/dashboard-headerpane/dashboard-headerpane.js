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
        'click [name=add_button]': 'addClicked'
    },
    initialize: function(options) {
        app.view.views.HeaderpaneView.prototype.initialize.call(this, options);
        this.model.off("change change:layout", null, this);
        this.model.on("change change:layout", function() {
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
        this.previousModelState = JSON.parse(JSON.stringify(this.model.attributes));
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
        app.navigate(this.context);
    },
    deleteClicked: function(evt) {
        this.handleDelete();
    },
    addClicked: function(evt) {
        var route = app.router.buildRoute(this.module, null, 'create');
        app.router.navigate(route, {trigger: true});
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
                        app.navigate(self.context, self.model);
                    } else {
                        self.changed = false;
                        self.setButtonStates('view');
                        self.model.trigger("setMode", "view");
                        self.toggleEdit(false);
                        app.alert.show('dashboard_notice', {level: 'success', title: app.lang.getAppString('LBL_SAVED'), autoClose: true});
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

                var template = Handlebars.compile(app.lang.get('LBL_DELETE_DASHBOARD_SUCCSS', self.module)),
                    message = template({name: self.model.get("name")});

                self.model.destroy({
                    success: function() {
                        app.alert.show('dashboard_notice', {level: 'error', title: message, autoClose: true});
                        var route = app.router.buildRoute(self.module);
                        app.router.navigate(route, {trigger: true});
                    },
                    error: function() {
                        app.alert.show('error_while_save', {level:'error', title: app.lang.getAppString('ERR_INTERNAL_ERR_MSG'), messages: app.lang.getAppString('ERR_HTTP_500_TEXT'), autoClose: true});
                    }
                });
            }
        });
    },
    toggleEdit: function(isEdit) {
        this.toggleFields(this.editableFields, isEdit);
    },
    initButtons: function() {
        app.view.views.RecordView.prototype.initButtons.call(this);
    },
    registerFieldAsButton: function(buttonName) {
        app.view.views.RecordView.prototype.registerFieldAsButton.call(this, buttonName);
    },
    setButtonStates: function(state) {
        app.view.views.RecordView.prototype.setButtonStates.call(this, state);
    },
    setEditableFields: function() {
        app.view.views.RecordView.prototype.setEditableFields.call(this);
    },
    handleValidationError: function(errors) {
        _.each(errors, function (fieldErrors, fieldName) {
            //TODO: Layout UI will change later
            if(fieldName === 'metadata') {
                fieldName = 'layout';
            }

            var field = _.find(this.fields, function(field) {
                return field.name === fieldName;
            });

            if(field) {
                var message = '',
                    $fieldEl = field.getFieldElement();
                if($fieldEl.length > 0) {
                    $fieldEl.addClass("local-error");
                    var tooltipEl = field.$(".error-tooltip[rel=tooltip]");
                    if(tooltipEl.length === 0) {
                        tooltipEl = $('<span class="add-on local error-tooltip" rel="tooltip"><i class="icon-exclamation-sign"></i></span>');
                        $fieldEl.after(tooltipEl);
                    }
                    _.each(fieldErrors, function (errorContext, errorName) {
                        message += app.error.getErrorString(errorName, errorContext);
                    });
                    tooltipEl.attr("data-original-title", message);
                    tooltipEl.tooltip({placement:"bottom", container: "body"});
                }
            }
        }, this);
    }
})
