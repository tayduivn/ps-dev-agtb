
({
    events:{
        'click [name=log_refresh_button]': 'logRefreshClick'
    },
    /**
     * {@inheritdocs}
     *
     * Sets up the file field to edit mode
     *
     * @param {View.Field} field
     * @private
     */
    _renderField: function(field) {
        app.view.View.prototype._renderField.call(this, field);
    },
    logRefreshClick : function() {
        app.alert.show('getLog', {level: 'process', title: 'Loading', autoclose: false});
        var self = this;
        var logModel=$('#logPmseId').text();
        switch(logModel)
        {
            case 'PMSE Log':
                var pmseInboxUrl = app.api.buildURL(this.module + '/getLog/pmse');
                app.api.call('READ', pmseInboxUrl, {},{
                    success: function(data)
                    {
                        self.getLogRefresh(data);
                    }
                });
                break;
            case 'SugarCRM Log':
                var pmseInboxUrl = app.api.buildURL(this.module + '/getLog/sugar');
                app.api.call('READ', pmseInboxUrl, {},{
                    success: function(data)
                    {
                        self.getLogRefresh(data);
                    }
                });
                break;
        }
    },
    getLogRefresh: function(data) {
        $("textarea").html(data);
        app.alert.dismiss('getLog');
    }
})
