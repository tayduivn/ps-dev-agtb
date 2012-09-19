({
    extendsFrom: "DetailView",
    editMode: false,

    initialize: function(options) {
        test = this;
        var extraEvents = {
            "click .record-edit": "toggleEdit",
            "click .record-edit-link-wrapper": "handleEdit",
            "click .record-save": "handleSave",
            "click .record-cancel": "handleCancel",
            "click .record-delete": "handleDelete"
        };

        _.bindAll(this);

        app.view.views.DetailView.prototype.initialize.call(this, options);

        // Re delegate events adding some of our custom
        this.delegateEvents(_.extend(this.events, extraEvents));

        // Set the save button to show if the model has been edited.
        this.model.on("change", function() {
            if (true || this.editMode) {
                this.$(".record-save-prompt").show();
            }

            this.previousModelState = this.model.previousAttributes();
        }, this);

        if (this.context.get("create") === true) {
            this.model.isNotEmpty = true;
        }
    },

    render: function() {
        var totalFieldCount = 0;

        _.each(this.meta.panels, function(panel) {
            var columns = (panel.columns) || 1,
                count = 0,
                rows = [],
                row = [];

            _.each(panel.fields, function(field) {
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

                if (count % columns == columns - 1) {
                    rows.push(row);
                    row = [];
                }

                count++;
            }, this);

            panel.grid = rows;
        }, this);

        app.view.View.prototype.render.call(this);

        // Check if this is a new record, if it is, enable the edit view
        if (this.context.has("create") && this.model.isNew) {
            this.editAllMode = false;
            this.toggleEdit();
        }
    },

    // Overloaded functions
    _renderHtml: function() { // Use original original
        app.view.View.prototype._renderHtml.call(this);
    },

    toggleMoreLess: function() {
        this.$(".less").toggleClass("hide");
        this.$(".more").toggleClass("hide");
        this.$(".panel_hidden").toggleClass("hide");
    },

    bindDataChange: function() {
        if (this.model) {
            this.model.on("change", function() {
                if (this.context.get('subnavModel')) {
                    this.context.get('subnavModel').set({
                        'title': this.model.get('name'),
                        'meta': this.meta
                    });

                    if (this.model.isNotEmpty !== true) {
                        this.model.isNotEmpty = true;
                        this.render();
                    }
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

    // Handler functions
    toggleEdit: function() {
        _.each(this.fields, function(field) {

            // Exclude image picker,
            // This is just a stop gap solution.
            if (field.type == "img") {
                return;
            }

            field.options.viewName = (!this.editAllMode) ? "edit" : "detail";
            field.render();
        }, this);

        this.editAllMode = (this.editAllMode) ? false : true;
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
        this.editMode = true;

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

        this.editMode = false;
        this.model.save({}, {
            success: function() {
                if (self.context.get("create") === true) {
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
        this.editMode = false;

        if (!_.isEmpty(this.previousModelState)) {
            this.model.set(this.previousModelState);
        }

        this.toggleEdit();
    },

    handleDelete: function() {
        // Open up a modal
        var self = this,
            modal = this.$(".delete-confirmation").modal();

        this.$(".confirm-delete").on("click", function() {
            self.model.destroy();
            app.router.navigate("#" + self.module, {trigger: true});
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

        field.options.viewName = ((!field.options.viewName || field.options.viewName == "detail") && !close)
            ? "edit" : field.options.viewName = "detail";

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
            nextCell = this.getNextCell(index, field, cell);

            if (nextCell && (nextCell[0] !== cell[0])) { // Next tab element not within same cell
                this.toggleCell(field, cell, true);
                this.handleEdit(null, nextCell);
            }

            e.preventDefault();

            // Since we prevented the default we still need to trigger a change.
            field.$el.find("input").trigger("change");
        } else if (e.which == 27) { // If esc
            this.toggleCell(field, cell, true);
        }
    }
})