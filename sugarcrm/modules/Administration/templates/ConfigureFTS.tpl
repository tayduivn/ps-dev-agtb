{* //FILE SUGARCRM flav!=sales ONLY*}
{*
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */
*}
<script type="text/javascript" src="{sugar_getjspath file='cache/include/javascript/sugar_grp_yui_widgets.js'}"></script>
<form name="ConfigureFTS" method="POST"  method="POST" action="index.php" onsubmit="SUGAR.FTS.saveModuleFilterSettings();">
	<input type="hidden" name="module" value="Administration">
	<input type="hidden" name="action" value="UpdateFTS">
    <input type="hidden" name="sched" value="" id="sched">
    <input type="hidden" name="disabled_modules" value="" id="disabled_modules">
	<input type="hidden" name="return_module" value="{$RETURN_MODULE}">
	<input type="hidden" name="return_action" value="{$RETURN_ACTION}">

	{$title}<br>

    {if $fts_scheduled}
        {$ftsScheduleEnabledText}
        <br><br>
    {/if}

	<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="button primary"
		   type="submit" name="saveButton" value="{$APP.LBL_SAVE_BUTTON_LABEL}" onclick="return check_form('ConfigureFTS')" />
    <input title="{$MOD.LBL_SAVE_SCHED_BUTTON}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="button primary sched_button" id='sched_button' name='sched_button'
        		   type="submit" name="saveButton" value="{$MOD.LBL_SAVE_SCHED_BUTTON}" onclick="return SUGAR.FTS.confirmSchedule();" {$scheduleDisableButton} />
	<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button"
		   onclick="this.form.action.value='index'; this.form.module.value='Administration';" type="submit" name="CancelButton"
		   value="{$APP.LBL_CANCEL_BUTTON_LABEL}"/>
    <br><br>
    <table width="50%" border="0" cellspacing="1" cellpadding="0" class="edit view">
    <tbody>
        <tr><th align="left" scope="row" colspan="4"><h4>{$MOD.LBL_FTS_SETTINGS_TITLE}</h4></th></tr>

        <tr>
            <td width="15%" scope="row" valign="middle">{$MOD.LBL_FTS_TYPE}&nbsp;</td>
            <td width="85%" align="left" valign="middle"><select name="fts_type" id="fts_type">{$fts_type}</select></td>
        </tr>
        <tr>
            <td width="15%" scope="row" valign="middle">{$MOD.LBL_FTS_HOST}&nbsp;</td>
            <td width="85%" align="left" valign="middle"><input type="text" name="fts_host" id="fts_host" value="{$fts_host}"></td>
        </tr>
        <tr>
            <td width="15%" scope="row" valign="middle">{$MOD.LBL_FTS_PORT}&nbsp;</td>
            <td width="85%" align="left" valign="middle"><input type="text" name="fts_port" id="fts_port" maxlength="5" size="5" value="{$fts_port}"></td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>
                <input type="button" title="{$MOD.LBL_FTS_TEST}" accessKey="{$MOD.LBL_FTS_TEST}" class="button" onclick="SUGAR.FTS.testSettings();" value="{$MOD.LBL_FTS_TEST}"/>
            </td>
        </tr>
        <tr>
            <td colspan='2'>
                <a class='tabFormAdvLink' href='javascript:SUGAR.FTS.toggleAdvancedOptions();'>
                    <span id='advanced_search_img_span'>
                        {sugar_getimage alt=$alt_show_hide name="advanced_search" ext=".gif" other_attributes='border="0" id="advanced_search_img" '}
                    </span>
                    <span id='basic_search_img_span' style="display:none;">
                        {sugar_getimage alt=$alt_show_hide name="basic_search" ext=".gif" other_attributes='border="0" id="basic_search_img" '}
                    </span>
                    {$MOD.LBL_ADVANCED}
                </a>
            </td>
        </tr>
        <tr>
            <td colspan='2'>
                <div id='moduleConfig' class='add_table' style='margin-bottom:5px;display:none;'>
                    <table class="GlobalSearchSettings edit view" style='margin-bottom:0px;' border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width='1%'>
                                <div id="enabled_div"></div>
                            </td>
                            <td>
                                <div id="disabled_div"></div>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </tbody>
    </table>

    <input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="button primary"
    		   type="submit" name="saveButton" value="{$APP.LBL_SAVE_BUTTON_LABEL}" onclick="return check_form('ConfigureFTS')" />
    <input title="{$MOD.LBL_SAVE_SCHED_BUTTON}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="button primary sched_button" id='sched_button' name='sched_button'
        		   type="submit" name="saveButton" value="{$MOD.LBL_SAVE_SCHED_BUTTON}" onclick="return SUGAR.FTS.confirmSchedule();" {$scheduleDisableButton} />
    <input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button"
           onclick="this.form.action.value='index'; this.form.module.value='Administration';" type="submit" name="CancelButton"
           value="{$APP.LBL_CANCEL_BUTTON_LABEL}"/>
</form>

<script type="text/javascript">
    var enabled_modules = {$enabled_modules};
    var disabled_modules = {$disabled_modules};
    var lblEnabled = '{sugar_translate label="LBL_ACTIVE_MODULES" module="Administration"}';
    var lblDisabled = '{sugar_translate label="LBL_DISABLED_MODULES" module="Administration"}';
    {literal}
    SUGAR.FTS = {
        confirmSchedule : function()
        {
            if( confirm(SUGAR.language.get('Administration','LBL_SAVE_SCHED_WARNING')) )
            {
                $("#sched").val('1');
                return true;
            }
            else
            {
                $("#sched").val('0');
                return false;
            }
        },
        testSettings : function()
        {
            var host = document.getElementById('fts_host').value;
            var port = document.getElementById('fts_port').value;
            var typeEl = document.getElementById('fts_type');
            var type = typeEl.options[typeEl.selectedIndex].value;
            if(host == "" || port == "" || type == "")
            {
                check_form('ConfigureFTS');
                return
            }

            SUGAR.FTS.rsPanel = new YAHOO.widget.SimpleDialog("FTSPanel", {
                                    modal: true,
                                    width: "200px",
                                    visible: true,
                                    constraintoviewport: true,
                                    loadingText: SUGAR.language.get("app_strings", "LBL_EMAIL_LOADING"),
                                    close: true
                                });

            var panel = SUGAR.FTS.rsPanel;
            panel.setHeader(SUGAR.language.get('Administration','LBL_STATUS')) ;
            panel.setBody(SUGAR.language.get("app_strings", "LBL_EMAIL_LOADING"));
            panel.render(document.body);
            panel.show();
            panel.center();

            var callback = {
                success: function(o) {
                    var r = YAHOO.lang.JSON.parse(o.responseText);
                    panel.setBody(r.status);
                    if(r.valid)
                    {
                        $('.sched_button').removeAttr('disabled');
                    }
                    else
                    {
                        $('.sched_button').attr('disabled', 'disabled');
                    }

                },
                failure: function(o) {}
            }
                encodeURI()
            var sUrl = 'index.php?to_pdf=1&module=Administration&action=checkFTSConnection&type='
                + encodeURIComponent(type) + '&host=' + encodeURIComponent(host) + '&port=' + encodeURIComponent(port);

            var transaction = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback, null);

        },
        globalSearchEnabledTable : new YAHOO.SUGAR.DragDropTable(
                "enabled_div",
                [{key:"label",  label: lblEnabled, width: 200, sortable: false},
                 {key:"module", label: lblEnabled, hidden:true}],
                new YAHOO.util.LocalDataSource(enabled_modules, {
                    responseSchema: {fields : [{key : "module"}, {key : "label"}]}
                }),
                {height: "200px"}
        ),
        globalSearchDisabledTable : new YAHOO.SUGAR.DragDropTable(
                "disabled_div",
                [{key:"label",  label: lblDisabled, width: 200, sortable: false},
                 {key:"module", label: lblDisabled, hidden:true}],
                new YAHOO.util.LocalDataSource(disabled_modules, {
                    responseSchema: {fields : [{key : "module"}, {key : "label"}]}
                }),
                {height: "200px"}
        ),
        toggleAdvancedOptions: function()
        {
            if (document.getElementById('moduleConfig').style.display == 'none')
            {
                SUGAR.FTS.globalSearchEnabledTable.render();
                SUGAR.FTS.globalSearchDisabledTable.render();
                document.getElementById('moduleConfig').style.display = '';
                document.getElementById('basic_search_img_span').style.display = '';
                document.getElementById('advanced_search_img_span').style.display = 'none';
            }
            else
            {
                document.getElementById('moduleConfig').style.display = 'none';
                document.getElementById('basic_search_img_span').style.display = 'none';
                document.getElementById('advanced_search_img_span').style.display = '';
            }
        },
        saveModuleFilterSettings : function()
        {
            var enabledTable = SUGAR.FTS.globalSearchDisabledTable;
            var modules = "";
            for(var i=0; i < enabledTable.getRecordSet().getLength(); i++){
                var data = enabledTable.getRecord(i).getData();
                if (data.module && data.module != '')
                    modules += "," + data.module;
            }
            modules = modules == "" ? modules : modules.substr(1);
            document.getElementById('disabled_modules').value = modules;
        },
    };

    //Setup enable table
    SUGAR.FTS.globalSearchEnabledTable.disableEmptyRows = true;
    SUGAR.FTS.globalSearchDisabledTable.disableEmptyRows = true;
    SUGAR.FTS.globalSearchEnabledTable.addRow({module: "", label: ""});
    SUGAR.FTS.globalSearchDisabledTable.addRow({module: "", label: ""});
    SUGAR.FTS.globalSearchEnabledTable.render();
    SUGAR.FTS.globalSearchDisabledTable.render();

    $('#fts_type').change(function(e)
    {
        if($(this).val() == '')
        {
            $('.sched_button').attr('disabled', 'disabled');
        }
    });
    {/literal}
addForm('ConfigureFTS');
addToValidateMoreThan('ConfigureFTS', 'fts_port', 'int', true, '{$MOD.LBL_FTS_PORT}', 1);
addToValidate('ConfigureFTS', 'fts_host', 'varchar', 'true', '{$MOD.LBL_FTS_URL}');
</script>