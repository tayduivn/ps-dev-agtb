<script type="text/javascript">
function allowInsideView() {ldelim}
document.getElementById('insideViewFrame').src = '{$AJAX_URL}';
document.getElementById('insideViewConfirm').style.display = 'none';
document.getElementById('insideViewFrame').style.display = 'block';
document.getElementById('insideViewDiv').style.height='430px';
YAHOO.util.Connect.asyncRequest('GET', 'index.php?module=Connectors&action=CallConnectorFunc&source_id=ext_rest_insideview&source_func=allowInsideView', {ldelim}{rdelim}, null);
{rdelim}
    YAHOO.util.Event.onDOMReady(function(){ldelim}
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
    {rdelim});
</script>
<div id='insideViewDiv' style='width:100%'>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="formHeader h3Row">
        <tbody>
            <tr>
                <td nowrap="" style="padding: 0px;">
                    <h3 style="margin: -6px;">
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
                    <img height="1" width="1" src="themes/default/images/blank.gif?s=10c8793605d02302b4c4992681454492&amp;c=1" alt="">
                </td>
            </tr>
        </tbody>
    </table>
  <div id='subpanel_insideview' style='width:100%' {if !$showInsideView}align="center"{/if}>
      <div id='insideViewConfirm' class="detail view" style="padding: 15px; width: 775px; text-align: left; position: relative;{if $showInsideView}display:none;{/if}">
      
      <a href="#" onclick="hideSubPanel('insideview');document.getElementById('hide_link_insideview').style.display='none';document.getElementById('show_link_insideview').style.display='';return false;"><img src="{$close}" border="0" style='position: absolute; top: -8px; right: -9px;'></a>
      
      
      <div style="font-size: 14px;">
      	<a href='http://www.insideview.com/' target='_blank' style='text-decoration: none; font-size: 14px;'><strong style='color: #d71e00;'>InsideView</strong></a> <strong>now comes preinstalled in SugarCRM.</strong>
      </div>
      
   
      
      
	<div style="float: left; padding-bottom: 10px; font-size: 13px; padding-right: 20px; padding-top: 10px;">
      

       
           Get relevant company information, contacts, news, and social media insights all within your CRM.<br> The InsideView connector is a <strong>FREE</strong> service that automatically displays in your leads, accounts,<br> contacts and opportunities.

       
      </div>
      
      
         <div style="float: left; padding-bottom: 10px; width: 190px;"><img style="margin-right: 10px; border-radius: 6px 6px 6px 6px; -moz-border-radius: 6px 6px 6px 6px; -webkit-border-radius: 6px 6px 6px 6px;" src="{$video}" align="left"/><a href='http://www.insideview.com/' target='_blank' style='text-decoration: none; position: relative; top: 15px;'>InsideView in<br>30 seconds.</a></div>
      
      
               <hr style="width: 770px;">
     By clicking 'Get Started' you agree to InsideView's  <a href='http://www.insideview.com/cat-terms-use.html' target='_blank' style='text-decoration: none;'>terms of use</a> and <a href='http://www.insideview.com/cat-privacy.html' target='_blank' style='text-decoration: none;'>privacy policy</a>.

      <button onclick="allowInsideView(); return false;" class='button primary' style='float: right; border: 1px solid #821200; background-color: #eeeeee; background-image: none; text-shadow: 1px 1px #FFFFFF; color: #222; margin-bottom: 5px; background-image: -moz-linear-gradient(center top , #F9F9F9 0%, #F2F2F2 50%, #F1F1F1 50%, #DDDDDD 100%);'>Get Started!</button>
      <div class="clear"></div>
      
      </div>
      <iframe id='insideViewFrame' src='{$URL}' scrolling="no" style='border:0px; width:100%;height:400px;overflow:hidden;{if !$showInsideView}display:none;{else}display:block;{/if}'></iframe>
   </div>
</div>