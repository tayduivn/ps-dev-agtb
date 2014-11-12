({
    extendsFrom: 'CreateView',

    saveOpenBusinessRulesName: 'save_open_businessrules',

    SAVEACTIONS: {
        SAVE_OPEN_BUSINESRULES: 'saveOpenBusinessRules'
    },

    initialize: function (options) {
        app.view.invokeParent(this, {type: 'view', name: 'create-actions', method: 'initialize', args:[options]});

        var createViewEvents = {};
        createViewEvents['click a[name=' + this.saveOpenBusinessRulesName + ']:not(.disabled)'] = 'saveOpenBusinessRules';
        this.events = _.extend({}, this.events, createViewEvents);
    },

    saveOpenBusinessRules: function() {
        this.context.lastSaveAction = this.SAVEACTIONS.SAVE_OPEN_BUSINESRULES;
        this.initiateSave(_.bind(function () {
            app.navigate(this.context, this.model, 'layout/businessrules');
        }, this));
    }
})
