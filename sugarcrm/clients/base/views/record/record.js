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
        var $el = this.$(event.target);
        if( $el[0].offsetWidth < $el[0].scrollWidth ) {
            $el.tooltip('show');
        } else {
            $el.tooltip('destroy');
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

        // Set the save button to show if the model has been edited.
        this.model.on("change", function() {
            if (this.inlineEditMode) {
                this.previousModelState = this.model.previousAttributes();
                this.setButtonStates(this.STATE.EDIT);
            }
        }, this);
        this.context.on("change:record_label", this.setLabel, this);

        this.delegateButtonEvents();

        if (this.createMode) {
            this.model.isNotEmpty = true;
        }
    },

    setLabel: function(context, value) {
        this.$(".record-label[data-name=" + value.field + "]").text(value.label);
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
                    field = {
                        name: field
                    };
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

                if (_.isUndefined(field.dismiss_label)) {
                    field.dismiss_label = false;
                }

                // if the label is to be dismissed, then the field should be allowed to take up the space that
                // was originally dedicated for the label, which is similar to saying that labels are on top
                if (field.dismiss_label === true) {
                    maxFieldSpan = maxSpanForFieldWithLabelOnTop;
                }

                // calculate the 2/3 ratio for the field span
                if (_.isUndefined(field.span)) {
                    field.span = Math.floor(maxFieldSpan / columns);
                }

                // prevent a span of 0
                if (field.span < 1) {
                    field.span = 1;
                }

                // 4 for label span because we are using a 1/3 ratio between field span and label span with a max of 12
                if (_.isUndefined(field.labelSpan)) {
                    field.labelSpan = Math.floor(4 / columns);
                }

                // prevent a labelSpan of 0
                if (field.labelSpan < 1) {
                    field.labelSpan = 1;
                }

                // if the label is inline and is to be dismissed, then the field should take up its space plus the
                // space set aside for its label, as long as that won't overflow the row
                if (isLabelInline && field.dismiss_label === true) {
                    field.span += field.labelSpan;
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

            panel.grid = rows;
        }, this);
    },

    _render: function() {
        this._renderPanels(this.meta.panels);

        app.view.View.prototype._render.call(this);
        this.initButtons();
        this.setButtonStates(this.STATE.VIEW);
        this.setEditableFields();
    },

    setEditableFields: function() {
        delete this.editableFields;
        this.editableFields = [];

        var previousField, firstField;
        _.each(this.fields, function(field, index) {
            if ( field.type === "img" || field.parent || (field.name && this.buttons[field.name])) {
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
        this.collection.previous = listCollection.models[recordIndex-1] ? listCollection.models[recordIndex-1] : undefined;
        this.collection.next = listCollection.models[recordIndex+1] ? listCollection.models[recordIndex+1] : undefined;
    },

    registerFieldAsButton: function(buttonName) {
        var button = this.getField(buttonName);
        if (button) {
            this.buttons[buttonName] = button;
        }
    },

    _renderHtml: function() {
        this.checkAclForButtons();
        this.showPreviousNextBtnGroup();
        app.view.View.prototype._renderHtml.call(this);
    },

    /**
     * Check to see if the buttons should be displayed
     */
    checkAclForButtons: function() {
        if (this.context.get("model").module === "Users") {
            this.hasAccess = (app.user.get("id") == this.context.get("model").id);
        } else if (this.createMode) {
            this.hasAccess = true;
        } else {
            this.hasAccess = app.acl.hasAccessToModel("edit", this.model);
        }
    },

    toggleMoreLess: function() {
        this.$(".less").toggleClass("hide");
        this.$(".more").toggleClass("hide");
        this.$(".panel_hidden").toggleClass("hide");
    },

    bindDataChange: function() {
        if (this.model) {
            this.model.on("change", function() {
                if (this.model.isNotEmpty !== true) {
                    this.model.isNotEmpty = true;
                    this.render();
                }
            }, this);
        }
    },

    duplicateClicked: function() {
        app.cache.set("duplicate"+this.module, this.model.attributes);
        this.layout.trigger("drawer:create:fire", {
            components: [{
                layout : 'create',
                context: {
                    create: true
                }
            }]
        }, this);
    },
    
    findDuplicatesClicked: function() {
        this.layout.trigger("drawer:find-duplicates:fire", {
            components: [{
                layout : 'find-duplicates',
                context: {
                    dupeCheckModel: this.model,
                    dupelisttype: 'dupecheck-list-multiselect'
                }
            }]
        }, this);
    },

    editClicked: function() {
        this.previousModelState = this.model.previousAttributes();
        this.setButtonStates(this.STATE.EDIT);
        this.toggleEdit(true);
    },

    saveClicked: function() {
        this.setButtonStates(this.STATE.VIEW);
        this.handleSave();
    },

    cancelClicked: function() {
        this.setButtonStates(this.STATE.VIEW);
        this.handleCancel();
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

        // TODO: Refactor this for fields to support their own focus handling in future.
        // Add your own field type handling for focus / editing here.
        switch (field.type) {
            case "img":
                break;
            default:
                this.toggleField(field);
        }
    },

    handleSave: function() {
        var self = this;
        this.inlineEditMode = false;

        this.model.save({}, {
            success: function() {
                if (self.createMode) {
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
        this.inlineEditMode = false;

        if (!_.isEmpty(this.previousModelState)) {
            this.model.set(this.previousModelState);
        }

        this.toggleEdit(false);
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
        var nextCell,
            index = field.$el.parent().data("index");

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
    }
})
