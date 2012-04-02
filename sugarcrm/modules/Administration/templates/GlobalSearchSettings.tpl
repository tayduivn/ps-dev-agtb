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
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td colspan="100">
        <h2> {$moduleTitle}</h2>
    </td>
</tr>
<tr>
    <td colspan="100">{$MOD.LBL_GLOBAL_SEARCH_SETTINGS_TITLE}</td>
</tr>
<tr>
    <td>
        <br>
    </td>
</tr>
<tr>
<td colspan="100">

<script type="text/javascript" src="{sugar_getjspath file='cache/include/javascript/sugar_grp_yui_widgets.js'}"></script>
<link rel="stylesheet" type="text/css" href="{sugar_getjspath file='modules/Connectors/tpls/tabs.css'}"/>
<form name="GlobalSearchSettings" method="POST">
	<input type="hidden" name="module" value="Administration">
	<input type="hidden" name="action" value="updateWirelessEnabledModules">
	<input type="hidden" name="enabled_modules" value="">

	<table border="0" cellspacing="1" cellpadding="1">
		<tr>
			<td>
			<input title="{$APP.LBL_SAVE_BUTTON_LABEL}" accessKey="{$APP.LBL_SAVE_BUTTON_TITLE}" class="button primary" onclick="SUGAR.saveGlobalSearchSettings();" type="button" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">
                <input title="{$MOD.LBL_SAVE_SCHED_BUTTON}" class="button primary schedFullSystemIndex" onclick="SUGAR.FTS.schedFullSystemIndex();" style="display: none;text-decoration: none;" id='schedFullSystemIndexBtn' type="button" name="button" value="{$MOD.LBL_SAVE_SCHED_BUTTON}">
            <input title="{$APP.LBL_CANCEL_BUTTON_LABEL}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="document.GlobalSearchSettings.action.value='';" type="submit" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
			</td>
		</tr>
	</table>

	<div class='add_table' style='margin-bottom:5px'>
		<table id="GlobalSearchSettings" class="GlobalSearchSettings edit view" style='margin-bottom:0px;' border="0" cellspacing="0" cellpadding="0">
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
{* //BEGIN SUGARCRM flav=pro ONLY *}
    <br>
    {$MOD.LBL_FTS_PAGE_DESC}
    <br><br>
    <table width="50%" border="0" cellspacing="1" cellpadding="0" class="edit view">
        <tbody>
            <tr><th align="left" scope="row" colspan="4"><h4>{$MOD.LBL_FTS_SETTINGS_TITLE}</h4></th></tr>

            <tr>
                <td width="25%" scope="row" valign="middle">{$MOD.LBL_FTS_TYPE}:&nbsp;{sugar_help text=$MOD.LBL_FTS_TYPE_HELP}</td>
                <td width="25%" align="left" valign="middle"><select name="fts_type" id="fts_type">{$fts_type}</select></td>
                <td width="60%">&nbsp;</td>
            </tr>
            <tr class="shouldToggle">
                <td width="25%" scope="row" valign="middle">{$MOD.LBL_FTS_HOST}:&nbsp;{sugar_help text=$MOD.LBL_FTS_HOST_HELP}</td>
                <td width="25%" align="left" valign="middle"><input type="text" name="fts_host" id="fts_host" value="{$fts_host}" {if $disableEdit} disabled {/if}></td>
                <td width="60%" valign="bottom">&nbsp;<a href="javascript:void(0);" onclick="SUGAR.FTS.testSettings();" style="text-decoration: none;">{$MOD.LBL_FTS_TEST}</a></td>
            </tr>
            <tr class="shouldToggle">
                <td width="25%" scope="row" valign="middle">{$MOD.LBL_FTS_PORT}:&nbsp;{sugar_help text=$MOD.LBL_FTS_PORT_HELP}</td>
                <td width="25%" align="left" valign="middle"><input type="text" name="fts_port" id="fts_port" maxlength="5" size="5" value="{$fts_port}" {if $disableEdit} disabled {/if}></td>
                <td width="60%"></td>
            </tr>
            <tr class="shouldToggle">
                <td colspan="2">&nbsp;</td>
            </tr>
        </tbody>
    </table>
{* //END SUGARCRM flav=pro ONLY *}
	<table border="0" cellspacing="1" cellpadding="1">
		<tr>
			<td>
				<input title="{$APP.LBL_SAVE_BUTTON_LABEL}" class="button primary" onclick="SUGAR.saveGlobalSearchSettings();" type="button" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">
                <input title="{$MOD.LBL_SAVE_SCHED_BUTTON}" class="button primary schedFullSystemIndex" onclick="SUGAR.FTS.schedFullSystemIndex();" style="display: none;text-decoration: none;" id='schedFullSystemIndex' type="button" name="button" value="{$MOD.LBL_SAVE_SCHED_BUTTON}">
                <input title="{$APP.LBL_CANCEL_BUTTON_LABEL}" class="button" onclick="document.GlobalSearchSettings.action.value='';" type="submit" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}">
			</td>
		</tr>
	</table>
</form>

<script type="text/javascript">
(function(){ldelim}
    var Connect = YAHOO.util.Connect;
	Connect.url = 'index.php';
    Connect.method = 'POST';
    Connect.timeout = 300000;
	var get = YAHOO.util.Dom.get;

	var enabled_modules = {$enabled_modules};
	var disabled_modules = {$disabled_modules};
	var lblEnabled = '{sugar_translate label="LBL_ACTIVE_MODULES"}';
	var lblDisabled = '{sugar_translate label="LBL_DISABLED_MODULES"}';
	{literal}
	SUGAR.globalSearchEnabledTable = new YAHOO.SUGAR.DragDropTable(
		"enabled_div",
		[{key:"label",  label: lblEnabled, width: 200, sortable: false},
		 {key:"module", label: lblEnabled, hidden:true}],
		new YAHOO.util.LocalDataSource(enabled_modules, {
			responseSchema: {fields : [{key : "module"}, {key : "label"}]}
		}),
		{height: "300px"}
	);
	SUGAR.globalSearchDisabledTable = new YAHOO.SUGAR.DragDropTable(
		"disabled_div",
		[{key:"label",  label: lblDisabled, width: 200, sortable: false},
		 {key:"module", label: lblDisabled, hidden:true}],
		new YAHOO.util.LocalDataSource(disabled_modules, {
			responseSchema: {fields : [{key : "module"}, {key : "label"}]}
		}),
		{height: "300px"}
	);

	SUGAR.globalSearchEnabledTable.disableEmptyRows = true;
	SUGAR.globalSearchDisabledTable.disableEmptyRows = true;
	SUGAR.globalSearchEnabledTable.addRow({module: "", label: ""});
	SUGAR.globalSearchDisabledTable.addRow({module: "", label: ""});
	SUGAR.globalSearchEnabledTable.render();
	SUGAR.globalSearchDisabledTable.render();

	SUGAR.saveGlobalSearchSettings = function()
	{
        {* //BEGIN SUGARCRM flav=pro ONLY *}
        var host = document.getElementById('fts_host').value;
        var port = document.getElementById('fts_port').value;
        var typeEl = document.getElementById('fts_type');
        var type = typeEl.options[typeEl.selectedIndex].value;

        if( type != "")
        {
            if(!check_form('GlobalSearchSettings'))
                return;
        }
        {* //END SUGARCRM flav=pro ONLY *}
		var enabledTable = SUGAR.globalSearchEnabledTable;
		var modules = "";
		for(var i=0; i < enabledTable.getRecordSet().getLength(); i++){
			var data = enabledTable.getRecord(i).getData();
			if (data.module && data.module != '')
			    modules += "," + data.module;
		}
		modules = modules == "" ? modules : modules.substr(1);

		ajaxStatus.showStatus(SUGAR.language.get('Administration', 'LBL_SAVING'));
		Connect.asyncRequest(
            Connect.method,
            Connect.url,
            {success: SUGAR.saveCallBack},
			SUGAR.util.paramsToUrl({
				module: "Administration",
				action: "saveglobalsearchsettings",
                {* //BEGIN SUGARCRM flav=pro ONLY *}
                host: host,
                port: port,
                type: type,
                {* //END SUGARCRM flav=pro ONLY *}
				enabled_modules: modules
			}) + "to_pdf=1"
        );

		return true;
	}

	SUGAR.saveCallBack = function(o)
	{
	   ajaxStatus.flashStatus(SUGAR.language.get('app_strings', 'LBL_DONE'));
	   if (o.responseText == "true")
	   {
	       window.location.assign('index.php?module=Administration&action=index');
	   } else {
	       YAHOO.SUGAR.MessageBox.show({msg:o.responseText});
	   }
	}
})();
{/literal}
</script>
<script type="text/javascript">
    {* //BEGIN SUGARCRM flav=pro ONLY *}
    var shouldHide = '{$scheduleDisableButton}';
    var justRequestedAScheduledIndex = '{$justRequestedAScheduledIndex}';
    {literal}
    $(document).ready(function()
    {
        if (shouldHide)
        {
            $('.shouldToggle').toggle(false);
        }
        if(justRequestedAScheduledIndex)
            alert(SUGAR.language.get('Administration','LBL_FTS_CONN_SUCCESS_SHORT'));
    });

    SUGAR.FTS = {
        schedFullSystemIndex : function()
        {
            if( confirm(SUGAR.language.get('Administration','LBL_SAVE_SCHED_WARNING')) )
            {
                var host = document.getElementById('fts_host').value;
                var port = document.getElementById('fts_port').value;
                var typeEl = document.getElementById('fts_type');
                var type = typeEl.options[typeEl.selectedIndex].value;
                if(host == "" || port == "" || type == "")
                {
                    check_form('GlobalSearchSettings');
                    return
                }
                var sUrl = 'index.php?to_pdf=1&module=Administration&action=ScheduleFTSIndex&sched=true&type='
                                + encodeURIComponent(type) + '&host=' + encodeURIComponent(host) + '&port=' + encodeURIComponent(port);

                var callback = {
                success: function(o) {
                    var r = YAHOO.lang.JSON.parse(o.responseText);
                    if(r.success)
                    {
                        alert(SUGAR.language.get('Administration','LBL_FTS_CONN_SUCCESS_SHORT'));
                    }
                    else
                    {
                        alert(SUGAR.language.get('Administration','LBL_FTS_CONN_FAILURE_SHORT'));
                    }

                },
                failure: function(o) {
                    alert(SUGAR.language.get('Administration','LBL_FTS_CONN_FAILURE_SHORT'));
                }
            }
                var transaction = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback, null);
            }

        },
        testSettings : function()
        {
            var host = document.getElementById('fts_host').value;
            var port = document.getElementById('fts_port').value;
            var typeEl = document.getElementById('fts_type');
            var type = typeEl.options[typeEl.selectedIndex].value;
            if(type != "")
            {
                if(!check_form('GlobalSearchSettings'))
                    return
            }

            SUGAR.FTS.rsPanel = new YAHOO.widget.SimpleDialog("FTSPanel", {
                                    modal: true,
                                    width: "260px",
                                    visible: true,
                                    constraintoviewport: true,
                                    loadingText: SUGAR.language.get("app_strings", "LBL_EMAIL_LOADING"),
                                    close: true
                                });

            var panel = SUGAR.FTS.rsPanel;
            panel.setHeader(SUGAR.language.get('Administration','LBL_CONNECT_STATUS')) ;
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
                        $('.schedFullSystemIndex').show();
                    }
                    else
                    {
                        $('.schedFullSystemIndex').hide();
                    }

                },
                failure: function(o) {}
            }

            var sUrl = 'index.php?to_pdf=1&module=Administration&action=checkFTSConnection&type='
                + encodeURIComponent(type) + '&host=' + encodeURIComponent(host) + '&port=' + encodeURIComponent(port);

            var transaction = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback, null);

        }
    };

    $('#fts_type').change(function(e)
    {
        $('.shouldToggle').toggle();

        if($(this).val() == '')
        {
            $('.sched_button').attr('disabled', 'disabled');
        }
    });
    {/literal}
addForm('GlobalSearchSettings');
addToValidateMoreThan('GlobalSearchSettings', 'fts_port', 'int', true, '{$MOD.LBL_FTS_PORT}', 1);
addToValidate('GlobalSearchSettings', 'fts_host', 'varchar', 'true', '{$MOD.LBL_FTS_URL}');
    {* //END SUGARCRM flav=pro ONLY *}
</script>