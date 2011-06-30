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

// $Id: step3.tpl 25541 2007-01-11 21:57:54Z jmertic $

*}
{literal}
<style>
<!--
textarea { width: 20em }

#selected_indices
{
    padding-left:30px;
    padding-right:30px;
}
-->
</style>
{/literal}
<script type="text/javascript" src="{sugar_getjspath file='include/javascript/sugar_grp_yui_widgets.js'}"></script>
{overlib_includes}
{$MODULE_TITLE}
<form enctype="multipart/form-data" real_id="importstepdup" id="importstepdup" name="importstepdup" method="POST" action="index.php">

{foreach from=$smarty.request key=k item=v}
    {if $k neq 'current_step'}
    <input type="hidden" name="{$k}" value="{$v}">
    {/if}
{/foreach}

<input type="hidden" name="module" value="Import">
<input type="hidden" name="import_type" value="{$smarty.request.import_type}">
<input type="hidden" name="type" value="{$smarty.request.type}">
<input type="hidden" name="file_name" value="{$smarty.request.tmp_file}">
<input type="hidden" name="source_id" value="{$SOURCE_ID}">
<input type="hidden" name="to_pdf" value="1">
<input type="hidden" name="display_tabs_def">
<input type="hidden" id="enabled_dupes" name="enabled_dupes" value="">
<input type="hidden" id="disabled_dupes" name="disabled_dupes" value="">
<input type="hidden" id="current_step" name="current_step" value="{$CURRENT_STEP}">

    <br />
    <div style="padding-left:20px">
    <table border="0" cellpadding="30" id="importTable" style="width:60% !important;">
    <tr>
        <td scope="row" align="left" colspan="2" style="text-align: left;">{$MOD.LBL_VERIFY_DUPS}&nbsp;{sugar_help text=$MOD.LBL_VERIFY_DUPLCATES_HELP}</td>
    </tr>
    <tr>
        <td  width="40%" colspan="2">
           <table id="DupeCheck" class="themeSettings edit view" style='margin-bottom:0px;' border="0" cellspacing="10" cellpadding="0"  width = '100%'>
                <tr>
                    <td align="right">
                        <div id="enabled_div" class="enabled_tab_workarea">
                        </div>
                    </td>
                    <td align="left">
                        <div id="disabled_div" class="disabled_tab_workarea">
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    </table>
    <br>
    <span scope="row"><strong>{$MOD.LBL_SAVE_MAPPING_AS}</strong></span>
            <span >
                <input type="text" name="save_map_as" id="save_map_as" value=""
                    style="width: 20em" maxlength="254">
                &nbsp;{sugar_help text=$MOD.LBL_SAVE_MAPPING_HELP}
            </span>
    </div>

{$JAVASCRIPT_CHOOSER}

<br />
<table width="100%" cellpadding="2" cellspacing="0" border="0">
<tr>
    <td align="left">
        <input title="{$MOD.LBL_BACK}" accessKey="" id="goback" class="button" type="submit" name="button" value="  {$MOD.LBL_BACK}  ">&nbsp;
        <input title="{$MOD.LBL_IMPORT_NOW}" accessKey="" id="importnow" class="button" type="button" name="button" value="  {$MOD.LBL_IMPORT_NOW}  ">
    </td>
</tr>
</table>

</form>
{literal}
<script type="text/javascript">
<!--
/**
 * Singleton to handle processing the import
 */
ProcessImport = new function()
{
    /*
     * number of file to process processed
     */
    this.fileCount         = 0;

    /*
     * total files to processs
     */
    this.fileTotal         = {/literal}{$FILECOUNT-1}{literal};

    /*
     * total records to process
     */
    this.recordCount       = {/literal}{$RECORDCOUNT}{literal};

    /*
     * maximum number of records per file
     */
    this.recordThreshold   = {/literal}{$RECORDTHRESHOLD}{literal};

    /*
     * submits the form
     */
    this.submit = function()
    {
        document.getElementById("importstepdup").tmp_file.value =
            document.getElementById("importstepdup").tmp_file_base.value + '-' + this.fileCount;
        YAHOO.util.Connect.setForm(document.getElementById("importstepdup"));
        YAHOO.util.Connect.asyncRequest('POST', 'index.php',
            {
                success: function(o) {
                    if (o.responseText.replace(/^\s+|\s+$/g, '') != '') {
                        this.failure(o);
                    }
                    else {
                        var locationStr = "index.php?module=Import"
                            + "&action=Last"
                            + "&current_step=" + document.getElementById("importstepdup").current_step.value
                            + "&type={/literal}{$TYPE}{literal}"
                            + "&import_module={/literal}{$IMPORT_MODULE}{literal}"
                            + "&has_header=" +  document.getElementById("importstepdup").has_header.value ;
                        if ( ProcessImport.fileCount >= ProcessImport.fileTotal ) {
                        	YAHOO.SUGAR.MessageBox.updateProgress(1,'{/literal}{$MOD.LBL_IMPORT_COMPLETE}{literal}');
                        	SUGAR.util.hrefURL(locationStr);
                        }
                        else {
                            document.getElementById("importstepdup").save_map_as.value = '';
                            ProcessImport.fileCount++;
                            ProcessImport.submit();
                        }
                    }
                },
                failure: function(o) {
                	YAHOO.SUGAR.MessageBox.minWidth = 500;
                	YAHOO.SUGAR.MessageBox.show({
                    	type:  "alert",
                    	title: '{/literal}{$MOD.LBL_IMPORT_ERROR}{literal}',
                    	msg:   o.responseText,
                        fn: function() { window.location.reload(true); }
                    });
                }
            }
        );
        var move = 0;
        if ( this.fileTotal > 0 ) {
            move = this.fileCount/this.fileTotal;
        }
        YAHOO.SUGAR.MessageBox.updateProgress( move,
            "{/literal}{$MOD.LBL_IMPORT_RECORDS}{literal} " + ((this.fileCount * this.recordThreshold) + 1)
                        + " {/literal}{$MOD.LBL_IMPORT_RECORDS_TO}{literal} " + Math.min(((this.fileCount+1) * this.recordThreshold),this.recordCount)
                        + " {/literal}{$MOD.LBL_IMPORT_RECORDS_OF}{literal} " + this.recordCount );
    }

    /*
     * begins the form submission process
     */
    this.begin = function()
    {
        datestarted = '{/literal}{$MOD.LBL_IMPORT_STARTED}{literal} ' +
                YAHOO.util.Date.format('{/literal}{$datetimeformat}{literal}');
        YAHOO.SUGAR.MessageBox.show({
            title: '{/literal}{$STEP4_TITLE}{literal}',
            msg: datestarted,
            width: 500,
            type: "progress",
            closable:false,
            animEl: 'importnow'
        });
        SUGAR.saveConfigureDupes();
        this.submit();
    }
}
{/literal}
//begin dragdrop code
	var enabled_dupes = {$enabled_dupes};
	var disabled_dupes = {$disabled_dupes};
	var lblEnabled = '{sugar_translate label="LBL_INDEX_USED"}';
	var lblDisabled = '{sugar_translate label="LBL_INDEX_NOT_USED"}';
	{literal}

	SUGAR.enabledDupesTable = new YAHOO.SUGAR.DragDropTable(
		"enabled_div",
		[{key:"label",  label: lblEnabled, width: 225, sortable: false},
		 {key:"module", label: lblEnabled, hidden:true}],
		new YAHOO.util.LocalDataSource(enabled_dupes, {
			responseSchema: {
			   resultsList : "dupeVal",
			   fields : [{key : "dupeVal"}, {key : "label"}]
			}
		}),
		{
			height: "300px",
			group: ["enabled_div", "disabled_div"]
		}
	);
	SUGAR.disabledDupesTable = new YAHOO.SUGAR.DragDropTable(
		"disabled_div",
		[{key:"label",  label: lblDisabled, width: 225, sortable: false},
		 {key:"module", label: lblDisabled, hidden:true}],
		new YAHOO.util.LocalDataSource(disabled_dupes, {
			responseSchema: {
			   resultsList : "dupeVal",
			   fields : [{key : "dupeVal"}, {key : "label"}]
			}
		}),
		{
			height: "300px",
		 	group: ["enabled_div", "disabled_div"]
		 }
	);
	SUGAR.enabledDupesTable.disableEmptyRows = true;
    SUGAR.disabledDupesTable.disableEmptyRows = true;
    SUGAR.enabledDupesTable.addRow({module: "", label: ""});
    SUGAR.disabledDupesTable.addRow({module: "", label: ""});
	SUGAR.enabledDupesTable.render();
	SUGAR.disabledDupesTable.render();


	SUGAR.saveConfigureDupes = function()
	{
		var enabledTable = SUGAR.enabledDupesTable;
		var dupeVal = [];
		for(var i=0; i < enabledTable.getRecordSet().getLength(); i++){
			var data = enabledTable.getRecord(i).getData();
			if (data.dupeVal && data.dupeVal != '')
			    dupeVal[i] = data.dupeVal;
		}
		    YAHOO.util.Dom.get('enabled_dupes').value = YAHOO.lang.JSON.stringify(dupeVal);

        var disabledTable = SUGAR.disabledDupesTable;
		var dupeVal = [];
		for(var i=0; i < disabledTable.getRecordSet().getLength(); i++){
			var data = disabledTable.getRecord(i).getData();
			if (data.dupeVal && data.dupeVal != '')
			    dupeVal[i] = data.dupeVal;
		}
			YAHOO.util.Dom.get('disabled_dupes').value = YAHOO.lang.JSON.stringify(dupeVal);
	}

-->
</script>
{/literal}
{$JAVASCRIPT}
{literal}
<script type="text/javascript" language="Javascript">
enableQS(false);
{/literal}{$getNameJs}{literal}
{/literal}{$getNumberJs}{literal}
{/literal}{$currencySymbolJs}{literal}
{/literal}{$confirmReassignJs}{literal}
</script>
{/literal}
