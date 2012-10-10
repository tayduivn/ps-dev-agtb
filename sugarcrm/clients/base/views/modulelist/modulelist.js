({
    events: {
        'click #module_list li a': 'onModuleTabClicked'
    },

    initialize: function(options) {
        this.app.events.on("app:sync:complete", this.render, this);
        this.app.events.on("app:view:change", this.render, this);

        this.app.view.View.prototype.initialize.call(this, options);

        if (this.layout) {
            this.layout.on("view:resize", this.resize, this);
        }
    },

    /**
     * Render list of modules
     * @private
     */
    _renderHtml: function() {
        if (!this.app.api.isAuthenticated() || this.app.config.appStatus == 'offline') return;

        this.module_list = SUGAR.App.metadata.data.module_list;
        this.app.view.View.prototype._renderHtml.call(this);
        this.initMenu();
    },

    /**
     * When user clicks tab navigation in header
     */
    onModuleTabClicked: function(evt) {
        var moduleHref = this.$(evt.currentTarget).attr('href');
        if(!moduleHref.match(/^javascript\:/g)) {
            evt.preventDefault();
            evt.stopPropagation();
            this.$('#module_list li').removeClass('active');
            this.$(evt.currentTarget).parent().addClass('active');
            this.app.router.navigate(moduleHref, {trigger: true});
        }
    },

    /**
     * Reset the module list to the full list
     */
    initMenu: function() {
        var moduleList = this.$("#module_list"),
            activeMenu = moduleList.find(".active");
        if (activeMenu.length > 0 && activeMenu[0]._nextSibling) {
            activeMenu[0]._nextSibling.before(activeMenu);
        }
        //restore back to the module list
        this.$(".more").before(moduleList.find(".dropdown-menu").children());
        this.$(".dropdown.open").toggleClass("open");
        moduleList.find("." + this.app.controller.context.get("module")).addClass("active");
    },

    /**
     * Resize the module list to the specified width and move the extra module names to the dropdown
     */
    resize: function (width) {
        //TODO: ie Compatible, scrollable dropdown for low-res. window
        //TODO: Theme Compatible, Filtered switching menu
        var currentModuleList = this.$("#module_list"),
            menuItemsWidth = currentModuleList.width(),
            menuItems = currentModuleList.children("li"),
            menuLength = menuItems.length,
            menuNode = currentModuleList.find(".more"),
            moreMenuLength = menuNode.find(".dropdown-menu li").length,
            dropdownNode = menuNode.find(".dropdown-menu"),
        //TODO: User preferences maximum menu count
            max_tabs = menuLength + moreMenuLength,
            nextMenuNode = null;

        if(menuItemsWidth > width){ //Flip
            menuNode = menuNode.prev();
            //Move the overflooding menu item into the dropdown
            //until the current menu item width exceeds the max. available width
            //To avoid the race condition the loop lasts until all menu items iterates once.
            while(menuItemsWidth >= width && menuLength-- > 0){

                if(menuNode.hasClass("active")){
                    if(_.isUndefined(menuNode[0]._nextSibling)) {
                        menuNode[0]._nextSibling = dropdownNode.children("li:first");
                    }
                    menuNode = menuNode.prev();
                }
                if(menuNode.hasClass("home")){
                    menuNode = menuNode.prev();
                }
                if(menuNode.hasClass("more")){
                    menuNode = menuNode.prev();
                }

                nextMenuNode = menuNode.prev();
                dropdownNode.prepend(menuNode.attr("width", menuNode.width()));
                menuItemsWidth = currentModuleList.width();
                menuNode = nextMenuNode;

            }
        } else { //Expand
            var insertNode = dropdownNode.children("li:first");
            while(menuItemsWidth <= width && (menuLength <= max_tabs)){
                var menuNodeWidth = insertNode.width();
                //If current proposing item exceeds the maxium availble width,
                //it should skip the expanding job.
                if (menuItemsWidth + menuNodeWidth > width) {
                    break;
                }
                menuLength++;

                nextMenuNode = insertNode.next();

                if(menuNode.prev().hasClass("active")) {
                    menuNode = menuNode.prev();
                    if(menuNode[0]._nextSibling && menuNode[0]._nextSibling.attr("class") == insertNode.attr("class")) {
                        menuNode = menuNode.next();
                    }
                }
                menuNode.before(insertNode);
                menuItemsWidth = currentModuleList.width();
                insertNode = nextMenuNode;
            }
        }
    }
})