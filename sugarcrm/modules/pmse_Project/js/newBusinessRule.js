/*global RestClient, validateForm, $, SUGAR_URL, MessagePanel, SUGAR_REST, SUGAR_AJAX_URL, translate*/
function initBusinessRuleForm(e) {
    $('#businessRule-form').attr("novalidate", "novalidate").on('submit', function (e) {
        var name, module, message, brName,
            restClient = new RestClient(),
            result = true,
            result2 = true,
            valid = validateForm('businessRule-form'),
            mp = new MessagePanel({
                title: 'Warning',
                wtype: 'Warning'
            });

        $('#rst_name').val($.trim($('#rst_name').val()));
        name = $('#rst_name').val();
        brName = name;
        module = $('#rst_module').val();

//        if(!(name && module)) {
//            e.preventDefault();
//            alert(translate('LBL_PMSE_LABEL_ERROR_NEW_PROCESS_INCOMPLETE_FORM', '"' + translate('LBL_PMSE_LABEL_TARGETMODULE') + '" ' + translate('LBL_PMSE_LABEL_AND') + ' "' + translate('LBL_PMSE_LABEL_BUSINESSRULENAME') + '"'));
        if (valid.valid) {
            //validate if the name exists in database
            restClient.setRestfulBehavior(SUGAR_REST);
            if (!SUGAR_REST) {
                restClient.setBackupAjaxUrl(SUGAR_AJAX_URL);
            }
            restClient.getCall({
                url: SUGAR_URL + '/rest/v10/CrmData/validateBusinessRuleName',
                id: brName,
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
                    mp.setTitle('Error');
                    mp.setMessageType('Error');
                    mp.setMessage(translate('LBL_PMSE_LABEL_ERROR_GENERIC'));
                    mp.show();
                    result2 = false;
                    //console.log(response);
                    //TODO Process HERE error at loading project
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
    });

}

$(initBusinessRuleForm);