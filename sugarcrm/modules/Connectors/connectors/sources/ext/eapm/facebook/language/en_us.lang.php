<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

$connector_strings = array (
    'LBL_LICENSING_INFO' => '<table border="0" cellspacing="1"><tr><td valign="top" width="35%" class="dataLabel">Obtain an API Key and App Secret from Facebook by creating an application for your Sugar instance.<br/><br>Steps to create an application for your instance:<br/><br/><ol><li>Go to the following Facebook to create the application: <a href=\'http://www.facebook.com/developers/createapp.php\' target=\'_blank\'>http://www.facebook.com/developers/createapp.php</a>.</li><li>Sign in to Facebook using the account under which you would like to create the application.</li><li>Within the "Create Application" page, enter a name for the application. This is the name users will see when they authenticate their Facebook accounts from within Sugar.</li><li>Select "Agree" to agree to the Facebook Terms.</li><li>Click "Create App"</li><li>Enter and submit the security words to pass the Security Check.</li><li>Within the page for your application, go to the "Web Site" area (link in lefthand menu) and enter the local URL of your Sugar instance for "Site URL."</li><li>Click "Save Changes"</li><li>Go to the "Facebook Integration" page (link in lefthand menu) and find the API Key and App Secret. Enter the Application ID and Application Secret below.</li></ol></td></tr></table>',
    'oauth_consumer_key' => 'API Key',
    'oauth_consumer_secret' => 'App Secret',
);

