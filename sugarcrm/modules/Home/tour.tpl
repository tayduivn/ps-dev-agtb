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

<div id="tourStart">
    <div class="modal-header">
    <a class="close" data-dismiss="modal">×</a>
    <h3>{$APP.LBL_TOUR_WELCOME}</h3>
    </div>
    
	<div class="modal-body" {if $view_calendar_url}style="overflow: auto;"{/if}>
        <div style="float: left;">
            <div style="float: left; width: 300px;">
				{$APP.LBL_TOUR_FEATURES_670}
				<p>{$APP.LBL_TOUR_VISIT} <a href="javascript:void window.open('http://support.sugarcrm.com/02_Documentation/01_Sugar_Editions/{$APP.documentation.$sugarFlavor}')">{$APP.LNK_TOUR_DOCUMENTATION}</a>.</p>

                {if $view_calendar_url}
                <div style="border-top: 1px solid #F5F5F5;padding-top: 3px;" >
                    <p>{$view_calendar_url}</p>
                </div>
                {/if}

            </div>
            <div class="well" style="float: left; width: 220px; margin-left: 20px;"><img src="themes/default/images/pt-screen0-thumb.png" width="220" id="thumbnail_0" class="thumb"></div>
        </div>
        </div>
        <div class="clear"></div>
    <div class="modal-footer">
    <a href="#" class="btn btn-primary">{$APP.LBL_TOUR_TAKE_TOUR}</a>
    <a href="#" class="btn btn-invisible">{$APP.LBL_TOUR_SKIP}</a>
    </div>
</div>
<div id="tourEnd" style="display: none;">
    <div class="modal-header">
    <a class="close" data-dismiss="modal">×</a>
    <h3>{$APP.LBL_TOUR_DONE}</h3>
    </div>
    
	<div class="modal-body">
		<div style="float: left;"> 
			<div style="float: left; width: 360px; margin-right: 50px;">
			<p>
			{$APP.LBL_TOUR_REFERENCE_1} <a href="javascript:void window.open('http://support.sugarcrm.com/02_Documentation/01_Sugar_Editions/{$APP.documentation.$sugarFlavor}')">{$APP.LNK_TOUR_DOCUMENTATION}</a> {$APP.LBL_TOUR_REFERENCE_2}
<br>
				<i class="icon-arrow-right icon-lg" style="float: right; position: relative; right: -33px; top: -30px;"></i>
			</p>
			</div>
			<div style="float: left">
				<img src="themes/default/images/pt-profile-link.png" width="152" height="221">
			</div>
		</div>
	</div>
    <div class="clear"></div>
    
    <div class="modal-footer">
    <a href="#" class="btn btn-primary">{$APP.LBL_TOUR_BTN_DONE}</a>
    <a href="#" class="btn">{$APP.LBL_TOUR_BACK}</a>

    </div>
</div>

<script type="text/javascript">
    {literal}
    $('#thumbnail_0').live("click", function(){
        $("#tour_screenshot .modal-header h3").html("{/literal}{$APP.LBL_TOUR_WELCOME}{literal}");
        $("#tour_screenshot .modal-body").html("<img src='themes/default/images/pt-screen0-full.png' width='600'>");
        $("#tour_screenshot").modal("show");
    });
    {/literal}
</script>