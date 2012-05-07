(function(app) {

    /**
     * View that displays header for current app
     * @class View.Views.HeaderView
     * @alias SUGAR.App.layout.HeaderView
     * @extends View.View
     */
    app.view.views.HeaderView = app.view.View.extend({
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
            var self = this;
            self.createListLabels = [];
            try {
                if(app.acl.hasAccess('edit', app.data.createBean('Bugs',{assigned_user_id: 'assignedUserId'}))) {
                    self.createListLabels.push(app.lang.get('LBL_CREATE_BUG', 'Emails'));
                }
                if(app.acl.hasAccess('edit', app.data.createBean('Cases', {assigned_user_id: 'assignedUserId'}))) {
                    self.createListLabels.push(app.lang.get('LBL_CREATE_CASE', 'Emails'));
                }
                if(app.acl.hasAccess('edit', app.data.createBean('Leads', {assigned_user_id: 'assignedUserId'}))) {
                    self.createListLabels.push(app.lang.get('LBL_CREATE_LEAD', 'Emails'));
                }
                // At time of writing KBDocuments module is not supported so this will throw. I put it here so that bugs, cases,
                // and leads still render properly. When available, Create KBDocument will be pushed into second to last indice. 
                if(app.acl.hasAccess('edit', app.data.createBean('KBDocuments', {assigned_user_id: 'assignedUserId'}))) {
                    self.createListLabels.splice([self.createListLabels.length - 1], 0, app.lang.getAppStrings('LBL_CREATE_KB_DOCUMENT'));
                    
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
