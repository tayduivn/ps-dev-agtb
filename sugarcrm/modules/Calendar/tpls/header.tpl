{if $controls}

<div style='width: 100%; margin-top: 12px;'></div>

<div style='float:left; width: 50%;'>
{foreach name=tabs from=$tabs key=k item=tab}
	<input type="button" class="button" {if $pview == $tab} selected {/if} title="{$tabs_params[$tab].title}" value=" {$tabs_params[$tab].title} " onclick="{$tabs_params[$tab].link}">
{/foreach}
</div>

<div style="float:left; text-align: right; width: 50%; font-size: 12px;">
	{if $pview != 'year'}
	<span class="dateTime">
					<img border="0" src="{$cal_img}" alt="Enter Date" id="goto_date_trigger" align="absmiddle">					
					<input type="hidden" id="goto_date" name="goto_date" value="{$current_date}">		
					<script type="text/javascript">
					Calendar.setup ({literal}{{/literal}
						inputField : "goto_date",
						ifFormat : "%m/%d/%Y",
						daFormat : "%m/%d/%Y",
						button : "goto_date_trigger",
						singleClick : true,
						dateStr : "{$current_date}",
						step : 1,
						onUpdate: goto_date_call,
						startWeekday: {$start_weekday},
						weekNumbers:false
					{literal}}{/literal});	
					{literal}	
					YAHOO.util.Event.onDOMReady(function(){
						YAHOO.util.Event.addListener("goto_date","change",goto_date_call);
					});
					function goto_date_call(){
						CAL.goto_date_call();
					}
					{/literal}
					</script>
	</span>
	{/if}
	<input type="button" class="button" onclick="CAL.toggle_settings()" value="{$MOD.LBL_SETTINGS}">
			
	&nbsp;&nbsp;&nbsp;&nbsp;
</div>

<div style='clear: both;'></div>

{/if}


<div class="{if $controls}monthHeader{/if}">
	<div style='float: left; width: 20%;'>{$previous}</div>
	<div style='float: left; width: 60%; text-align: center;'><h3>{$date_info}</h3></div>
	<div style='float: right;'>{$next}</div>
	<br style='clear:both;'>
</div>

