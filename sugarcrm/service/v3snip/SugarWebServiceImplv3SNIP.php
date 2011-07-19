<?php
if(!defined('sugarEntry'))define('sugarEntry', true);
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 *
 ********************************************************************************/

require_once('service/v3/SugarWebServiceImplv3.php');

class SugarWebServiceImplv3SNIP extends SugarWebServiceImplv3 {

    /**
	 * Import emails from the SNIP service.
	 *
	 * @param String $session -- Session ID returned by a previous call to login.
	 * @exception 'SoapFault' -- The SOAP error, if any
	 */
	function import_emails($session, $email)
	{
		$GLOBALS['log']->info('Begin: SugarWebServiceImpl->import_emails');
		$error = new SoapError();
		// TODO: permissions?
		if (! self::$helperObject->checkSessionAndModuleAccess($session, 'invalid_session', '', '', '',  $error)) {
			$GLOBALS['log']->info('End: SugarWebServiceImpl->import_emails denied.');
			return;
		} // if
		require_once 'modules/SNIP/SugarSNIP.php';
		$snip = SugarSNIP::getInstance();
		$snip->importEmail($email);
		$GLOBALS['log']->info('End: SugarWebServiceImpl->import_emails');
		return array('results' => TRUE, 'count' => 1, 'message' => '');
	}

	/**
	 * Return new contact emails since $timestamp for current user
	 * @param string $session
	 * @param int $timestamp
	 */
	function update_contacts($session, $timestamp)
	{
		$GLOBALS['log']->info('Begin: SugarWebServiceImpl->update_contacts');
		$error = new SoapError();
		if (! self::$helperObject->checkSessionAndModuleAccess($session, 'invalid_session', '', 'read', 'no_access',  $error)) {
			$GLOBALS['log']->info('End: SugarWebServiceImpl->update_contacts denied.');
			return;
		} // if

    	$query = "SELECT DISTINCT ea.email_address as email  FROM email_addresses ea
		JOIN email_addr_bean_rel eabr ON ea.id=eabr.email_address_id
		WHERE ea.deleted=0 AND eabr.deleted=0 AND eabr.bean_module <> 'Users' AND eabr.bean_module <> 'Employees'
		";
		if($timestamp) {
			$dbdate = gmdate($GLOBALS['timedate']->get_db_date_time_format(), $timestamp);
		    $query .= " AND ea.date_modified >= '$dbdate'";

		}

		$seed = new User();
		$res = $seed->db->query($query);
		$emails = array();
		while($row = $seed->db->fetchByAssoc($res)) {
				$emails[] = $row['email'];
		}
		return array('results' => $emails, 'count' => count($emails), 'message' => '');
	}

}
