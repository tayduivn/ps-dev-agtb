{*
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
*}
{{* Add the preForm code if it is defined (used for vcards) *}}
{{if $preForm}}
	{{$preForm}}
{{/if}}

<script>
testing_module = "{$smarty.request.module}";
{literal}
$(document).ready(function(){
    if (testing_module == "Contacts") {
    	var selector = "#content ul.clickMenu li span";
        $("#content ul.subnav.multi").parent().append("<span class='ab'></span>"); //Only shows drop down trigger when js is enabled - Adds empty span tag after ul.subnav
    } else {
    	var selector = "#content ul.clickMenu li";
	    $("#content ul.subnav.multi").parent().append("<span></span>"); //Only shows drop down trigger when js is enabled - Adds empty span tag after ul.subnav
    }
    
    
	$(selector).click(function(event) { //When trigger is clicked...
	$(document).find("ul.subnav").hide();//hide all menus
	$(this).parent().find("ul.subnav").show(); //Drop down the subnav on click

  $('body').one('click',function() {
    // Hide the menus
     $(this).parent().find("ul.subnav").hide();
  });

  event.stopPropagation();

		//Following events are applied to the trigger (Hover events for the trigger)
		}).hover(function() { 
			$(this).addClass("subhover"); //On hover over, add class "subhover"
		}, function(){	//On Hover Out
			$(this).removeClass("subhover"); //On hover out, remove class "subhover"
	});


    //Tool Tips
   	$(function(){
		$(".clickMenu span.ab").tipTip({maxWidth: "auto", edgeOffset: 10, content: "More Actions", defaultPosition: "top"});
		
	});
});
 

{/literal}
</script>


<table cellpadding="0" cellspacing="0" border="0" width="100%" id="">
<tr>
<td class="buttons" align="left" NOWRAP>
<div class="actionsContainer">
<form action="index.php" method="post" name="DetailView" id="form">
<input type="hidden" name="module" value="{$module}">
<input type="hidden" name="record" value="{$fields.id.value}">
<input type="hidden" name="return_action">
<input type="hidden" name="return_module">
<input type="hidden" name="return_id">
<input type="hidden" name="module_tab">
<input type="hidden" name="isDuplicate" value="false">
<input type="hidden" name="offset" value="{$offset}">
<input type="hidden" name="action" value="EditView">
{{if isset($form.hidden)}}
{{foreach from=$form.hidden item=field}}
{{$field}}
{{/foreach}}
{{/if}}
        <ul class="clickMenu" id="detailViewActions">
            <li style="cursor: pointer">

            {{if $module == "Contacts"}}
            {{sugar_actions_link module="$module" id="EDIT2" view="$view"}}
            {{else}}
                <a id='' href="javascript: void(0);">Actions</a>
            {{/if}}

                <ul class="subnav multi">



{{if !isset($form.buttons)}}
    {{if $module != "Contacts"}}
        {{sugar_actions_link module="$module" id="EDIT" view="$view"}}
    {{/if}}
{{sugar_actions_link module="$module" id="DUPLICATE" view="EditView"}}
{{sugar_actions_link module="$module" id="DELETE" view="$view"}}
{{else}}
	{{counter assign="num_buttons" start=0 print=false}}
	{{foreach from=$form.buttons key=val item=button}}
	  {{if !is_array($button) && in_array($button, $built_in_buttons)}}
	     {{counter print=false}}
	         {{if $module != "Contacts" || $button != "EDIT"}}
	         {{sugar_actions_link module="$module" id="$button" view="EditView"}}
            {{/if}}
	  {{/if}}
	{{/foreach}}

	{{if count($form.buttons) > $num_buttons}}
			{{foreach from=$form.buttons key=val item=button}}
			  {{if is_array($button) && $button.customCode}}

			  {{sugar_actions_link module="$module" id="$button" view="EditView"}}

			  {{/if}}
			{{/foreach}}
	{{/if}}
{{/if}}

{{if empty($form.hideAudit) || !$form.hideAudit}}
{{sugar_actions_link module="$module" id="Audit" view="EditView"}}
{{/if}}


                </ul>
            </li>

        </ul>
</form>
</div>

</td>


<td align="right" width="50%">{$ADMIN_EDIT}
	{{if $panelCount == 0}}
	    {{* Render tag for VCR control if SHOW_VCR_CONTROL is true *}}
		{{if $SHOW_VCR_CONTROL}}
			{$PAGINATION}
		{{/if}}
		{{counter name="panelCount" print=false}}
	{{/if}}
</td>
{{* Add $form.links if they are defined *}}
{{if !empty($form) && isset($form.links)}}
	<td align="right" width="10%">&nbsp;</td>
	<td align="right" width="100%" NOWRAP>
	{{foreach from=$form.links item=link}}
	    {{$link}}&nbsp;
	{{/foreach}}
	</td>
{{/if}}
</tr>
</table>