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
        'click [name="create_qli_button"]': '_onCreateQLIBtnClicked',
        'click [name="create_comment_button"]': '_onCreateCommentBtnClicked',
        'click [name="edit_bundle_button"]': '_onEditBundleBtnClicked',
        'click [name="delete_bundle_button"]': '_onDeleteBundleBtnClicked'
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
     * The CSS class for the save icon
     */
    saveIconCssClass: '.group-loading-icon',

    /**
     * How many times the group has been called to start or stop saving
     */
    groupSaveCt: undefined,

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

        this.groupSaveCt = 0;
        this.layout.on('quotes:group:save:start', this._onGroupSaveStart, this);
        this.layout.on('quotes:group:save:stop', this._onGroupSaveStop, this);
    },

    /**
     * Handles displaying the loading icon when a group starts saving
     *
     * @private
     */
    _onGroupSaveStart: function() {
        this.groupSaveCt++;
        this.$(this.saveIconCssClass).show();
    },

    /**
     * Handles hiding the loading icon when a group save is complete
     *
     * @private
     */
    _onGroupSaveStop: function() {
        this.groupSaveCt--;
        if (this.groupSaveCt === 0) {
            this.$(this.saveIconCssClass).hide();
        }

        if (this.groupSaveCt < 0) {
            this.groupSaveCt = 0;
        }
    },

    /**
     * Handles when the create Quoted Line Item button is clicked
     *
     * @param {MouseEvent} evt The mouse click event
     * @private
     */
    _onCreateQLIBtnClicked: function(evt) {
        this.context.trigger('quotes:group:create:qli:' + this.model.get('id'), this.model, 'products');
    },

    /**
     * Handles when the create Comment button is clicked
     *
     * @param {MouseEvent} evt The mouse click event
     * @private
     */
    _onCreateCommentBtnClicked: function(evt) {
        this.context.trigger('quotes:group:create:note:' + this.model.get('id'), this.model, 'product_bundle_notes');
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
        this.context.parent.trigger('quotes:group:delete', this.layout);
    }
})
