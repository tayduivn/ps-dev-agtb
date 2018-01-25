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
 * @class View.Views.Base.Quotes.ProductCatalogView
 * @alias SUGAR.App.view.views.QuotesProductCatalogView
 * @extends View.View
 */
({
    plugins: ['CanvasDataRenderer'],

    events: {
        'keyup .product-catalog-search-term': 'onSearchTermChange'
    },

    /**
     * The JSTree Object reference
     */
    jsTree: undefined,

    /**
     * The data for the JSTree Object
     */
    jsTreeData: undefined,

    /**
     * If we are actively fetching data from the server
     */
    isFetchActive: false,

    /**
     * Holds placeholder text for the search input
     */
    searchText: undefined,

    /**
     * Holds the previous search term to prevent duplicate fetches
     */
    previousSearchTerm: undefined,

    /**
     * Keeps track of how many fetches are active
     */
    activeFetchCt: undefined,

    /**
     * Keeps track of the MouseWheel event name for phaser create and dispose
     */
    wheelEventName: undefined,

    /**
     * The PhaserIO game object reference
     */
    phaser: undefined,

    /**
     * Flag if the Phaser Lib has finished loading
     */
    phaserReady: undefined,

    /**
     * Flag if the data has finished loading
     */
    dataLoaded: undefined,

    /**
     * The current search filter term the user is searching for
     */
    currentFilterTerm: undefined,

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.activeFetchCt = 0;
        this.searchText = app.lang.get('LBL_SEARCH_CATALOG_PLACEHOLDER', 'Quotes');

        this.dataLoaded = false;
        this.phaserReady = false;

        this.context.on('phaserio:ready', function() {
            this.phaserReady = true;
            this.checkBuildPhaser();
        }, this);
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        this._super('bindDataChange');

        // need to trigger on app.controller.context because of contexts changing between
        // the PCDashlet, and Opps create being in a Drawer, or as its own standalone page
        // app.controller.context is the only consistent context to use
        app.controller.context.on('productCatalogDashlet:add:complete', this._onProductDashletAddComplete, this);
        $(window).on('resize', _.bind(this._resizePhaserCanvas, this));
    },

    /**
     * Gets the search term from the text input
     */
    onSearchTermChange: _.debounce(function(evt) {
        var term = $(evt.target).val().trim();

        if (term !== this.previousSearchTerm) {
            this.previousSearchTerm = term;
            this.loadData({
                searchTerm: term
            });
        }
    }, 500),

    /**
     * @inheritdoc
     */
    loadData: function(options) {
        var callbacks;
        var url;
        var term = options && options.searchTerm;
        var method = 'read';
        var payload = {};

        url = 'ProductTemplates/tree';

        if (term) {
            method = 'create';
            payload.filter = term;
            this.currentFilterTerm = term;
        } else {
            this.currentFilterTerm = undefined;
        }

        this.$('.product-catalog-no-results').addClass('hidden');

        url = app.api.buildURL(url, method);

        this.toggleLoading(true);

        callbacks = {
            context: this,
            success: this._onCatalogFetchSuccess,
            complete: _.bind(function() {
                // when complete, remove the spinning refresh icon from the cog
                // and add back the cog icon
                this.toggleLoading(false);
            }, this)
        };

        this.activeFetchCt++;
        app.api.call(method, url, payload, null, callbacks);
    },

    /**
     * Toggles the spinning Loading icon on the header bar
     *
     * @param {boolean} startLoading If we should start the spinning icon or hide it
     */
    toggleLoading: function(startLoading) {
        if (startLoading) {
            this.$('.loading-icon').show();
        } else {
            this.$('.loading-icon').hide();
        }
    },

    /**
     * Handles the ProductTemplates/tree endpoint response
     * and parses data to be used by the tree
     *
     * @param response
     * @private
     */
    _onCatalogFetchSuccess: function(response) {
        this.jsTreeData = response;
        this.activeFetchCt--;

        if (this.activeFetchCt === 0) {
            if (this.jsTreeData.records.length === 0) {
                this.$('.product-catalog-no-results').removeClass('hidden');
            } else {
                this.$('.product-catalog-no-results').addClass('hidden');
                this.$('.product-catalog-search-term').removeClass('hidden');
            }
        }

        this.dataLoaded = true;

        if (_.isUndefined(this.phaser)) {
            this.checkBuildPhaser();
        } else {
            this.phaser.events.onSetTreeData.dispatch(this.jsTreeData);
        }
    },

    /**
     * Checks if data has been loaded and Phaser is ready to be run
     */
    checkBuildPhaser: function() {
        if (this.dataLoaded && this.phaserReady) {
            this._createPhaser();
        }
    },

    /**
     * @inheritdoc
     */
    render: function() {
        this._super('render');

        this.checkBuildPhaser();
    },

    /**
     * When the DOM MouseEvent wheel scroll happens,
     * this function handles it and passes the delta info to Phaser
     *
     * @param {MouseEvent} mouseEvent The mouse scroll wheel event
     * @private
     */
    _onMouseWheelChange: function(mouseEvent) {
        var delta = mouseEvent.type === 'mousewheel' ?
            mouseEvent.originalEvent.wheelDelta / 20 :
            mouseEvent.originalEvent.deltaY;

        mouseEvent.preventDefault();
        this.phaser.events.onScrollWheel.dispatch(delta);
    },

    /**
     * Event listener for when the Phaser "Tree" State triggers its onTreeReady event
     */
    onPhaserTreeReadyHandler: function() {
        this.phaser.events.onSetTreeData.dispatch(this.jsTreeData);
    },

    /**
     * This function creates the actual PhaserIO game object
     *
     * @private
     */
    _createPhaser: function() {
        var bootState;
        var loadState;
        var treeState;
        var manifest = {
            atlasJSONHash: [
                {
                    id: 'prodCatTS',
                    imagePath: 'modules/Quotes/clients/base/views/product-catalog/product-catalog-ss.png',
                    dataPath: 'modules/Quotes/clients/base/views/product-catalog/product-catalog-ss.json'
                }
            ]
        };
        var EventHub = function() {};
        EventHub.prototype = {

            /**
             * Event called outside Phaser to pass tree data into Phaser for parsing/rendering
             */
            onSetTreeData: new Phaser.Signal(),

            /**
             * Event dispatched by Phaser when the Tree State has finished it's create function
             * and is ready for data
             */
            onTreeReady: new Phaser.Signal(),

            /**
             * Event called outside Phaser to pass mouse scroll wheel data into Phaser
             * to know how much to move the camera up or down
             */
            onScrollWheel: new Phaser.Signal(),

            /**
             * Handles disposing any Signal events and listeners
             */
            destroy: function() {
                for (var eventName in this) {
                    if (this.hasOwnProperty(eventName) && _.isFunction(this[eventName].dispose)) {
                        this[eventName].dispose();
                    }
                }
            }
        };

        // use 100% for the width and 260px for the height
        this.phaser = new Phaser.Game({
            height: 260,
            parent: 'product-catalog-canvas-' + this.cid,
            renderer: Phaser.CANVAS,
            transparent: true,
            width: '100'
        });

        this.phaser._view = this;

        this.wheelEventName = 'onwheel' in document.createElement('div') ? 'wheel' : // Modern browsers support "wheel"
            document.onmousewheel !== undefined ? 'mousewheel' : // Webkit and IE support at least "mousewheel"
                'DOMMouseScroll'; // let's assume that remaining browsers are older Firefox

        this.$('.product-catalog-container-' + this.cid).off(this.wheelEventName);
        this.$('.product-catalog-container-' + this.cid).on(
            this.wheelEventName,
            _.bind(this._onMouseWheelChange, this)
        );

        this.phaser.events = new EventHub();
        this.phaser.events.onTreeReady.add(this.onPhaserTreeReadyHandler, this);

        bootState = {
            /**
             * Preload is called as the BootState initializes and lets us set any flags we need later.
             * This is the place for setting any Phaser variables we might need at runtime.
             */
            preload: function() {
                this.game.hasTreeData = false;
            },

            /**
             * After the BootState is done preloading, this function calls the LoadState
             */
            create: function() {
                this.game.state.start('load');
            }
        };

        loadState = {
            /**
             * Preload is called as the LoadState initializes and lets us load any assets we'll use later.
             * This would also be the place to add preloading progressbar
             */
            preload: function() {
                // loop over anything in the manifest and load it
                _.each(manifest, function(itemsToLoad, key) {
                    if (!_.isEmpty(itemsToLoad)) {
                        _.each(itemsToLoad, function(item) {
                            switch (key) {
                                case 'json':
                                case 'image':
                                    this.game.load[key](item.id, item.path);
                                    break;

                                case 'atlasJSONHash':
                                case 'bitmapFont':
                                    this.game.load[key](item.id, item.imagePath, item.dataPath);
                                    break;
                            }
                        }, this);
                    }
                }, this);
            },

            /**
             * After the LoadState is done preloading assets, this function calls the TreeState
             */
            create: function() {
                this.game.state.start('tree');
            }
        };

        treeState = {
            categoryColor: '#000000',
            itemColor: '#167DE5',
            itemFont: '12px Helvetica Neue',
            iconTextPadding: 5,
            iconWidth: 16,
            iconHeight: 16,
            iconScale: 0.25,
            iconStartX: 5,
            iconStartY: 15,
            iconYOffset: 8,
            textYOffset: 1,
            itemRowYPadding: 21,
            childRowYPadding: 10,
            containerRowStartY: 0,
            isLoading: false,
            groups: undefined,
            rootGroup: undefined,
            gameWorldHeight: undefined,
            gameWorldWidth: undefined,
            cameraY: undefined,
            GroupEventHub: undefined,
            showMoreNode: {
                data: 'Show More',
                type: 'showMore'
            },

            /**
             * Preload is called as the TreeState initializes and lets us setup any vars we need for the state
             */
            preload: function() {
                this.groups = [];
                this.gameWorldHeight = 0;
                this.gameWorldWidth = this.game._view.$('.product-catalog-dashlet-' + this.cid).width();
                this.cameraY = 0;
                this.isLoading = false;

                this.game.events.onSetTreeData.add(this._setTreeData, this);
                this.game.events.onScrollWheel.add(this._onScrollWheel, this);

                this.GroupEventHub = function() {
                    return {
                        onChangeY: new Phaser.Signal(),
                        destroy: function() {
                            for (var eventName in this) {
                                if (this.hasOwnProperty(eventName) && _.isFunction(this[eventName].dispose)) {
                                    this[eventName].dispose();
                                }
                            }
                        }
                    };
                };

                if (this.game.hasTreeData) {
                    this._setTreeData(this.game.treeData);
                }
            },
            /**
             * After preload is done, create runs and lets us let the Sugar.App know our tree is ready
             */
            create: function() {
                this._updateGameWorldSize();
                this.game.events.onTreeReady.dispatch();
            },

            /**
             * Update is called about 60 times a second to let us update our camera position
             * based on any mouse scrolling a user may have done
             */
            update: function() {
                if (this.cameraY !== 0) {
                    this.game.camera.y += this.cameraY;
                    this.cameraY = 0;
                }
            },

            /**
             * Handles the Mouse ScrollWheel event being passed from the DOM to Phaser.
             * The yDelta value gets added to `this.cameraY` so that the next game "tick" event
             * that happens during the state's `update` function, we can move the
             * game's camera object up or down.
             *
             * @param yDelta
             * @private
             */
            _onScrollWheel: function(yDelta) {
                this.cameraY += yDelta;
            },

            /**
             * This event is triggered when a user clicks to expand or collapse a Group.
             * yDelta will be positive (the group needs to expand) or negative (the group is being hidden)
             *
             * @param {number} yDelta The amount to change the Y value of Groups
             * @private
             */
            _onChangeY: function(yDelta) {
                var rowIndex;
                var groups;
                var groupLen;

                if (this.parent.parent instanceof Phaser.Stage) {
                    // once we hit the "rootGroup" group level
                    // just return and break out of this event loop
                    return;
                }

                // we need to move all groups that come after this group
                // get the next index for any items we need to move after this group
                rowIndex = this._rowIndex + 1;

                // get all of the Phaser.Groups at "this" level (this.parent.children)
                groups = _.filter(this.parent.children, function(child) {
                    return child instanceof Phaser.Group;
                });

                // get the length of the groups
                groupLen = groups.length;

                // loop over each group after "this" group and move it up/down by the yDelta
                for (rowIndex; rowIndex < groupLen; rowIndex++) {
                    groups[rowIndex].y += yDelta;
                }

                // update the parent group with the correct yOffset to apply to its children
                this.parent._yOffset += yDelta;

                // cause the event to "bubble" up to the next level
                this.parent._events.onChangeY.dispatch(yDelta, this);
            },

            /**
             * Sets the main tree data and starts building the levels and nodes.
             * This is called when the page loads and when a user types in to search for data.
             *
             * @param treeData
             * @private
             */
            _setTreeData: function(treeData) {
                var groupIndex = 0;

                this.gameWorldWidth = this.game._view.$('.product-catalog-dashlet-' + this.cid).width();
                this.gameWorldHeight = 15;
                this.cameraY = 0;
                this.game.camera.y = 0;

                if (this.rootGroup) {
                    if (this.rootGroup.childGroup) {
                        this.rootGroup.childGroup._events.destroy();
                        this.rootGroup.childGroup.destroy();
                    }

                    this.rootGroup._events.destroy();
                    this.rootGroup.destroy();
                }

                this.rootGroup = this.game.add.group();
                this.rootGroup.name = 'rootGroup';
                this.rootGroup._groupIndex = -1;
                this.rootGroup._events = new this.GroupEventHub();
                this.rootGroup._events.onChangeY.add(this._onChangeY, this.rootGroup);
                this.rootGroup.childGroup = this._createGroupObject(-1, 0);

                _.each(treeData.records, function(node, index) {
                    this._createLevel(this.rootGroup.childGroup, node, groupIndex, index);
                }, this);

                if (treeData.next_offset !== -1) {
                    this._createLevel(this.rootGroup.childGroup, this.showMoreNode, groupIndex, treeData.next_offset);
                }

                this._updateGameWorldSize();
                // reset the camera back to the top 0 position
                this.game.camera.y = 0;
            },

            /**
             * Creates a Phaser.Group and sets some default properties
             *
             * @private
             * @return {Phaser.Group} group The newly created group
             */
            _createGroupObject: function(groupIndex, rowIndex) {
                var group = this.game.add.group();

                // set the group's name and some other indexes to help keep track
                // of where this group exists in its parents hierarchy
                group.name = 'group-' + groupIndex + '-' + rowIndex;
                group._groupIndex = groupIndex;
                group._rowIndex = rowIndex;
                group._yOffset = 0;

                // add an Events hub on this group to pass events
                group._events = new this.GroupEventHub();
                group._events.onChangeY.add(this._onChangeY, group);

                return group;
            },

            /**
             * Creates a "level" including category and any children
             *
             * @param {Phaser.Group} parentGroup The parent group to add this category and children to
             * @param {Object} node The JSON Object data from the tree for this node level
             * @param {number} groupIndex The Group level index from rootGroup
             * @param {number} rowIndex The specific row level index of this node inside its Group
             *                  groups pushing this group down
             * @private
             */
            _createLevel: function(parentGroup, node, groupIndex, rowIndex) {
                var group = this._createGroupObject(groupIndex, rowIndex);
                var groupYOffset = parentGroup._yOffset || 0;

                // create the group's icon and text label
                this._createNode(group, node, groupIndex, rowIndex);

                // update the overall game world height
                this.gameWorldHeight += this.itemRowYPadding;

                // add this new group to the parent
                parentGroup.add(group);

                if (parentGroup._groupIndex !== -1 && parentGroup.parent._groupIndex !== -1) {
                    group.parentGroup = parentGroup.parent;
                } else {
                    group.parentGroup = parentGroup;
                }

                if (node.type === 'showMore') {
                    group.parentGroup._nextOffset = rowIndex;
                }

                group.x = groupIndex === 0 ? 0 : this.iconWidth + this.iconStartX;
                group.y = this.containerRowStartY + (this.itemRowYPadding * rowIndex) + groupYOffset;
            },

            /**
             * This function creates the actual Icon and Text label and adds them to the Group
             *
             * @param {Phaser.Group} group
             * @param {Object} node The JSON Object data from the tree for this node level
             * @param {number} groupIndex The Group level index from rootGroup
             * @param {number} rowIndex The specific row level index of this node inside its Group
             * @private
             */
            _createNode: function(group, node, groupIndex, rowIndex) {
                var icon;
                var text;
                var iconName;
                var textColor;
                var itemType = node.type;
                var itemId = node.id;
                var itemName = node.data;

                if (itemType === 'category') {
                    textColor = this.categoryColor;
                    iconName = node.state === 'closed' ? 'folder' : 'folder-open-o';
                } else if (itemType === 'product') {
                    textColor = this.itemColor;
                    iconName = 'list-alt';
                } else if (itemType === 'showMore') {
                    textColor = this.itemColor;
                    iconName = 'empty';
                }

                // create the icon
                icon = this.game.add.image(
                    this.iconStartX + 8,
                    this.iconYOffset,
                    'prodCatTS',
                    iconName
                );
                icon.height = this.iconHeight;
                icon.width = this.iconWidth;
                icon.anchor.setTo(0.5, 0.5);
                icon._itemName = itemName;
                icon._itemId = itemId;
                icon._itemType = itemType;
                icon._tween = this.game.add.tween(icon).to({
                    angle: 360
                }, 3600, null, false, 0, -1);

                icon.inputEnabled = true;
                icon.events.onInputDown.add(this._itemClicked, this);

                text = this.game.add.text(
                    this.iconStartX + this.iconWidth + this.iconTextPadding,
                    this.textYOffset,
                    node.data,
                    {
                        font: this.itemFont,
                        fill: textColor
                    }
                );

                text._itemName = itemName;
                text._itemId = itemId;
                text._itemType = itemType;

                text.inputEnabled = true;
                text.events.onInputDown.add(this._itemClicked, this);

                group.name = group.name + '-' + itemName;
                group._itemName = itemName;
                group._itemId = itemId;
                group._itemType = itemType;
                group._icon = icon;
                group._text = text;
                group.add(icon);
                group.add(text);
            },

            /**
             * Handles when any item on the stage is clicked.
             *
             * @param {Phaser.Image|Phaser.Text} target The Phaser text or icon that was clicked
             * @private
             */
            _itemClicked: function(target) {
                var isIcon = target instanceof Phaser.Image;

                if (target._itemType === 'category' || target._itemType === 'showMore') {
                    this._categoryClicked(target, isIcon);
                } else {
                    if (isIcon) {
                        this._iconClicked(target);
                    } else {
                        this._nameClicked(target);
                    }
                }
            },

            /**
             * Handles when a Product Category or "Show More" is clicked
             *
             * @param {Phaser.Image|Phaser.Text} target The Phaser text or icon that was clicked
             * @param {boolean} isIcon If the `target` is an image/icon or Text
             * @private
             */
            _categoryClicked: function(target, isIcon) {
                var changeYDelta;
                var isVisible;
                var icon = isIcon ? target : target.parent._icon;
                var isShowMore = target._itemType === 'showMore';
                var newFrameName = icon.frameName === 'folder' ? 'folder-open-o' : 'folder';

                if (isIcon) {
                    icon = target;
                    target = _.find(target.parent.children, function(item) {
                        return item instanceof Phaser.Text && item._itemId === target._itemId;
                    });
                } else {
                    icon = _.find(target.parent.children, function(item) {
                        return item instanceof Phaser.Image && item._itemId === target._itemId;
                    });
                }

                if (target._isFetching) {
                    return;
                }

                if (isShowMore || newFrameName === 'folder-open-o') {
                    this._getMoreRecords(target, icon, isShowMore);
                } else {
                    icon.frameName = newFrameName;
                    // subtract the group height from the game world height and update
                    this.gameWorldHeight -= target.parent.childGroup.height;
                    this._updateGameWorldSize();
                }

                if (target.parent.childGroup) {
                    isVisible = !target.parent.childGroup.visible;
                    target.parent.childGroup.visible = isVisible;
                    changeYDelta = target.parent.childGroup.height;

                    if (!isVisible) {
                        changeYDelta = -changeYDelta;
                    }

                    target.parent._events.onChangeY.dispatch(changeYDelta, target.parent);
                }
            },

            /**
             * Handles fetching more records for the target root item
             *
             * @param {Phaser.Image|Phaser.Text} target The Phaser text or icon that was clicked
             * @param {Phaser.Image} icon The icon image object for the clicked item
             * @private
             */
            _getMoreRecords: function(target, icon, isShowMore) {
                var offset;
                var itemId = target._itemId;

                if (isShowMore && target.parent.parentGroup && target.parent.parentGroup._nextOffset !== -1) {
                    offset = target.parent.parentGroup._nextOffset;
                    itemId = target.parent.parentGroup._itemId;
                }

                if (_.isUndefined(target.parent.childGroup) || (!_.isUndefined(offset) && offset !== -1)) {
                    icon.frameName = 'refresh';
                    icon._tween.start();
                    target._isFetching = true;
                    icon._isFetching = true;

                    this.game._view._fetchMoreRecords(
                        itemId,
                        offset,
                        isShowMore,
                        _.bind(this._setMoreRecordsData, this, target, icon, isShowMore)
                    );
                } else {
                    icon.frameName = 'folder-open-o';
                    // add the group height to the game world height and update
                    this.gameWorldHeight += target.parent.childGroup.height;
                    this._updateGameWorldSize();
                }
            },

            /**
             * Updates Phaser's bounds and world size with the current gameWorldHeight
             *
             * @private
             */
            _updateGameWorldSize: function() {
                this.game.world.setBounds(0, 0, this.gameWorldWidth, this.gameWorldHeight);
                this.game.world.resize(this.gameWorldWidth, this.gameWorldHeight);
                this.game.camera.setBoundsToWorld();
            },

            /**
             * Handles when a user clicks a new category or Show More
             *
             * @param {Phaser.Image|Phaser.Text} target The Phaser text or icon that was clicked
             * @param {Phaser.Image} icon The icon image object for the clicked item
             * @param {boolean} isIcon If the `target` is an image/icon or Text
             * @param {Object} data The server data with records and offset
             * @private
             */
            _setMoreRecordsData: function(target, icon, isShowMore, data) {
                var childGroup;
                var triggerParent;
                var groupIndex = target.parent._groupIndex;
                var isVisible;
                var changeYDelta;
                var nextRowIndex = 0;

                target._isFetching = false;
                icon._isFetching = false;

                if (isShowMore) {
                    // in the case where a user searches for a term, the root group is the parentGroup
                    // and there is no childGroup on that parentGroup
                    childGroup = target.parent.parentGroup.childGroup || target.parent.parentGroup;
                    // set the next row index to whatever the offset is
                    nextRowIndex = target.parent.parentGroup._nextOffset;
                    // the correct parent to trigger changeY on
                    triggerParent = target.parent.parentGroup;
                } else {
                    childGroup = target.parent.childGroup;
                    // only increment the group's index if this is not a "Show More" group situation
                    groupIndex++;
                    // the correct parent to trigger changeY on
                    triggerParent = target.parent;
                }

                if (!childGroup) {
                    childGroup = this._createGroupObject(groupIndex, 0);

                    target.parent.childGroup = childGroup;
                    target.parent.add(childGroup);
                }

                childGroup._previousHeight = childGroup.height;

                _.each(data.records, function(node, index) {
                    this._createLevel(childGroup, node, groupIndex, nextRowIndex + index);
                }, this);

                if (data.next_offset !== -1) {
                    this._createLevel(childGroup, this.showMoreNode, groupIndex, data.next_offset);
                }

                icon.frameName = 'folder-open-o';
                icon.angle = 0;
                icon._tween.stop();

                if (isShowMore) {
                    // shrink game world as we remove Show More later
                    this.gameWorldHeight -= this.itemRowYPadding;

                    target.parent.remove(icon);
                    target.parent.remove(target);
                    icon.destroy();
                    target.destroy();
                } else {
                    childGroup.y = this.itemRowYPadding;
                    childGroup._nextOffset = data.next_offset;
                }

                isVisible = childGroup.visible;
                changeYDelta = childGroup.height - childGroup._previousHeight;

                if (!isVisible) {
                    changeYDelta = -changeYDelta;
                }

                triggerParent._events.onChangeY.dispatch(changeYDelta, triggerParent);

                this._updateGameWorldSize();
            },

            /**
             * When a ProductCatalog item's icon gets clicked
             *
             * @param {Phaser.Image} target The icon that was clicked
             * @private
             */
            _iconClicked: function(target) {
                this.game._view._fetchProductTemplate(target._itemId, {
                    success: _.bind(this.game._view._openItemInDrawer, this.game._view)
                });
            },

            /**
             * When a ProductCatalog item's name gets clicked
             *
             * @param {Phaser.Text} target The text label that was clicked
             * @private
             */
            _nameClicked: function(target) {
                this.game._view._fetchProductTemplate(target._itemId, {
                    success: _.bind(this.game._view._sendItemToQuote, this.game._view)
                });
            }
        };

        this.phaser.state.add('boot', bootState);
        this.phaser.state.add('load', loadState);
        this.phaser.state.add('tree', treeState);

        this.phaser.state.start('boot');
    },

    /**
     * Fetches additional records from the database for a given node id and offset
     *
     * @param {string} id The parent hash id for the record
     * @param {int} offset
     * @param callback
     * @private
     */
    _fetchMoreRecords: function(id, offset, isShowMore, callback) {
        var callbacks;
        var method = 'create';
        var url = app.api.buildURL('ProductTemplates/tree', method);
        var payload = {};

        if (!_.isUndefined(id)) {
            payload.root = id;
        }

        if (!_.isUndefined(offset)) {
            payload.offset = offset;
        }

        if (isShowMore && !_.isUndefined(this.currentFilterTerm)) {
            payload.filter = this.currentFilterTerm;
        }

        this.toggleLoading(true);

        callbacks = {
            context: this,
            success: callback,
            complete: _.bind(function() {
                this.activeFetchCt--;
                // when complete, remove the spinning refresh icon from the cog
                // and add back the cog icon
                this.toggleLoading(false);
            }, this)
        };

        this.activeFetchCt++;
        app.api.call(method, url, payload, null, callbacks);
    },

    /**
     * Fetchs a Product Template record given the ID, and sends the response data to `callbacks.success`
     *
     * @param {string} id The ProductTemplate ID Hash to fetch
     * @param {Object} callbacks The callback object with any success/error/complete handler functions
     * @private
     */
    _fetchProductTemplate: function(id, callbacks) {
        var url = app.api.buildURL('ProductTemplates/' + id, 'read');
        app.api.call('read', url, null, null, callbacks);
    },

    /**
     * Sends the ProductTemplate data item to the Quote
     *
     * @param {Object} data The ProductTemplate data
     * @private
     */
    _sendItemToQuote: function(data) {
        data.position = 0;
        data._forcePosition = true;

        // copy Template's id and name to where the QLI expects them
        data.product_template_id = data.id;
        data.product_template_name = data.name;

        // remove ID/etc since we dont want Template ID to be the record id
        delete data.id;
        delete data.date_entered;
        delete data.date_modified;

        // need to trigger on app.controller.context because of contexts changing between
        // the PCDashlet, and Opps create being in a Drawer, or as its own standalone page
        // app.controller.context is the only consistent context to use
        app.controller.context.trigger('productCatalogDashlet:add', data);
    },

    /**
     * Sends the ProductTemplate data item to a Drawer layout
     *
     * @param {Object} data The ProductTemplate data
     * @private
     */
    _openItemInDrawer: function(data) {
        var model = app.data.createBean('ProductTemplates', data);
        app.drawer.open({
            layout: 'product-catalog-dashlet-drawer-record',
            context: {
                module: 'ProductTemplates',
                model: model
            }
        });
    },

    /**
     * Handles when sending ProductTemplate data has been complete and we can enable the tree again
     *
     * @private
     */
    _onProductDashletAddComplete: function() {
        this.isFetchActive = false;
        this.$('#product-catalog-container-' + this.cid).removeClass('disabled');
    },

    /**
     * Resizes the Phaser Canvas width and height when the window is resized
     *
     * @private
     */
    _resizePhaserCanvas: function() {
        var $el = this.$('.product-catalog-container-' + this.cid);

        if (this.phaser && $el.length) {
            this.phaser.scale.setGameSize($el.width(), $el.height());
        }
    },

    /**
     * @inheritdoc
     */
    _dispose: function() {
        // any cleanup
        this.$('.product-catalog-container-' + this.cid).off(this.wheelEventName);
        // remove window resize event
        $(window).off('resize');
        if (app.controller && app.controller.context) {
            app.controller.context.off('productCatalogDashlet:add:complete', null, this);
        }
        // If Phaser exists, destroy it
        if (this.phaser) {
            this.phaser.events.destroy();
            this.phaser.destroy();
        }

        this._super('_dispose');
    }
})
