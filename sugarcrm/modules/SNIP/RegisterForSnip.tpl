{*
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.Sugarcrm.com/EULA.  By installing or using this file, You have
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

// $Id: PasswordManager.tpl 37436 2009-06-01 01:14:03Z Faissah $
*}

	<h2>SNIP</h2>

	<table width="100%" cellspacing="0" cellpadding="0" border="0" class="edit view">
		<tr>
		<td>
			{if $SNIP_STATUS=='notpurchased'}
				SNIP is an automatic email archiving system. It allows you to see emails that were sent to or from your contacts inside SugarCRM, without you having to manually import and link the emails.<br><br>

				In order to use SNIP, you must <a href="{$SNIP_PURCHASEURL}">purchase a license</a> for your SugarCRM instance.
			{else}
			
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td scope="row">
						<slot>SNIP Status</slot>

					</td>

					<td>
						<form name='ToggleSnipStatus' method="POST" action="index.php?module=SNIP&action=RegisterForSnip">
						<input type='hidden' id='save_config' name='save_config' value='0'/>
						{if $SNIP_STATUS == 'purchased_enabled'}
							<div style='font-size:15px;display:inline'><span style='color:green;font-weight:bold'>Enabled (Service Online)</span></div>
							<input class='button' type='submit' value='Disable' onclick='document.getElementById("save_config").value="disable"'>
							{if $FORM_ERROR}<br><span style='color:red'>{$FORM_ERROR}</span><br>{/if}
							<br>This instance has a SNIP license, and the service is enabled and running.
						{elseif $SNIP_STATUS == 'purchased_disabled'}
							<div style='font-size:15px;display:inline'><span style='color:#777777;font-weight:bold'>Disabled</span></div>
							<input class='button' type='submit' value='Enable' onclick='document.getElementById("save_config").value="enable"'>	
							{if $FORM_ERROR}<br><span style='color:red'>{$FORM_ERROR}</span><br>{/if}
							<br>This instance has a SNIP license, but you have disabled SNIP.
							
						{elseif $SNIP_STATUS == 'purchased_down'}
							<div style='font-size:15px;display:inline'><span style='color:red;font-weight:bold'>Service Down</span></div>
							<br>This instance has a SNIP license and you have enabled SNIP, but the the service is currently unavailable.
						{/if}
						</form>
						<br>
					</td>
				</tr>
				<tr>
					<td width="15%" scope="row">
						<slot>{$MOD.LBL_SNIP_SUGAR_URL}</slot>
					</td>
					<td width="85%">
						<slot>{$SUGAR_URL}</slot>
					</td>
				</tr>
				<tr>
					<td scope="row">
						<slot>{$MOD.LBL_SNIP_CALLBACK_URL}</slot>
					</td>
					<td>
						<slot><div style='float:left;width:325px' id='snipurl'>{$SNIP_URL}</div>
						<form id='snipurlui' style='float:left;margin-top:-3px;margin-left:12px' method="POST" action="index.php?module=SNIP&action=RegisterForSnip">
							
						</form></slot>
					</td>
				</tr>
			</table>
			{/if}
		</td>
		</tr>
	</table>

	
	




{literal}
<script type='text/javascript'>
    (function(){
    	snipurlui=document.getElementById('snipurlui');
        snipurlspan = document.getElementById('snipurl');
        ourl = snipurlspan.innerHTML;
        var islabelui = document.createElement('input');
        islabelui.setAttribute('type','button');
        islabelui.setAttribute('value','Edit');

        var istextui = document.createElement('span');
            var istextui_cancel = document.createElement('input');
            istextui_cancel.setAttribute('type','button');
            istextui_cancel.setAttribute('value','Cancel');

            var istextui_save = document.createElement('input');
            istextui_save.setAttribute('type','button');
            istextui_save.setAttribute('value','Save');
        
        istextui.appendChild(istextui_cancel);
        istextui.appendChild(istextui_save);

        function setUI(ui){
            while (snipurlui.childNodes.length>0)
                snipurlui.removeChild(snipurlui.firstChild); 
            snipurlui.appendChild(ui);
        }

        islabelui.onclick=function(){
            snipurl = snipurlspan.firstChild.innerHTML;
            snipurlspan.innerHTML="<input type='text' style='width:325px;margin-top:-1px' name='change_snipurl' value='"+snipurl+"'>";
            setUI(istextui);
        }

        istextui_cancel.onclick = function(){
            snipurlspan.innerHTML="<div style='float:left;background-color:#F1F1F1;width:320px;height:17px;padding-top:3px;padding-left:5px'>"+ourl+"</div>";
            setUI(islabelui);
        }

        istextui_save.onclick = function(){
            document.getElementById('snipurlui').submit();
        }

        istextui_cancel.onclick();
    })()
</script>
{/literal}