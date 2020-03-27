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
 * Custom field type for conditional cascade fields. This field type
 * adds a checkbox to the frontend that enables/disables the parent field.
 * Usage Example:
 * [
 *     'name' => 'conditional_sales_stage',
 *     'type' => 'opportunities-conditional-fieldset',
 *     'label' => 'LBL_SALES_STAGE',
 *     'fields' => [
 *         'sales_stage',
 *      ],
 * ],
 *
 * That creates a fieldset that adds a checkbox below the `sales_stage` field to
 * disable it when the checkbox is false.
 *
 * @class View.Fields.Base.Opportunities.ConditionalFieldsetField
 * @alias SUGAR.App.view.fields.BaseOpportunitiesConditionalFieldsetField
 * @extends View.Fields.Base.FieldsetField
 */
({
    extendsFrom: 'FieldsetField',
    baseFieldName: '',
    boolFieldName: '',

    /**
     * @inheritdoc
     * @param options
     */
    initialize: function(options) {
        this.baseFieldName = this.options.def.fields[0].name;
        // We don't want to add our checkbox, or any of the relevant handlers
        // in create view, pipeline view, or in ops only mode
        var oppConfig = app.metadata.getModule('Opportunities', 'config');
        if (options.view.name !== 'create' &&
            oppConfig && oppConfig.opps_view_by === 'RevenueLineItems' &&
            !this._inPipelineView(options)) {

            this._addBoolField(options);
            this._bindEventListeners(options);

            // This is a hack to add translatable text after the checkbox
            // without disrupting document flow.
            var lblString = app.lang.get('LBL_UPDATE_OPPORTUNITIES_RLIS', 'Opportunities') + ' ' +
                             app.lang.getModuleName('RevenueLineItems', {plural: true});
            $('<style>.opportunities_cascade_condition::after{content:"'  +
                lblString + '";</style>').appendTo('head');
        }
        this._super('initialize', [options]);
        this.type = 'fieldset';
    },

    /**
     * Disable/Enable the base field when user clicks the checkbox
     *
     * @private
     */
    _toggleBaseField: function() {
        var baseField = this.view.getField(this.baseFieldName);
        if (this.model.get(this.boolFieldName)) {
            baseField.setDisabled(false, {'trigger': false});
        } else {
            this.model.set(this.baseFieldName, this.model.getSynced(this.baseFieldName));
            this.model.set(this.baseFieldName + '_cascade', '');
            baseField.setDisabled(true, {'trigger': false});
        }
    },

    /**
     * Set the value of our cascade field when the dropdown is updated to
     * a new value
     * @private
     */
    _setCascadeValue: function() {
        var cascadeValue = '';
        var syncedFieldValue = this.model.getSynced(this.baseFieldName);
        var currentFieldValue = this.model.get(this.baseFieldName);
        if (this.model.get(this.boolFieldName) &&
            syncedFieldValue !== currentFieldValue) {
            cascadeValue = currentFieldValue;
        }
        this.model.set(this.baseFieldName + '_cascade', cascadeValue);
    },

    /**
     * Util method to add a checkbox to our fieldset
     *
     * @param options options passed in when field is initialized
     * @private
     */
    _addBoolField: function(options) {
        this.boolFieldName = this.baseFieldName + '_should_cascade';
        var boolDef = {
            name: this.boolFieldName,
            type: 'bool',
            css_class: 'opportunities_cascade_condition',
        };
        this.options.def.fields.push(boolDef);
        // Ensure field is checked on render
        options.view.on('render', function() {
            this.model.set(this.boolFieldName, true);
        }, this);
    },

    /**
     * Util method to bind event listeners needed to make checkbox interactive
     *
     * @param options options bassed in when field is initialized
     * @private
     */
    _bindEventListeners: function(options) {
        // Bind change listeners to our fields
        this.model.on('change:' + this.boolFieldName, function() {
            this._toggleBaseField();
        }, this);
        this.model.on('change:' + this.baseFieldName, function() {
            this._setCascadeValue();
        }, this);

        // SugarLogic triggers this event when setting calculated fields
        // readonly. We hook in here to set the field according to the checkbox
        // instead.
        options.context.on('field:disabled', function(fieldName) {
            if (fieldName !== this.baseFieldName) {
                return;
            }
            this._toggleBaseField();
        }, this);
    },

    /**
     * Util to see if we're in Tile view. Tile view renders record view in a
     * side drawer. We don't need our bool field or event listeners if we're
     * in tile view.
     * @private
     */
    _inPipelineView: function(options) {
        var context = options.context;
        while (context) {
            if (context.get('layout') === 'pipeline-records') {
                return true;
            }
            context = context.parent;
        }
        return false;
    },
})
