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
<input type="hidden" name="{$k}" value="{$v}">
{/foreach}
    
<input type="hidden" name="module" value="Import">
<input type="hidden" name="import_type" value="{$TYPE}">
<input type="hidden" name="source" value="{$SOURCE}">
<input type="hidden" name="source_id" value="{$SOURCE_ID}">
<input type="hidden" name="to_pdf" value="1">
<input type="hidden" name="current_step" value="{$CURRENT_STEP}">
<input type="hidden" name="display_tabs_def">

<br />
<div style="padding-left:20px">
<table border="0" cellpadding="30" id="importTable" class="detail view" style="width:60% !important;">
<tr>
    <td scope="row" align="left" colspan="2" style="text-align: left;">{$MOD.LBL_VERIFY_DUPS}&nbsp;{sugar_help text=$MOD.LBL_VERIFY_DUPLCATES_HELP}</td>
</tr>
<tr>
    <td  width="40%" colspan="2">
            {$TAB_CHOOSER}
    </td>
</tr>
<tr>
    <td align="left" colspan="4" style="background: transparent;">
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <span scope="row"><strong>{$MOD.LBL_SAVE_MAPPING_AS}</strong></span>
        <span >
            <input type="text" name="save_map_as" id="save_map_as" value=""
                style="width: 20em" maxlength="254">
            &nbsp;{sugar_help text=$MOD.LBL_SAVE_MAPPING_HELP}
        </span>
    </td>
</tr>
</table>
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
                            + "&type={/literal}{$TYPE}{literal}"
                            + "&import_module={/literal}{$IMPORT_MODULE}{literal}";
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
        this.submit();
    }
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
