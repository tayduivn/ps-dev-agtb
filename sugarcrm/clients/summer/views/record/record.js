({
    extendsFrom: "DetailView",
    editMode: false,

    initialize: function(options) {
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

        var panels = this.meta.panels;
        var index = 0;

        for (var i in panels) {
            var columns = (panels[i].columns) ? panels[i].columns : 1;
            var count = 0;
            var rows = [];
            var row = [];
            for (var j in panels[i].fields) {
                if (panels[i].placeholders)panels[i].fields[j].placeholder = panels[i].fields[j].label;
                if (_.isUndefined(panels[i].labels))panels[i].labels = true;
                //8 for span because we are using a 2/3 ratio between field span and label span with a max of 12
                maxSpan = (panels[i].labels) ? 8 : 12;
                if (_.isUndefined(panels[i].fields[j].span))panels[i].fields[j].span = Math.floor(maxSpan / columns);
                //4 for label span because we are using a 1/3 ratio between field span and label span with a max of 12
                if (_.isUndefined(panels[i].fields[j].labelSpan))panels[i].fields[j].labelSpan = Math.floor(4 / columns);
                row.push(panels[i].fields[j]);
                if (count % columns == columns - 1) {
                    rows.push(row);
                    row = [];
                }
                count++;
            }
            if (i == 0) {
                this.fieldsToDisplay = count;
                console.log('fieldsToDsiplay', count);
            }
            rows.push(row);
            row = [];
            panels[i].grid = rows;
        }

        this.meta.panels = panels;

        console.log(this.meta);
        app.view.views.DetailView.prototype.render.call(this);

    },

    // Overloaded functions
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

    getFieldIndex: function(field) {
        return _.indexOf(_.pluck(this.options.meta.panels[0].fields, "name"), field.name);
    },

    getNextField: function(field) {
        var nextField = this.options.meta.panels[0].fields[this.getFieldIndex(field) + 1];
        return (nextField) ? this.getField(nextField.name) : false;
    },

    // Handler functions
    toggleEdit: function(e) {
        console.log("Toggle Edit", this.editAllMode);
        _.each(this.fields, function(field) {
            if (field.type == "relate") { return; }

            field.options.viewName = (!this.editAllMode) ? "edit" : "detail";
            field.render();
        }, this);

        this.editAllMode = (this.editAllMode) ? false : true;
    },

    handleEdit: function(e, field) {
        var target;

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
            case "relate":
                break;
            default:
                this.toggleField(field, target);
                target.parent().find("input").focus().val(target.parent().find("input").val());
        }
    },

    handleSave: function() {
        var self = this;

        this.editMode = false;
        this.model.save({}, {
            success: function(model) {
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
        var self = this;

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
            field = e.data.field;

        if (e.which == 9) {
            next = this.getNextField(field);
            this.handleEdit(null, next);
            //next.$el.focus();
        } else if (e.which == 27) {
            this.fieldClose(e, field, target);
        }
    }
})