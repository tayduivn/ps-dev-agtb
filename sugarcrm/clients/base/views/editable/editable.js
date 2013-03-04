({
    /**
     * Switches entire fields between detail and edit modes.
     *
     * @params fields {Array} Fields that needs to be toggled
     * @param isEdit {Boolean} Force into edit mode
     */
    toggleFields: function(fields, isEdit) {
        var viewName = (isEdit) ? 'edit' : this.action;
        _.each(fields, function(field) {
            if(field.action == viewName){
                return; //don't toggle if it's the same
            }
            var meta = this.getFieldMeta(field.name);
            if (meta && isEdit && meta.readonly) {
                return;
            }

            _.defer(function(field){
                field.setMode(viewName);
            }, field);

            field.$el.off("keydown.record");
            $(document).off("mousedown.record" + field.name);
        }, this);
    },

    /**
     * Switches each individual field between detail and edit modes.
     *
     * @param field {View.Field} Field that needs to be toggled
     * @param cell {jQuery Node} Cell that field belongs in
     * @param isEdit {Boolean} Force into edit mode
     */
    toggleField: function(field, isEdit) {
        var viewName;

        if(_.isUndefined(isEdit)) {
            viewName = (field.tplName === this.action) ? "edit" : this.action;
        } else {
            viewName = (isEdit) ? "edit" : this.action;
        }

        field.setMode(viewName);

        if (viewName === "edit") {
            var self = this;

            if (_.isFunction(field.focus)) {
                field.focus();
            } else {
                var $el = field.$(field.fieldTag + ":first");
                $el.focus().val($el.val());
            }

            if (field.type !== 'image') {
                field.$el.on("keydown.record", function(evt) {
                    self.handleKeyDown.call(self, evt, field);
                });
                $(document).on("mousedown.record" + field.name, _.debounce(function(evt) {
                    //Some fields (like email) may have buttons and the mousedown event will fire before the one
                    //attached to the button is fired. As a workaround we wrap the buttons with .prevent-mousedown
                    if ($(evt.target).closest('.prevent-mousedown').length === 0) {
                        self.fieldClose.call(self, evt, field);
                    }
                }, 0));
            }
        } else {
            field.$el.off("keydown.record");
            $(document).off("mousedown.record" + field.name);
        }
    },

    fieldClose: function(evt, field) {
        if (field.tplName === this.action) {
            return;
        }

        var currFieldParent = field.$el,
            targetPlaceHolder = this.$(evt.target).parents("span[sfuuid='" + field.sfId + "']");

        // When mouse clicks the document, it should maintain the edit mode within the following cases
        // - If mouse is clicked within the same field placeholder area
        // - If cursor is focused among the field's input elements
        // - If current view is blocked by drawer
        if (targetPlaceHolder.length > 0
            || currFieldParent.find(":focus").length > 0
            || currFieldParent.parents(".drawer-squeezed").length > 0) {
            return;
        }
        this.toggleField(field, false);
    },

    handleKeyDown: function(e, field) {
        if (e.which == 27) { // If esc
            this.toggleField(field, false);
        }
    }
})
