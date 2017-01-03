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
        'click [name="group_button"]': '_onCreateGroupBtnClicked',
        'click [name="massdelete_button"]': '_onDeleteBtnClicked'
    },

    /**
     * @inheritdoc
     */
    plugins: [
        'MassCollection',
        'QuotesLineNumHelper'
    ],

    /**
     * @inheritdoc
     */
    tagName: 'thead',

    /**
     * @inheritdoc
     */
    className: 'quote-data-list-header',

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
        console.log(this.meta.panels);

        if (this.layout.isCreateView) {
            this.leftColumns.push({
                'type': 'fieldset',
                'fields': [],
                'value': false,
                'sortable': false
            });
        } else {
            this.addMultiSelectionAction();
        }

        this._fields = _.flatten(_.pluck(this.meta.panels, 'fields'));
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this._super('bindDataChange');

        // massCollection has the Quote record as its only model,
        // reset this during initialization so it's empty
        if (this.massCollection) {
            this.massCollection.on('add remove reset', this._massCollectionChange, this);
        }
    },

    /**
     * Called when items are added or removed from the massCollection
     *
     * @param {Data.Bean} model The model that was added or removed
     * @param {Data.MixedBeanCollection} massCollection The mass collection on the context
     * @private
     */
    _massCollectionChange: function(model, massCollection) {
        var setDisabled = true;
        var groupBtn = _.find(this.fields, function(field) {
            return field.name === 'group_button';
        });
        var massDeleteBtn = _.find(this.fields, function(field) {
            return field.name === 'massdelete_button';
        });

        if (massCollection.length) {
            setDisabled = false;
        }

        if (groupBtn) {
            groupBtn.setDisabled(setDisabled);
        }
        if (massDeleteBtn) {
            massDeleteBtn.setDisabled(setDisabled);
        }
    },

    /**
     * @inheritdoc
     */
    _render: function() {
        this._super('_render');

        if (this.massCollection) {
            // remove any Quotes models from the massCollectio
            this.massCollection.models = _.filter(this.massCollection.models, function(model) {
                return model.module !== 'Quotes';
            });
        }
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
        if (this.massCollection.length) {
            this.context.on('quotes:group:create:success', this._onNewGroupedItemsCreateSuccess, this);
            this.context.trigger('quotes:group:create');
        } else {
            app.alert.show('quote_grouping_message', {
                level: 'error',
                title: '',
                messages: [
                    app.lang.get('LBL_GROUP_NOTHING_SELECTED', this.module)
                ]
            });
        }
    },

    /**
     * Called when the group in which any selected items are to be grouped has
     * successfully been saved. Clears app alerts and removes the context listener
     * for the create success event
     *
     * @param {Object} newGroupData The new ProductBundle to add selected items into
     * @private
     */
    _onNewGroupedItemsCreateSuccess: function(newGroupData) {
        this.context.off('quotes:group:create:success', this._onNewGroupedItemsCreateSuccess);
        this.layout.moveMassCollectionItemsToNewGroup(newGroupData);
    },

    /**
     * Handles when the Delete button is clicked
     *
     * @param {MouseEvent} evt The mouse click event
     * @private
     */
    _onDeleteBtnClicked: function(evt) {
        var deleteConfirmMsg = 'LBL_ALERT_CONFIRM_DELETE';
        if (this.massCollection.length) {
            if (this.massCollection.length > 1) {
                deleteConfirmMsg += '_PLURAL';
            }

            app.alert.show('confirm_delete', {
                level: 'confirmation',
                title: app.lang.get('LBL_ALERT_TITLE_WARNING') + ':',
                messages: [app.lang.get(deleteConfirmMsg, '')],
                onConfirm: _.bind(function() {
                    app.alert.show('deleting_line_item', {
                        level: 'info',
                        messages: [app.lang.get('LBL_ALERT_DELETING_ITEM', 'ProductBundles')]
                    });
                    this.context.trigger('quotes:selected:delete', this.massCollection);
                }, this)
            });
        } else {
            app.alert.show('quote_grouping_message', {
                level: 'error',
                title: '',
                messages: [
                    app.lang.get('LBL_DELETE_NOTHING_SELECTED', this.module)
                ]
            });
        }
    },

    /**
     * @inheritdoc
     */
    _dispose: function() {
        // in case something weird happens where this view gets
        // disposed between adding the listener and removing,
        // go ahead and remove it on dispose if it exists
        this.context.off('quotes:group:create:success', null, this);

        if (this.massCollection) {
            this.massCollection.off('add remove reset', null, this);
        }

        this._super('_dispose');
    }
})
