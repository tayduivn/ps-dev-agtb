<script type="text/javascript">
function allowInsideView() {ldelim}
document.getElementById('insideViewFrame').src = '{$URL}';
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
                <td nowrap="">
                    <h3>
                        <span>
                            <a name="insideview"> </a>
                            <span id="show_link_insideview" style="display: none">
                                <a href="#" class="utilsLink" onclick="current_child_field = 'insideview';showSubPanel('insideview',null,null,'insideview');document.getElementById('show_link_insideview').style.display='none';document.getElementById('hide_link_insideview').style.display='';return false;"><img src="themes/default/images/advanced_search.gif?s=10c8793605d02302b4c4992681454492&amp;c=1" width="8" height="8" alt="Show" border="0 align=" absmiddle""=""></a>
                            </span>
                            <span id="hide_link_insideview" style="display: ">
                                <a href="#" class="utilsLink" onclick="hideSubPanel('insideview');document.getElementById('hide_link_insideview').style.display='none';document.getElementById('show_link_insideview').style.display='';return false;"><img src="themes/default/images/basic_search.gif?s=10c8793605d02302b4c4992681454492&amp;c=1" width="8" height="8" alt="Hide" border="0" align="absmiddle"></a>
                            </span>&nbsp;InsideView
                        </span>
                    </h3>
                </td>
                <td width="100%">
                    <img height="1" width="1" src="themes/default/images/blank.gif?s=10c8793605d02302b4c4992681454492&amp;c=1" alt="">
                </td>
            </tr>
        </tbody>
    </table>
  <div id='subpanel_insideview' style='width:100%'>
      <div id='insideViewConfirm' class="detail view" style="padding: 10px;{if $showInsideView}display:none;{/if}">

      <a href='http://www.insideview.com/' target='_blank' style='text-decoration: none;'><strong style='color: #d71e00;'>InsideView</strong></a> now comes preinstalled in <strong>SugarCRM</strong>, giving you access to relevant company information, contacts, news, and social media insights all within your CRM. The InsideView connector is a <strong>FREE</strong> service that automatically displays in your leads, accounts, contacts and opportunities.
      <br/><br/>
     By clicking 'Get Started' you agree to InsideView's  <a href='http://www.insideview.com/cat-terms-use.html' target='_blank' style='text-decoration: none;'>terms of use</a> and <a href='http://www.insideview.com/cat-privacy.html' target='_blank' style='text-decoration: none;'>privacy policy</a>.

      <hr>
      <a href='http://www.insideview.com/' target='_blank'><img src="{$logo}" border="0" style="margin-bottom: 5px;"></a> <button onclick="allowInsideView(); return false;" class='button primary' style='float: right; border: 1px solid #d71e00; background-color: #eeeeee; background-image: none; text-shadow: none; color: #222; margin-top: 5px;'>Get Started</button>
      </div>
      <iframe id='insideViewFrame' src='{$URL}' scrolling="no" style='border:0px; width:100%;height:400px;overflow:hidden;{if !$showInsideView}display:none;{else}display:block;{/if}'></iframe>
   </div>
</div>