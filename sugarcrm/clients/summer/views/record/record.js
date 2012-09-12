({
    extendsFrom: "DetailView",
    editMode: false,

    initialize: function(options) {
        test = this;
        var extraEvents = {
            "click .record-edit": "toggleEdit",
            "click .record-edit-link-wrapper": "handleEdit",
            "click .record-save": "handleSave",
            "click .record-cancel": "handleCancel"
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

    getNextField: function(index) {
        console.log("Grabbing next field", index);
        var nextIndex = index + 1,
            nextField = this.$(".index" + nextIndex),
            fieldName = nextField.data("fieldname");
        console.log("Nextfield", nextField, fieldName);
        return (fieldName) ? this.getField(fieldName) : false;
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

    handleEdit: function(e, field) {
        var target,
            cell;

        console.log("Handling edit(): ", e, field);

        // This would be the default code path unless tabbed. Only e event object
        // should be supplied by the click event.
        if (!field) {
            target = this.$(e.target);
            cell = target.parents(".record-cell");
            cellData = cell.data();
            field = this.getField(cellData.name);
        } else { // This is the tab field code path
            cell = field.$el.parent().find(".record-cell");
        }

        // Set Editing mode to on.
        this.editMode = true;

        switch (field.type) {
            case "img":
                break;
            case "fieldset":
                // If it is a field set, we need all the fields to switch to edit mode.
                this.toggleCell(field, cell);
                cell.first("input").focus().val(cell.first("input").val());
                break;
            default:
                console.log("input focusin", cell.find("input"), cell);
                this.toggleCell(field, cell);
                cell.find("input").focus().val(cell.find("input").val());
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
    },

    /**
     * Handles the toggling of fields within itself. Also binds change and focus
     * listeners.
     * @param field {View.Field} Field or fieldset
     * @param cell {jQuery Node} Current target cell
     */
    toggleCell: function(field, cell, close) {
        var fields = field.fields || [field];

        if (field.options.viewName != "edit") {
//            cell.on("focusout.record", {field: field, cell: cell}, this.fieldClose);

            $(document).on("mousedown.record" + field.name, {field: field, cell: cell}, this.fieldClose);
            console.log("binding mousedown.record" + field.name);
        }

        _.each(fields, function(field) {
            this.toggleField(field, cell, close);
        }, this);
    },

    /**
     * Switches each individual field between detail and edit modes
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
            field.$el.on("change.record", "input", {field: field, cell: cell}, this.fieldClose);
            field.$el.on("keydown.record", "input", {field: field, cell: cell}, this.handleKeyDown);
        } else if (close) {
            $(document).off("mousedown.record" + field.name);
//            cell.off("focusout.record");
            field.$el.off("change.record");
            field.$el.off("keydown.record");
        }
    },

    fieldClose: function(e) {
        var self = this,
            cell = e.data.cell,
            field = e.data.field;

        if (field.options.viewName == "detail") {
            return;
        }

        if (e.type == "mousedown") {
            var currFieldParent = $(cell),
                targetParent = self.$(e.target).parents(".record-cell");

            if (currFieldParent[0] == targetParent[0]) {
                return;
            }
        }
        if (currFieldParent.data("type") == "fieldset") {
            self.toggleCell(field, cell, true);
        } else {
            self.toggleField(field, cell, true);
        }

    },

    handleKeyDown: function(e) {
        var next,
            cell = e.data.cell,
            field = e.data.field,
            index = field.$el.parent().data("index");

        if (e.which == 9) { // If tab
            next = this.getNextField(index);
            console.log("tabbed", next, cell);
            this.toggleField(field, cell, true);
            this.handleEdit(null, next);
        } else if (e.which == 27) {
            this.fieldClose(e, field, cell);
        }
    }
})