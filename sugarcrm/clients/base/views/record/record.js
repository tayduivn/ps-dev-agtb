({
    inlineEditMode: false,
    createMode: false,
    previousModelState: null,

    events: {
        'click .record-edit-link-wrapper': 'handleEdit',
        'click a[name=cancel_button]': 'cancelClicked',
        'click .more': 'toggleMoreLess',
        'click .less': 'toggleMoreLess'
    },

    // button states
    STATE: {
        EDIT: 'edit',
        VIEW: 'view'
    },

    initialize: function(options) {
        _.bindAll(this);

        app.view.View.prototype.initialize.call(this, options);

        this.createMode = this.context.get("create") ? true : false;

        // Set the save button to show if the model has been edited.
        this.model.on("change", function() {
            if (this.inlineEditMode) {
                this.previousModelState = this.model.previousAttributes();
                this.setButtonStates(this.STATE.EDIT);
            }
        }, this);

        this.context.on('button:edit_button:click', this.editClicked, this);
        this.context.on('button:save_button:click', this.saveClicked, this);
        this.context.on('button:delete_button:click', this.deleteClicked, this);
        this.context.on('button:duplicate_button:click', this.duplicateClicked, this);

        if (this.createMode) {
            this.model.isNotEmpty = true;
        }
    },

    render: function() {
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

                if (_.isUndefined(panel.labels)) {
                    panel.labels = true;
                }
                //8 for span because we are using a 2/3 ratio between field span and label span with a max of 12
                maxSpan = (panel.labels) ? 8 : 12;

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

        app.view.View.prototype.render.call(this);
        this.setButtonStates(this.STATE.VIEW);

        // Check if this is a new record, if it is, enable the edit view
        if (this.createMode && this.model.isNew()) {
            this.toggleEdit(true);
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

    /**
     * Returns the next cell. If the current cell has more "inner focus elements", check to see if
     * we are at the end of that cell's last focus element.
     * @param index {Number} Index number of the current field.
     * @param field {Field} Current field that is in focus.
     * @param cell {Cell} Cell that the current field belongs to.
     * @return {*}
     */
    getNextCell: function(index, field, cell) {
        var nextIndex = index + 1,
            nextFieldEl = this.$(".index" + nextIndex),
            fieldName = nextFieldEl.data("fieldname"),
            nextField = this.getField(fieldName),
            nextCell = nextField.$el.parents(".record-cell");

        // Check to see if field has parent (usually used to get the fieldset parent of the current field).
        field = field.parent || field;

        // If the fieldset, check if it has more "inner fields" before getting the next field.
        if (field.focus && field.focus()) {
            return cell;
        } else if (cell[0] !== nextCell[0]) {
            return nextField.$el.parents(".record-cell");
        }

        return false;
    },

    duplicateClicked: function() {
        app.cache.set("duplicate"+this.module, this.model.attributes);
        app.router.navigate("#"+this.module+"/create", {trigger: true});
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
        _.each(this.fields, function(field) {
            // Exclude image picker, buttons, and button dropdowns
            // This is just a stop gap solution.
            if ((field.type == "img") || (field.type === 'button') || (field.type === 'buttondropdown')) {
                return;
            }

            if (isEdit) {
                field.options.viewName = "edit";
                this.$('.record-edit-link-wrapper').hide();
            } else {
                field.options.viewName = "detail";
                this.$('.record-edit-link-wrapper').show();
           }

            field.render();
        }, this);
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
            case "fieldset":
                this.toggleCell(field, cell);
                if (field.focus) {
                    field.focus();
                } else {
                    // If it is a field set, we need all the fields to switch to edit mode.
                    cell.find("input").first().focus().val(cell.find("input").first().val());
                }
                break;
            default:
                this.toggleCell(field, cell);
                field.$el.find("input").focus().val(field.$el.find("input").val());
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

    /**
     * Toggles a cell into editing or detail mode. This should be the entry point function.
     * @param field {View.Field} Field or fieldset to toggle
     * @param cell {jQuery Node} Current target cell
     */
    toggleCell: function(field, cell, close) {
        var fields;

        // If field is part of a fieldset, set the field to fieldset.
        field = (field.parent) ? field.parent : field;
        fields = field.fields || [field];

        if (field.options.viewName != "edit" && !close) { // About to be switched to edit
            $(document).on("mousedown.record" + field.name, {field: field, cell: cell}, this.fieldClose);
        }

        if (close) {
            $(document).off("mousedown.record" + field.name);
        }

        _.each(fields, function(field) {
            this.toggleField(field, cell, close);
        }, this);
    },

    /**
     * Switches each individual field between detail and edit modes. This method should not be called directly,
     * instead call toggleCell.
     * @param field {View.Field} Field that needs to be toggled
     * @param cell {jQuery Node} Cell that field belongs in
     * @param close {Boolean} Force into detail mode
     */
    toggleField: function(field, cell, close) {
        cell.toggleClass('edit-mode');

        var viewName = ((!field.options.viewName || field.options.viewName == "detail") && !close)
            ? "edit" : "detail";

        field.setViewName(viewName);

        field.render();

        if (field.options.viewName == "edit") {
            field.$el.on("keydown.record", "input", {field: field, cell: cell}, this.handleKeyDown);
        } else if (close) {
            field.$el.off("keydown.record");
        }
    },

    fieldClose: function(e) {
        var self = this,
            cell = e.data.cell,
            field = e.data.field,
            currFieldParent,
            targetParent;

        if (field.options.viewName == "detail") {
            return;
        }

        currFieldParent = $(cell);
        targetParent = self.$(e.target).parents(".record-cell");
        field.$el.find("input").trigger("change");

        if (currFieldParent[0] == targetParent[0]) {
            return;
        }

        self.toggleCell(field, cell, true);
    },

    handleKeyDown: function(e) {
        var nextCell,
            cell = e.data.cell,
            field = e.data.field,
            index = field.$el.parent().data("index");

        if (e.which == 9) { // If tab
            e.preventDefault();
            field.$el.find("input").trigger("change");
            nextCell = this.getNextCell(index, field, cell);

            if (nextCell && (nextCell[0] !== cell[0])) { // Next tab element not within same cell
                this.toggleCell(field, cell, true);
                this.handleEdit(null, nextCell);
            }
        } else if (e.which == 27) { // If esc
            this.toggleCell(field, cell, true);
        }
    },

    /**
     * Change the behavior of buttons depending on the state that they should be in
     * @param state
     */
    setButtonStates: function(state) {
        var $buttons = {
            edit: this.getField('edit_dropdown'),
            save: this.getField('save_dropdown'),
            cancel: this.getField('cancel_button')
        };

        switch (state) {
            case this.STATE.EDIT:
                if ($buttons.edit) {
                    $buttons.edit.hide();
                }
                if ($buttons.save) {
                    $buttons.save.show();
                }
                if ($buttons.cancel) {
                    $buttons.cancel.show();
                }
                break;
            case this.STATE.VIEW:
                if ($buttons.edit) {
                    $buttons.edit.show();
                }
                if ($buttons.save) {
                    $buttons.save.hide();
                }
                if ($buttons.cancel) {
                    $buttons.cancel.hide();
                }
                break;
            default:
                if ($buttons.edit) {
                    $buttons.edit.hide();
                }
                if ($buttons.save) {
                    $buttons.save.hide();
                }
                if ($buttons.cancel) {
                    $buttons.cancel.hide();
                }
                break;
        }
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
