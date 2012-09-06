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
                var cell = {};

                if (_.isUndefined(panel.labels)) panel.labels = true;
                //8 for span because we are using a 2/3 ratio between field span and label span with a max of 12
                maxSpan = (panel.labels) ? 8 : 12;
                if (_.isUndefined(field.span)) field.span = Math.floor(maxSpan / columns);
                //4 for label span because we are using a 1/3 ratio between field span and label span with a max of 12
                if (_.isUndefined(field.labelSpan)) field.labelSpan = Math.floor(4 / columns);

                // Setup our grid 'compartment' array
                cell.fields = (field.type == "fieldset") ? field.fields : [field];
                cell.name = field.name;
                cell.span = field.span;
                cell.label = field.label;
                cell.labelSpan = field.labelSpan;
                cell.noedit = field.noedit;

                _.each(cell.fields, function(field) {
                    if (field.name) {
                        field.index = totalFieldCount;
                        totalFieldCount++;
                    }
                    if (panel.placeholders) field.placeholder = field.label;
                });

                row.push(cell);

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
        var nextIndex = index + 1,
            nextField = this.$(".index" + nextIndex),
            fieldName = nextField.data("fieldname");

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
            fields;

        // This would be the default code path unless tabbed.
        if (!field) {
            target = this.$(e.target);
            targetData = target.data();
            field = this.getField(targetData.name);
        } else {
            target = field.$el.parent().find(".record-edit-link");
        }

        // Set Editing mode to on.
        this.editMode = true;

        switch (field.type) {
            case "img":
                break;
            case "fieldset":
                // If it is a field set, we need all the fields to switch to edit mode.
                console.log("FIELD SET", field);
                fields = field.fields();
                target.parent().parent().find("input").focus().val(target.parent().find("input").val());
                break;
            default:
                this.toggleField(field, target);
                target.parent().parent().find("input").focus().val(target.parent().find("input").val());
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

    handleCancel: function(e) {
        this.editMode = false;

        if (!_.isEmpty(this.previousModelState)) {
            this.model.set(this.previousModelState);
        }
    },

    toggleField: function(field, target) {
        $(target).closest('.record-row').toggleClass('edit-mode');

        field.options.viewName = (!field.options.viewName || field.options.viewName == "detail")
            ? "edit" : field.options.viewName = "detail";

        field.render();

        if (field.options.viewName == "edit") {
            field.$el.on("blur", "input", {field: field, target: target}, this.fieldClose);
            field.$el.on("change", "input", {field: field, target: target}, this.fieldClose);
            field.$el.on("keydown", "input", {field: field, target: target}, this.handleKeyDown);
        }
    },

    fieldClose: function(e) {
        var target = e.data.target,
            field = e.data.field;

        if (field.options.viewName == "detail") {
            return;
        }

        this.toggleField(field, target);

        field.$el.off("blur", "input", this.fieldClose);
        field.$el.off("change", "input", this.fieldClose);
        field.$el.off("keydown", "input", this.handleKeyDown);
    },

    handleKeyDown: function(e) {
        var next,
            target = e.data.target,
            field = e.data.field,
            index = field.$el.parent().data("index");

        if (e.which == 9) {
            next = this.getNextField(index);
            this.handleEdit(null, next);
        } else if (e.which == 27) {
            this.fieldClose(e, field, target);
        }
    }
})