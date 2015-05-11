/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
var MultipleCollapsiblePanel = function (settings) {
    CollapsiblePanel.call(this, jQuery.extend(true, {bodyHeight: 100}, settings));
    this._selectedPanel = null;
    this._panelList = null;
    this._htmlContent = null;
    this._htmlContentHeader = null;
    this._htmlContentTitle = null;
    this._lastSelectedPanel = null;
    this._selectedPanel = null;
    this._fastAccessObject = {};
    this._originalBodyHeight = null;
    MultipleCollapsiblePanel.prototype.init.call(this, settings);
};

MultipleCollapsiblePanel.prototype = new CollapsiblePanel();
MultipleCollapsiblePanel.prototype.constructor = MultipleCollapsiblePanel;
MultipleCollapsiblePanel.prototype.type = "MultipleCollapsiblePanel";

MultipleCollapsiblePanel.prototype.init = function () {
    this._panelList = new ListPanel({
        itemsContent: this._panelListItemContent(),
        onItemClick: this._onPanelListItemClick(),
        collapsed: false
    });
};

MultipleCollapsiblePanel.prototype.isParentOf = function (item) {
    return !!this.getItem(item.id);
};

MultipleCollapsiblePanel.prototype.getItem = function (item) {
    var searchedItem = null;

    if (typeof item === 'string') {
        searchedItem = this._items.find('id', item);
    } else if (typeof item === 'number') {
        searchedItem = this._items.get(item);
    } else if (item instanceof CollapsiblePanel && this.isParentOf(item)) {
        searchedItem = item;
    }

    return searchedItem;
};

MultipleCollapsiblePanel.prototype.disableItem = function(item) {
    var itemToChange = this.getItem(item);

    if (itemToChange) {
        itemToChange.disable();
    }
    return this;
};

MultipleCollapsiblePanel.prototype.enableItem = function(item) {
    var itemToChange = this.getItem(item);

    if (itemToChange) {
        itemToChange.enable();
    }
    return this;
};

MultipleCollapsiblePanel.prototype._onItemEnablementStatusChange = function () {
    var that = this;
    return function (item, active) {
        var accessObject = that._fastAccessObject[item.id],
            listItem = accessObject.listItem;
        listItem.setVisible(active);
        if (active) {
            accessObject.panel.expand();
        } else {
            if (!that.isCollapsed() && that._selectedPanel === item) {
                that.displayMenu(true);
            }
        }
    };
};

MultipleCollapsiblePanel.prototype._panelListItemContent = function () {
    var that = this;
    return function (listItem, data) {
        var a = this.createHTMLElement("a"),
            span = this.createHTMLElement("span"),
            i = this.createHTMLElement("i");
        a.className = "adam list-item-content";
        i.className = "adam list-item-arrow fa fa-arrow-circle-right";
        span.textContent = data["text"];
        a.appendChild(span);
        a.appendChild(i);
        return a;
    };
};

MultipleCollapsiblePanel.prototype._onPanelListItemClick = function () {
    var that = this;
    return function (listPanel, item) {
        that.displayPanel(item.getData().id);
    };
};

MultipleCollapsiblePanel.prototype._clearContent = function () {
    var nodes;
    if (this._htmlContent) {
        nodes = this._htmlContent.childNodes;
        while (nodes.length > 1) {
            if (nodes[0].remove) {
                this._htmlContent.lastChild.remove();
            } else {
                this._htmlContent.lastChild.removeNode(true);
            }
        }
    }

    return true;
};

MultipleCollapsiblePanel.prototype.expand = function (noAnimation) {
    this.displayMenu(true);
    CollapsiblePanel.prototype.expand.call(this, noAnimation);
    return this;
};

/*MultipleCollapsiblePanel.prototype.isParentOf = function (panel) {
    return !!this._items.indexOf(panel);
};*/

MultipleCollapsiblePanel.prototype.displayPanel = function (panel) {
    var panelToDisplay = this._items.find("id", panel), bodyHeight, contentHeaderHeight, w;
    if(this._selectedPanel !== panelToDisplay) {
        this._selectedPanel = panelToDisplay;
        if(this.html) {
            if (this._lastSelectedPanel !== panelToDisplay) {
                this._selectedPanel.getHTML();
                this._clearContent();
                this._htmlContentTitle.textContent = this._selectedPanel.getTitle();
                this._htmlContent.appendChild(this._selectedPanel._htmlBody);
            }

            bodyHeight = jQuery(this._selectedPanel._htmlBody).outerHeight();
            contentHeaderHeight = jQuery(this._htmlContentHeader).outerHeight();
            this._originalBodyHeight = this._bodyHeight;
            this.setBodyHeight(bodyHeight + contentHeaderHeight);

            w = $(this._htmlBody).innerWidth();
            this._htmlContent.style.left = w + "px";
            jQuery(this._panelList._htmlBody).animate({
                left: "-=" + w + "px"
            });
            jQuery(this._htmlContent).animate({
                left: 0
            });
        }
    }
};

MultipleCollapsiblePanel.prototype.displayMenu = function (noAnimation) {
    var w, selectedPanel;
    if (this._selectedPanel) {
        this.setBodyHeight(this._originalBodyHeight);
        selectedPanel = this._selectedPanel;
        this._lastSelectedPanel = this._selectedPanel;
        this._selectedPanel = null;
        this._panelList._htmlBody.scrollTop = 0;
        w = parseInt(this._panelList._htmlBody.style.left, 10) * -1;//jQuery(this._htmlBody).innerWidth(); //jQuery(this._panelList._htmlBody).outerWidth();
        if (noAnimation) {
            this._panelList._htmlBody.style.left = "0px";
            this._htmlContent.style.left = w + "px";
        } else {
            jQuery(this._panelList._htmlBody).add(this._htmlContent).animate({
                left: "+=" + w + "px"
            });
        }
        if (typeof selectedPanel.onCollapse === 'function') {
            selectedPanel.onCollapse(selectedPanel);
        }
    }
    return this;
};

MultipleCollapsiblePanel.prototype.setBodyHeight = function (height) {
    if (isNaN(height)) {
        throw new Error("setBodyHeight(): The parameter must be a number.");
    }
    this._bodyHeight = height;
    if(this._htmlBody) {
        this._htmlBody.style.maxHeight = this._htmlBody.style.height = height + "px";
    }
    return this;
};

MultipleCollapsiblePanel.prototype.clearItems = function () {
    this.displayMenu();
    if (this._panelList) {
        this._panelList.clearItems();
    }
    this._items.clear();
    return this;
};

MultipleCollapsiblePanel.prototype._paintItems = function () {
    var i, items;
    if (this._panelList) {
        items = this._items.asArray();
        this._panelList.clearItems();
        for (i = 0; i < items.length; i += 1) {
            this._paintItem(items[i]);
        }
    }
    return this;
};

MultipleCollapsiblePanel.prototype._paintItem = function (item) {
    var items;
    if (this._panelList) {
        this._panelList.addItem({
            data: {
                id: item.id,
                text: item.getTitle()
            },
            visible: !item.isDisabled()
        });
        items = this._panelList.getItems();
        this._fastAccessObject[item.id] = {
            listItem: items[items.length - 1],
            panel: item
        };
    }
    return this;
};

MultipleCollapsiblePanel.prototype._createItem = function (item) {
    var newItem;
    //item.onValueAction = this._onSubpanelItemAction();
    switch (item.type) {
        case "form":
            newItem = new FormPanel(item);
            break;
        case "list":
            newItem = new ListPanel(item);
            break;
        default:
            throw new Error("_createItem(): The parameter has an invalid \"type\" property.");
    }
    return newItem;
};

MultipleCollapsiblePanel.prototype.getValueObject = function (args) {
    return args.value;
};

MultipleCollapsiblePanel.prototype._onValueAction = function (anyArgument) {
    if(typeof this.onValueAction === 'function') {
        this.onValueAction(anyArgument.panel, this.getValueObject(anyArgument));
    }
    return this;
};

MultipleCollapsiblePanel.prototype._onSubpanelItemAction = function () {
    var that = this;
    return function (panel, panelValue) {
        that._onValueAction({panel: panel, value: panelValue});
    };
};

MultipleCollapsiblePanel.prototype.addItem = function(item) {
    var itemToAdd;
    if (item instanceof CollapsiblePanel) {
        itemToAdd = item;
    } else if (typeof item === 'object') {
        itemToAdd = this._createItem(item);
    } else {
        throw new Error("addItem(): The parameter must be an instance of CollapsiblePanel or an object.");
    }
    itemToAdd.setParent(this)
        .setOnValueActionHandler(this._onSubpanelItemAction())
        .setOnEnablementStatusChangeHandler(this._onItemEnablementStatusChange())
        .disableAnimations();
    this._items.insert(itemToAdd.expand());

    if (!this._massiveAction) {
        this._paintItem(item);
    }
    return this;
};

MultipleCollapsiblePanel.prototype.removeItem = function (item) {
    var itemToRemove = this.getItem(item);

    if (itemToRemove) {
        this._items.remove(itemToRemove);
        delete this._fastAccessObject[itemToRemove.id];
        if (this.html) {
            if (itemToRemove.html.remove) {
                itemToRemove.html.remove()
            } else {
                itemToRemove.html.removeNode(true);
            }
        }
    }

    return this;
};

MultipleCollapsiblePanel.prototype._attachListeners = function () {
    var that;
    if(this.html && !this._attachedListeners) {
        that = this;
        CollapsiblePanel.prototype._attachListeners.call(this);
        jQuery(this._htmlContentBackButton).on('click', function() {
            that.displayMenu();
        });
    }
    return this;
};

MultipleCollapsiblePanel.prototype._createBody = function () {
    var body, content, contentHeader, contentTitle, backButton;
    if (!this._htmlBody) {
        body = this.createHTMLElement("div");
        //body.className = "adam multiple-panel-body";
        content = this.createHTMLElement("div");
        content.className = "adam multiple-panel-content";
        contentHeader = this.createHTMLElement("header");
        contentHeader.className = "adam multiple-panel-contentheader";
        contentTitle = this.createHTMLElement("span");
        contentTitle.className = "adam multiple-panel-title";
        backButton = this.createHTMLElement("i");
        backButton.className = "adam multiple-panel-back fa fa-arrow-circle-left";

        this._panelList.getHTML();
        this._panelList._htmlBody.className += " adam-main-list";

        contentHeader.appendChild(contentTitle);
        contentHeader.appendChild(backButton);
        content.appendChild(contentHeader);
        body.appendChild(this._panelList._htmlBody);
        body.appendChild(content);

        this._htmlContent = content;
        this._htmlContentHeader = contentHeader;
        this._htmlContentTitle = contentTitle;
        this._htmlContentBackButton = backButton;
        this._htmlBody = body;
    }
    return this._htmlBody;
};

MultipleCollapsiblePanel.prototype.createHTML = function () {
    if (!this.html) {
        CollapsiblePanel.prototype.createHTML.call(this);
        this.html.className += " multiple-panel";
    }
    return this.html;
};

/*MultipleCollapsiblePanel.prototype._onValueAction = function (anyArgument) {
    if(typeof this.onValueAction === 'function') {
        this.onValueAction(anyArgument.panel, this.getValueObject(anyArgument));
    }
    return this;
};

MultipleCollapsiblePanel.prototype.getValueObject = function (args) {
    return args.value;
};



MultipleCollapsiblePanel.prototype.setOnCloseHandler = function(handler) {
    if (!(handler === null || typeof handler === 'function')) {
        throw new Error("setOnCloseHandler(): The parameter must be a function or null.");
    }
    this.onClose = handler;
    return this;
};

MultipleCollapsiblePanel.prototype._createItem = function (item) {
    var newItem;
    //item.onValueAction = this._onSubpanelItemAction();
    switch (item.type) {
        case "form":
            newItem = new FormPanel(item);
            break;
        case "list":
            newItem = new ListPanel(item);
            break;
        default:
            throw new Error("_createItem(): The parameter has an invalid \"type\" property.");
    }
    return newItem;
};

MultipleCollapsiblePanel.prototype._paintItems = function () {
    if(this._items.getSize() > 0) {
        this.selectPanel(0);
    }
    return this;
};

MultipleCollapsiblePanel.prototype.addItem = function(item) {
    var itemToAdd;
    if (item instanceof CollapsiblePanel) {
        itemToAdd = item;
    } else if (typeof item === 'object') {
        itemToAdd = this._createItem(item);
    } else {
        throw new Error("addItem(): The parameter must be an instance of CollapsiblePanel or an object.");
    }
    itemToAdd.setOnValueActionHandler(this._onSubpanelItemAction());
    this._items.insert(itemToAdd.expand());
    return this;
};



MultipleCollapsiblePanel.prototype.selectPanel = function (panel) {
    var panelToDisplay;
    if (typeof panel === 'number') {
        panelToDisplay = this._items.get(panel);
    } else if (panel instanceof CollapsiblePanel) {
        if(this.isParentOf(panel)) {
            panelToDisplay = panel;
        } else {
            throw new Error("selectPanel(): The panel to show must belong to the current parent panel.");
        }
    } else {
        throw new Error("selectPanel(): The parameter must be a number or an instance of CollapsiblePanel.");
    }
    if(this._selectedPanel !== panelToDisplay) {
        this._selectedPanel = panelToDisplay;
        if(this.html) {
            this._selectedPanel.getHTML();
            this._clearBody();
            this.setTitle(this._selectedPanel._title);
            this._htmlBody.appendChild(this._selectedPanel._htmlBody);
        }
    }
};

MultipleCollapsiblePanel.prototype._createBody = function () {
    var element = this.createHTMLElement('div');
    element.className = 'adam multiple-panel-body';
    return element;
};

MultipleCollapsiblePanel.prototype._addPanelToList = function (item, index) {
    var li, a;
    if (this._htmlPanelList) {
        li = this.createHTMLElement('li');
        a = this.createHTMLElement('a');
        a.setAttribute("data-panel-index", index !== undefined ? index : this._items.getSize() - 1);
        a.className = "adam collapsible-panel-listitem";
        a.href = "#";
        a.textContent = item._title;
        li.appendChild(a);
        this._htmlPanelList.appendChild(li);
    }
    return this;
};

MultipleCollapsiblePanel.prototype._updatePanelList = function () {
    var i, items;
    if(this._htmlPanelList) {
        items = this._items.asArray();
        jQuery(this._htmlPanelList).empty();
        for(i = 0; i < items.length; i++) {
            this._addPanelToList(items[i], i);
        }
    }
    return this;
};

MultipleCollapsiblePanel.prototype._onPanelListItemClick = function () {
    var that = this;
    return function (e) {
        var index = parseInt(this.getAttribute("data-panel-index"));
        e.preventDefault();
        that.selectPanel(index);
    };
};

MultipleCollapsiblePanel.prototype._onClose = function () {
    var that = this;
    return function () {
        if(typeof that.onClose === 'function') {
            that.onClose(that);
        }
    };
};

MultipleCollapsiblePanel.prototype._attachListeners = function () {
    var that;
    if(this._htmlPanelList && this.html && !this._attachedListeners) {
        CollapsiblePanel.prototype._attachListeners.call(this);
        jQuery(this._htmlPanelList).on('click', '.collapsible-panel-listitem', this._onPanelsListItemClick());
        jQuery(this._htmlCloseButton).on('click', this._onClose());
        this._attachedListeners = true;
    }
    return this;
};

MultipleCollapsiblePanel.prototype.createHTML = function () {
    var htmlButtonToolbar, listButton, closeButton, panelList, dropdownContainer;
    if(!this.html) {
        CollapsiblePanel.prototype.createHTML.call(this);
        //creates the bootstrap container for button group
        htmlButtonToolbar = this.createHTMLElement('div');
        htmlButtonToolbar.className = 'btn-group pull-right';
        //Create the list for the button
        listButton = this.createHTMLElement('button');
        listButton.className = 'btn btn-mini icon-caret-down dropdown-toggle';
        listButton.id = this.id + "-panels-button";
        listButton.setAttribute("data-toggle", "dropdown");
        //Create the button for closing
        closeButton = this.createHTMLElement('button');
        closeButton.className = "adam collapsible-panel-closebutton btn btn-mini icon-remove";
        //Create the panels list
        panelList = this.createHTMLElement('ul');
        panelList.className = "adam collapsible-panel-panelslist dropdown-menu";
        panelList.setAttribute("role", "menu");
        panelList.setAttribute("aria-labelledby", this.id + "-panels-button");

        htmlButtonToolbar.appendChild(listButton);
        htmlButtonToolbar.appendChild(panelList);
        htmlButtonToolbar.appendChild(closeButton);
        jQuery(this._htmlHeader).prepend(htmlButtonToolbar);

        this._htmlButtonToolbar = htmlButtonToolbar;
        this._htmlCloseButton = closeButton;
        this._htmlPanelListButton = listButton;
        this._htmlPanelList = panelList;
        this._updatePanelList();

        this._attachListeners();
    }
    return this.html;
};

*/