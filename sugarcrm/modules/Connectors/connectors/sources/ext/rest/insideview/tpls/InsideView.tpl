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
<script type="text/javascript">
function allowInsideView() {ldelim}

document.getElementById('insideViewFrame').src = "{$AJAX_URL}";
document.getElementById('insideViewConfirm').style.display = 'none';
document.getElementById('insideViewFrame').style.display = 'block';
document.getElementById('insideViewDiv').style.height='430px';
YAHOO.util.Connect.asyncRequest('GET', 'index.php?module=Connectors&action=CallConnectorFunc&source_id=ext_rest_insideview&source_func=allowInsideView', {ldelim}{rdelim}, null);
{rdelim}
SUGAR.util.doWhen("typeof(markSubPanelLoaded) != 'undefined' && document.getElementById('subpanel_insideview')", function() {ldelim}
	markSubPanelLoaded('insideview');
	var insideViewSubPanel = document.getElementById("subpanel_insideview" );
	insideViewSubPanel.cookie_name="insideview_v";
	if(div_cookies['insideview_v']){ldelim}
		if(div_cookies['insideview_v'] == 'none')
		{ldelim}
			hideSubPanel('insideview');
			document.getElementById('hide_link_insideview').style.display='none';
			document.getElementById('show_link_insideview').style.display='';
		{rdelim}
	{rdelim}
	toggleGettingStartedButton();
{rdelim});

function toggleGettingStartedButton(){ldelim}
	var acceptBox  = document.getElementById( "insideview_accept_box" );
	var gettingStartedButton  = document.getElementById( "insideview_accept_button" );

	if( acceptBox.checked ){ldelim}
		gettingStartedButton.disabled = '';
		gettingStartedButton.focus();
	{rdelim}
	else {ldelim}
		gettingStartedButton.disabled = "disabled";
	{rdelim}
{rdelim}
</script>
<div id='insideViewDiv' style='width:100%' class="doNotPrint">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="formHeader h3Row">
        <tbody>
            <tr>
                <td nowrap="" style="padding: 0px;">
                    <h3>
                        <span>
                            <a name="insideview"> </a>
                            <span id="show_link_insideview" style="display: none">
                                <a href="#" onclick="current_child_field = 'insideview';showSubPanel('insideview',null,null,'insideview');document.getElementById('show_link_insideview').style.display='none';document.getElementById('hide_link_insideview').style.display='';return false;"><img src="{$logo_collapsed}" border="0"></a>
                            </span>
                            <span id="hide_link_insideview" style="display: ">
                                <a href="#" onclick="hideSubPanel('insideview');document.getElementById('hide_link_insideview').style.display='none';document.getElementById('show_link_insideview').style.display='';return false;"><img src="{$logo_expanded}" border="0"></a>
                            </span>
                        </span>
                    </h3>
                </td>
                <td width="100%">
                    <img height="1" width="1" src="{sugar_getimagepath file='blank.gif'}" alt="">
                </td>
            </tr>
        </tbody>
    </table>
  <div id='subpanel_insideview' style='width:100%' {if !$showInsideView}align="center"{/if}>
      <div id='insideViewConfirm' class="detail view" style="padding: 20px; width: 700px; text-align: left; position: relative;{if $showInsideView}display:none;{/if}">
      
      <a href="#" onclick="hideSubPanel('insideview');document.getElementById('hide_link_insideview').style.display='none';document.getElementById('show_link_insideview').style.display='';return false;"><img src="{$close}" border="0" style='position: absolute; top: -8px; right: -9px;'></a>
      
      
      <div style="font-size: 14px;">
      	<a href='http://www.insideview.com/SUGARCRM/' target='_blank' style='text-decoration: none; font-size: 14px;'><strong style='color: #d71e00;'>{$connector_language.LBL_TAGLINK}</strong></a> <strong>{$connector_language.LBL_TAGLINE}</strong>
      </div>
      
   
      
      
	<div style="float: left; padding-bottom: 10px; font-size: 13px; padding-right: 20px; padding-top: 10px;">
      


        {$connector_language.iv_description0}<br>{$connector_language.iv_description1}<strong>{$connector_language.iv_description2}</strong>{$connector_language.iv_description3}<br/>{$connector_language.iv_description4}

       
      </div>
      
      
         <div style="float: right; padding-bottom: 10px; width: 190px;"><a href='http://www.insideview.com/SUGARCRM/' target='_blank' style='text-decoration: none;'><img style="margin-right: 10px; border-radius: 6px 6px 6px 6px; -moz-border-radius: 6px 6px 6px 6px; -webkit-border-radius: 6px 6px 6px 6px;" src="{$video}" align="left"/></a><a href='http://www.insideview.com/SUGARCRM/' target='_blank' style='text-decoration: none; position: relative; top: 15px;'>{$connector_language.LBL_VID0}<br>{$connector_language.LBL_VID1}</a></div>
      
               <hr style="width: 775px; border-color: #eee; background-color: #eee;">
     <form>
     <input type="checkbox" class="checkbox" name="insideview_accept_box" id="insideview_accept_box" onClick='toggleGettingStartedButton();'/>&nbsp;{$connector_language.LBL_TOS0}<a href='http://www.insideview.com/cat-terms-use.html' target='_blank' style='text-decoration: none;'>{$connector_language.LBL_TOS1}</a>{$connector_language.LBL_TOS2}<a href='http://www.insideview.com/cat-privacy.html' target='_blank' style='text-decoration: none;'>{$connector_language.LBL_TOS3}</a>.
         <button name="insideview_accept_button" id="insideview_accept_button" onclick="allowInsideView(); return false;" class='button primary' style='height: 25px; float: right; border: 1px solid #821200; background-color: #eeeeee; background-image: none; text-shadow: 1px 1px #FFFFFF; color: #222; margin-bottom: 0px; background-image: -moz-linear-gradient(center top , #F9F9F9 0%, #F2F2F2 50%, #F1F1F1 50%, #DDDDDD 100%); background-image: -webkit-gradient( linear,left top,left bottom,color-stop(0, #f9f9f9),color-stop(.5, #F2F2F2),color-stop(.5, #F1F1F1),color-stop(1, #DDDDDD)); filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#f9f9f9", endColorstr="#DDDDDD");'>{$connector_language.LBL_GET_STARTED}</button>
          
     </form>
      <div class="clear"></div>
      
      </div>
      <iframe id='insideViewFrame' src='{$URL}' scrolling="no" style='border:0px; width:100%;height:400px;overflow:hidden;{if !$showInsideView}display:none;{else}display:block;{/if}'></iframe>
   </div>
</div>