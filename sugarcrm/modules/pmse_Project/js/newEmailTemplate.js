/*global $, SUGAR_REST, SUGAR_URL, SUGAR_AJAX_URL, translate, validateForm,
MessagePanel, RestClient, location
*/
function onSuccess() {
    location.href = SUGAR_URL;
}

function onFailure() {
    var mp = new MessagePanel({
        title: 'Error',
        wtype: 'Error',
        message: translate('MSG_EMAIL_TEMPLATE_SAVE_FAILURE')
    });
    mp.show();
}

function emailTemplateIsValid(f) {
    validateForm('email_template_form');

    var valid = true, message = '';
    f.name.value = $.trim(f.name.value);
    if (!f.name.value) {
        valid = valid && false;
        message += ", \"" + translate('LBL_PMSE_LABEL_EMAILTEMPLATENAME')
            + "\"";
    }
    if (!f.pro_module.value) {
        valid = valid && false;
        message += ", \"" + translate('LBL_PMSE_LABEL_TARGETMODULE') + "\"";
    }

    if (message) {
        message = message.substr(1);
    }

    return {
        valid: !!valid,
        message: valid ? '' : translate('LBL_PMSE_LABEL_ERROR_FIELDS_TO_FILL')
            + message
    };
}

function onSubmit(e) {
    var valid = validateForm('email_template_form'),
        restClient = new RestClient(),
        result = true,
        result2 = true,
        message,
        emailTemaplateName = $.trim(this.name.value),
        mp = new MessagePanel({
            title: 'Warning',
            wtype: 'Warning'
        });

    if (valid.valid) {
        restClient.setRestfulBehavior(SUGAR_REST);
        if (!SUGAR_REST) {
            restClient.setBackupAjaxUrl(SUGAR_AJAX_URL);
        }
        restClient.getCall({
            url: SUGAR_URL + '/rest/v10/CrmData/validateEmailTemplateName',
            id: emailTemaplateName,
            data: {},
            success: function (xhr, response) {
                result = response.result;
                if (!result) {
                    mp.setTitle('Error');
                    mp.setMessageType('Error');
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

function init() {
    $('#email_template_form').attr("novalidate", "novalidate")
        .on('submit', onSubmit);
}

$(init);