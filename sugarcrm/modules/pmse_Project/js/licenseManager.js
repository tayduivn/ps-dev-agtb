var bpmActiveCodeModal,
    bpmLicenseDetailModal,
    bpmLicenseModal,
    bpmSimpleDialog,
    bpmProgressBar,
    showDetail;
$(document).ready(function () {
    $('#BpmActivationCodeModal').css('display', 'block');
    $('#BpmLicenseModal').css('display', 'block');
    $('#BpmLicenseDetailModal').css('display', 'block');

    var handleOk = function () {
        this.hide();
    };
    var handleLogOut = function () {
        location.href = SUGAR_URL + '/#logout/?clear=1';
    }

    showDetail = function (id) {
        bpmProgressBar.show();
        $.ajax({
            url: "./index.php?module=pmse_Config&action=getLicenseData&to_pdf=1",
            async: false,
            data: {id: id},
            dataType: 'json',
            type: 'POST'
        }).done(function (ajaxResponse) {
//                        console.log(ajaxResponse);
                bpmProgressBar.hide();
                if (ajaxResponse.success) {
                    $('#td_admins').html(ajaxResponse.lic_max_admins);
                    $('#td_inboxes').html(ajaxResponse.lic_max_users);
                    $('#td_cases').html(ajaxResponse.lic_max_cases);
                    $('#td_br').html((ajaxResponse.lic_enabled_br) ? 'True' : 'False');
                    $('#td_lic_exp').html(ajaxResponse.lic_product_expiration_date);
                    $('#td_sup_exp').html(ajaxResponse.lic_support_expiration_date);
                    bpmLicenseDetailModal.show();
                } else {
//              alert(ajaxResponse.message);
                    bpmSimpleDialog.setHeader(translate('LBL_PMSE_DASHLET_TITLE_ERROR'));
                    bpmSimpleDialog.setBody(ajaxResponse.message);
                    bpmSimpleDialog.show();
                }
            });
    };

    // Initialize the temporary Panel to display while waiting for external content to load
    bpmProgressBar = new YAHOO.widget.Panel("wait",
        {width: "240px",
            fixedcenter: true,
            close: false,
            draggable: false,
            zindex: 4,
            modal: true,
            visible: false
        }
    );
    bpmProgressBar.setHeader(translate('LBL_PMSE_LABEL_LOADING'));
    bpmProgressBar.setBody('<img src="http://l.yimg.com/a/i/us/per/gr/gp/rel_interstitial_loading.gif" />');
    bpmProgressBar.render(document.body);

    // Instantiate the Dialog
    bpmSimpleDialog = new YAHOO.widget.SimpleDialog("simpledialog1",
        { width: "300px",
            fixedcenter: true,
            visible: false,
            draggable: false,
            close: false,
//                    text: "Do you want to continue?",
//                    icon: YAHOO.widget.SimpleDialog.ICON_HELP,
            constraintoviewport: true,
            modal: true,
            buttons: [ { text: translate('LBL_PMSE_BUTTON_OK'), handler: handleOk, isDefault: true }]
        }
    );
    // Render the Dialog
    bpmSimpleDialog.render(document.body);

    // Instantiate the Dialog
    bpmActiveCodeModal = new YAHOO.widget.Dialog("BpmActivationCodeModal",
        { width : "400px",
            fixedcenter : true,
            visible : false,
            constraintoviewport : true,
            close: true,
            modal: true,
            draggable: true
        }
    );
    bpmActiveCodeModal.render();

    // Instantiate the Dialog
    bpmLicenseModal = new YAHOO.widget.Dialog("BpmLicenseModal",
        { width : "640px",
            fixedcenter : true,
            visible : false,
            constraintoviewport : true,
            close: true,
            modal: true,
            draggable: true
        }
    );
    bpmLicenseModal.render();

    // Instantiate the Dialog
    bpmLicenseDetailModal = new YAHOO.widget.Dialog("BpmLicenseDetailModal",
        { width : "400px",
            fixedcenter : true,
            visible : false,
            constraintoviewport : true,
            close: true,
            modal: true,
            draggable: true,
            buttons: [ { text: translate('LBL_PMSE_BUTTON_CLOSE'), handler: handleOk, isDefault: true }]
        }
    );
    bpmLicenseDetailModal.render();

    $('#btnActivationCode').click(function (e) {
        e.preventDefault();
        bpmActiveCodeModal.show();
    });

    $('#btnContinueValidation').on('click', function (e) {
        e.preventDefault();
        $('#BpmLicenseModal').find('h2').html(translate('LBL_PMSE_TITLE_LICENSE_VALIDATION'));
        $('#BpmLicenseModal').find('p:first').html(translate('LBL_PMSE_TITLE_LICENSE'));
        $('#BpmLicenseModal').find('textarea').val('').removeAttr('readonly');
        $('#BpmLicenseModal').find('p:last').html(translate('LBL_PMSE_LABEL_LICENCE_INTRODUCE'));
        $('#btnContinueValidation').css('display', 'none');
        $('#btnLicenseValidation').css('display', 'block');
    });

    $('#btnLicenseValidation').on('click', function (e) {
        e.preventDefault();
        var license = $('#txtRequestCode').val();
        bpmLicenseModal.hide();
        bpmProgressBar.show();
        $.ajax({
            url: "./index.php?module=pmse_Config&action=processLicense&to_pdf=1",
            async: false,
            data: {license: license},
            dataType: 'json',
            type: 'POST'
        }).done(function (ajaxResponse) {
//                console.log(ajaxResponse);
                bpmProgressBar.hide();
                if (ajaxResponse.success) {
                    bpmSimpleDialog.setHeader(translate('LBL_PMSE_DASHLET_LICENSE_INSTALL_SUCCESS'));
                    bpmSimpleDialog.setBody(ajaxResponse.message);
                    YAHOO.util.Event.removeListener(bpmSimpleDialog.getButtons()[0], "click", handleOk);
                    YAHOO.util.Event.addListener(bpmSimpleDialog.getButtons()[0], "click", handleLogOut);
                    bpmSimpleDialog.show();
                } else {
//              alert(ajaxResponse.message);
                    bpmSimpleDialog.setHeader(translate('LBL_PMSE_DASHLET_TITLE_ERROR'));
                    bpmSimpleDialog.setBody(ajaxResponse.message);
                    bpmSimpleDialog.show();
                }
            });
    });

    $('#btnNewLicense').on('click', function (e) {
        e.preventDefault();
        $('#BpmLicenseModal').find('h2').html(translate('LBL_PMSE_TITLE_LICENSE_VALIDATION'));
        $('#BpmLicenseModal').find('p:first').html(translate('LBL_PMSE_TITLE_LICENSE'));
        $('#BpmLicenseModal').find('textarea').val('').removeAttr('readonly');
        $('#BpmLicenseModal').find('p:last').html(translate('LBL_PMSE_LABEL_LICENCE_INTRODUCE'));
        $('#btnContinueValidation').css('display', 'none');
        $('#btnLicenseValidation').css('display', 'block');
        bpmLicenseModal.show();
    });

    $('#btnSendActivationCode').click(function (e) {
        e.preventDefault();
        bpmActiveCodeModal.hide();
        bpmProgressBar.show();
        $.ajax({
            url: "./index.php?module=pmse_Config&action=processActivationCode&to_pdf=1",
            async: false,
            data: {activationCode: $('#txtActivationCode').val()},
            dataType: 'json',
            type: 'POST'
        }).done(function (ajaxResponse) {
//                console.log(ajaxResponse);
                bpmProgressBar.hide();
                if (ajaxResponse.success) {
                    bpmSimpleDialog.setHeader(translate('LBL_PMSE_DASHLET_LICENSE_INSTALL_SUCCESS'));
                    bpmSimpleDialog.setBody(ajaxResponse.message);
                    YAHOO.util.Event.removeListener(bpmSimpleDialog.getButtons()[0], "click", handleOk);
                    YAHOO.util.Event.addListener(bpmSimpleDialog.getButtons()[0], "click", handleLogOut);
                    bpmSimpleDialog.show();
                } else {
                    if (typeof ajaxResponse.request !== 'undefined'){
//                  console.log(ajaxResponse.request);
                        $('#BpmLicenseModal').find('h2').html(translate('LBL_PMSE_TITLE_LICENSE_MANUAL_REGISTRATION'));
                        $('#BpmLicenseModal').find('p:first').html(translate('LBL_PMSE_TITLE_LICENSE_REQUEST'));
                        $('#BpmLicenseModal').find('textarea').val(ajaxResponse.request).attr('readonly', 'true');
                        $('#BpmLicenseModal').find('p:last').html(translate('LBL_PMSE_LABEL_LICENCE_SEND_REQUEST'));
                        $('#btnContinueValidation').css('display', 'block');
                        $('#btnLicenseValidation').css('display', 'none');
                        bpmLicenseModal.show();
                    } else {
                        bpmSimpleDialog.setHeader(translate('LBL_PMSE_DASHLET_TITLE_ERROR'));
                        bpmSimpleDialog.setBody(ajaxResponse.message);
                        bpmSimpleDialog.show();
                    }
                }
            });
        $('#txtActivationCode').val('');
    });
});


//$(document).ready(function() {
//
//    function getLicenseData() {
//        var responseData;
//        $.ajax({
//            url: "./index.php?module=ProcessMaker&action=getLicenseData&to_pdf=1",
//            async: false,
//            dataType: 'json'
//        }).done(function(ajaxResponse) {
//            responseData = {
//                data: ajaxResponse.data,
//                title: ajaxResponse.title
//            }
//        });
//        return responseData;
//    }
//
//    function showLicenseWindow() {
//        var response = getLicenseData();
//        $("#licenseManagerContainer").html(response.data);
//        $("#sendActivationCode").click(function(e) {
//            viewSendingDataForm();
//            $.ajax({
//                url: "./index.php?module=ProcessMaker&action=processActivationCode&to_pdf=1",
//                async: false,
//                data: {activationCode:$("#activationCode").value},
//                dataType: 'json',
//                type: 'POST'
//            }).done(function(ajaxResponse) {
//                closeSendingDataForm();
//                if (ajaxResponse.success) {
//                    reloadLicenseData(ajaxResponse.data);
//                } else {
//                    reloadLicenseData(ajaxResponse.data);
//                }
//            });
//        });
//    }
//
//    function showValidateWindow() {
//        $("#sendActivationCode").click(function(e) {
//            viewSendingDataForm();
//            $.ajax({
//                url: "./index.php?module=ProcessMaker&action=processActivationCode&to_pdf=1",
//                async: false,
//                data: {activationCode:$("#activationCode").value},
//                dataType: 'json',
//                type: 'POST'
//            }).done(function(ajaxResponse) {
//                closeSendingDataForm();
//                if (ajaxResponse.success) {
//                    reloadLicenseData(ajaxResponse.data);
//                } else {
//                    reloadLicenseData(ajaxResponse.data);
//                }
//            });
//        });
//    }
//
//
//    function viewSendingDataForm() {
//        $("#licenseManagerContainer").html("<img style=\"margin-left: auto; margin-right: auto; width: 4em;\" src=\"modules/ProcessMaker/img/ajax-loader.gif\"/>");
//    }
//
//    function closeSendingDataForm() {
//        $("#licenseManagerContainer").html("");
//    }
//
//    function reloadLicenseData(htmlData) {
//        $("#licenseManagerContainer").html(htmlData);
//
//        $("#okLicenseData").click(function(e) {
//            document.location.href = './index.php';
//        });
//
//        $("#validateLicense").click(function(e) {
//            $.ajax({
//                url: "./index.php?module=ProcessMaker&action=validateLicenseData&to_pdf=1",
//                async: false,
//                data: {activationCode:$("#licenseData").value},
//                dataType: 'json'
//            }).done(function(ajaxResponse) {
//                if (ajaxResponse.success) {
//                    viewLicenseDetail(ajaxResponse.data);
//                } else {
//                    viewLicenseRequestForm(ajaxResponse.data);
//                }
//            });
//        });
//    }
//
//
//
//    showLicenseWindow();
//});