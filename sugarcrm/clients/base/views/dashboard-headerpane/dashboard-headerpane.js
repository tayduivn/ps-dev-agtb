/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Views.Base.DashboardHeaderpaneView
 * @alias SUGAR.App.view.views.BaseDashboardHeaderpaneView
 * @extends View.Views.Base.RecordView
 */
({
    extendsFrom: 'RecordView',
    buttons: null,
    editableFields: null,
    className: 'preview-headerbar',
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
        this._super("initialize", [options]);
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

        // if this is a help dashboard, remove the delete button
        if (this.model.get('dashboard_type') === 'help-dashboard') {
            this.$('[name=delete_button]').hide();
        }
    },
    cancelClicked: function(evt) {
        this.changed = false;
        this.model.unset('updated');
        this.clearValidationErrors();
        this.setButtonStates('view');
        this.handleCancel();
        this.model.trigger("setMode", "view");
    },

    /**
     * Compare with last fetched data and return true if model contains changes
     *
     * See {@link app.plugins.view.editable}.
     *
     * @return true if current model contains unsaved changes
     */
    hasUnsavedChanges: function() {
        if (this.model.get('updated')) {
            return true;
        }
        if (this.model.isNew()) {
            return this.model.hasChanged();
        }
        return !_.isEmpty(this.model.changedAttributes(this.model.getSyncedAttributes()));
    },
    saveClicked: function(evt) {
        this.handleSave();
    },
    createCancelClicked: function(evt) {
        if(this.context.parent) {
            this.layout.navigateLayout('list');
        } else {
            app.navigate(this.context);
        }
    },
    deleteClicked: function(evt) {
        this.handleDelete();
    },
    addClicked: function(evt) {
        if(this.context.parent) {
            this.layout.navigateLayout('create');
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
                    self.model.unset('updated');
                    if(self.context.get("create")) {
                        if(self.context.parent) {
                            self.layout.navigateLayout(self.model.id);
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
                    app.alert.show('error_while_save', {
                        level: 'error',
                        title: app.lang.getAppString('ERR_INTERNAL_ERR_MSG'),
                        messages: app.lang.getAppString('ERR_HTTP_500_TEXT')
                    });
                }
            });
        } else {
            this.model.trigger("setMode", "view");
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
        app.alert.show('delete_confirmation', {
            level: 'confirmation',
            messages: app.lang.get('LBL_DELETE_DASHBOARD_CONFIRM', this.module),
            onConfirm: _.bind(function() {
                var message = app.lang.get('LBL_DELETE_DASHBOARD_SUCCESS', this.module, {
                    name: app.lang.get(this.model.get('name'), this.module)
                });
                this.model.destroy({
                    success: _.bind(function() {
                        //dispose safe
                        if (this.disposed) {
                            return;
                        }
                        if (this.context.parent) {
                            this.layout.navigateLayout('list');
                        } else {
                            var route = app.router.buildRoute(this.module);
                            app.router.navigate(route, {trigger: true});
                        }
                    }, this),
                    error: function() {
                        app.alert.show('error_while_save', {
                            level: 'error',
                            title: app.lang.getAppString('ERR_INTERNAL_ERR_MSG'),
                            messages: app.lang.getAppString('ERR_HTTP_500_TEXT')
                        });
                    },
                    //Show alerts for this request
                    showAlerts: {
                        'process': true,
                        'success': {
                            messages: message
                        }
                    }
                });
            }, this)
        });
    },
    bindDataChange: function () {
        //empty out because dashboard header does not need to switch the button sets while model is changed
    },
    toggleEdit: function(isEdit) {
        this.toggleFields(this.editableFields, isEdit);
    }
})
