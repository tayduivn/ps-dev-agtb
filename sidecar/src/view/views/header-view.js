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
            'click #createList li a': 'onCreateClicked'
        },
        onModuleTabClicked: function(evt) {
            evt.preventDefault();
            evt.stopPropagation();
            var moduleHref = $(evt.currentTarget).attr('href');
            $('#moduleList li').removeClass('active');
            $(evt.currentTarget).parent().addClass('active');
            app.router.navigate(moduleHref, {trigger: true});
        },

        onCreateClicked: function(evt) {
            var moduleHref, hashModule;
            moduleHref = evt.currentTarget.hash;
            hashModule = moduleHref.split('/')[0];
            $('#moduleList li').removeClass('active');
            $('#moduleList li a[href="'+hashModule+'"]').parent().addClass('active');
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
            // So this "re-binds" our delegate events defined above. We're re-rendering the
            // header view twice; (once for initial page load; I assume so we "look fast"),
            // and then again once app:sync:complete fires. This makes sense, but our events
            // get lost since, ultimately, backbone.render gets recalled. This fixes that ;=)
            this.delegateEvents(); 
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
                            if(app.acl.hasAccess('create', app.data.createBean(loadedModule,{assigned_user_id: 'assignedUserId'}))) {
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
        }

    });

}(SUGAR.App));
