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
<!DOCTYPE html PUBLIC "-//W3C//DTD html 4.01 Transitional//EN">
<html>
<head >
<link REL="SHORTCUT ICON" HREF="include/images/sugar_icon.ico">

<title>{$APP.LBL_BROWSER_TITLE}</title>
<style type="text/css">@import url("themes/{$THEME}/style.css?s={$SUGAR_VERSION}&c={$JS_CUSTOM_VERSION}"); </style>
<style type="text/css">@import url("themes/{$THEME}/yui.css?s={$SUGAR_VERSION}&c={$JS_CUSTOM_VERSION}"); </style>
<style type="text/css">@import url("themes/{$THEME}/calendar.css?s={$SUGAR_VERSION}&c={$JS_CUSTOM_VERSION}"); </style>
<style type="text/css">@import url("custom/style.css?s={$SUGAR_VERSION}&c={$JS_CUSTOM_VERSION}"); </style>
<link href="themes/{$THEME}/navigation.css?s={$SUGAR_VERSION}&c={$JS_CUSTOM_VERSION}" rel="stylesheet" type="text/css" />
<script language="javascript" src="themes/{$THEME}/menu.js?s={$SUGAR_VERSION}&c={$JS_CUSTOM_VERSION}"></script>
<script language="javascript" src="themes/{$THEME}/cookie.js?s={$SUGAR_VERSION}&c={$JS_CUSTOM_VERSION}"></script>
</head>

<body>

<div id='moduleLinks'>
				<ul id="tabRow"><div style="float: right;">{foreach from=$GCL key=name item=links}

						{foreach from=$links.linkinfo name=linkLoop key=linkName item=link}
							<a href="{$link}" id="{$linkName}Handle">{$linkName}</a>
							{if !$smarty.foreach.linkLoop.last} | {/if}
						{/foreach}

					{/foreach}</div>
		{foreach from=$TABS key=moduleKey item=moduleName}
			{if $moduleKey == $CURRENT_MODULE}
				{assign var='tab_class' value=$CURRENT_TAB_CLASS}
			{else}
				{assign var='tab_class' value=$OTHER_TAB_CLASS}
			{/if}
				<li class={$tab_class}><a href="index.php?module={$moduleKey}&action=index" class={$tab_class}>{$moduleName}</a></li>
		{/foreach}
		</ul>

</div>

<div id='shortCuts'>
	{foreach name=shortcutLoop from=$SHORTCUTS key=name item=link}
		<a class='link' href='{$link}'>{$name}</a>
		{if !$smarty.foreach.shortcutLoop.last} | {/if}
	{/foreach}
</div>
