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
     * The ID of the default group
     */
    defaultGroupId: undefined,

    /**
     * If this layout is currently in the /create view or not
     */
    isCreateView: undefined,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.groupIds = [];
        this.quoteDataGroupMeta = app.metadata.getLayout('ProductBundles', 'quote-data-group');

        this.isCreateView = this.context.get('create') || false;

        this.before('render', this.beforeRender, this);
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this.model.on('change:show_line_nums', this._onShowLineNumsChanged, this);
        this.model.on('change:bundles', this._onProductBundleChange, this);
        this.context.on('quotes:group:create', this._onCreateQuoteGroup, this);
        this.context.on('quotes:group:delete', this._onDeleteQuoteGroup, this);
        this.context.on('quotes:defaultGroup:create', this._onCreateDefaultQuoteGroup, this);
        this.context.on('quotes:defaultGroup:save', this._onSaveDefaultQuoteGroup, this);

        // check if this is create mode, in which case add an empty array to bundles
        if (this.isCreateView) {
            this.model.set({
                bundles: []
            });
        }
    },

    /**
     * Handles when the show_line_nums attrib changes on the Quotes model, triggers if
     * line numbers should be shown or not
     *
     * @param {Data.Bean} model The Quotes Bean the change happened on
     * @param {boolean} showLineNums If the line nums should be shown or not
     * @private
     */
    _onShowLineNumsChanged: function(model, showLineNums) {
        this.context.trigger('quotes:show_line_nums:changed', showLineNums);
    },

    /**
     * Handles the quotes:defaultGroup:create event from a separate layout context
     * and triggers the correct create event on the default group to add a new item
     *
     * @param {string} itemType The type of item to create: 'qli' or 'note'
     * @private
     */
    _onCreateDefaultQuoteGroup: function(itemType) {
        var linkName = itemType == 'qli' ? 'products' : 'product_bundle_notes';
        var group = this._getComponentByGroupId(this.defaultGroupId);
        group.trigger('quotes:group:create:' + itemType, linkName);
    },

    /**
     * @inheritdoc
     */
    _render: function() {
        var sortableItems;
        var cssClasses;

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
                });
            }, this);
        }

        //wrap in container div for scrolling
        if (!this.$el.parent().hasClass('flex-list-view-content')) {
            cssClasses = 'flex-list-view-content';
            if (this.isCreateView) {
                cssClasses += ' create-view';
            }
            this.$el.wrap(
                '<div class="' + cssClasses + '"></div>'
            );
            this.$el.parent().wrap(
                '<div class="flex-list-view scroll-width left-actions quote-data-table-scrollable"></div>'
            );
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
        var saveDefaultGroup;
        var newPosition;
        var existingRows;

        // get the new group (may be the same group)
        newGroup = this._getComponentByGroupId(newGroupId);

        // make sure item was dropped in a different group than it started in
        if (oldGroupId !== newGroupId) {
            // since the groups are different, also trigger events for the old group
            triggerOldGroup = true;

            // get the old and new quote-data-group components
            oldGroup = this._getComponentByGroupId(oldGroupId);

            // get the new position index
            existingRows = newGroup.$('tr.quote-data-group-list:not(:hidden):not(.empty-row)');
            newPosition = _.findIndex(existingRows, function(item) {
                return ($(item).attr('name') == $item.attr('name'));
            });

            // get if we need to save the new default group list or not
            saveDefaultGroup = newGroup.model.get('_notSaved') || false;

            // get the row id from the name="Products_modelID" attrib
            rowId = $item.attr('name').split('_')[1];

            // get the row model from the old group's collection
            rowModel = oldGroup.collection.get(rowId);

            // set the new position, so it's only set when the item is saved via the relationship change
            // and not again for the position update
            rowModel.set('position', newPosition);

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
            // get the requests from updated rows
            bulkSaveRequests = bulkSaveRequests.concat(this._updateRowPositions(newGroup));
        }

        // only make the bulk call if there are actual requests, if user drags row
        // but puts it in same place there should be no updates
        if (!this.isCreateView && !_.isEmpty(bulkSaveRequests)) {
            if (triggerOldGroup) {
                // trigger group changed for old group to check themselves
                oldGroup.trigger('quotes:group:changed');
                // trigger save start for the old group
                oldGroup.trigger('quotes:group:save:start');
                // trigger the group to reset it's line numbers
                oldGroup.trigger('quotes:line_nums:reset', oldGroup.groupId, oldGroup.collection);
            }

            // trigger group changed for new group to check themselves
            newGroup.trigger('quotes:group:changed');
            // trigger save start for the new group
            newGroup.trigger('quotes:group:save:start');
            // trigger the group to reset it's line numbers
            newGroup.trigger('quotes:line_nums:reset', newGroup.groupId, newGroup.collection);

            if (saveDefaultGroup) {
                this._saveDefaultGroupThenCallBulk(oldGroup, newGroup, bulkSaveRequests);
            } else {
                this._callBulkRequests(oldGroup, newGroup, bulkSaveRequests);
            }
        }

        // re-enable tooltips in the app
        app.tooltip._enable();
    },

    /**
     * Handles saving the default quote group when a user adds a new QLI/Note to an unsaved default group
     * and clicks the save button from the new QLI/Note row
     *
     * @param {Function} successCallback Callback function sent from the QuoteDataEditablelistField so the field
     *      knows when the group save is successful and the field can continue saving the new row model
     * @private
     */
    _onSaveDefaultQuoteGroup: function(successCallback) {
        var group = this._getComponentByGroupId(this.defaultGroupId);
        group.model.unset('id');
        group.model.unset('_notSaved');

        app.alert.show('saving_default_group_alert', {
            level: 'success',
            autoClose: false,
            messages: app.lang.get('LBL_SAVING_DEFAULT_GROUP_ALERT_MSG', 'Quotes')
        });

        app.api.relationships('create', 'Quotes', {
            'id': this.model.get('id'),
            'link': 'product_bundles',
            'related': {
                position: 0,
                default_group: true
            }
        }, null, {
            success: _.bind(function(group, successCallback, serverData) {
                app.alert.dismiss('saving_default_group_alert');

                this._updateDefaultGroupWithNewData(group, serverData.related_record);

                // call the callback to continue the save stuff
                successCallback();
            }, this, group, successCallback)
        });
    },

    /**
     * Updates a group with the latest server data, updates the model, groupId, and DOM elements
     *
     * @param {View.QuoteDataGroupLayout} group The QuoteDataGroupLayout to update
     * @param {Object} recordData The new record data from the server
     * @private
     */
    _updateDefaultGroupWithNewData: function(group, recordData) {
        if (this.defaultGroupId !== recordData.id) {
            // remove the old default group ID from groupIds
            this.groupIds = _.without(this.groupIds, this.defaultGroupId);
            // add the new group ID so we dont add the default group twice
            this.groupIds.push(recordData.id);
        }
        // update defaultGroupId with new id
        this.defaultGroupId = recordData.id;
        // set the new data on the group model
        group.model.set(recordData);
        // update groupId with new id
        group.groupId = this.defaultGroupId;
        // update the group's dom tbody el with the correct group id
        group.$el.attr('data-group-id', this.defaultGroupId);
        // update the tr's inside the group's dom tbody el with the correct group id
        group.$('tr').attr('data-group-id', this.defaultGroupId);
    },

    /**
     * Handles saving the default quote data group if it has not been saved yet,
     * then when that save success returns, it calls save on all the bulk requests
     * with the new proper group ID
     *
     * @param {View.QuoteDataGroupLayout} oldGroup The old QuoteDataGroupLayout
     * @param {View.QuoteDataGroupLayout} newGroup The new QuoteDataGroupLayout - default group that needs saving
     * @param {Array} bulkSaveRequests The array of bulk save requests
     * @private
     */
    _saveDefaultGroupThenCallBulk: function(oldGroup, newGroup, bulkSaveRequests) {
        var newGroupOldId = newGroup.model.get('id');
        newGroup.model.unset('id');
        newGroup.model.unset('_notSaved');

        app.alert.show('saving_default_group_alert', {
            level: 'success',
            autoClose: false,
            messages: app.lang.get('LBL_SAVING_DEFAULT_GROUP_ALERT_MSG', 'Quotes')
        });

        app.api.relationships('create', 'Quotes', {
            'id': this.model.get('id'),
            'link': 'product_bundles',
            'related': _.extend({
                position: 0
            }, newGroup.model.toJSON())
        }, null, {
            success: _.bind(this._onDefaultGroupSaveSuccess, this, oldGroup, newGroup, bulkSaveRequests, newGroupOldId)
        });
    },

    /**
     * Called when the default group has been saved successfully and we have the new proper group id. It
     * updates all the bulk requests replacing the old "fake" group ID with the new proper DB-saved group ID,
     * updates newGroup with the new data and group ID and calls the save on the remaining bulk requests
     *
     * @param {View.QuoteDataGroupLayout} oldGroup The old QuoteDataGroupLayout
     * @param {View.QuoteDataGroupLayout} newGroup The new QuoteDataGroupLayout
     * @param {Array} bulkSaveRequests The array of bulk save requests
     * @param {string} newGroupOldId The previous "fake" group ID for newGroup
     * @param {Object} serverData The server response from saving the newGroup
     * @private
     */
    _onDefaultGroupSaveSuccess: function(oldGroup, newGroup, bulkSaveRequests, newGroupOldId, serverData) {
        var newId = serverData.related_record.id;
        app.alert.dismiss('saving_default_group_alert');

        // update all the bulk save requests that have the old newGroup ID with the newly saved group ID
        _.each(bulkSaveRequests, function(req) {
            req.url = req.url.replace(newGroupOldId, newId);
        }, this);

        this._updateDefaultGroupWithNewData(newGroup, serverData.related_record);

        // call the remaining bulk requests
        this._callBulkRequests(oldGroup, newGroup, bulkSaveRequests);
    },

    /**
     * Calls the bulk request endpoint with an array of requests
     *
     * @param {View.QuoteDataGroupLayout} oldGroup The old QuoteDataGroupLayout
     * @param {View.QuoteDataGroupLayout} newGroup The new QuoteDataGroupLayout
     * @param {Array} bulkSaveRequests The array of bulk save requests
     * @private
     */
    _callBulkRequests: function(oldGroup, newGroup, bulkSaveRequests) {
        app.api.call('create', app.api.buildURL(null, 'bulk'), {
            requests: bulkSaveRequests
        }, {
            success: _.bind(this._onSaveUpdatedGroupSuccess, this, oldGroup, newGroup)
        });
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
        var deleteResponse = _.find(bulkResponses, function(resp) {
            return resp.contents.id;
        });
        var deletedGroupId = deleteResponse && deleteResponse.contents.id;
        var deletedGroup;
        var newGroupBundle;
        var deletedGroupBundle;
        var bundles;
        var updateModelWithRecord;

        if (oldGroup) {
            oldGroup.trigger('quotes:group:save:stop');
        }
        newGroup.trigger('quotes:group:save:stop');

        // remove the deleted group if it exists
        if (deletedGroupId) {
            app.alert.dismiss('deleting_bundle_alert');
            app.alert.show('deleted_bundle_alert', {
                level: 'success',
                autoClose: true,
                messages: app.lang.get('LBL_DELETED_BUNDLE_SUCCESS_MSG', 'Quotes')
            });

            // get the deleted group
            deletedGroup = this._getComponentByGroupId(deletedGroupId);
            // get the bundle for the deleted group
            deletedGroupBundle = deletedGroup.model.get('product_bundle_items');
            // get the bundle for the new group
            newGroupBundle = newGroup.model.get('product_bundle_items');
            // add the deleted group's models to the new group
            _.each(deletedGroupBundle.models, function(model) {
                newGroupBundle.add(model);
            }, this);
        }

        // reusable method to update a mode once the bulk responses come back.
        updateModelWithRecord = function(model, record) {
            if (model) {
                model.setSyncedAttributes(record);
                model.set(record);
            }
        };

        _.each(bulkResponses, _.bind(function(oldGroup, newGroup, data) {
            var record = data.contents.record;
            var relatedRecord = data.contents.related_record;
            var model;

            // on Delete record and relatedRecord will both be missing
            if (record && relatedRecord) {
                // only update if there are new records to update with
                if (oldGroup && !oldGroup.disposed) {
                    // check if record is the one on this collection
                    if (oldGroup.model && record && oldGroup.model.get('id') === record.id) {
                        updateModelWithRecord(oldGroup.model, record);
                    }
                    // if oldGroup exists, check if the related_record is in the oldGroup
                    model = oldGroup.collection.get(relatedRecord.id);
                    updateModelWithRecord(model, relatedRecord);
                }
                if (newGroup) {
                    // check if record is the one on this collection
                    if (newGroup.model && record && newGroup.model.get('id') === record.id) {
                        updateModelWithRecord(newGroup.model, record);
                    }
                    // check if the related_record is in the newGroup
                    model = newGroup.collection.get(relatedRecord.id);
                    updateModelWithRecord(model, relatedRecord);
                }
            }
        }, this, oldGroup, newGroup), this);

        if (deletedGroupId) {
            // remove the deleted group ID from the main groupIds
            this.groupIds = _.without(this.groupIds, deletedGroupId);
            // get the main bundles collection
            bundles = this.model.get('bundles');
            // remove the deleted group's model from the main bundles
            bundles.remove(deletedGroup.model);

            // dispose the group
            deletedGroup.dispose();
            // remove the component from the layout
            this.removeComponent(deletedGroup);

            // once new items are added to the default group, update the group's line numbers
            newGroup.trigger('quotes:line_nums:reset', newGroup.groupId, newGroup.collection);
        }
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

        if (retCalls.length) {
            // if items have changed positions, sort the collection
            // using the collection.comparator compare function
            dataGroup.collection.sort();
        }
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
     * Creates the default ProductBundles Bean with default group ID
     *
     * @return {Data.Bean}
     * @protected
     */
    _getDefaultGroupModel: function() {
        // if there is not a default group yet, add one
        this.defaultGroupId = app.utils.generateUUID();
        return this._createNewProductBundleBean(this.defaultGroupId, 0, true);
    },

    /**
     * Creates a new ProductBundle Bean
     *
     * @param {String) groupId The groupId to use, if not passed in, will generate a new UUID
     * @param {number) newPosition The position to use for the group
     * @param {boolean) isDefaultGroup If this group is the default group or not
     * @return {Data.Bean}
     * @protected
     */
    _createNewProductBundleBean: function(groupId, newPosition, isDefaultGroup) {
        groupId = groupId || app.utils.generateUUID();
        newPosition = newPosition || 0;
        isDefaultGroup = isDefaultGroup || false;
        return app.data.createBean('ProductBundles', {
            id: groupId,
            _notSaved: true,
            _module: 'ProductBundles',
            _action: 'create',
            link: 'product_bundles',
            default_group: isDefaultGroup,
            currency_id: this.model.get('currency_id'),
            base_rate: this.model.get('base_rate'),
            product_bundle_items: [],
            product_bundle_notes: [],
            position: newPosition
        });
    },

    /**
     * Handler for when quote_data changes on the model
     */
    _onProductBundleChange: function(productBundles) {
        var hasDefaultGroup = false;
        var defaultGroupModel;

        // after adding and deleting models, the change event is like its change for the model, where the
        // model is the first param and not the actual value it's self.
        if (productBundles instanceof Backbone.Model) {
            productBundles = productBundles.get('bundles');
        }

        // check to see if there's a default group in the bundle
        if (productBundles && productBundles.length > 0) {
            hasDefaultGroup = _.some(productBundles.models, function(bundle) {
                return bundle.get('default_group');
            });
        }

        if (!hasDefaultGroup) {
            defaultGroupModel = this._getDefaultGroupModel();
            // calling unshift on the collection with silent so it doesn't
            // cause this function to be triggered again halfway thru
            productBundles.unshift(defaultGroupModel);
        } else {
            // default group exists, get the ID
            defaultGroupModel = _.find(productBundles.models, function(bundle) {
                return bundle.get('default_group');
            });
            this.defaultGroupId = defaultGroupModel.get('id');
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
     * @param {Object} [bundle] The ProductBundle data object
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
        var newBundle;

        // handle on the off chance that no bundles exist on the quote.
        if (!_.isEmpty(highestPositionBundle)) {
            nextPosition = parseInt(highestPositionBundle.get('position')) + 1;
        }

        if (this.isCreateView) {
            // do not perform saves on create view
            newBundle = this._createNewProductBundleBean(undefined, nextPosition, false);
            // set the _justSaved flag so the new bundle header starts in edit mode
            newBundle.set('_justSaved', true);
            // add the new bundle which will add it to the layout and groupIds
            bundles.add(newBundle);
        } else {
            app.alert.show('adding_bundle_alert', {
                level: 'info',
                autoClose: false,
                messages: app.lang.get('LBL_ADDING_BUNDLE_ALERT_MSG', 'Quotes')
            });

            app.api.relationships('create', 'Quotes', {
                'id': this.model.get('id'),
                'link': 'product_bundles',
                'related': {
                    currency_id: this.model.get('currency_id'),
                    base_rate: this.model.get('base_rate'),
                    position: nextPosition
                }
            }, null, {
                success: _.bind(this._onCreateQuoteGroupSuccess, this)
            });
        }
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
        newBundleData.related_record._justSaved = true;
        // now add the new record to the bundles collection
        bundles.add(newBundleData.related_record);

        if (this.model.get('show_line_nums')) {
            // if show_line_nums is true, trigger the event so the new group will add the line_num field
            this.context.trigger('quotes:show_line_nums:changed', true);
        }
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
        var defaultGroup = this._getComponentByGroupId(this.defaultGroupId);
        var bulkRequests = [];
        var bundleItems;
        var positionStart;
        var linkName;
        var url;

        app.alert.show('deleting_bundle_alert', {
            level: 'info',
            autoClose: false,
            messages: app.lang.get('LBL_DELETING_BUNDLE_ALERT_MSG', 'Quotes', {
                groupName: groupName
            })
        });

        if (this.isCreateView) {
            this._removeGroupFromLayout(groupId, groupToDelete);
        } else {
            if (groupToDelete.model && groupToDelete.model.has('product_bundle_items')) {
                bundleItems = groupToDelete.model.get('product_bundle_items');
            }

            // remove any unsaved models
            _.each(bundleItems.models, _.bind(function(bundleItems, groupToDelete, model, key, list) {
                // in _.each, if list is an object, model becomes undefined and list becomes
                // an array with the last model
                model = model || list[0];
                if (model.has('_notSaved') && model.get('_notSaved')) {
                    var groupList = groupToDelete.getGroupListComponent();
                    delete groupList.toggledModels[model.get('id')];
                    bundleItems.remove(model);
                }
            }, this, bundleItems, groupToDelete), this);

            if (defaultGroup.model && defaultGroup.model.has('product_bundle_items')) {
                positionStart = defaultGroup.model.get('product_bundle_items').length;
            }

            if (bundleItems && bundleItems.length > 0) {
                _.each(bundleItems.models, _.bind(function(bulkRequests, positionStart, model, key, list) {
                    linkName = (model.module === 'Products' ? 'products' : 'product_bundle_notes');
                    url = app.api.buildURL('ProductBundles/' + this.defaultGroupId + '/link/' +
                        linkName + '/' + model.id);
                    model.set('position', positionStart);

                    bulkRequests.push({
                        url: url.substr(4),
                        method: 'POST',
                        data: {
                            id: this.defaultGroupId,
                            link: linkName,
                            relatedId: model.id,
                            related: {
                                position: positionStart
                            }
                        }
                    });

                    positionStart++;
                }, this, bulkRequests, positionStart));
            }

            url = app.api.buildURL('ProductBundles/' + groupId);

            bulkRequests.push({
                url: url.substr(4),
                method: 'DELETE'
            });

            if (defaultGroup.model.get('_notSaved')) {
                this._saveDefaultGroupThenCallBulk(groupToDelete, defaultGroup, bulkRequests);
            } else {
                this._callBulkRequests(groupToDelete, defaultGroup, bulkRequests);
            }
        }
    },

    /**
     * Removes a group from the layout
     *
     * @param {string} groupId The model ID of the deleted group
     * @param {View.Layout} groupToDelete The Layout for the deleted group
     * @private
     */
    _removeGroupFromLayout: function(groupId, groupToDelete) {
        app.alert.dismiss('deleting_bundle_alert');

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
