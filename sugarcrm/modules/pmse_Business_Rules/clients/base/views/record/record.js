({
    extendsFrom: 'RecordView',

    initialize: function (options) {
        app.view.invokeParent(this, {type: 'view', name: 'record', method: 'initialize', args:[options]});
        this.context.on('button:design_businessrules:click', this.designBusinessRules, this);
        this.context.on('button:export_businessrules:click', this.exportBusinessRules, this);
    },

    designBusinessRules: function(model) {
        app.navigate(this.context, model, 'layout/businessrules');
    },

    exportBusinessRules: function(model) {
        var url = app.api.buildURL(model.module, 'brules', {id: model.id}, {platform: app.config.platform});

        if (_.isEmpty(url)) {
            app.logger.error('Unable to get the Project download uri.');
            return;
        }

        app.api.fileDownload(url, {
            error: function(data) {
                // refresh token if it has expired
                app.error.handleHttpError(data, {});
            }
        }, {iframe: this.$el});
    }
})
