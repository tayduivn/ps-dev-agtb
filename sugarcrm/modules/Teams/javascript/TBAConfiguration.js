/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

$(document).ready(function() {
    var getDisabledModules = function() {
            var moduleList = [];
            $.each($('input[data-group=tba_em]:not(:checked)'), function(index, item) {
                moduleList.push($(item).val());
            });
            return moduleList;
        },
        saveConfiguration = function(isTBAEnabled, disabledModulesList) {
            ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_SAVING'));

            var queryString = SUGAR.util.paramsToUrl({
                    module: 'Teams',
                    action: 'savetbaconfiguration',
                    enabled: isTBAEnabled,
                    disabled_modules: disabledModulesList,
                    csrf_token: SUGAR.csrf.form_token
                }) + 'to_pdf=1';

            $.ajax({
                url: 'index.php',
                data: queryString,
                type: 'POST',
                dataType: 'json',
                timeout: 300000,
                success: function(response) {
                    ajaxStatus.flashStatus(SUGAR.language.get('app_strings', 'LBL_DONE_BUTTON_LABEL'));
                    if (response['status'] === false) {
                        ajaxStatus.showStatus(response.message);
                    }
                    app.router.goBack();
                },
                error: function() {
                    ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'ERR_GENERIC_SERVER_ERROR'));
                }
            });
        },
        initState = {
            isTBAEnabled: $('input#tba_set_enabled').attr('checked') === 'checked',
            disabledModules: getDisabledModules()
        };

    $('input[data-group=tba_em]').on('click', function(e) {
        var $td = $(this).closest('td');
        if ($td.hasClass('active')) {
            $td.removeClass('active');
        } else {
            $td.addClass('active');
        }
    });

    if ($('input#tba_set_enabled').attr('checked') === 'checked') {
        $('#tba_em_block').show();
    } else {
        $('#tba_em_block').hide();
    }

    $('input#tba_set_enabled').on('click', function() {
        if ($(this).attr('checked') === 'checked') {
            var disabledModules = getDisabledModules();
            _.each($('input[data-group=tba_em]'), function(item) {
                if (_.indexOf(disabledModules, $(item).val()) === -1) {
                    $(item).attr('checked', 'checked');
                }
            });
            $('#tba_em_block').show();
        } else {
            $('#tba_em_block').hide();
        }
    });

    $('input[name=save]').on('click', function() {
        var disabledModules = getDisabledModules(),
            isTBAEnabled = $('input#tba_set_enabled').attr('checked') === 'checked';

        if ((initState.isTBAEnabled && $('input#tba_set_enabled').attr('checked') !== 'checked') ||
            _.difference(disabledModules, initState.disabledModules).length > 0) {
            app.alert.show('submit_tba_confirmation', {
                level: 'confirmation',
                messages: SUGAR.language.get('Teams', 'LBL_TBA_CONFIGURATION_WARNING'),
                onConfirm: function() {
                    saveConfiguration(isTBAEnabled, disabledModules);
                }
            });
        } else {
            saveConfiguration(isTBAEnabled, disabledModules);
        }
    });
});
