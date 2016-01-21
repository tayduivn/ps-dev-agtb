{*
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
*}
</form>
{{if $externalJSFile}}
require_once("'".$externalJSFile."'");
{{/if}}
{$set_focus_block}
{{if isset($scriptBlocks)}}
<!-- Begin Meta-Data Javascript -->
{{$scriptBlocks}}
<!-- End Meta-Data Javascript -->
{{/if}}


<div class="h3Row" id="scheduler"></div>

<div>
<h3>{$MOD.LBL_RECURRENCE}</h3>
{include file='modules/Calendar/tpls/repeat.tpl'}
{sugar_getscript file='modules/Meetings/recurrence.js'}
<script type="text/javascript">
{literal}
SUGAR.util.doWhen(function() {
    return typeof CAL != "undefined";
}, function () {
    CAL.fillRepeatForm({/literal}{$repeatData}{literal});
});
{/literal}
</script>
</div>

<script type="text/javascript">
{literal}
SUGAR.calls = {};
var callsLoader = new YAHOO.util.YUILoader({
    require : ["sugar_grp_jsolait"],
    // Bug #48940 Skin always must be blank
    skin: {
        base: 'blank',
        defaultSkin: ''
    },
    onSuccess: function(){
		SUGAR.calls.fill_invitees = function() {
			if (typeof(GLOBAL_REGISTRY) != 'undefined')  {
				SugarWidgetScheduler.fill_invitees(document.EditView);
			}
		}
		var root_div = document.getElementById('scheduler');
		var sugarContainer_instance = new SugarContainer(document.getElementById('scheduler'));
		sugarContainer_instance.start(SugarWidgetScheduler);
		if ( document.getElementById('save_and_continue') ) {
			var oldclick = document.getElementById('save_and_continue').attributes['onclick'].nodeValue;
			document.getElementById('save_and_continue').onclick = function(){
				SUGAR.calls.fill_invitees();
				eval(oldclick);
			}
		}
	}
});
callsLoader.addModule({
    name :"sugar_grp_jsolait",
    type : "js",
    fullpath: "cache/include/javascript/sugar_grp_jsolait.js",
    varName: "global_rpcClient",
    requires: []
});
callsLoader.insert();
YAHOO.util.Event.onContentReady("{/literal}{{$form_name}}{literal}",function() {
    var durationHours = document.getElementById('duration_hours');
    if (durationHours) {
        document.getElementById('duration_minutes').tabIndex = durationHours.tabIndex;
    }
});
{/literal}
</script>
</form>
<form >
{sugar_csrf_form_token}
	<div class="buttons">
		{{if !empty($form) && !empty($form.buttons_footer)}}
		   {{foreach from=$form.buttons_footer key=val item=button}}
		      {{sugar_button module="$module" id="$button" location="FOOTER" view="$view"}}
		   {{/foreach}}
		{{else}}
				{{sugar_button module="$module" id="SAVE" view="$view"}}
				{{sugar_button module="$module" id="CANCEL" view="$view"}}
		{{/if}}
		{{sugar_button module="$module" id="Audit" view="$view"}}
	</div>
</form> 
