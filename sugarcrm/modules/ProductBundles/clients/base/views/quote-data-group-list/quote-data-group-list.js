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
 * @class View.Views.Base.ProductBundles.QuoteDataGroupListView
 * @alias SUGAR.App.view.views.BaseProductBundlesQuoteDataGroupListView
 * @extends View.Views.Base.View
 */
({
    /**
     * @inheritdoc
     */
    events: {
        'click [name="edit_row_button"]': '_onEditRowBtnClicked',
        'click [name="delete_row_button"]': '_onDeleteRowBtnClicked'
    },

    /**
     * @inheritdoc
     */
    plugins: [
        'Editable',
        'ErrorDecoration',
        'LinkedModel',
        'MassCollection',
        'SugarLogic'
    ],

    /**
     * @inheritdoc
     */
    className: 'quote-data-group-list',

    /**
     * Array of fields to use in the template
     */
    _fields: undefined,

    /**
     * The colspan value for the list
     */
    listColSpan: 0,

    /**
     * Array of left column fields
     */
    leftColumns: undefined,

    /**
     * Array of left column fields
     */
    leftSaveCancelColumn: undefined,

    /**
     * List of current inline edit models.
     */
    toggledModels: null,

    /**
     * Object containing the row's fields
     */
    rowFields: {},

    /**
     * ProductBundleNotes QuoteDataGroupList metadata
     */
    pbnListMetadata: undefined,

    /**
     * QuotedLineItems QuoteDataGroupList metadata
     */
    qliListMetadata: undefined,

    /**
     * ProductBundleNotes Description field metadata
     */
    pbnDescriptionMetadata: undefined,

    /**
     * Array of IDs that have row models created for them but have not been saved yet
     * todo: Possibly use this with a before navigate event to let users know they have unsaved data
     */
    newIdsToSave: undefined,

    /**
     * Track all the SugarLogic Contexts that we create for each record in bundle
     *
     * @type {Object}
     */
    sugarLogicContexts: {},

    /**
     * Track the module dependencies for the line item, so we dont have to fetch them every time
     *
     * @type {Object}
     */
    moduleDependencies: {},

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this.newIdsToSave = [];
        this.pbnListMetadata = app.metadata.getView('ProductBundleNotes', 'quote-data-group-list');
        this.qliListMetadata = app.metadata.getView('Products', 'quote-data-group-list');

        this.pbnDescriptionMetadata = _.find(this.pbnListMetadata.panels[0].fields, function(field) {
            return field.name === 'description';
        }, this);

        // make sure we're using the layout's model
        options.model = options.model || options.layout.model;
        // get the product_bundle_items collection from the model
        options.collection = options.model.get('product_bundle_items');
        this.listColSpan = options.layout.listColSpan;

        this._super('initialize', [options]);

        this.viewName = 'list';
        this.action = 'list';

        this._fields = _.flatten(_.pluck(this.qliListMetadata.panels, 'fields'));

        this.toggledModels = {};
        this.leftColumns = [];
        this.leftSaveCancelColumn = [];
        this.addMultiSelectionAction();

        /**
         * Due to BackboneJS, this view would have a wrapper tag around it e.g. QuoteDataGroupHeader.tagName "tr"
         * so this would have also been wrapped in div/tr whatever the tagName was for the view.
         * I am setting this.el to be the Layout's el (QuoteDataGroupLayout) which is a tbody element.
         * In the render function I am then manually appending this list of records template
         * after the group header tr row
         */
        this.el = this.layout.el;
        this.setElement(this.el);

        this.context.on('quotes:group:create:qli:' + this.model.get('id'), this.onAddNewItemToGroup, this);
        this.context.on('quotes:group:create:note:' + this.model.get('id'), this.onAddNewItemToGroup, this);
        this.context.on('editablelist:cancel:' + this.model.get('id'), this.onCancelRowEdit, this);
        this.context.on('editablelist:save:' + this.model.get('id'), this.onSaveRowEdit, this);

        // for each item in the collection, setup SugarLogic
        this.collection.each(this.setupSugarLogicForModel, this);
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this.collection.on('add', this.setupSugarLogicForModel, this);
    },

    /**
     * Load and cache SugarLogic dependencies for a module
     *
     * @param {Data.Bean} model
     * @return {Array}
     * @private
     */
    _getSugarLogicDependenciesForModel: function(model) {
        var module = model.module;
        if (_.isUndefined(this.moduleDependencies[module])) {
            var dependencies;
            var moduleMetadata;
            //TODO: These dependencies would normally be filtered by view action. Need to make that logic
            // external from the Sugarlogic plugin. Probably somewhere in the SidecarExpressionContext class...
            // first get the module from the metadata
            moduleMetadata = app.metadata.getModule(module) || {};
            // load any dependencies found there
            dependencies = moduleMetadata.dependencies || [];
            // now lets check the record view to see if it has any local ones on it.
            if (moduleMetadata.views && moduleMetadata.views.record) {
                var recordMetadata = moduleMetadata.views.record.meta;
                if (!_.isUndefined(recordMetadata.dependencies)) {
                    dependencies = dependencies.concat(recordMetadata.dependencies);
                }
            }

            // cache the results so we don't have to do this expensive lookup any more
            this.moduleDependencies[module] = dependencies;
        }

        return this.moduleDependencies[module];
    },

    /**
     * Setup dependencies for a specific model.
     *
     * @param {Data.Bean} model
     * @param {Data.Collection} collection
     * @param {Object} options
     */
    setupSugarLogicForModel: function(model, collection, options) {
        var slContext;
        var dependencies = this._getSugarLogicDependenciesForModel(model);
        if (_.size(dependencies) > 0) {

            app.logger.debug('Setting up SugarLogic for "' + model.get('id') + '" with module of "' +
                model.module + '" with "' + dependencies.length + '" dependencies');

            slContext = this.initSugarLogicForModelWithDeps(
                model,
                dependencies,
                _.has(this.toggledModels, model.get('id'))
            );
            this.sugarLogicContexts[model.get('id')] = slContext;
        }
    },

    /**
     * Handler for when a new QLI/Note row has been added and then canceled
     *
     * @param {Data.Bean} rowModel The row collection model that was created and now canceled
     */
    onCancelRowEdit: function(rowModel) {
        if (rowModel.has('_notSaved')) {
            var rowId = rowModel.get('id');
            this.newIdsToSave = _.without(this.newIdsToSave, rowId);
            this.collection.remove(rowModel);

            if (!_.isUndefined(this.sugarLogicContexts[rowId])) {
                // cleanup any sugarlogic contexts
                this.sugarLogicContexts[rowId].dispose();
            }
        }
    },

    /**
     * Handles when a row is saved. Since newly added (but not saved) rows have temporary
     * id's assigned to them, this is needed to go back and fix row id html attributes and
     * also resets the rowFields with the new model's ID so rows toggle properly
     *
     * @param {Data.Bean} rowModel
     * @param {string} oldModelId
     */
    onSaveRowEdit: function(rowModel, oldModelId) {
        var $oldRow;
        var modelId = rowModel.get('id');
        var modelModule = rowModel.module;

        if (rowModel.has('_notSaved')) {
            // if the rowModel still has _notSaved on it, remove it
            rowModel.unset('_notSaved');

            if (this.toggledModels[oldModelId]) {
                delete this.toggledModels[oldModelId];
            }
        }

        // If this was a newly created row that was saved, oldModelId will
        // be different from the current rowModel's id, and we need to redelegate list events
        if (modelId !== oldModelId) {
            $oldRow = this.$('tr[name=' + modelModule + '_' + oldModelId + ']');
            if ($oldRow.length) {
                $oldRow.attr('name', modelModule + '_' + modelId);
                // re-set the row fields based on new model IDs
                this._setRowFields();
            }
        }
        this.toggleRow(modelModule, modelId, false);
    },

    /**
     * Called when a group's Create QLI or Create Note button is clicked
     *
     * @param {Data.Bean} groupModel The ProductBundle model
     * @param {string} linkName The link name of the new item to create: products or product_bundle_notes
     */
    onAddNewItemToGroup: function(groupModel, linkName) {
        var relatedModel = this.createLinkModel(groupModel, linkName);
        var maxPositionModel;
        var position = 0;
        var newRelatedModelId = app.utils.generateUUID();

        // save the new model ID
        this.newIdsToSave.push(newRelatedModelId);

        if (this.collection.length) {
            // get the model with the highest position
            maxPositionModel = _.max(this.collection.models, function(model) {
                return +model.get('position');
            });

            // get the position of the highest model's position and add one to it
            position = +maxPositionModel.get('position') + 1;
        }

        // set a few items on the model
        relatedModel.set({
            'position': position,
            id: newRelatedModelId,
            _notSaved: true
        });

        // this model's fields should be set to render
        relatedModel.modelView = 'edit';

        // add model to toggledModels to be toggled next render
        this.toggledModels[relatedModel.id] = relatedModel;

        // adding to the collection will trigger the render
        this.collection.add(relatedModel);
    },

    /**
     * Overriding _renderHtml to specifically place this template after the
     * quote data group header
     *
     * @inheritdoc
     * @override
     */
    _renderHtml: function() {
        this.$('tr.quote-data-group-header').after(this.template(this));
    },

    /**
     * @inheritdoc
     */
    _render: function() {
        this._super('_render');

        // set row fields after rendering to prep if we need to toggle rows
        this._setRowFields();

        if (!_.isEmpty(this.toggledModels)) {
            _.each(this.toggledModels, function(model, modelId) {
                this.toggleRow(model.module, modelId, true);
            }, this);
        }
    },

    /**
     * Handles when the Delete button is clicked
     *
     * @param {MouseEvent} evt The mouse click event
     * @private
     */
    _onEditRowBtnClicked: function(evt) {
        var $ulEl = $(evt.target).closest('ul');
        var rowModule;
        var rowModelId;
        if ($ulEl.length) {
            rowModule = $ulEl.data('row-module');
            rowModelId = $ulEl.data('row-model-id');
            this.toggleRow(rowModule, rowModelId, true);
        }
    },

    /**
     * Handles when the Delete button is clicked
     *
     * @param {MouseEvent} evt The mouse click event
     * @private
     */
    _onDeleteRowBtnClicked: function(evt) {

    },

    /**
     * Toggle editable selected row's model fields.
     *
     * @param {string} rowModule The row model's module.
     * @param {string} rowModelId The row model's ID
     * @param {boolean} isEdit True for edit mode, otherwise toggle back to list mode.
     */
    toggleRow: function(rowModule, rowModelId, isEdit) {
        var toggleModel;
        var row;
        if (isEdit) {
            toggleModel = this.collection.get(rowModelId);
            toggleModel.modelView = 'edit';
            this.toggledModels[rowModelId] = toggleModel;
        } else {
            if (this.toggledModels[rowModelId]) {
                this.toggledModels[rowModelId].modelView = 'list';
            }
            delete this.toggledModels[rowModelId];
        }

        row = this.$('tr[name=' + rowModule + '_' + rowModelId + ']');
        row.toggleClass('tr-inline-edit', isEdit);
        this.toggleFields(this.rowFields[rowModelId], isEdit);

        if (isEdit) {
            this.context.trigger('list:editrow:fire');
        } else if (row.hasClass('not-sortable')) {
            // if this is not edit mode and row still has not-sortable (from being a brand new row)
            // then remove the not-sortable and add the sortable classes
            row
                .removeClass('not-sortable')
                .addClass('sortable ui-sortable');
        }
    },

    /**
     * Set, or reset, the collection of fields that contains each row.
     *
     * This function is invoked when the view renders. It will update the row
     * fields once the `Pagination` plugin successfully fetches new records.
     *
     * @private
     */
    _setRowFields: function() {
        this.rowFields = {};
        _.each(this.fields, function(field) {
            if (field.model && field.model.id && _.isUndefined(field.parent)) {
                this.rowFields[field.model.id] = this.rowFields[field.model.id] || [];
                this.rowFields[field.model.id].push(field);
            }
        }, this);
    },

    /**
     * Overriding to allow panels to come from whichever module was passed in
     *
     * @inheritdoc
     * @override
     */
    getFieldNames: function(module) {
        var fields = [];
        var panels;
        module = module || this.context.get('module');

        if (module === 'Quotes' || module === 'Products') {
            panels = _.clone(this.qliListMetadata.panels);
        } else if (module === 'ProductBundleNotes') {
            panels = _.clone(this.pbnListMetadata.panels);
        }

        if (panels) {
            fields = _.reduce(_.map(panels, function(panel) {
                var nestedFields = _.flatten(_.compact(_.pluck(panel.fields, 'fields')));
                return _.pluck(panel.fields, 'name').concat(
                    _.pluck(nestedFields, 'name')).concat(
                    _.flatten(_.compact(_.pluck(panel.fields, 'related_fields'))));
            }), function(memo, field) {
                return memo.concat(field);
            }, []);
        }

        fields = _.compact(_.uniq(fields));

        var fieldMetadata = app.metadata.getModule(module, 'fields');
        if (fieldMetadata) {
            // Filter out all fields that are not actual bean fields
            fields = _.reject(fields, function(name) {
                return _.isUndefined(fieldMetadata[name]);
            });

            // we need to find the relates and add the actual id fields
            var relates = [];
            _.each(fields, function(name) {
                if (fieldMetadata[name].type == 'relate') {
                    relates.push(fieldMetadata[name].id_name);
                } else if (fieldMetadata[name].type == 'parent') {
                    relates.push(fieldMetadata[name].id_name);
                    relates.push(fieldMetadata[name].type_name);
                }
                if (_.isArray(fieldMetadata[name].fields)) {
                    relates = relates.concat(fieldMetadata[name].fields);
                }
            });

            fields = _.union(fields, relates);
        }

        return fields;
    },

    /**
     * Adds the left column fields
     */
    addMultiSelectionAction: function() {
        var _generateMeta = function(buttons, disableSelectAllAlert) {
            return {
                'type': 'fieldset',
                'fields': [
                    {
                        'type': 'quote-data-actionmenu',
                        'buttons': buttons || [],
                        'disable_select_all_alert': !!disableSelectAllAlert
                    }
                ],
                'value': false,
                'sortable': false
            };
        };
        var buttons = this.meta.selection.actions;
        var disableSelectAllAlert = !!this.meta.selection.disable_select_all_alert;
        this.leftColumns.push(_generateMeta(buttons, disableSelectAllAlert));

        this.leftSaveCancelColumn.push({
            'type': 'fieldset',
            'label': '',
            'sortable': false,
            'fields': [{
                type: 'quote-data-editablelistbutton',
                label: '',
                tooltip: 'LBL_CANCEL_BUTTON_LABEL',
                name: 'inline-cancel',
                icon: 'fa-close',
                css_class: 'btn-link btn-invisible inline-cancel ellipsis_inline'
            }, {
                type: 'quote-data-editablelistbutton',
                label: '',
                tooltip: 'LBL_SAVE_BUTTON_LABEL',
                name: 'inline-save',
                icon: 'fa-check-circle',
                css_class: 'ellipsis_inline'
            }]
        });
    },

    /**
     * @inheritdoc
     */
    _dispose: function() {
        _.each(this.sugarLogicContexts, function(slContext) {
            slContext.dispose();
        });
        this._super('_dispose');
        this.rowFields = null;
        this.sugarLogicContexts = {};
        this.moduleDependencies = {};
    }
})
