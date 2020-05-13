/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Fields.Base.ConsoleConfiguration.FieldListField
 * @alias SUGAR.App.view.fields.BaseConsoleConfigurationFieldListField
 * @extends View.Fields.Base.BaseField
 */
({
    removeFldIcon: '<i class="fa fa-times-circle console-field-remove"></i>',
    removeColIcon: '<i class="fa fa-times-circle multi-field-column-remove"></i>',

    events: {
        'click .fa.fa-times-circle.console-field-remove': 'removeSelectedFieldClicked',
        'click .fa.fa-times-circle.multi-field-column-remove': 'removeMultiFieldColumnClicked'
    },

    /**
     * The field properties to get from multi-line-list
     */
    whitelistedProperties: [
        'name',
        'label',
        'widget_name',
    ],

    /**
     * Fields mapped to their subfields
     */
    mappedFields: {},

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.mappedFields = this.getMappedFields();
    },

    /**
     * @inheritdoc
     *
     * Overrides the parent bindDataChange to make sure this field is re-rendered
     * when the config is reset
     */
    bindDataChange: function() {
        if (this.model) {
            this.context.on('consoleconfig:reset:defaultmetarelay', function() {
                // the default meta data is ready, use it to re-render
                var defaultViewMeta = this.context.get('defaultViewMeta');
                var moduleName = this.model.get('enabled_module');
                if (!_.isEmpty(defaultViewMeta) && !_.isEmpty(defaultViewMeta[moduleName])) {
                    this.mappedFields = this.getMappedFields();
                    this.context.set('defaultViewMeta', null);
                    this.render();
                }
            }, this);
        }
    },

    /**
     * Return the proper view metadata.
     *
     * @param {string} moduleName The selected module name from the available modules.
     */
    getViewMetaData: function(moduleName) {
        // If defaultViewMeta exists, it means we are restoring the default settings.
        var defaultViewMeta = this.context.get('defaultViewMeta');
        if (!_.isEmpty(defaultViewMeta) && !_.isEmpty(defaultViewMeta[moduleName])) {
            return this.context.get('defaultViewMeta')[moduleName];
        }

        // Not restoring defaults, use the regular view meta data
        return app.metadata.getView(moduleName, 'multi-line-list');
    },

    /**
     * @inheritdoc
     */
    _render: function() {
        this._super('_render');
        this.handleDragAndDrop();
    },

    /**
     * Gets the module's multi-line list fields from the model with the parent field mapping
     *
     * @return {Object} the fields
     */
    getMappedFields: function() {
        var tabContentFields = {};

        var multiLineMeta = this.getViewMetaData(this.model.get('enabled_module'));

        _.each(multiLineMeta.panels, function(panel) {
            _.each(panel.fields, function(fieldDefs) {
                var subfields = [];
                _.each(fieldDefs.subfields, function(subfield) {
                    var parsedSubfield = _.pick(subfield, this.whitelistedProperties);

                    // if label does not exist, get it from the parent's vardef
                    if (!_.has(parsedSubfield, 'label')) {
                        parsedSubfield.label = this.model.fields[parsedSubfield.name].label ||
                            this.model.fields[parsedSubfield.name].vname;
                    }

                    parsedSubfield.parent_name = fieldDefs.name;
                    parsedSubfield.parent_label = fieldDefs.label;

                    if (_.has(parsedSubfield, 'widget_name')) {
                        parsedSubfield.name = parsedSubfield.widget_name;
                    }

                    subfields = subfields.concat(parsedSubfield);
                }, this);

                tabContentFields[fieldDefs.name] = _.has(tabContentFields, fieldDefs.name) ?
                    tabContentFields[fieldDefs.name].concat(subfields) : subfields;
            }, this);
        }, this);

        return tabContentFields;
    },

    /**
     * Handles the dragging of the items from the available fields list to the columns list section
     * and sorting/drag & dropping to multi fields list
     */
    handleDragAndDrop: function() {
        this.$('#columns-sortable').sortable({
            connectWith: '.connectedSortable',
            items: '.pill.outer',
            update: _.bind(function(event, ui) {
                var lastMultiFields = $(event.target).find('#multi-field-sortable.multi-field.connectedSortable:last');
                var moduleName = this.model.get('enabled_module');
                var fields = app.metadata.getModule(moduleName, 'fields');
                var fieldDrop = ui.item.attr('fieldname');
                var fieldsInMiltiLine = (lastMultiFields.children()) ? lastMultiFields.children() : null;
                var dropMiltiLine = this.isMultilineField(fieldDrop, fields);
                var hasMultiline = _.find(fieldsInMiltiLine, function(field) {
                    return field.getAttribute('fieldname') &&
                        this.isMultilineField(field.getAttribute('fieldname'), fields);
                }, this);
                var hintText = _.find(lastMultiFields.children(), function(child) {
                    return $(child).hasClass('multi-field-hint');
                }, this);
                var isMultiFieldAtLast = $(event.target).hasClass('field-list') === true && event.target.lastChild &&
                    $(event.target.lastChild).hasClass('multi-field-block');
                var columnsSortable = $('#' + this.model.get('enabled_module') + '-side').find('#columns-sortable');
                // handles this only when multi-field-column is the last element
                if (isMultiFieldAtLast) {
                    if (lastMultiFields.children() &&
                        (
                            ((_.isUndefined(dropMiltiLine) && _.isUndefined(hasMultiline)) &&
                                lastMultiFields.children().length > 3) ||
                            ((!_.isUndefined(dropMiltiLine) || !_.isUndefined(hasMultiline)) &&
                                (
                                    (!hintText && lastMultiFields.children().length > 2) ||
                                    (hintText && lastMultiFields.children().length > 3)
                                )
                            )
                        )
                    ) {
                        if (ui.sender) {
                            // 1. prevents from sorting/dropping to a target having more than two pills
                            // (one header + two pills) when no multi-line field involved
                            // 2. prevents from sorting/dropping to a target having more than one pill
                            // (one header + one pill) when there's multi-line field involved
                            // 3. not moving into multi-field-column, don't prevent the move, skip
                            if ((_.isUndefined(dropMiltiLine) && _.isUndefined(hasMultiline)) ||
                                !_.isUndefined(hasMultiline)) {
                                ui.sender.sortable('cancel');
                            }
                        } else {
                            var dropFieldName = ui.item.attr('fieldname');
                            var i = 0;
                            // dropping a pill to the last multi-field-column that contains two pills (or one pill when
                            // there's multi-line field involved) sometimes misses sortable's receive event,
                            // we need to handle that
                            _.each(lastMultiFields.children(), function(child) {
                                if (i++ > 0 && child.getAttribute('fieldname') === dropFieldName) {
                                    child.remove();
                                    if (!ui.item.hasClass('outer')) {
                                        ui.item.addClass('outer');
                                    }
                                    columnsSortable.append(ui.item);
                                }
                            }, this);
                        }
                    }
                }

                this.handleColumnsChanging();
            }, this),
            receive: _.bind(function(event, ui) {
                // prevents dropping from a multi-field-column into another multi-field-column
                if (ui.sender && ui.item.hasClass('outer') && ui.sender.attr('id') === 'multi-field-sortable') {
                    ui.item.removeClass('outer');
                    ui.sender.sortable('cancel');
                } else {
                    if (!ui.item.hasClass('outer')) {
                        ui.item.addClass('outer');
                    }
                    if (ui.item.find('i.console-field-remove').length === 0) {
                        ui.item.append(this.removeFldIcon);
                    }
                }
            }, this)
        });

        _.each(this.$('#columns-sortable').find('#multi-field-sortable.multi-field.connectedSortable'),
            function(multiField) {
            this.getSortable($(multiField));
        }, this);
    },

    /**
     * Update columns property of the model basing on the selected columns
     */
    handleColumnsChanging: function() {
        let columns = {};
        const module = this.model.get('enabled_module');
        const columnsSortable = $('#' + module + '-side')
            .find('#columns-sortable .pill:not(.multi-field-block)');

        const fields = app.metadata.getModule(module, 'fields');
        _.each(columnsSortable, function(item) {
            const fieldName = $(item).attr('fieldname');
            columns[fieldName] = fields[fieldName];
        });

        this.model.set('columns', columns);
    },

    /**
     * Gets jQuery sortable for multi field column
     *
     * @param {Object} element the element to be sortable enabled
     */
    getSortable: function(element) {
        element.sortable({
            connectWith: '.connectedSortable',
            items: '.pill',
            update: _.bind(function(event, ui) {
                var multiRow = app.lang.get('LBL_CONSOLE_MULTI_ROW', this.module);
                var multiRowHint = app.lang.get('LBL_CONSOLE_MULTI_ROW_HINT', 'ConsoleConfiguration');
                var hint = '<div class="multi-field-hint">' + multiRowHint + '</div>';
                var multiFields = $('#' + this.model.get('enabled_module') + '-side')
                    .find('#multi-field-sortable.multi-field.connectedSortable');
                if (multiFields.length > 0) {
                    // re-concatenates header for each multi-field-columns
                    _.each(multiFields, function(fields) {
                        if (fields.children && fields.children.length > 0) {
                            var header = '';
                            var headerLabel = '';
                            var i = 0;
                            _.each(fields.children, function(field) {
                                if (i > 1) {
                                    header += '/';
                                    headerLabel += '/';
                                }
                                if (i++ > 0 && !_.isUndefined(field) && !_.isUndefined(field.textContent)) {
                                    if (field.textContent.trim() === multiRowHint) {
                                        // clean hint text, it will be added later
                                        $(field).remove();
                                    } else {
                                        header += field.textContent.trim();
                                        headerLabel += field.getAttribute('fieldlabel');
                                    }
                                }
                            }, this);
                            if (header.endsWith('/')) {
                                header = header.slice(0, -1);
                                headerLabel = headerLabel.slice(0, -1);
                            }
                            header = header ? header : multiRow;
                            $(fields.firstElementChild).text(header)
                                .append(this.removeColIcon);
                            $(fields.firstElementChild).attr('data-original-title', header);
                            $(fields.firstElementChild).attr('fieldname', header);
                            $(fields.firstElementChild).attr('fieldlabel', headerLabel);
                            if (header === multiRow) {
                                $(fields).append(hint);
                            }
                        }
                    }, this);
                }
            }, this),
            receive: _.bind(function(event, ui) {
                var moduleName = this.model.get('enabled_module');
                var fields = app.metadata.getModule(moduleName, 'fields');
                // field that is dropping
                var fieldDrop = ui.item.attr('fieldname');
                // fields in milti-field-column
                var fieldsInMiltiLine = (event.target.children) ? event.target.children : null;
                // finds if dropping field a muti-line field
                var dropMiltiLine = this.isMultilineField(fieldDrop, fields);
                // finds muti-line fields in multi-field-column
                var hasMultiline = _.find(fieldsInMiltiLine, function(field) {
                    return field.getAttribute('fieldname') &&
                        this.isMultilineField(field.getAttribute('fieldname'), fields);
                }, this);
                // finds if target multi-field column contains hint text element
                var hintText = _.find(event.target.children, function(child) {
                    return $(child).hasClass('multi-field-hint');
                }, this);
                if (ui.item.hasClass('multi-field-block') ||
                    ((_.isUndefined(dropMiltiLine) && _.isUndefined(hasMultiline)) &&
                        event.target.children.length && event.target.children.length > 3) ||
                    ((!_.isUndefined(hasMultiline) || !_.isUndefined(dropMiltiLine)) &&
                        event.target.children.length &&
                        (
                            (!hintText && event.target.children.length > 2) ||
                            (hintText && event.target.children.length > 3)
                        )
                    )
                ) {
                    // 1. prevents a multi-line field from sorting/dropping to a multi-field-column that contains
                    // at least one header + one pill (or sometimes one header + one hint Text) already,
                    // or the orhte way
                    // 2. when there's no multi-line field involved, a multi-field-column can't contain more than
                    // two pills, i.e. one header + two pills
                    ui.sender.sortable('cancel');
                } else {
                    if (hintText) {
                        hintText.remove();
                    }
                    if (ui.item.hasClass('outer')) {
                        ui.item.removeClass('outer');
                    }
                    if (ui.item.find('i.console-field-remove').length === 0) {
                        ui.item.append(this.removeFldIcon);
                    }
                }
            }, this)
        });
    },

    /**
     * Checks if fields contains the multi-line field
     * @param {string} fieldToCheck field to check
     * @param {Array} fields all fields
     */
    isMultilineField: function(fieldToCheck, fields) {
        return _.find(fields, function(field) {
            return field.name === fieldToCheck &&
                field.type === 'widget' &&
                field.multiline &&
                field.multiline === true;
        }, this);
    },

    /**
     * Remove a selected field
     * @param e
     */
    removeSelectedFieldClicked: function(e) {
        var selectedField = e.currentTarget.parentNode || {};
        var fieldsSortable = $('#' + this.model.get('enabled_module') + '-side').find('#fields-sortable');
        var multiRow = app.lang.get('LBL_CONSOLE_MULTI_ROW', this.module);
        var multiRowHint = app.lang.get('LBL_CONSOLE_MULTI_ROW_HINT', 'ConsoleConfiguration');
        var hint = '<div class="multi-field-hint">' + multiRowHint + '</div>';
        if (selectedField) {
            var fieldName = e.currentTarget.parentNode.getAttribute('fieldname');
            var parentName = e.currentTarget.parentNode.getAttribute('parentname');
            var fieldLabel = e.currentTarget.parentNode.getAttribute('fieldlabel');
            var headerElement = $('ul.field-list').find('[fieldname=' + fieldName + ']')[0].parentNode;
            if (headerElement.getAttribute('id') !== 'columns-sortable') {
                var header = '';
                var newHeader = '';
                var i = 0;
                // re-concatenates header for each multi-field-columns when deleting a pill
                _.each(headerElement.children, function(child) {
                    if (i++ > 0 && child && child.getAttribute('fieldname')) {
                        if (fieldName !== child.getAttribute('fieldname')) {
                            header += child.textContent.trim();
                        }
                    }
                }, this);
                newHeader = header ? header : multiRow;
                $(headerElement.firstElementChild).text(newHeader)
                    .append(this.removeColIcon);
                $(headerElement.firstElementChild).attr('data-original-title', header);
                if (newHeader === multiRow) {
                    $(headerElement).append(hint);
                }
            }
            var text = e.currentTarget.parentNode.textContent.trim();
            var field = '<li class="pill outer" fieldname="' + fieldName + '" fieldlabel="' + fieldLabel +
                '" rel="tooltip" data-original-title="' + text + '">' + text + '</li>';
            fieldsSortable.append(field);
            selectedField.remove();

            this.handleColumnsChanging();
        }
    },

    /**
     * Remove a multi field column and fields inside
     * @param e
     */
    removeMultiFieldColumnClicked: function(e) {
        var miltiFieldColumn = e.currentTarget.closest('.multi-field-block') || {};
        var fieldsSortable = $('#' + this.model.get('enabled_module') + '-side').find('#fields-sortable');
        if (miltiFieldColumn) {
            var fields = e.currentTarget.parentNode.parentNode.children;
            var i = 0;
            // re-concatenates header for each multi-field-columns when deleting a multi-field-column
            _.each(fields, function(field) {
                if (i++ > 0 && field.textContent) {
                    var text = field.textContent.trim();
                    var avField = '<li class="pill outer" fieldname="' + field.getAttribute('fieldname') +
                        '" fieldlabel="' + field.getAttribute('fieldlabel') +
                        '" rel="tooltip" data-original-title="' + text + '">' + text + '</li>';
                    if (!$(field).hasClass('multi-field-hint')) {
                        fieldsSortable.append(avField);
                    }
                }
            }, this);
            miltiFieldColumn.remove();
            this.handleColumnsChanging();
        }
    },
})
