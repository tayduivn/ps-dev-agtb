({
    extends: "DetailView",
    editMode: false,

    initialize: function(options) {
        var extraEvents = {
            "click .record-edit-link": "handleEdit",
            "click .record-save": "handleSave",
            "click .record-cancel": "handleCancel"
        };

        app.view.views.DetailView.prototype.initialize.call(this, options);

        // Re delegate events adding some of our custom
        this.delegateEvents(_.extend(this.events, extraEvents));
        this.meta.type = "detail";

        // Set the save button to show if the model has been edited.
        this.model.on("change", function() {
            if (this.editMode) {
                this.$(".record-save-prompt").show();
            }

            this.previousModelState = this.model.previousAttributes();
        }, this);
    },

    handleEdit: function(e) {
        var target = this.$(e.target),
            field;

        this.editMode = true;
        targetData = target.data();
        field = this.getField(targetData.name);

        switch (field.type) {
            default:
                this.toggleField(field);
        }
    },

    handleSave: function(e) {
        var self = this;

        this.editMode = false;
        this.model.save({}, {
            success: function() {
                self.toggleField();
            }
        });

        this.$(".record-save-prompt").hide();
    },

    handleCancel: function(e) {
        this.editMode = false;

        if (!_.isEmpty(this.previousModelState)) {
            this.model.set(this.previousModelState);
        }
    },

    toggleField: function(field) {
        var self = this;

        function fieldClose(e) {
            self.toggleField(field);

            field.$el.off("focusout", "input", fieldClose);
            field.$el.off("change", "input", fieldClose);
        }

        field.options.viewName = field.options.viewName || null;
        field.options.viewName = (!field.options.viewName || field.options.viewName == "detail")
            ? "edit" : field.options.viewName = "detail";

        field.render();

        if (field.options.viewName == "edit") {
            field.$el.on("focusout", "input", fieldClose);
            field.$el.on("change", "input", fieldClose);
        }
    }
})