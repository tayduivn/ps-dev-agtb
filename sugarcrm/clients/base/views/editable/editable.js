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

            field.$(field.fieldTag).off("keydown.record", this.keyDowned);
            $(document).off("mousedown.record" + field.name, this.mouseClicked);
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
                if (_.isFunction(field.bindKeyDown)) {
                    field.bindKeyDown(this.keyDowned);
                } else {
                    field.$(field.fieldTag).on("keydown.record", {field: field}, this.keyDowned);
                }

                $(document).on("mousedown.record" + field.name, {field: field}, this.mouseClicked);
            }
        } else {
            field.$(field.fieldTag).off("keydown.record");
            $(document).off("mousedown.record" + field.name);
        }
    },
    keyDowned: function(evt) {
        this.handleKeyDown.call(this, evt, evt.data.field);
    },
    mouseClicked: _.debounce(function(evt) {
        this.fieldClose.call(this, evt, evt.data.field);
    }, 0),
    fieldClose: function(evt, field) {
        if (field.tplName === this.action) {
            return;
        }

        var currFieldParent = field.$el,
            targetPlaceHolder = this.$(evt.target).parents("span[sfuuid='" + field.sfId + "']"),
            preventPlaceholder = this.$(evt.target).closest('.prevent-mousedown');

        // When mouse clicks the document, it should maintain the edit mode within the following cases
        // - Some fields (like email) may have buttons and the mousedown event will fire before the one
        //   attached to the button is fired. As a workaround we wrap the buttons with .prevent-mousedown
        // - If mouse is clicked within the same field placeholder area
        // - If cursor is focused among the field's input elements
        if (preventPlaceholder.length > 0
            || targetPlaceHolder.length > 0
            || currFieldParent.find(":focus").length > 0
            || !_.isEmpty(app.drawer._components)) {
            return;
        }
        this.toggleField(field, false);
    },

    handleKeyDown: function(e, field) {
        if (e.which == 27) { // If esc
            this.toggleField(field, false);
        }
    },
    _dispose: function() {
        $(document).off("mousedown", this.mouseClicked);
        app.view.Component.prototype._dispose.call(this);
    }
})
