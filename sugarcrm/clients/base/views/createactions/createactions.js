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
        var singularModules = this.app.lang.getAppListStrings("moduleListSingular");
        this.createListLabels = [];

        if(singularModules) {
            this.createListLabels = this.creatableModuleList;
        }
    },

    /**
     * Retrieves list of available modules and current module information
     */
    setModuleInfo: function() {
        //TODO: sidecar needs a function to pull this list from user prefs
        this.creatableModuleList = this.app.metadata.getModuleNames(true,"create");
    }
})