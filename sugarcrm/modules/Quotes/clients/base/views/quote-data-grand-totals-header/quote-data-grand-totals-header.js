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
 * @class View.Views.Base.Quotes.QuoteDataGrandTotalsHeaderView
 * @alias SUGAR.App.view.views.BaseQuotesQuoteDataGrandTotalsHeaderView
 * @extends View.Views.Base.View
 */
({
    /**
     * @inheritdoc
     */
    events: {
        'click [name="create_qli_button"]' : '_onCreateQLIBtnClicked',
        'click [name="create_comment_button"]' : '_onCreateCommentBtnClicked',
        'click [name="create_group_button"]' : '_onCreateGroupBtnClicked'
    },

    /**
     * @inheritdoc
     */
    className: 'quote-data-grand-totals-header-wrapper quote-totals-row',

    /**
     * Contains an Array of the panel field objects to be displayed
     *
     * @type Array
     */
    panelFields: undefined,

    /**
     * Contains an Array of the panel field objects' names to determine
     * if we need to re-render the panel on a model change
     *
     * @type Array
     */
    panelFieldNames: undefined,

    /**
     * Contains a hash map of the panel fields for quick updating
     *
     * @type Object
     */
    panelFieldsObj: undefined,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        // initializing these here because they dont get reset to empty
        // arrays without a page load or having them here
        this.panelFields = [];
        this.panelFieldNames = [];
        this.panelFieldsObj = {};
        this._super('initialize', [options]);

        this.initPanelFieldNames();
    },

    /**
     * Initialize the panelFields and panelFieldNames Arrays
     * with default values for the fields in this view
     */
    initPanelFieldNames: function() {
        _.each(this.meta.panels, function(panel) {
            _.each(panel.fields, function(field, i) {
                field.value = this.model.get(field.name) || '0';
                field.label = app.lang.get(field.label, 'Quotes');

                // format field for current locale
                field.value = app.currency.formatAmountLocale(field.value);

                this.panelFields.push(field);
                this.panelFieldNames.push(field.name);
                this.panelFieldsObj[field.name] = field;
            }, this);
        }, this);
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this.model.on('change', this.onChangeCheckRender, this);
    },

    /**
     * Handles when the model changes, checks to see if any of the changed fields on the model were
     * fields used in this view.  If so, triggers the view to re-render only if one of the fields changed.
     *
     * @param {Data.Bean} model
     */
    onChangeCheckRender: function(model) {
        var needsRender = _.some(_.keys(model.changed), function(key) {
            return _.contains(this.panelFieldNames, key);
        }, this);

        if (needsRender) {
            this.render();
        }
    },

    /**
     * @inheritdoc
     */
    _render: function() {
        var val;

        _.each(this.panelFieldNames, function(fieldName) {
            val = this.model.get(fieldName) || '0';
            this.panelFieldsObj[fieldName].value = app.currency.formatAmountLocale(val);
        }, this);

        // render the field into the its placeholder
        return this._super('_render');
    },

    /**
     * Handles when the create Quoted Line Item button is clicked
     *
     * @param {MouseEvent} evt The mouse click event
     * @private
     */
    _onCreateQLIBtnClicked: function(evt) {

    },

    /**
     * Handles when the create Comment button is clicked
     *
     * @param {MouseEvent} evt The mouse click event
     * @private
     */
    _onCreateCommentBtnClicked: function(evt) {

    },

    /**
     * Handles when the create Group button is clicked
     *
     * @param {MouseEvent} evt The mouse click event
     * @private
     */
    _onCreateGroupBtnClicked: function(evt) {

    }
})
