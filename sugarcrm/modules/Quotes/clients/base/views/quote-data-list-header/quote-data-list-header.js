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
 * @class View.Views.Base.Quotes.QuoteDataListHeaderView
 * @alias SUGAR.App.view.views.BaseQuotesQuoteDataListHeaderView
 * @extends View.Views.Base.View
 */
({
    /**
     * @inheritdoc
     */
    events: {
        'click [name="group_button"]' : '_onCreateGroupBtnClicked',
        'click [name="massdelete_button"]' : '_onDeleteBtnClicked'
    },

    /**
     * @inheritdoc
     */
    plugins: [
        'MassCollection'
    ],

    /**
     * @inheritdoc
     */
    tagName: 'thead',

    /**
     * @inheritdoc
     */
    className: 'flex-list-view scroll-width left-actions quote-data-list-header',

    /**
     * Array of left column fields
     */
    leftColumns: undefined,

    /**
     * Array of fields to use in the template
     */
    _fields: undefined,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.leftColumns = [];

        var qliListMetadata = app.metadata.getView('Products', 'quote-data-group-list');
        if (qliListMetadata && qliListMetadata.panels) {
            this.meta.panels = qliListMetadata.panels;
        }

        this.addMultiSelectionAction();
        this._fields = _.flatten(_.pluck(this.meta.panels, 'fields'));
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
     * Handles when the create Group button is clicked
     *
     * @param {MouseEvent} evt The mouse click event
     * @private
     */
    _onCreateGroupBtnClicked: function(evt) {

    },

    /**
     * Handles when the Delete button is clicked
     *
     * @param {MouseEvent} evt The mouse click event
     * @private
     */
    _onDeleteBtnClicked: function(evt) {

    }
})
