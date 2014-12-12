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

    loadData: function () {
    },

    initialize: function(options) {
        var sep = '/';
        this.inboxId = options.context.attributes.modelId;
        this.flowId = options.context.attributes.action;
        var self = this;
        var pmseInboxUrl = app.api.buildURL(options.module + '/case/' + this.inboxId + sep + this.flowId ,'',{},{});
        app.api.call('READ', pmseInboxUrl, {},{
            success: function(data)
            {
                self.initCaseView(data)
            }
        });
    },

    initCaseView: function(data){
        //console.log(data);
        if(data.case.flow.cas_flow_status==='FORM'){
            this.params = {
                action: 'detail',
                layout: 'pmse-case',
                modelId: data.case.flow.cas_sugar_object_id,
                module: data.case.flow.cas_sugar_module,
                case: data.case
            };
            app.controller.loadView(this.params);
        }else{
            app.alert.show('message-id', {
                level: 'warning',
                messages: app.lang.get('LBL_NSC_MESSAGE','pmse_Inbox')+data.case.flow.cas_flow_status,
                autoClose: false
            });
            app.router.goBack();
        }
    }
})
