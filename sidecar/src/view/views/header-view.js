(function(app) {

    /**
     * View that displays header for current app
     * @class View.Views.HeaderView
     * @alias SUGAR.App.layout.HeaderView
     * @extends View.View
     */
    app.view.views.HeaderView = app.view.View.extend({
        /**
         * Initialize the View
         *
         * @constructor
         * @param {Object} options
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
                            if(app.acl.hasAccess('create', app.data.createBean(loadedModule,{assigned_user_id: 'assignedUserId'}))) {
                                self.createListLabels.push('Create '+singular);
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
