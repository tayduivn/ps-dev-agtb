({
    events: {
        'click #createList li a': 'onCreateClicked'
    },

    initialize: function(options) {
        this.app.events.on("app:sync:complete", this.render, this);
        this.app.view.View.prototype.initialize.call(this, options);
    },

    _renderHtml: function() {
        if (!this.app.api.isAuthenticated() || this.app.config.appStatus == 'offline') return;

        this.setModuleInfo();
        this.setCreateTasksList();
        this.app.view.View.prototype._renderHtml.call(this);
    },

    onCreateClicked: function(evt) {
        var moduleHref, hashModule;
        moduleHref = evt.currentTarget.hash;
        hashModule = moduleHref.split('/')[0];
        this.$('#module_list li').removeClass('active');
        this.$('#module_list li a[href="'+hashModule+'"]').parent().addClass('active');
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

    /**
     * Retrieves list of available modules and current module information
     */
    setModuleInfo: function() {
        this.createListLabels = [];
        this.currentModule = this.module;
        //TODO: sidecar needs a function to pull this list from user prefs
        //The module list needs to be key:value pairs of module name and its translated label
        this.module_list = SUGAR.App.metadata.data.module_list;
        this.creatableModuleList = app.metadata.getModuleNames(true,"create");
    }
})