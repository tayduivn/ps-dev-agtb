/*global validateForm, RestClient,SUGAR_REST,SUGAR_URL, MessagePanel, $, SUGAR_AJAX_URL, translate*/
function onSubmit(e) {
    var restClient,
        proxy,
        result2 = true,
        projectName = $.trim(this.prj_name.value),
        valid = validateForm('newProcessForm'),
        mp = new MessagePanel({
            title: 'Warning',
            wtype: 'Warning'
        }),
        result;
    this.prj_name.value = projectName;

//    if (!(this.pro_module.value && this.prj_name.value)) {
//        e.preventDefault();
//        alert(translate('LBL_PMSE_LABEL_ERROR_NEW_PROCESS_INCOMPLETE_FORM', '"' + translate('LBL_PMSE_LABEL_TARGETMODULE') + '" ' + translate('LBL_PMSE_LABEL_AND') + ' "' + translate('LBL_PMSE_LABEL_PROCESSNAME') + '"'));
//    }
    if (valid.valid) {
        restClient = new RestClient();
        restClient.setRestfulBehavior(SUGAR_REST);
        if (!SUGAR_REST) {
            restClient.setBackupAjaxUrl(SUGAR_AJAX_URL);
        }

        restClient.getCall({
            url: SUGAR_URL + '/rest/v10/CrmData/validateProjectName',
            id: projectName,
            data: {},
            success: function (xhr, response) {
                result = response.result;
                if (!result) {
                    mp.setTitle('Error');
                    mp.setMessageType('Error');
                    if (response.message) {
                        mp.setMessage(response.message);
                    } else {
                        mp.setMessage(translate('LBL_PMSE_LABEL_ERROR_GENERIC'));
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
        e.preventDefault();
        mp.setTitle('Warning');
        mp.setMessageType('Warning');
        mp.setMessage(valid.message);
        mp.show();
    }
    return result2;

}

$(function () {
    $('#newProcessForm').attr("novalidate", "novalidate").on('submit', onSubmit);
});