({
    extendsFrom: 'CreateView',

    saveOpenEmailsTemplatesName: 'save_open_emailstemplates',

    SAVEACTIONS: {
        SAVE_OPEN_EMAILS_TEMPLATES: 'saveOpenEmailsTemplates'
    },

    initialize: function (options) {
        app.view.invokeParent(this, {type: 'view', name: 'create-actions', method: 'initialize', args:[options]});

        var createViewEvents = {};
        createViewEvents['click a[name=' + this.saveOpenEmailsTemplatesName + ']:not(.disabled)'] = 'saveOpenEmailsTemplates';
        this.events = _.extend({}, this.events, createViewEvents);

    },

    saveOpenEmailsTemplates: function() {
        this.context.lastSaveAction = this.SAVEACTIONS.SAVE_OPEN_EMAILS_TEMPLATES;
        this.initiateSave(_.bind(function () {
            app.navigate(this.context, this.model, 'layout/emailtemplates');
        }, this));
    }
})
