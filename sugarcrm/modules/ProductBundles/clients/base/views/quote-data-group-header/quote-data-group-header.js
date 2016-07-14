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
 * @class View.Views.Base.ProductBundles.QuoteDataGroupHeaderView
 * @alias SUGAR.App.view.views.BaseProductBundlesQuoteDataGroupHeaderView
 * @extends View.Views.Base.View
 */
({
    /**
     * @inheritdoc
     */
    events: {
        'click [name="create_qli_button"]' : '_onCreateQLIBtnClicked',
        'click [name="create_comment_button"]' : '_onCreateCommentBtnClicked',
        'click [name="edit_bundle_button"]' : '_onEditBundleBtnClicked',
        'click [name="delete_bundle_button"]' : '_onDeleteBundleBtnClicked'
    },

    /**
     * @inheritdoc
     */
    plugins: [
        'MassCollection'
    ],

    /**
     * Array of fields to use in the template
     */
    _fields: undefined,

    /**
     * The colspan value for the list
     */
    listColSpan: 0,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        // make sure we're using the layout's model
        options.model = options.model || options.layout.model;

        this.listColSpan = options.layout.listColSpan;

        this._super('initialize', [options]);

        this._fields = _.flatten(_.pluck(this.meta.panels, 'fields'));

        // ninjastuff
        this.el = this.layout.el;
        this.setElement(this.el);
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
     * Handles when the edit Group button is clicked
     *
     * @param {MouseEvent} evt The mouse click event
     * @private
     */
    _onEditBundleBtnClicked: function(evt) {

    },

    /**
     * Handles when the delete Group button is clicked
     *
     * @param {MouseEvent} evt The mouse click event
     * @private
     */
    _onDeleteBundleBtnClicked: function(evt) {

    }
})
