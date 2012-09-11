({

    /**
     * View that displays header for current app
     * @class View.Views.HeaderView
     * @alias SUGAR.App.layout.HeaderView
     * @extends View.View
     */
    events: {
        'click #moduleList li a': 'onModuleTabClicked',
        'click #createList li a': 'onCreateClicked',
        'click .typeahead a': 'clearSearch',
        'click .navbar-search span.add-on': 'gotoFullSearchResultsPage',
        'click .promo li': 'onPromoClicked'
    },

    /**
     * Renders Header view
     */
    initialize: function(options) {
        app.events.on("app:sync:complete", this.render, this);
        app.view.View.prototype.initialize.call(this, options);
    },
    _renderHtml: function() {
        var self = this,
            menuTemplate;
        if (!app.api.isAuthenticated() || app.config.appStatus == 'offline') return;

        self.setModuleInfo();
        self.setCreateTasksList();
        self.setCurrentUserName();
        self.setCurrentUserImage();                
        app.view.View.prototype._renderHtml.call(self);

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
    onPromoClicked: function() {


    },

    /**
     * Callback for the searchahead plugin .. note that
     * 'this' points to the plugin (not the header view!)
     */
    fireSearchRequest: function (term) {
        var plugin = this, mlist, params;
        mlist = app.metadata.getModuleNames(true).join(','); // visible
        params = {q: term, fields: 'name, id', moduleList: mlist, max_num: app.config.maxSearchQueryResult};

        app.api.search(params, {
            success:function(data) {
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

        term = this.$('.search-query').val();
        if(term && term.length) {
            app.router.navigate('#search/'+term, {trigger: true});
        }
    },

    /**
     * When user clicks tab navigation in header
     */
    onModuleTabClicked: function(evt) {
        evt.preventDefault();
        evt.stopPropagation();
        var moduleHref = this.$(evt.currentTarget).attr('href');
        this.$('#moduleList li').removeClass('active');
        this.$(evt.currentTarget).parent().addClass('active');
        app.router.navigate(moduleHref, {trigger: true});
    },
    onCreateClicked: function(evt) {
        var moduleHref, hashModule;
        moduleHref = evt.currentTarget.hash;
        hashModule = moduleHref.split('/')[0];
        this.$('#moduleList li').removeClass('active');
        this.$('#moduleList li a[href="' + hashModule + '"]').parent().addClass('active');
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
    setCurrentUserImage: function() {
        var picture = app.user.get('picture');
        this.pictureUrl = (picture) ? app.api.buildFileURL({
            module: 'Users',
            id: app.user.get('id'),
            field: 'picture'
        }) : "../clients/summer/views/imagesearch/anonymous.jpg"; 
    },    
    /**
     * Creates the task create drop down list
     */
    setCreateTasksList: function() {
        var self = this, singularModules;
        self.createListLabels = [];

        try {
            singularModules = SUGAR.App.lang.getAppListStrings("moduleListSingular");
            if (singularModules) {
                _.each(self.moduleList, function(loadedModule) {

                    // Continue on Leads, Notes, or KBDocuments, but for all others:
                    // check access to create and push to list
                    if (loadedModule === 'Leads' || loadedModule === 'Notes' || loadedModule === 'KBDocuments') {
                        app.logger.debug("Not a module user can create so not putting in dropdown. Skipping: " + loadedModule);
                    } else {
                        if (app.acl.hasAccess('create', loadedModule)) {
                            self.createListLabels.push(loadedModule);
                        }
                    }
                });
            }
        } catch (e) {
            return;
        }
    },
    setModuleInfo: function() {
        var self = this;
        this.createListLabels = [];
        this.currentModule = this.module;
        this.moduleList = app.metadata.getModuleNames(true);
        this.promoList = [
            {name: 'Reports', url: 'http://cnn.com'}
        ];
    },

    /**
     * Clears out search upon user following search result link in menu
     */
    clearSearch: function(evt) {
        this.$('.search-query').val('');
    }

})
