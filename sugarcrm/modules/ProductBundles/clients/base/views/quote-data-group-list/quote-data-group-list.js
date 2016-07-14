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
     * @inheritdoc
     */
    initialize: function(options) {
        // make sure we're using the layout's model
        options.model = options.model || options.layout.model;
        this.rowCollection = new Backbone.Collection();
        this.listColSpan = options.layout.listColSpan;

        this._super('initialize', [options]);

        this.viewName = 'list';

        var quotesListMeta = app.metadata.getView('Quotes', 'quote-data-list-header');
        this._fields = _.flatten(_.pluck(quotesListMeta.panels, 'fields'));

        this.leftColumns = [];
        this.addMultiSelectionAction();

        /**
         * REAL NINJA SHIT HERE
         * Due to BackboneJS, this view would have a wrapper tag around it e.g. QuoteDataGroupHeader.tagName "tr"
         * so this would have also been wrapped in div/tr whatever the tagName was for the view.
         * I am setting this.el to be the Layout's el (QuoteDataGroupLayout) which is a tbody element.
         * In the render function I am then manually appending this list of records template
         * after the group header tr row
         */
        this.el = this.layout.el;
        this.setElement(this.el);

        this.buildRowsData();
    },

    /**
     * Iterates through related_records on the model and builds this.rowCollection
     */
    buildRowsData: function() {
        var bean;
        _.each(this.model.get('related_records'), function(record) {
            bean = app.data.createBean(record._module, record);
            this.rowCollection.add(bean);
        }, this);
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
    },

    /**
     * Handles when the Delete button is clicked
     *
     * @param {MouseEvent} evt The mouse click event
     * @private
     */
    _onEditRowBtnClicked: function(evt) {

    },

    /**
     * Handles when the Delete button is clicked
     *
     * @param {MouseEvent} evt The mouse click event
     * @private
     */
    _onDeleteRowBtnClicked: function(evt) {

    }
})
