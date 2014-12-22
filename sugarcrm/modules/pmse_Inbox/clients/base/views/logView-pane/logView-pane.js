/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

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
            case app.lang.get('LBL_PMSE_BUTTON_PROCESS_AUTHOR_LOG', self.module):
                var pmseInboxUrl = app.api.buildURL(this.module + '/getLog/pmse');
                app.api.call('READ', pmseInboxUrl, {},{
                    success: function(data)
                    {
                        self.getLogRefresh(data);
                    }
                });
                break;
            case app.lang.get('LBL_PMSE_BUTTON_SUGARCRM_LOG', self.module):
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
