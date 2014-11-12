({
    extendsFrom: 'CreateView',

    saveOpenDesignName: 'save_open_design',

    SAVEACTIONS: {
        SAVE_OPEN_DESIGN: 'saveOpenDesign'
    },

    initialize: function (options) {
        app.view.invokeParent(this, {type: 'view', name: 'create-actions', method: 'initialize', args:[options]});

        var createViewEvents = {};
        createViewEvents['click a[name=' + this.saveOpenDesignName + ']:not(.disabled)'] = 'saveOpenDesign';
        this.events = _.extend({}, this.events, createViewEvents);

    },

    saveOpenDesign: function() {
        this.context.lastSaveAction = this.SAVEACTIONS.SAVE_OPEN_DESIGN;
        this.initiateSave(_.bind(function () {
            app.navigate(this.context, this.model, 'layout/designer');
        }, this));
    }
})
