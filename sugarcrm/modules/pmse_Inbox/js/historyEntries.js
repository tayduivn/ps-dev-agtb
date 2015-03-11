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
function showHistory(caseId, caseIndex) {
    var restClient, proxy, logPanel, w2, label, _App;
    if (App) {
        _App = App;
    } else {
        _App = parent.SUGAR.App;
    }

    /*proxy = new SugarProxy({
        //url: SUGAR_URL + '/rest/v10/Log/',
        url: 'pmse_Inbox/historyLog/' + caseId,
        restClient: restClient,
        uid : caseId,
        callback: null
    });*/

    var pmseHistoryUrl = _App.api.buildURL('pmse_Inbox/historyLog', null, {id:caseId});

    logPanel = new HistoryPanel({
        logType: 'difList',
        items: [ ],
        callback :{
            'loaded': function (data) {
                var logs, beforeArray = [], afterArray = [], fieldArray = [],
                    i, items, log, newLog, j;
                //proxy.getData(null, {
                    //success: function(logs) {
                _App.api.call('read', pmseHistoryUrl, {}, {
                    success: function (logs) {
                        if (logs) {
                            for (i = 0; i < logs.result.length; i += 1) {
                                beforeArray = [];
                                afterArray = [];
                                fieldArray = [];
                                items = [];
                                log = logs.result[i];

                                var end_date=Date.parse(log.end_date);
                                var current_date=Date.parse(log.current_date);
                                var delegate_date=Date.parse(log.delegate_date);
                                var start_date=Date.parse(log.start_date);

                                //IE, Firefox date fix
                                if (isNaN(end_date)){
                                    end_date= Date.parse(log.end_date.replace(/\s/g, "T"));
                                }
                                if (isNaN(current_date)){
                                    current_date= Date.parse(log.current_date.replace(/\s/g, "T"));
                                }
                                if (isNaN(delegate_date)){
                                    delegate_date= Date.parse(log.delegate_date.replace(/\s/g, "T"));
                                }
                                if (isNaN(start_date)){
                                    start_date= Date.parse(log.start_date.replace(/\s/g, "T"));
                                }

                                if (end_date) {
                                    label = log.data_info + '. <strong> ( ' + timeElapsedString(current_date, end_date, true) + ' )</strong> ';
                                } else {
                                    label = log.data_info + '. <strong>' + translate('LBL_PMSE_HISTORY_LOG_NO_YET_STARTED', 'pmse_Inbox') + '</strong> ';
                                }

                                var pictureUrl = _App.api.buildFileURL({
                                    module: 'Users',
                                    id: log.cas_user_id,
                                    field: 'picture'
                                });

                                newLog = {
                                    name: 'log' + i,
                                    label: label,
                                    user: log.user,
                                    picture : pictureUrl,
                                    duration: '<strong> ' + timeElapsedString(end_date, delegate_date) + ' <strong>',
                                    startDate: (start_date) ? log.start_date :  translate('LBL_PMSE_HISTORY_LOG_NO_YET_STARTED', 'pmse_Inbox'),
                                    //startDate: (Date.parse(log.start_date)) ? Date.parse(log.start_date).toString('MMMM d, yyyy HH:mm') :  translate('LBL_PMSE_MESSAGE_NOYETSTARTED'),
                                    //startDate: translate('LBL_PMSE_MESSAGE_NOYETSTARTED'),
                                    completed: log.completed
                                };
                                //parse all log var_values collected
                                if (log.var_values && log.var_values !== '') {
                                    $.each(log.var_values.before_data, function(a, obj) {
                                        fieldArray.push(a);
                                        beforeArray.push(obj);

                                    });
                                    $.each(log.var_values.after_data, function(a, obj) {
                                        afterArray.push(obj);
                                    });
                                }

                                for (j = 0; j < afterArray.length; j += 1) {
                                    items.push({
                                        field: fieldArray[j],
                                        before: beforeArray[j],
                                        after: afterArray[j]
                                    });

                                }
                                if (items) {
                                    $.extend(true, newLog, {items: items});
                                }
                                logPanel.addLog(newLog);
                            }
                        }
                        _App.alert.dismiss('upload');
                        w2.html.style.display = 'inline';
                    }
                });


            }
        }
    });

    w2 = new Window({
        width: 800,
        height: 350,
        modal: true,
        title: '# ' + caseId + ': ' + translate('LBL_PMSE_TITLE_HISTORY', 'pmse_Inbox')
    });

    w2.addPanel(logPanel);
    w2.show();
    w2.html.style.display = 'none';
    _App.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});
}