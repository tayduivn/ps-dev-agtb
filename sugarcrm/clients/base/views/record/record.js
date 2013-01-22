({
    inlineEditMode: false,
    createMode: false,
    previousModelState: null,
    extendsFrom: 'EditableView',

    enableHeaderPane: true,
    events: {
        'click .record-edit-link-wrapper': 'handleEdit',
        'click a[name=cancel_button]': 'cancelClicked',
        'click .more': 'toggleMoreLess',
        'click .less': 'toggleMoreLess'
    },
    // button fields defined in view definition
    buttons: {},

    // button states
    STATE: {
        EDIT: 'edit',
        VIEW: 'view'
    },

    // current button states
    currentState: null,

    initialize: function(options) {
        _.bindAll(this);

        app.view.views.EditableView.prototype.initialize.call(this, options);

        this.createMode = this.context.get("create") ? true : false;
        this.action = this.createMode ? 'edit' : 'detail';

        // Set the save button to show if the model has been edited.
        this.model.on("change", function() {
            if (this.inlineEditMode) {
                this.previousModelState = this.model.previousAttributes();
                this.setButtonStates(this.STATE.EDIT);
            }
        }, this);
        this.context.on("change:record_label", this.setLabel, this);

        this.delegateButtonEvents();

        if (this.createMode) {
            this.model.isNotEmpty = true;
        }
    },

    setLabel: function(context, value) {
        this.$(".record-label[data-name=" + value.field + "]").text(value.label);
    },

    delegateButtonEvents: function() {
        this.context.on('button:edit_button:click', this.editClicked, this);
        this.context.on('button:save_button:click', this.saveClicked, this);
        this.context.on('button:delete_button:click', this.deleteClicked, this);
        this.context.on('button:duplicate_button:click', this.duplicateClicked, this);
    },

    _render: function() {
        var totalFieldCount = 0;

        _.each(this.meta.panels, function(panel) {
            var columns = (panel.columns) || 1,
                rows = [],
                row = [],
                size = panel.fields.length;

            // Set flag so that show more link can be displayed to show hidden panel.
            if (panel.hide) {
                this.hiddenPanelExists = true;
            }

            _.each(panel.fields, function(field, index) {
                var maxSpan;

                //labels: visibility for the label
                //labelsOnTop: true for on the top of the field
                //             false for on the left of the field
                if (_.isUndefined(panel.labels)) {
                    panel.labels = true;
                }
                //8 for span because we are using a 2/3 ratio between field span and label span with a max of 12
                maxSpan = (panel.labelsOnTop === false && panel.labels) ? 8 : 12;

                if (_.isUndefined(field.span)) {
                    field.span = Math.floor(maxSpan / columns);
                }

                //4 for label span because we are using a 1/3 ratio between field span and label span with a max of 12
                if (_.isUndefined(field.labelSpan)) {
                    field.labelSpan = Math.floor(4 / columns);
                }

                totalFieldCount++;
                field.index = totalFieldCount;
                row.push(field);

                if ((index % columns === columns - 1) || (index === size - 1)) {
                    rows.push(row);
                    row = [];
                }
            }, this);

            panel.grid = rows;
        }, this);

        app.view.View.prototype._render.call(this);
        this.initButtons();
        this.setButtonStates(this.STATE.VIEW);
        this.setEditableFields();
    },

    setEditableFields: function() {
        delete this.editableFields;
        this.editableFields = [];

        var previousField, firstField;
        _.each(this.fields, function(field, index) {
            if ( field.type === "img" || field.type === "buttondropdown" || field.parent || (field.name && this.buttons[field.name])) {
                return;
            }
            if(previousField) {
                previousField.nextField = field;
            } else {
                firstField = field;
            }
            previousField = field;
            this.editableFields.push(field);
        }, this);
        if(previousField) {
            previousField.nextField = firstField;
        }
    },

    initButtons: function() {
        if(this.options.meta && this.options.meta.buttons) {
            _.each(this.options.meta.buttons, function(button) {
                if (button.type === 'buttondropdown') {
                    _.each(button.buttons, function(dropdownButton) {
                        this.registerFieldAsButton(dropdownButton.name);
                    }, this);
                } else if (button.type === 'button') {
                    this.registerFieldAsButton(button.name);
                }
            }, this);
        }
    },

    registerFieldAsButton: function(buttonName) {
        var button = this.getField(buttonName);
        if (button) {
            this.buttons[buttonName] = button;
        }
    },

    _renderHtml: function() {
        this.checkAclForButtons();
        app.view.View.prototype._renderHtml.call(this);
    },

    /**
     * Check to see if the buttons should be displayed
     */
    checkAclForButtons: function() {
        if (this.context.get("model").module === "Users") {
            this.hasAccess = (app.user.get("id") == this.context.get("model").id);
        } else if (this.createMode) {
            this.hasAccess = true;
        } else {
            this.hasAccess = app.acl.hasAccessToModel("edit", this.model);
        }
    },

    toggleMoreLess: function() {
        this.$(".less").toggleClass("hide");
        this.$(".more").toggleClass("hide");
        this.$(".panel_hidden").toggleClass("hide");
    },

    bindDataChange: function() {
        if (this.model) {
            this.model.on("change", function() {
                if (this.model.isNotEmpty !== true) {
                    this.model.isNotEmpty = true;
                    this.render();
                }
            }, this);
        }
    },

    duplicateClicked: function() {
        app.cache.set("duplicate"+this.module, this.model.attributes);
        this.layout.trigger("drawer:create:fire", {
            components: [{
                layout : 'create',
                context: {
                    create: true
                }
            }]
        }, this);
    },
    
    editClicked: function() {
        this.previousModelState = this.model.previousAttributes();
        this.setButtonStates(this.STATE.EDIT);
        this.toggleEdit(true);
    },

    saveClicked: function() {
        this.setButtonStates(this.STATE.VIEW);
        this.handleSave();
    },

    cancelClicked: function() {
        this.setButtonStates(this.STATE.VIEW);
        this.handleCancel();
    },

    deleteClicked: function() {
        this.handleDelete();
    },

    /**
     * Render fields into either edit or view mode.
     * @param isEdit
     */
    toggleEdit: function(isEdit) {
        if (isEdit) {
            this.$('.record-edit-link-wrapper').hide();
        } else {
            this.$('.record-edit-link-wrapper').show();
        }

        this.toggleFields(this.editableFields, isEdit);
    },

    /**
     * Handler for intent to edit. This handler is called both as a callback from click events, and also
     * triggered as part of tab focus event.
     * @param e {Event} jQuery Event object (should be from click)
     * @param cell {jQuery Node} cell of the target node to edit
     */
    handleEdit: function(e, cell) {
        var target,
            cellData,
            field;

        if (e) { // If result of click event, extract target and cell.
            target = this.$(e.target);
            cell = target.parents(".record-cell");
        }

        cellData = cell.data();
        field = this.getField(cellData.name);

        // Set Editing mode to on.
        this.inlineEditMode = true;

        // TODO: Refactor this for fields to support their own focus handling in future.
        // Add your own field type handling for focus / editing here.
        switch (field.type) {
            case "img":
                break;
            default:
                this.toggleField(field);
        }
    },

    handleSave: function() {
        var self = this;
        this.inlineEditMode = false;

        this.model.save({}, {
            success: function() {
                if (self.createMode) {
                    app.navigate(self.context, self.model);
                } else {
                    self.render();
                }
            }
        });

        this.$(".record-save-prompt").hide();
        this.render();
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
            messages: app.lang.get('NTC_DELETE_CONFIRMATION'),
            onConfirm: function() {
                self.model.destroy();
                app.router.navigate("#" + self.module, {trigger: true});
            }
        });
    },

    handleKeyDown: function(e, field) {
        app.view.views.EditableView.prototype.handleKeyDown.call(this, e, field);
        var nextCell,
            index = field.$el.parent().data("index");

        if (e.which == 9) { // If tab
            e.preventDefault();
            field.$(field.fieldTag).trigger("change");
            if(field.nextField) {
                this.toggleField(field, false);
                this.toggleField(field.nextField, true);
            }
        }
    },

    /**
     * Show/hide buttons depending on the state defined for each buttons in the metadata
     * @param state
     */
    setButtonStates: function(state) {
        this.currentState = state;

        _.each(this.buttons, function(field) {
            if (_.isUndefined(field.showOn()) || (field.showOn() === state)) {
                field.show();
            } else {
                field.hide();
            }
        });
    },

    /**
     * Set the title in the header pane
     * @param title
     */
    setTitle: function(title) {
        var $title = this.$('.headerpane h1.title');
        if ($title.length > 0) {
            $title.text(title);
        } else {
            this.$('.headerpane').prepend('<h1 class="title">' + title + '</h1>');
        }
    }
})
