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
/**
 * That method builds the show History window
 * if ago options is enabled
 * @param {Number} caseId
 * @return {*}
 */
function showReassign(caseId) {
    var restClient, proxy, logPanel, w2, label, callback, usersProxy;
//    restClient = new RestClient();
//    restClient.setRestfulBehavior(SUGAR_REST);
//    if (!SUGAR_REST) {
//        restClient.setBackupAjaxUrl(SUGAR_AJAX_URL);
//    }
    proxy = new SugarProxy({
        //url: SUGAR_URL + '/rest/v10/CrmData/changeCaseUser/',
        url : 'pmse_Inbox/changeCaseUser/' + caseId,
//        restClient: restClient,
//        uid : caseId,
        callback: null
    });
    usersProxy = new SugarProxy({
//        url: SUGAR_URL + '/rest/v10/CrmData/userListByTeam/',
          url: 'pmse_Project/CrmData/users'
//        restClient: restClient,
//        uid : caseId,
//        callback: null
    });

    logPanel = new ReassignForm({
        logType: 'difList',
        //items: [ ],
        buttons: [
            { jtype: 'normal', caption: translate('LBL_PMSE_BUTTON_SAVE'), handler: function () {
                var result = [], i, saveProxy;
                for (i = 0; i < logPanel.items.length; i += 1) {
                    result.push(logPanel.items[i].getObjectValue());
                }
                saveProxy = new SugarProxy({
                    url: 'pmse_Inbox/updateChangeCaseFlow',
//                    restClient: restClient,
//                    uid: caseId,
                    callback: null
                });
                saveProxy.sendData(result);
                w2.close();
            }},
            { jtype: 'normal', caption: translate('LBL_PMSE_BUTTON_CANCEL'), handler: function () {
                w2.close();
//                }
            }}
        ],
        callback: {
            'loaded': function (data) {
                var logs, beforeArray = [], afterArray = [], fieldArray = [], users,
                    i, items, log, itemSettings, j, newItem, html;
//                logs = proxy.getData();

                proxy.getData(null, {
                    success: function(logs) {
                        if (logs) {
                            for (i = 0; i < logs.result.length; i += 1) {
                                log = logs.result[i];
                                //get users
                                usersProxy.url = 'pmse_Inbox/userListByTeam/' + log.act_assign_team;
                                usersProxy.getData(null, {

                                    success: function(users) {
                                        itemSettings = {
                                            act_name: log.act_name,
                                            cas_delegate_date: log.cas_delegate_date,
                                            cas_due_date: log.cas_due_date,
                                            cas_index: log.cas_index,
                                            act_expected_time: log.act_expected_time,
                                            comboId: log.cas_id,
                                            options: users.result,
                                            defaultValue: log.cas_user_id
                                        };
                                        newItem = new ReassignField(itemSettings);
                                        newItem.setParent(logPanel);
                                        html = newItem.createHTML();
                                        logPanel.body.appendChild(html);
                                        logPanel.items.push(newItem);
                                    }
                                });



                            }
                        }
                    }
                });

            }
        },
        columns: [
            'Current Task',
            'Task Delegated Data',
            'Expected Time',
            'Due Date',
            'Current User'
        ]
    });

    w2 = new Window({
        width: 800,
        height: 350,
        modal: true,
        title: '# ' + caseId + ': ' + translate('LBL_PMSE_LABEL_REASSIGN_CASES', 'pmse_Inbox')
    });

    w2.addPanel(logPanel);
    w2.show();
}