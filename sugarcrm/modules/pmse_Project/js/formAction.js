var w, hp;

//var getFormData = function(address)
//{
//    var responseData;
//    $.ajax({
//        url: address,
//        async: false
//    }).done(function(ajaxResponse) {
//        responseData = ajaxResponse;
//    });
//    return responseData;
//}

var confirmAdhocReassign = function()
{
    $.ajax({
        url: './?module=ProcessMaker&action=routeCase&to_pdf=1&current_assigned_user_id=' + $("#cas_current_user_id").val() + '&reassigned_to_user_id=' + $("#cas_user_id").val() + "&Type=Adhoc",
        async: false,
        method: 'POST',
        data: $('#showCaseForm').serialize()
    }).done(function(ajaxResponse) {
        window.location.href = "./index.php"
    });
}

var confirmReassign = function()
{
    $.ajax({
        url: './?module=ProcessMaker&action=reassignRecord&to_pdf=1&current_assigned_user_id=' + $("#cas_current_user_id").val() + '&reassigned_to_user_id=' + $("#cas_user_id").val() + '&Type=Reassign',
        async: false,
        method: 'POST',
        data: $('#showCaseForm').serialize()
    }).done(function(ajaxResponse) {
        w.close();
        //window.location.href = "./index.php"
    });
}

var reassignForm = function(casId, casIndex, flowId, pmseInboxId, taskName, values)
{
    //showModalWindow("?module=ProcessMaker&action=reassignForm&to_pdf=1&cas_id=" + casId + "&cas_index=" + casIndex + "&team_id=" + teamId, '# ' + casId + ': Reassignment');
    showModalWindow(casId, casIndex, 'reassign', flowId, pmseInboxId, taskName, values);
}
var adhocForm = function(casId, casIndex, flowId, pmseInboxId, taskName, values){
    showModalWindow(casId, casIndex, 'adhoc', flowId, pmseInboxId, taskName, values);
}
var claim_case = function(cas_id,cas_index){
    var value = {};
    value.cas_id = cas_id;
    value.cas_index = cas_index;
    var pmseInboxUrl = App.api.buildURL('pmse_Inbox/engine_claim','',{},{});
    App.api.call('update', pmseInboxUrl, value,{
        success: function (){
//                  App.route.refresh();
            window.location.reload();
        }
    });
}
//var adhocForm = function(casId, casIndex)
//{
//
//    showModalWindow("?module=ProcessMaker&action=adhocForm&to_pdf=1&cas_id=" + casId + "&cas_index=" + casIndex, '# ' + casId + ': Adhoc Reassignment');
// //   showModal(casId, casIndex);
//
//}

//var showModalWindow = function (address, title)
//{
//    hp = new HtmlPanel({
//        source: getFormData(address),
//        scroll: false
//    });
//    w = new Window({
//        width: 800,
//        height: 340,
//        modal: true,
//        title: title
//    });
//
//    w.addPanel(hp);
//    w.show();
//}
var showModalWindow = function (casId, casIndex, wtype, flowId, pmseInboxId,taskName,values) {
    var f,
        w,
        combo_users,
        items,
        proxy,
        proxyUsers,
        textArea,
        url,
        wtitle,
        wWidth,
        wHeight,
        casIdField,
        casIndexField,
        combo_type,
        casFlowId,
        casInboxId,
        task_Name,
        user_Name,
        module_Name,
        bean_Id,
        full_Name,
        valAux;
//        restClient = new RestClient ();
//    restClient.setRestfulBehavior(SUGAR_REST);
//    if (!SUGAR_REST) {
//        restClient.setBackupAjaxUrl(SUGAR_AJAX_URL);
//    }
    module_Name = new HiddenField({
        name: 'moduleName',
        value: values.moduleName
    });
    bean_Id = new HiddenField({
        name: 'beanId',
        value: values.beanId
    });
    if(values.name)
    {
        valAux=values.name;
    }else{
        valAux=values.full_name;
    }
    full_Name = new HiddenField({
        name: 'full_name',
        value: valAux
    });
    task_Name = new HiddenField({
        name: 'taskName',
        value: taskName
    });

    casIdField = new HiddenField({
        name: 'cas_id',
        value: casId
    });

    casIndexField = new HiddenField({
        name: 'cas_index',
        value: casIndex
    });
    casFlowId = new HiddenField({
        name: 'flow_id',
        value: flowId
    });

    casInboxId = new HiddenField({
        name: 'inbox_id',
        value: pmseInboxId
    });
    combo_users = new ComboboxField({
        jtype: 'combobox',
        label: translate('LBL_PMSE_FORM_LABEL_USER', 'pmse_Inbox'),
        name: 'adhoc_user',
        submit: true,
        //change: hiddenUpdateFn,
        proxy:null,
//        proxy: new RestProxy({
//            url: SUGAR_URL + '/rest/v10/AdhocForm/users',
//            restClient: restClient,
//            uid : '',
//            callback: null
//        }),
        required: true,
        helpTooltip: {
            message: translate('LBL_PMSE_FORM_TOOLTIP_SELECT_USER', 'pmse_Inbox')
        }

    });
    combo_type = new ComboboxField({
        name: 'adhoc_type',
        label: translate('LBL_PMSE_FORM_LABEL_TYPE', 'pmse_Inbox'),
        options: [
            {text: 'Round Trip', value: 'ROUND_TRIP'},
            {text: 'One Way', value: 'ONE_WAY'}
        ],
        initialValue: 'ROUND_TRIP',
        required: true
    });

    textArea = new TextareaField({
        name: 'adhoc_comment',
        label: translate('LBL_PMSE_FORM_LABEL_NOTE', 'pmse_Inbox'),
        fieldWidth: '300px',
        fieldHeight: '100px'
    });
    user_Name = new HiddenField({
        name: 'user_name',
        value: ''
    });

    if (wtype === 'reassign') {
        url = 'pmse_Inbox/AdhocReassign';
        wtitle = translate('LBL_PMSE_TITLE_AD_HOC', 'pmse_Inbox');
        wWidth = 550;
        wHeight = 300;
        items = [
            casIdField,
            casIndexField,
            casFlowId,
            casInboxId,
            combo_users,
            combo_type,
            textArea,
            task_Name,
            user_Name,
            module_Name,
            bean_Id,
            full_Name
        ];
        combo_users.setName('adhoc_user');
        textArea.setName('adhoc_comment')
    } else {
        url = 'pmse_Inbox/ReassignForm';
        wtitle = translate('LBL_PMSE_TITLE_REASSIGN', 'pmse_Inbox');
        wWidth = 500;
        wHeight = 250;
        items = [
            casIdField,
            casIndexField,
            casFlowId,
            casInboxId,
            combo_users,
            textArea,
            task_Name,
            user_Name,
            module_Name,
            bean_Id,
            full_Name
        ];
        combo_users.setName('reassign_user');
        textArea.setName('reassign_comment');
    }
    flowId = (flowId) ? flowId : urlCase.id; // esta modificacion es por la version de compatibilidad
    proxyUsers =  new SugarProxy({
        url: url + '/users/' + flowId,
        //restClient: this.canvas.project.restClient,
        uid: null,
        callback: null
    });
    combo_users.setProxy(proxyUsers);
    proxy = new SugarProxy({
        url: url,
//        restClient: restClient,
        uid : '',
        callback: null
    });
    f = new Form({
        //proxy: proxy,
        items: items,
        closeContainerOnSubmit: true,
        buttons: [
            {jtype: 'normal', caption: translate('LBL_PMSE_BUTTON_SAVE') , handler: function () {
                var cbDate=$("#reassign_user option:selected").html();
                items[8].setValue($("#adhoc_user option:selected").html());
                f.submit();
                if (wtype == 'reassign')
                {
                    App.router.redirect('Home');
                }
                else if(wtype == 'adhoc')
                {
                    if ($('#assigned_user_name').length)
                    {
                        $("#assigned_user_name").val(cbDate);
                    }
                    else
                    {
                        App.router.refresh();
                    }
                }
            }},
            { jtype: 'normal', caption: translate('LBL_PMSE_BUTTON_CLOSE', 'pmse_Inbox'), handler: function () {
                w.close();
            }}
        ],
        labelWidth: 300,
        callback : {
            'loaded': function (data) {
                casIdField.setValue(casId);
                casIndexField.setValue(casIndex);

                var users, aUsers = [{'text':translate('LBL_PMSE_FORM_OPTION_SELECT'), 'value':''}];
                combo_users.proxy.getData(null, {
                    success: function(users) {
                        if (users) {
                            aUsers = aUsers.concat(users.result);
                            combo_users.setOptions(aUsers);
                        }
                    }
                });
                f.setProxy(proxy);
            }
        }
    });
    w = new Window({
        width: wWidth,
        height: wHeight,
        modal: true,
        title: wtitle
    });
    w.addPanel(f);
    w.show();
}


function onSubmit(e) {
    var result2 = true,
        i,
        ele,
        msg = '<div>',
        mp = new MessagePanel({
            title: 'Warning',
            wtype: 'Warning'
        }),
        restClient;
    if (RECLAIMCASE){
        //TODO RECLAIM CASE
        restClient = new RestClient ();
        restClient.setRestfulBehavior(SUGAR_REST);
        if (!SUGAR_REST) {
            restClient.setBackupAjaxUrl(SUGAR_AJAX_URL);
        }
        restClient.getCall({
            url: SUGAR_URL + '/rest/v10/CrmData/validateReclaimCase',
            id: '',
            data: {cas_id: SBPM_CASE_ID, cas_index: SBPM_CASE_INDEX},
            success: function (xhr, response) {
                result = response.result;
                if (!result) {
                    mp.setTitle('Error');
                    mp.setMessageType('Error');
                    mp.setButtons([
                        {
                            jtype: 'normal',
                            caption: translate('LBL_PMSE_BUTTON_OK'),
                            handler: function () {
                                location.href = SUGAR_URL;
                            }
                        }
                    ]);
                    if (response.message) {
                        mp.setMessage(response.message);
                    } else {
                        mp.setMessage(translate('LBL_PMSE_LABEL_PLEASELOGINAGAIN'));
                    }
                    mp.show();
                    result2 = false;

                }
            },
            failure: function (xhr, response) {
                //console.log(response);
                mp.setTitle('Error');
                mp.setMessageType('Error');
                mp.setMessage(translate('LBL_PMSE_LABEL_ERROR_GENERIC'));
                mp.show();
                result2 = false;
            }
        });

    } else {
        if (PMVAL) {
            for (i = 0; i < PMVAL.length; i += 1) {
                ele = document.getElementById(PMVAL[i]);
                if (ele && ele.value) {
                    if (ele.value.trim && ele.value.trim() == '') {
                        $(ele).addClass('required');
                        msg += PMVAL[i] + '<br>';
                        result2 = false;
                    }
                }
            }
        }
        if (!result2) {
            mp.setMessage('The following fields are required and must be properly filled:' + msg);
            mp.show();
        }
    }

    return result2;
}

$(function () {
    $('#showCaseForm').attr("novalidate", "novalidate").on('submit', onSubmit);

});