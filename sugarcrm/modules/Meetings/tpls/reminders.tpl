{*
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
*}

	{if $fields.reminder_time}            	
            	
            	{assign var="REMINDER_TIME_OPTIONS" value=$fields.reminder_time.options}
            	{assign var="EMAIL_REMINDER_TIME_OPTIONS" value=$fields.reminder_time.options}

            	{if !$fields.reminder_checked.value}
            		{assign var="REMINDER_TIME" value=-1}
            	{else}
            		{assign var="REMINDER_TIME" value=$fields.reminder_time.value}
            	{/if}
	{/if}

{if $view == "EditView" || $view == "QuickCreate" || $view == "QuickEdit" || $view == "wirelessedit"}

{* //BEGIN SUGARCRM flav!=pro ONLY *}
{if $view == "EditView" || $view == "QuickCreate" || $view == "QuickEdit"}
{* //END SUGARCRM flav!=pro ONLY *}

		<div>

		    	<div id="reminder_list">
		    		<select tabindex="{$REMINDER_TABINDEX}" name="reminder_time">
					{html_options options=$REMINDER_TIME_OPTIONS selected=$REMINDER_TIME}
				</select>
		    	</div>
            	</div>
            	<div>

		</div>
	{else}
		<div>
            {$REMINDER_TIME_OPTIONS[$REMINDER_TIME]}
		</div>
	{/if}
