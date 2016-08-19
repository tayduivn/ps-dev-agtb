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
 * @class View.Views.Base.ProductBundles.QuoteDataGroupFooterView
 * @alias SUGAR.App.view.views.BaseProductBundlesQuoteDataGroupFooterView
 * @extends View.Views.Base.View
 */
({
    /**
     * The colspan value for the list
     */
    listColSpan: 0,

    /**
     * Array of fields to use in the template
     */
    _fields: undefined,

    /**
     * If this group is an empty group
     */
    isEmptyGroup: undefined,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        var groupId;

        options.model = options.model || options.layout.model;

        // +1 to colspan since there are no leftColumns in the footer
        this.listColSpan = options.layout.listColSpan + 1;

        this._super('initialize', [options]);

        this._fields = _.flatten(_.pluck(this.meta.panels, 'fields'));

        this.isEmptyGroup = this.layout.collection.length === 0;

        // ninjastuff
        this.el = this.layout.el;
        this.setElement(this.el);

        groupId = this.model.get('id');

        // these events come from different button clicks or actions and are all handled by the same fn
        this.context.on('quotes:group:create:qli:' + groupId, this._onNewItemChanged, this);
        this.context.on('quotes:group:create:note:' + groupId, this._onNewItemChanged, this);
        this.context.on('editablelist:cancel:' + groupId, this._onNewItemChanged, this);
        this.context.on('quotes:group:changed:' + groupId, this._onNewItemChanged, this);

        // listen directly on the parent QuoteDataGroupLayout
        this.layout.on('quotes:sortable:over', this._onSortableGroupOver, this);
        this.layout.on('quotes:sortable:out', this._onSortableGroupOut, this);

        this.layout.collection.on('add remove', this._onNewItemChanged, this);
    },

    /**
     * Handles updating if we should show the empty row when QLI/Notes have
     * been created or canceled before saving
     *
     * @private
     */
    _onNewItemChanged: function() {
        this.isEmptyGroup = this.layout.collection.length === 0;
        this.toggleEmptyRow(this.isEmptyGroup);
    },

    /**
     * Handles when this group receives a sortover event that the user
     * has dragged an item into this group
     *
     * @param {jQuery.Event} evt The jQuery sortover event
     * @param {Object} ui The jQuery Sortable UI Object
     * @private
     */
    _onSortableGroupOver: function(evt, ui) {
        // When entering a new group, always hide the empty row
        this.toggleEmptyRow(false);
    },

    /**
     * Handles when this group receives a sortout event that the user has
     * dragged an item out of this group
     *
     * @param {jQuery.Event} evt The jQuery sortout event
     * @param {Object} ui The jQuery Sortable UI Object
     * @private
     */
    _onSortableGroupOut: function(evt, ui) {
        var isSenderNull = _.isNull(ui.sender);
        var isSenderSameGroup = isSenderNull ||
            ui.sender.length && ui.sender.get(0) === this.el;

        // if the group was originally empty, show the empty row
        // if the group was not empty and had more than one row in it, hide the empty row
        var showEmptyRow = this.isEmptyGroup;

        // if there is only one item in this group, and the out event happens on a group that is the line item's
        // original group, and the existing single row is currently hidden,
        // set showEmptyRow = true so we show the Click + message
        if (this.layout.collection.length === 1 &&
            isSenderSameGroup && $(ui.item.get(0)).css('display') === 'none') {
            showEmptyRow = true;
        }

        this.toggleEmptyRow(showEmptyRow);
    },

    /**
     * Toggles showing and hiding the empty-row message row
     *
     * @param {boolean} showEmptyRow True to show the empty row, false to hide it
     */
    toggleEmptyRow: function(showEmptyRow) {
        if (showEmptyRow) {
            this.$('.empty-row').removeClass('hidden');
        } else {
            this.$('.empty-row').addClass('hidden');
        }
    },

    /**
     * Overriding _renderHtml to specifically place this template after the
     * quote data group list rows
     *
     * @inheritdoc
     * @override
     */
    _renderHtml: function() {
        var $els = this.$('tr.quote-data-group-list');
        if ($els.length) {
            // get the last table row with calss quote-data-group-list and place
            // this template after it  quote-data-group-header
            $(_.last($els)).after(this.template(this));
        } else {
            // the list is empty so just add the footer after the header
            $(this.$('tr.quote-data-group-header')).after(this.template(this));
        }

        this.toggleEmptyRow(this.isEmptyGroup);
    }
})
