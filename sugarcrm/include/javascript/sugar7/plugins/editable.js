(function (app) {
    app.events.on("app:init", function () {
        /**
         * Editable plug-in will help the view controller's fields switching in edit mode
         *
         * This plugin register two main feature
         *
         * - toggleFields: switching mode within array of fields
         * - toggleField: switching mode a single field.
         *                In this case, key and mouse listerer will be enabled.
         *                This plugin automatically back from the editable mode
         *                when user clicks escape key or mouse key in out of the field area
         *                (fieldClose, editableHandleKeyDown will take care of this feature)
         * To override more key event handler, bind this.on("editable:keydown", function(evt, field))
         * The trigger will pass two parameters([mouse event], [field])
         */
        app.plugins.register('editable', ['view'], {
            onAttach: function(component, plugin) {
                this.editableKeyDowned = _.bind(function(evt) {
                    this.editableHandleKeyDown.call(this, evt, evt.data.field);
                }, this);

                this.editableMouseClicked = _.debounce(_.bind(function(evt) {
                    this.fieldClose.call(this, evt, evt.data.field);
                }, this), 0);
            },

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
                        if (field.disposed !== true) {
                            field.setMode(viewName);
                        }
                    }, field);

                    field.$(field.fieldTag).off("keydown.record", this.editableKeyDowned);
                    $(document).off("mousedown.record" + field.name, this.editableMouseClicked);
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
                            field.bindKeyDown(this.editableKeyDowned);
                        } else {
                            field.$(field.fieldTag).on("keydown.record", {field: field}, this.editableKeyDowned);
                        }
                        $(document).on("mousedown.record" + field.name, {field: field}, this.editableMouseClicked);
                    }
                } else {
                    field.$(field.fieldTag).off("keydown.record");
                    $(document).off("mousedown.record" + field.name);
                }
            },
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

            editableHandleKeyDown: function(e, field) {
                if (e.which == 27) { // If esc
                    this.toggleField(field, false);
                }
                this.trigger("editable:keydown", e, field);
            },
            onDetach: function() {
                this.editableKeyDowned = null;
                this.editableMouseClicked = null;
                $(document).off("mousedown", this.editableMouseClicked);
            }
        });
    });
})(SUGAR.App);
