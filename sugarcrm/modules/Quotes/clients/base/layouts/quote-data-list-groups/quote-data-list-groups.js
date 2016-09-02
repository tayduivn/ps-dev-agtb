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
 * @class View.Layouts.Base.Quotes.QuoteDataListGroupsLayout
 * @alias SUGAR.App.view.layouts.BaseQuotesQuoteDataListGroupsLayout
 * @extends View.Views.Base.Layout
 */
({
    /**
     * @inheritdoc
     */
    tagName: 'table',

    /**
     * @inheritdoc
     */
    className: 'table dataTable quote-data-list-table',

    /**
     * Array of records from the Quote data
     */
    records: undefined,

    /**
     * An Array of ProductBundle IDs currently in the Quote
     */
    groupIds: undefined,

    /**
     * Holds the layout metadata for ProductBundlesQuoteDataGroupLayout
     */
    quoteDataGroupMeta: undefined,

    /**
     * The Element tag to apply jQuery.Sortable on
     */
    sortableTag: 'tbody',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.groupIds = [];
        this.quoteDataGroupMeta = app.metadata.getLayout('ProductBundles', 'quote-data-group');

        this.before('render', this.beforeRender, this);
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this.model.on('change:bundles', this._onProductBundleChange, this);
        this.context.on('quotes:group:create', this._onCreateQuoteGroup, this);
        this.context.on('quotes:group:delete', this._onDeleteQuoteGroup, this);
    },

    /**
     * @inheritdoc
     */
    _render: function() {
        var sortableItems;

        this._super('_render');

        sortableItems = this.$(this.sortableTag);
        if (sortableItems.length) {
            _.each(sortableItems, function(sortableItem) {
                $(sortableItem).sortable({
                    // allow draggable items to be connected with other tbody elements
                    connectWith: 'tbody',
                    // allow drag to only go in Y axis direction
                    axis: 'y',
                    // the items to make sortable
                    items: 'tr.sortable',
                    // make the "helper" row (the row the user actually drags around) a clone of the original row
                    helper: 'clone',
                    // adds a slow animation when "dropping" a group, removing this causes the row
                    // to immediately snap into place wherever it's sorted
                    revert: true,
                    // the CSS class to apply to the placeholder underneath the helper clone the user is dragging
                    placeholder: 'ui-state-highlight',
                    // handler for when dragging starts
                    start: _.bind(this._onDragStart, this),
                    // handler for when dragging stops; the "drop" event
                    stop: _.bind(this._onDragStop, this),
                    // handler for when dragging an item into a group
                    over: _.bind(this._onGroupDragTriggerOver, this),
                    // handler for when dragging an item out of a group
                    out: _.bind(this._onGroupDragTriggerOut, this),
                    // the cursor to use when dragging
                    cursor: 'move'
                }).disableSelection();
            }, this);
        }
    },

    /**
     * Event handler for the sortstart "drag" event
     *
     * @param {jQuery.Event} evt The jQuery sortstart event
     * @param {Object} ui The jQuery Sortable UI Object
     * @private
     */
    _onDragStart: function(evt, ui) {
        // clear the current displayed tooltip
        app.tooltip.clear();
        // disable any future tooltips from appearing until drag stop has occurred
        app.tooltip._disable();
    },

    /**
     * Event handler for the sortstop "drop" event
     *
     * @param {jQuery.Event} evt The jQuery sortstop event
     * @param {Object} ui The jQuery Sortable UI Object
     * @private
     */
    _onDragStop: function(evt, ui) {
        var $item = $(ui.item.get(0));
        var oldGroupId = $item.data('group-id');
        var newGroupId = $($item.parent()).data('group-id');
        // check if the row is in edit mode
        var isRowInEdit = $item.hasClass('tr-inline-edit');
        var triggerOldGroup = false;
        var oldGroup;
        var newGroup;
        var rowId;
        var rowModel;
        var bulkSaveRequests = [];
        var url;
        var linkName;

        // make sure item was dropped in a different group than it started in
        if (oldGroupId !== newGroupId) {
            // since the groups are different, also trigger events for the old group
            triggerOldGroup = true;

            // get the old and new quote-data-group components
            oldGroup = this._getComponentByGroupId(oldGroupId);
            newGroup = this._getComponentByGroupId(newGroupId);

            // get the row id from the name="Products_modelID" attrib
            rowId = $item.attr('name').split('_')[1];

            // get the row model from the old group's collection
            rowModel = oldGroup.collection.get(rowId);

            // remove the rowModel from the old group
            oldGroup.removeRowModel(rowModel, isRowInEdit);

            // add rowModel to the new group
            newGroup.addRowModel(rowModel, isRowInEdit);

            // get the requests from updated rows for old and new group
            bulkSaveRequests = bulkSaveRequests.concat(this._updateRowPositions(oldGroup));
            bulkSaveRequests = bulkSaveRequests.concat(this._updateRowPositions(newGroup));

            linkName = rowModel.module === 'Products' ? 'products' : 'product_bundle_notes';
            url = app.api.buildURL('ProductBundles/' + newGroupId + '/link/' + linkName + '/' + rowId);

            // add the group switching call to the beginning of the bulk requests
            bulkSaveRequests.unshift({
                url: url.substr(4),
                method: 'POST',
                data: {
                    id: newGroupId,
                    link: linkName,
                    relatedId: rowModel.get('id'),
                    related: {
                        position: rowModel.get('position')
                    }
                }
            });
        } else {
            // item was sorted in the same group
            newGroup = this._getComponentByGroupId(newGroupId);
            // get the requests from updated rows
            bulkSaveRequests = bulkSaveRequests.concat(this._updateRowPositions(newGroup));
        }

        // only make the bulk call if there are actual requests, if user drags row
        // but puts it in same place there should be no updates
        if (!_.isEmpty(bulkSaveRequests)) {
            if (triggerOldGroup) {
                // trigger group changed for old group to check themselves
                oldGroup.trigger('quotes:group:changed');
                // trigger save start for the old group
                oldGroup.trigger('quotes:group:save:start');
            }

            // trigger group changed for new group to check themselves
            newGroup.trigger('quotes:group:changed');
            // trigger save start for the new group
            newGroup.trigger('quotes:group:save:start');

            app.api.call('create', app.api.buildURL(null, 'bulk'), {
                requests: bulkSaveRequests
            }, {
                success: _.bind(this._onSaveUpdatedGroupSuccess, this, oldGroup, newGroup)
            });
        }

        // re-enable tooltips in the app
        app.tooltip._enable();
    },

    /**
     * The success event handler for when a user reorders or moves an item to a different group
     *
     * @param {View.QuoteDataGroupLayout} oldGroup The old QuoteDataGroupLayout
     * @param {View.QuoteDataGroupLayout} newGroup The new QuoteDataGroupLayout
     * @param {Array} bulkResponses The responses from each of the bulk requests
     * @protected
     */
    _onSaveUpdatedGroupSuccess: function(oldGroup, newGroup, bulkResponses) {
        if (oldGroup) {
            oldGroup.trigger('quotes:group:save:stop');
        }
        newGroup.trigger('quotes:group:save:stop');

        _.each(bulkResponses, _.bind(function(oldGroup, newGroup, data) {
            var record = data.contents.related_record;
            var model;
            if (oldGroup) {
                // if oldGroup exists, check if the record is in the oldGroup
                model = oldGroup.collection.get(record.id);
                if (model) {
                    model.set(record);
                }
            }

            if (newGroup) {
                // check if the record is in the newGroup
                model = newGroup.collection.get(record.id);
                if (model) {
                    model.set(record);
                }
            }
        }, this, oldGroup, newGroup), this);
    },

    /**
     * Iterates through all rows in a group and updates the positions for the rows if necessary
     *
     * @param {View.QuoteDataGroupLayout} dataGroup The group component
     * @return {Array}
     * @protected
     */
    _updateRowPositions: function(dataGroup) {
        var retCalls = [];
        var rows = dataGroup.$('tr.quote-data-group-list:not(:hidden):not(.empty-row)');
        var $row;
        var rowNameSplit;
        var rowId;
        var rowModule;
        var rowModel;
        var url;
        var linkName;

        _.each(rows, _.bind(function(dataGroup, retObj, row, index) {
            $row = $(row);
            rowNameSplit = $row.attr('name').split('_');
            rowModule = rowNameSplit[0];
            rowId = rowNameSplit[1];

            rowModel = dataGroup.collection.get(rowId);
            if (rowModel.get('position') != index) {
                linkName = rowModule === 'Products' ? 'products' : 'product_bundle_notes';
                url = app.api.buildURL('ProductBundles/' + dataGroup.groupId + '/link/' + linkName + '/' + rowId);
                retCalls.push({
                    url: url.substr(4),
                    method: 'PUT',
                    data: {
                        position: index
                    }
                });

                rowModel.set('position', index);
            }
        }, this, dataGroup, retCalls), this);

        return retCalls;
    },

    /**
     * Gets a quote-data-group component by the group ID
     *
     * @param {string} groupId The group's id
     * @protected
     */
    _getComponentByGroupId: function(groupId) {
        return _.find(this._components, function(group) {
            return group.name === 'quote-data-group' && group.groupId === groupId;
        });
    },

    /**
     * Handles when user drags an item into/over a group
     *
     * @param {jQuery.Event} evt The jQuery sortover event
     * @param {Object} ui The jQuery Sortable UI Object
     * @protected
     */
    _onGroupDragTriggerOver: function(evt, ui) {
        var groupId = $(evt.target).data('group-id');
        var group = this._getComponentByGroupId(groupId);
        if (group) {
            group.trigger('quotes:sortable:over', evt, ui);
        }
    },

    /**
     * Handles when user drags an item out of a group
     *
     * @param {jQuery.Event} evt The jQuery sortout event
     * @param {Object} ui The jQuery Sortable UI Object
     * @private
     */
    _onGroupDragTriggerOut: function(evt, ui) {
        var groupId = $(evt.target).data('group-id');
        var group = this._getComponentByGroupId(groupId);
        if (group) {
            group.trigger('quotes:sortable:out', evt, ui);
        }
    },

    /**
     * Removes the sortable plugin from any rows that have the plugin added
     * so we don't add plugin multiple times and for dispose cleanup
     */
    beforeRender: function() {
        var groups = this.$(this.sortableTag);
        if (groups.length) {
            _.each(groups, function(group) {
                if ($(group).hasClass('ui-sortable')) {
                    $(group).sortable('destroy');
                }
            }, this);
        }
    },

    /**
     * Handler for when quote_data changes on the model
     */
    _onProductBundleChange: function(productBundles) {
        // fixme: SFA-4399 will add "groupless" rows in here before the groups
        // after adding and deleting the change event is like ite change for the model, where the
        // model is the first param and not the actual value it's self.
        if (productBundles instanceof Backbone.Model) {
            productBundles = productBundles.get('bundles');
        }

        productBundles.each(function(bundle) {
            if (!_.contains(this.groupIds, bundle.get('id'))) {
                this.groupIds.push(bundle.get('id'));
                this._addQuoteGroupToLayout(bundle);
            }
        }, this);

        this.render();
    },

    /**
     * Adds the actual quote-data-group layout component to this layout
     *
     * @param {Object} [bundleData] The ProductBundle data object - defaults to empty Object
     * @private
     */
    _addQuoteGroupToLayout: function(bundle) {
        var pbContext = this.context.getChildContext({module: 'ProductBundles'});
        pbContext.prepare();
        var groupLayout = app.view.createLayout({
            context: pbContext,
            meta: this.quoteDataGroupMeta,
            type: 'quote-data-group',
            layout: this,
            module: 'ProductBundles',
            model: bundle
        });

        groupLayout.initComponents(undefined, pbContext, 'ProductBundles');
        this.addComponent(groupLayout);
    },

    /**
     * Handles the quotes:group:create event
     * Creates a new empty quote data group and renders the groups
     *
     * @private
     */
    _onCreateQuoteGroup: function() {
        var bundles = this.model.get('bundles');
        var nextPosition = 0;
        var highestPositionBundle = bundles.max(function(bundle) {
            return bundle.get('position');
        });

        // handle on the off chance that no bundles exist on the quote.
        if (!_.isEmpty(highestPositionBundle)) {
            nextPosition = parseInt(highestPositionBundle.get('position')) + 1;
        }

        app.alert.show('adding_bundle_alert', {
            level: 'info',
            autoClose: false,
            messages: app.lang.get('LBL_ADDING_BUNDLE_ALERT_MSG', 'Quotes')
        });

        app.api.relationships('create', 'Quotes', {
            'id': this.model.get('id'),
            'link': 'product_bundles',
            'related': {
                position: nextPosition
            }
        }, null, {
            success: _.bind(this._onCreateQuoteGroupSuccess, this)
        });
    },

    /**
     * Success callback handler for when a quote group is created
     *
     * @param {Object} newBundleData The new Quote group data
     * @private
     */
    _onCreateQuoteGroupSuccess: function(newBundleData) {
        app.alert.dismiss('adding_bundle_alert');

        app.alert.show('added_bundle_alert', {
            level: 'success',
            autoClose: true,
            messages: app.lang.get('LBL_ADDED_BUNDLE_SUCCESS_MSG', 'Quotes')
        });

        var bundles = this.model.get('bundles');
        // make sure that the product_bundle_items array is there
        if (_.isUndefined(newBundleData.related_record.product_bundle_items)) {
            newBundleData.related_record.product_bundle_items = [];
        }
        // now add the new record to the bundles collection
        bundles.add(newBundleData.related_record);
    },

    /**
     * Deletes the passed in ProductBundle
     *
     * @param {ProductBundlesQuoteDataGroupLayout} groupToDelete The group layout to delete
     * @private
     */
    _onDeleteQuoteGroup: function(groupToDelete) {
        var groupId = groupToDelete.model.get('id');
        var groupName = groupToDelete.model.get('name') || '';

        app.alert.show('confirm_delete_bundle', {
            level: 'confirmation',
            autoClose: false,
            messages: app.lang.get('LBL_DELETING_BUNDLE_CONFIRM_MSG', 'Quotes', {
                groupName: groupName
            }),
            onConfirm: _.bind(this._onDeleteQuoteGroupConfirm, this, groupId, groupName, groupToDelete)
        });
    },

    /**
     * Handler for when the delete quote group confirm box is confirmed
     *
     * @param {string} groupId The model ID of the deleted group
     * @param {string} groupName The model name of the deleted group
     * @param {View.Layout} groupToDelete The Layout for the deleted group
     * @private
     */
    _onDeleteQuoteGroupConfirm: function(groupId, groupName, groupToDelete) {
        app.alert.show('deleting_bundle_alert', {
            level: 'info',
            autoClose: false,
            messages: app.lang.get('LBL_DELETING_BUNDLE_ALERT_MSG', 'Quotes', {
                groupName: groupName
            })
        });

        app.api.records('delete', 'ProductBundles', {
            id: groupId
        }, null, {
            success: _.bind(this._onDeleteQuoteGroupSuccess, this, groupId, groupToDelete)
        });
    },

    /**
     * Success callback when the quote group is deleted
     *
     * @param {string} groupId The model ID of the deleted group
     * @param {View.Layout} groupToDelete The Layout for the deleted group
     * @private
     */
    _onDeleteQuoteGroupSuccess: function(groupId, groupToDelete) {
        app.alert.dismiss('deleting_bundle_alert');

        app.alert.show('deleted_bundle_alert', {
            level: 'success',
            autoClose: true,
            messages: app.lang.get('LBL_DELETED_BUNDLE_SUCCESS_MSG', 'Quotes')
        });

        var bundles = this.model.get('bundles');
        bundles.remove(groupToDelete.model);

        this.groupIds = _.without(this.groupIds, groupId);

        // dispose the group
        groupToDelete.dispose();
    },

    /**
     * @inheritdoc
     */
    _dispose: function() {
        this.beforeRender();
        this._super('_dispose');
    }
})
