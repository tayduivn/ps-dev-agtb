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
            'click .cube': 'onHomeClicked'
        },
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

        /**
         * Renders Header view
         */
        initialize: function(options) {
            var self = this;
            app.events.on("app:sync:complete", function() {
                self.render();
            });
            app.view.View.prototype.initialize.call(this, options);
        },
        /**
         * Renders Header view
         */
        render: function() {
            if (!app.api.isAuthenticated()) return;
            this.setModuleInfo();
            this.setCreateTasksList();
            app.view.View.prototype.render.call(this);
        },
        hide: function() {
            this.$el.hide();
        },
        show: function() {
            this.$el.show();
        },
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
            this.currentModule = this.context.get('module');
            this.moduleList = _.toArray(app.metadata.getModuleList());

            if (app.config && app.config.displayModules) {
                this.moduleList = _.intersection(this.moduleList, app.config.displayModules)
            };
        }

    });

}(SUGAR.App));
