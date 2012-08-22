({
    extends: "DetailView",
    editMode: false,

    initialize: function(options) {
        var extraEvents = {
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
            if (this.editMode) {
                this.$(".record-save-prompt").show();
            }

            this.previousModelState = this.model.previousAttributes();
        }, this);
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
            default:
                this.toggleField(field, target);
        }
    },

    handleSave: function(e) {
        var self = this;

        this.editMode = false;
        this.model.save({}, {
            success: function() {
                self.render();
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
            next.$el.focus();
        } else if (e.which == 27) {
            this.fieldClose(e, field, target);
        }
    }
})