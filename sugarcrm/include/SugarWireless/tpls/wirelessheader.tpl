
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
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>{sugar_translate label='LBL_BROWSER_TITLE' module=''}</title>
<link rel="apple-touch-icon" href="{sugar_getimagepath file='sugar_icon.png'}" />
<link href="{sugar_getjspath file='include/SugarWireless/css/wireless.css'}" type="text/css" rel="stylesheet">
<link media="only screen and (max-device-width: 480px)" href="{sugar_getjspath file='include/SugarWireless/css/iphone.css'}" type= "text/css" rel="stylesheet">
<meta name="viewport" content="user-scalable=no, width=device-width">
<meta name="apple-touch-fullscreen" content="yes" />
</head>
<body>
{sugar_getimage name="company_logo" ext=".png" width="212" height="40" alt=$app_strings.LBL_COMPANY_LOGO other_attributes='border="0" id="companylogo" '}
<hr />
{if $WELCOME}
<div class="sec welcome" align="right">
<small>{sugar_translate label='NTC_WELCOME' module=''}, {$user_name} [<a href="index.php?module=Users&action=Logout">{sugar_translate label='LBL_LOGOUT' module=''}</a>]</small><br />
</div>
{/if}
