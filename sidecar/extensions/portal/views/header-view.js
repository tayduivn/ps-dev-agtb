(function(app) {

    /**
     * View that displays header for current app
     * @class View.Views.HeaderView
     * @alias SUGAR.App.layout.HeaderView
     * @extends View.View
     */
    app.view.views.HeaderView = app.view.View.extend({
        events: {
            'click #moduleList li a': 'onModuleTabClicked',
            'click #createList li a': 'onCreateClicked',
            'click .cube': 'onHomeClicked',
            'click .typeahead a': 'clearSearch'
        },

        /**
         * Renders Header view
         */
        initialize: function(options) {
        app.events.on("app:sync:complete", this.render, this);
            app.view.View.prototype.initialize.call(this, options);
        },
        _renderSelf: function() {
            var self = this,
                menuTemplate;
            if (!app.api.isAuthenticated()) return;

            self.setModuleInfo();
            self.setCreateTasksList();
            app.view.View.prototype._renderSelf.call(self);

            // Search ahead drop down menu stuff
            menuTemplate = app.template.getView('dropdown-menu');
            this.$('.search-query').searchahead({
                request:  self.fireSearchRequest,
                compiler: menuTemplate,
                buttonElement: '.navbar-search a.btn'
            });
        },
        /** 
         * Callback for the searchahead plugin .. note that
         * 'this' points to the plugin (not the header view!)
         */
        fireSearchRequest: function (term) {
            var plugin = this, mlist, params;
            mlist = app.metadata.getDelimitedModuleList(',', true);
            params = {query: term, fields: 'name, id', moduleList: mlist, maxNum: app.config.maxSearchQueryResult};
            app.api.search(params, {
                success:function(data) {
                    plugin.provide(data);
                }
            });
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
        onHomeClicked: function(evt) {
            // Just removes active on modules for now.
            // TODO: Maybe we should highlight the "cube"?
            this.$('#moduleList li').removeClass('active');
        },
        onCreateClicked: function(evt) {
            var moduleHref, hashModule;
            moduleHref = evt.currentTarget.hash;
            hashModule = moduleHref.split('/')[0];
            this.$('#moduleList li').removeClass('active');
            this.$('#moduleList li a[href="'+hashModule+'"]').parent().addClass('active');
        },
        hide: function() {
            this.$el.hide();
        },
        show: function() {
            this.$el.show();
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
                    _.each(self.moduleList, function(loadedModule) {

                        // Continue on Leads, Notes, or KBDocuments, but for all others:
                        // check access to create and push to list
                        if(loadedModule === 'Leads' || loadedModule === 'Notes' || loadedModule === 'KBDocuments') {
                            app.logger.debug("Not a module user can create so not putting in dropdown. Skipping: "+loadedModule);
                        } else {
                            var singular = (singularModules[loadedModule]) ? singularModules[loadedModule] : loadedModule;
                            if(app.acl.hasAccess('create', loadedModule)) {
                                self.createListLabels.push({label:'Create '+singular, module: loadedModule});
                            }
                        }
                    });
                }
            } catch(e) {
                return;
            }
        },

        setModuleInfo: function() {
            var self = this;
            this.createListLabels = [];
            this.moduleList = app.metadata.getModuleList({visible: true});
        },

        /**
         * Clears out search upon user following search result link in menu
         */
        clearSearch: function(evt) {
            this.$('.search-query').val('');
        }

    });

}(SUGAR.App));
