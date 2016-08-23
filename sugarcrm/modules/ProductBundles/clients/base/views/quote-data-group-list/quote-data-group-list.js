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
        'click [name="edit_row_button"]' : '_onEditRowBtnClicked',
        'click [name="delete_row_button"]' : '_onDeleteRowBtnClicked'
    },

    /**
     * @inheritdoc
     */
    plugins: [
        'Editable',
        'ErrorDecoration',
        'LinkedModel'
    ],

    /**
     * @inheritdoc
     */
    className: 'quote-data-group-list',

    /**
     * Collection of data for the list rows
     * @type Backbone.Collection
     */
    rowCollection: undefined,

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
        this.rowCollection = options.rowCollection || options.layout.rowCollection;
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

        this.buildRowsData();

        this.context.on('quotes:group:create:qli:' + this.model.get('id'), this.onAddNewItemToGroup, this);
        this.context.on('quotes:group:create:note:' + this.model.get('id'), this.onAddNewItemToGroup, this);
        this.context.on('editablelist:cancel:' + this.model.get('id'), this.onCancelRowEdit, this);
        this.context.on('editablelist:save:' + this.model.get('id'), this.onSaveRowEdit, this);
    },

    /**
     * Handler for when a new QLI/Note row has been added and then canceled
     *
     * @param {Data.Bean} rowModel The row collection model that was created and now canceled
     */
    onCancelRowEdit: function(rowModel) {
        if (rowModel._notSaved) {
            this.newIdsToSave = _.without(this.newIdsToSave, rowModel.get('id'));
            this.rowCollection.remove(rowModel);
        }
    },

    /**
     * Handles when a row is saved. Since newly added (but not saved) rows have temporary
     * id's assigned to them, this is needed to go back and fix row id html attributes and
     * also resets the rowFields with the new model's ID so rows toggle properly
     *
     * @param {Data.Bean} rowModel
     * @param oldModelId
     */
    onSaveRowEdit: function(rowModel, oldModelId) {
        var $oldRow;
        var modelId = rowModel.get('id');
        var modelModule = rowModel.module;

        if (rowModel.hasOwnProperty('_notSaved')) {
            // if the rowModel still has _notSaved on it, remove it
            delete rowModel._notSaved;

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

        // set the new model ID on the model
        relatedModel.set('id', newRelatedModelId);

        if (this.rowCollection.length) {
            // get the model with the highest position
            maxPositionModel = _.max(this.rowCollection.models, function(model) {
                return +model.get('position');
            });

            // get the position of the highest model's position and add one to it
            position = +maxPositionModel.get('position') + 1;
        }

        // set the new position for the row model
        relatedModel.set('position', position);

        // this model's fields should be set to render
        relatedModel.modelView = 'edit';

        // Set _notSaved flag on model so when we save we can remove the fake ID
        relatedModel._notSaved = true;

        // add model to toggledModels to be toggled next render
        this.toggledModels[relatedModel.id] = relatedModel;

        // adding to the collection will trigger the render
        this.rowCollection.add(relatedModel);
    },

    /**
     * Iterates through related_records on the model and builds this.rowCollection
     */
    buildRowsData: function() {
        var bundleItems = this.model.get('product_bundle_items');
        if (bundleItems && bundleItems.records) {
            _.each(bundleItems.records, function(record) {
                var bean = app.data.createBean(record._module, record);
                // reset modelView back to list
                bean.modelView = 'list';

                if (record._module === 'ProductBundleNotes' && bean.fields && bean.fields.description) {
                    bean.fields.description = _.extend(bean.fields.description, this.pbnDescriptionMetadata);
                }

                this.rowCollection.add(bean);
            }, this);
        }
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

        if (isEdit) {
            toggleModel = this.rowCollection.get(rowModelId);
            toggleModel.modelView = 'edit';
            this.toggledModels[rowModelId] = toggleModel;
        } else {
            if (this.toggledModels[rowModelId]) {
                this.toggledModels[rowModelId].modelView = 'list';
            }
            delete this.toggledModels[rowModelId];
        }

        this.$('tr[name=' + rowModule + '_' + rowModelId + ']').toggleClass('tr-inline-edit', isEdit);
        this.toggleFields(this.rowFields[rowModelId], isEdit);
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
        this._super('_dispose');
        this.rowFields = null;
    }
})
