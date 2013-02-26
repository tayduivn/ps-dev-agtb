({
    inlineEditMode: false,
    createMode: false,
    previousModelState: null,
    extendsFrom: 'EditableView',

    enableHeaderPane: true,
    events: {
        'click .record-edit-link-wrapper': 'handleEdit',
        'click a[name=cancel_button]': 'cancelClicked',
        'click .more': 'toggleMoreLess',
        'click .less': 'toggleMoreLess',
        'mouseenter .ellipsis_inline':'addTooltip'
    },
    addTooltip: function(event){
        if (_.isFunction(app.utils.handleTooltip)) {
            app.utils.handleTooltip(event, this);
        }
    },
    // button fields defined in view definition
    buttons: null,

    // button states
    STATE: {
        EDIT: 'edit',
        VIEW: 'view'
    },

    // current button states
    currentState: null,

    initialize: function(options) {
        _.bindAll(this);
        options.meta = _.extend({}, app.metadata.getView(null, 'record'), options.meta);
        app.view.views.EditableView.prototype.initialize.call(this, options);

        this.buttons = {};

        this.createMode = this.context.get("create") ? true : false;
        this.action = this.createMode ? 'edit' : 'detail';

        this.model.on("error:validation", this.handleValidationError, this);
        this.context.on("change:record_label", this.setLabel, this);
        this.context.set("viewed", true);
        this.model.on("duplicate:before", this.setupDuplicateFields, this);

        this.delegateButtonEvents();

        if (this.createMode) {
            this.model.isNotEmpty = true;
        }
    },

    /**
     * Called when current record is being duplicated to allow customization of fields
     * that will be copied into new record.
     *
     * Override to setup the fields on this bean prior to being displayed in Create dialog
     *
     * @param {Object} prefill Bean that will be used for new record
     */
    setupDuplicateFields: function(prefill){

    },

    setLabel: function(context, value) {
        this.$(".record-label[data-name=" + value.field + "]").text(value.label);
    },

    /**
     * Handle validation errors on save of Record.
     * Makes the fields editable and decorates the fields that have errors.
     * Fields decorate themselves because they may have customized HTML/CSS
     *
     * @param errors Validation errors
     */
    handleValidationError: function(errors){
        var errorFields = _.filter(this.editableFields,function(field){
            return errors[field.name];
        });
        this.toggleFields(errorFields, true);  // Set field to edit mode before decorating it
        _.defer(function(errorFields, self){ // Must defer decorating because field toggling is deferred
            _.each(errorFields, function(field){
                field.$el.parents('.record-cell').addClass("inline-error");
                if(field.decorateError){
                    field.decorateError(errors[field.name]);
                }
            });
        }, errorFields, this);

    },

    delegateButtonEvents: function() {
        this.context.on('button:edit_button:click', this.editClicked, this);
        this.context.on('button:save_button:click', this.saveClicked, this);
        this.context.on('button:delete_button:click', this.deleteClicked, this);
        this.context.on('button:duplicate_button:click', this.duplicateClicked, this);
        this.context.on('button:find_duplicates_button:click', this.findDuplicatesClicked, this);
    },

    _renderPanels: function(panels) {
        var totalFieldCount = 0;

        _.each(panels, function(panel) {
            var columns    = (panel.columns) || 1,
                rows       = [],
                row        = [],
                size       = panel.fields.length,
                rowSpan    = 0,
                rowSpanMax = 12,
                colCount   = 0;

            var _startNewRow = function() {
                rows.push(row); // push the current row onto the grid

                // reset variables that keep track of the current row's state
                row      = [];
                rowSpan  = 0;
                colCount = 0;
            };

            // Set flag so that show more link can be displayed to show hidden panel.
            if (panel.hide) {
                this.hiddenPanelExists = true;
            }

            _.each(panel.fields, function(field, index) {
                var isLabelInline,
                    fieldSpan,
                    maxFieldSpan,
                    maxSpanForFieldWithInlineLabel = 8,
                    maxSpanForFieldWithLabelOnTop  = 12;

                //The code below assumes that the field is an object but can be a string
                if(_.isString(field)) {
                    panel.fields[index] = field = {
                        name: field
                    };
                }

                //Disable the pencil icon if the user doesn't have ACLs
                if (!app.acl.hasAccessToModel('edit', this.model, field.name)) {
                    field.readonly = true;
                }

                //labels: visibility for the label
                //labelsOnTop: true for on the top of the field
                //             false for on the left of the field
                if (_.isUndefined(panel.labels)) {
                    panel.labels = true;
                }

                //8 for span because we are using a 2/3 ratio between field span and label span with a max of 12
                isLabelInline = (panel.labelsOnTop === false && panel.labels);
                maxFieldSpan  = isLabelInline ? maxSpanForFieldWithInlineLabel : maxSpanForFieldWithLabelOnTop;

                // calculate the 2/3 ratio for the field span
                if (_.isUndefined(field.span)) {
                    field.span = Math.floor(maxFieldSpan / columns);

                    // if the field span was undefined, then prevent a span of 0
                    if (field.span < 1) {
                        field.span = 1;
                    }
                }

                // 4 for label span because we are using a 1/3 ratio between field span and label span with a max of 12
                if (_.isUndefined(field.labelSpan)) {
                    field.labelSpan = Math.floor(4 / columns);

                    // if the field span was undefined, then prevent a labelSpan of 0
                    if (field.labelSpan < 1) {
                        field.labelSpan = 1;
                    }
                }

                if (_.isUndefined(field.dismiss_label)) {
                    field.dismiss_label = false;
                }

                // if the label is inline and is to be dismissed, then the field should take up its space plus the
                // space set aside for its label
                if (isLabelInline && field.dismiss_label === true) {
                    // add the label span to the field span
                    field.span += field.labelSpan;

                    // ignore the label span from here on out, since it has now served its purpose
                    // set it to 0 so it doesn't impact future calculations that may occur
                    field.labelSpan = 0;

                    // the field should be allowed to take up the space that was originally dedicated for the label,
                    // which is similar to saying that labels are on top
                    maxFieldSpan = maxSpanForFieldWithLabelOnTop;
                }

                // fields can't be greater than the maximum allowable span
                // however, there is no policing of (field.span + field.labelSpan) so overflow is still possible
                if (field.span > maxFieldSpan) {
                    field.span = maxFieldSpan;
                }

                totalFieldCount++;
                field.index = totalFieldCount;

                // by default, the field takes up the space specified by its span
                fieldSpan = field.span;

                // if the labels are to the left of the field then the field takes up the space
                // specified by its span plus the space its label takes up
                if (isLabelInline && field.dismiss_label === false) {
                    fieldSpan += field.labelSpan;
                }

                // if there isn't enough room remaining on the current row to contain the field or all available
                // columns in the row have been filled, then start a new row
                if ((rowSpan + fieldSpan) > rowSpanMax || colCount == columns) {
                    _startNewRow();
                }

                row.push(field);
                rowSpan += fieldSpan; // update rowSpan to account for span of the field that was just added to the row

                // push the last row if there are no more fields in the panel
                if ((index === size - 1)) {
                    _startNewRow();
                }

                colCount++; // increment the column count now that we've filled a column
            }, this);

            // Display module label in header panel it doesn't contain the picture field
            if (panel.header) {
                panel.isAvatar = !!_.find(panel.fields, function(f) { return f.name === 'picture'; });
            }

            panel.grid = rows;
        }, this);
    },

    _render: function() {
        this._renderPanels(this.meta.panels);

        app.view.View.prototype._render.call(this);

        // Field labels in headerpane should be hidden on view but displayed in edit and create
        _.each(this.fields, function(field) {
            var toggleLabel = _.bind(function() {
                this.toggleLabelByField(field);
            }, this);

            field.off('render', toggleLabel);
            if (field.$el.closest('.headerpane').length > 0) {
                field.on('render', toggleLabel);
            }
        }, this);

        this.toggleHeaderLabels(this.createMode);
        this.initButtons();
        this.setButtonStates(this.STATE.VIEW);
        this.setEditableFields();
    },

    setEditableFields: function() {
        delete this.editableFields;
        this.editableFields = [];

        var previousField, firstField;
        _.each(this.fields, function(field, index) {
            //Exclude read only fields
            if (field.def.readonly || field.parent || (field.name && this.buttons[field.name])) {
                return;
            }
            if(previousField) {
                previousField.nextField = field;
            } else {
                firstField = field;
            }
            previousField = field;
            this.editableFields.push(field);
        }, this);
        if(previousField) {
            previousField.nextField = firstField;
        }
    },
    initButtons: function() {
        if(this.options.meta && this.options.meta.buttons) {
            _.each(this.options.meta.buttons, function(button) {
                this.registerFieldAsButton(button.name);
                if (button.buttons) {
                    _.each(button.buttons, function(dropdownButton) {
                        this.registerFieldAsButton(dropdownButton.name);
                    }, this);
                }
            }, this);
        }
    },
    showPreviousNextBtnGroup:function() {
        var listCollection = this.context.get('listCollection') || new Backbone.Collection();
        var recordIndex = listCollection.indexOf(listCollection.get(this.model.id));
        if(this.collection){
            this.collection.previous = listCollection.models[recordIndex-1] ? listCollection.models[recordIndex-1] : undefined;
            this.collection.next = listCollection.models[recordIndex+1] ? listCollection.models[recordIndex+1] : undefined;
        }
    },

    registerFieldAsButton: function(buttonName) {
        var button = this.getField(buttonName);
        if (button) {
            this.buttons[buttonName] = button;
        }
    },

    _renderHtml: function() {
        this.showPreviousNextBtnGroup();
        app.view.View.prototype._renderHtml.call(this);
    },

    toggleMoreLess: function() {
        this.$(".less").toggleClass("hide");
        this.$(".more").toggleClass("hide");
        this.$(".panel_hidden").toggleClass("hide");
    },

    bindDataChange: function() {
        this.model.on("change", function(fieldType) {
            if (this.inlineEditMode) {
                this.setButtonStates(this.STATE.EDIT);
            }
            if (this.model.isNotEmpty !== true && fieldType !== 'image') {
                this.model.isNotEmpty = true;
                this.render();
            }
        }, this);
    },

    duplicateClicked: function() {
        var self = this, 
            prefill = app.data.createBean(this.model.module);

        prefill.copy(this.model);
        self.model.trigger("duplicate:before", prefill);
        prefill.unset("id");
        app.drawer.open({
            layout: 'create',
            context: {
                create: true,
                model : prefill
            }
        }, function(newModel) {
            if(newModel && newModel.id) {
                app.router.navigate("#" + self.model.module + "/" + newModel.id, {trigger: true});
            }
        });
    },

    findDuplicatesClicked: function() {
        var model = app.data.createBean(this.model.module);

        model.copy(this.model);
        model.set('id', this.model.id);
        app.drawer.open({
            layout : 'find-duplicates',
            context: {
                dupeCheckModel: model,
                dupelisttype: 'dupecheck-list-multiselect'
            }
        });
    },

    editClicked: function() {
        if (_.isEmpty(this.previousModelState)) {
            this.previousModelState = JSON.parse(JSON.stringify(this.model.attributes));
        }
        this.setButtonStates(this.STATE.EDIT);
        this.toggleEdit(true);
    },

    saveClicked: function() {
        this.$('.inline-error').removeClass('inline-error');
        if(this.model.isValid(this.getFields(this.module))){
            this.setButtonStates(this.STATE.VIEW);
            this.handleSave();
        }
    },

    cancelClicked: function() {
        this.setButtonStates(this.STATE.VIEW);
        this.handleCancel();
        this.clearValidationErrors(this.editableFields);
    },
    /**
     * Remove validation error decoration from fields
     *
     * @param fields Fields to remove error from
     */
    clearValidationErrors: function(fields){
        _.defer(function(){
            _.each(fields, function(field){
                field.$el.parents('.record-cell').removeClass("inline-error");
                if(field.clearErrorDecoration){
                    field.clearErrorDecoration();
                }
            });
        }, fields);

    },

    deleteClicked: function() {
        this.handleDelete();
    },

    /**
     * Render fields into either edit or view mode.
     * @param isEdit
     */
    toggleEdit: function(isEdit) {
        if (isEdit) {
            this.$('.record-edit-link-wrapper').hide();
        } else {
            this.$('.record-edit-link-wrapper').show();
        }

        this.toggleFields(this.editableFields, isEdit);
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

        if (_.isEmpty(this.previousModelState)) {
            this.previousModelState = JSON.parse(JSON.stringify(this.model.attributes));
        }

        this.setButtonStates(this.STATE.EDIT);

        // TODO: Refactor this for fields to support their own focus handling in future.
        // Add your own field type handling for focus / editing here.
        switch (field.type) {
            case "image":
                var self = this;
                app.file.checkFileFieldsAndProcessUpload(self.model, {
                        success:function () {
                            self.toggleField(field);
                        }
                    },
                    { deleteIfFails:false}
                );
                break;
            default:
                this.toggleField(field);
        }
    },

    /**
     * Hide/show all field labels in headerpane
     * @param isEdit
     */
    toggleHeaderLabels: function(isEdit) {
        if (isEdit) {
            this.$('.headerpane .record-label').show();
        } else {
            this.$('.headerpane .record-label').hide();
        }
    },

    /**
     * Hide/show field label given a field
     * @param field
     */
    toggleLabelByField: function(field) {
        if (field.action === 'edit') {
            field.$el.closest('.record-cell').find('.record-label').show();
        } else {
            field.$el.closest('.record-cell').find('.record-label').hide();
        }
    },

    handleSave: function() {
        var self = this;
        self.inlineEditMode = false;

        var finalSuccess = function () {
            if (!_.isEmpty(self.previousModelState)) {
                self.previousModelState = {};
            }

            if (self.createMode) {
                app.navigate(self.context, self.model);
            } else {
                self.render();
            }
        };
        app.file.checkFileFieldsAndProcessUpload(self.model, {
                success:function () {
                    self.model.save({}, {
                        success:finalSuccess,
                        viewed: true
                    });
                }
            },
            { deleteIfFails:false});

        this.$(".record-save-prompt").hide();
        this.render();
    },

    handleCancel: function() {
        this.inlineEditMode = false;
        this.toggleEdit(false);
        if (!_.isEmpty(this.previousModelState)) {
            this.model.set(JSON.parse(JSON.stringify(this.previousModelState)));
            this.previousModelState = {};
        }
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

    handleKeyDown: function(e, field) {
        app.view.views.EditableView.prototype.handleKeyDown.call(this, e, field);

        if (e.which == 9) { // If tab
            e.preventDefault();
            field.$(field.fieldTag).trigger("change");
            if(field.nextField) {
                this.toggleField(field, false);
                this.toggleField(field.nextField, true);
            }
        }
    },

    /**
     * Show/hide buttons depending on the state defined for each buttons in the metadata
     * @param state
     */
    setButtonStates: function(state) {
        this.currentState = state;

        _.each(this.buttons, function(field) {
            var showOn = field.def.showOn;
            if (_.isUndefined(showOn) || (showOn === state)) {
                field.show();
            } else {
                field.hide();
            }
        });
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
    },
    _dispose: function(){
        app.view.Component.prototype._dispose.call(this);
        if(this.context){
            this.context.off(null, null, this);
            this.context = null;
        }
    }
})
