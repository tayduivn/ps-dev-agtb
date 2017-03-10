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

// $Id: sugar_version.php 56713 2010-05-27 20:55:55Z kjing $

$sugar_version      = '@_SUGAR_VERSION';
$sugar_db_version   = '@_SUGAR_VERSION';
$sugar_flavor       = '@_SUGAR_FLAV';
$sugar_build        = '@_SUGAR_BUILD_NUMBER';
$sugar_timestamp    = '@_SUGAR_BUILD_TIME';

//BEGIN SUGARCRM flav=int ONLY
 $sugar_codename 	= '<font color="red">(( Windex ))</font>';
//END SUGARCRM flav=int ONLY
?>
