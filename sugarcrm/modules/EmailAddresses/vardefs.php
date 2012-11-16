<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/

/**
 * Stub class to allow Link class easily use SugarEmailAddress
 */
global $dictionary;
include SugarAutoLoader::existingCustomOne('metadata/email_addressesMetaData.php');
include SugarAutoLoader::existingCustomOne('metadata/emails_beansMetaData.php');
