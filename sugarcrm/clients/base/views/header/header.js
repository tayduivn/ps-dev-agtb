({

/**
 * View that displays header for current app
 * @class View.Views.HeaderView
 * @alias SUGAR.App.layout.HeaderView
 * @extends View.View
 */
    events: {
        'click #module_list li a': 'onModuleTabClicked',
        'click #createList li a': 'onCreateClicked',
        'click .typeahead a': 'clearSearch',
        'click .navbar-search span.add-on': 'gotoFullSearchResultsPage'
    },

    /**
     * Renders Header view
     */
    initialize: function(options) {
        app.events.on("app:sync:complete", this.render, this);
        app.events.on("app:view:change", this.render, this);
        app.view.View.prototype.initialize.call(this, options);
    },
    _renderHtml: function() {
        var self = this,
            menuTemplate;
        if (!app.api.isAuthenticated() || app.config.appStatus == 'offline') return;

        self.setModuleInfo();
        self.setCreateTasksList();
        self.setCurrentUserName();
        app.view.View.prototype._renderHtml.call(self);
        self.initMenu();
        $(window).off("resize", self.resizeMenu).on("resize", self.resizeMenu);
        self.resizeMenu();
        // Search ahead drop down menu stuff
        menuTemplate = app.template.getView('dropdown-menu');
        this.$('.search-query').searchahead({
            request:  self.fireSearchRequest,
            compiler: menuTemplate,
            onEnterFn: function(hrefOrTerm, isHref) {
                // if full href treat as user clicking link
                if(isHref) {
                    window.location = hrefOrTerm;
                } else {
                    // It's the term only (user didn't select from drop down
                    // so this is essentially the term typed
                    app.router.navigate('#search/'+hrefOrTerm, {trigger: true});
                }
            }
        });
    },
    /** 
     * Callback for the searchahead plugin .. note that
     * 'this' points to the plugin (not the header view!)
     */
    fireSearchRequest: function (term) {
        var plugin = this, mlist, params;
        mlist = app.metadata.getModuleNames(true).join(','); // visible
        params = {q: term, fields: 'name, id', module_list: mlist, max_num: app.config.maxSearchQueryResult};
        app.api.search(params, {
            success:function(data) {
                data.module_list = app.metadata.getModuleNames(true,"create");
                plugin.provide(data);
            },
            error:function(error) {
                app.error.handleHttpError(error, plugin);
                app.logger.error("Failed to fetch search results in search ahead. " + error);
            }
        });
    },
    /**
     * Takes user to full search results page 
     */
    gotoFullSearchResultsPage: function(evt) {
        var term;
        // Don't let plugin kick in. Navigating directly to search results page
        // when clicking on adjacent button is, to my mind, special case portal
        // application requirements so I'd rather do here than change plugin.
        evt.preventDefault();
        evt.stopPropagation();
        // URI encode search query string so that it can be safely
        // decoded by search handler (bug55572)
        term = encodeURIComponent(this.$('.search-query').val());
        if(term && term.length) {
            app.router.navigate('#search/'+term, {trigger: true});
        }
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
            app.router.navigate(moduleHref, {trigger: true});
        }
    },
    onCreateClicked: function(evt) {
        var moduleHref, hashModule;
        moduleHref = evt.currentTarget.hash;
        hashModule = moduleHref.split('/')[0];
        this.$('#module_list li').removeClass('active');
        this.$('#module_list li a[href="'+hashModule+'"]').parent().addClass('active');
    },
    hide: function() {
        this.$el.hide();
    },
    show: function() {
        this.$el.show();
    },
    setCurrentUserName: function() {
        this.fullName = app.user.get('full_name');
    },
    /**
     * Creates the task create drop down list 
     */
    setCreateTasksList: function() {
        var self = this, singularModules;
        self.createListLabels = [];

        try {
            singularModules = SUGAR.App.lang.getAppListStrings("moduleListSingular");
            if(singularModules) {
                self.createListLabels = this.creatableModuleList;
            }
        } catch(e) {
            return;
        }
    },
    setModuleInfo: function() {
        var self = this;
        this.createListLabels = [];
        this.currentModule = this.module;
        this.module_list = app.metadata.getModuleNames(true);
        this.creatableModuleList = app.metadata.getModuleNames(true,"create");
    },

    /**
     * Clears out search upon user following search result link in menu
     */
    clearSearch: function(evt) {
        this.$('.search-query').val('');
    },
    initMenu: function() {
        var moduleList = this.$("#module_list"),
            activeMenu = moduleList.find(".active");
        if(activeMenu.length > 0 && activeMenu[0]._nextSibling) {
            activeMenu[0]._nextSibling.before(activeMenu);
        }
        //restore back to the module list
        this.$(".more").before(moduleList.find(".dropdown-menu").children());
        this.$(".dropdown.open").toggleClass("open");
        moduleList.find("." + app.controller.context.get("module")).addClass("active");
    },
    /**
     * Resize the module list to fit the window resolution
     */
    resizeMenu: function () {
        //TODO: ie Compatible, scrollable dropdown for low-res. window
        //TODO: Theme Compatible, Filtered switching menu
        var maxMenuWidth = this.$(".navbar-inner > .container-fluid").width() - 100 //100px: spacing for submegamenu, padding and border lines
            - this.$("#userList").width() - this.$("#searchForm").width();
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

        if(menuItemsWidth > maxMenuWidth){ //Flip
            menuNode = menuNode.prev();
            //Move the overflooding menu item into the dropdown
            //until the current menu item width exceeds the max. available width
            //To avoid the race condition the loop lasts until all menu items iterates once.
            while(menuItemsWidth >= maxMenuWidth && menuLength-- > 0){

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
            while(menuItemsWidth <= maxMenuWidth && (menuLength <= max_tabs)){
                var menuNodeWidth = insertNode.width();
                //If current proposing item exceeds the maxium availble width,
                //it should skip the expanding job.
                if (menuItemsWidth + menuNodeWidth > maxMenuWidth) {
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
