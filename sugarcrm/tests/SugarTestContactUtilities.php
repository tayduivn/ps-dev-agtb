<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once 'modules/Contacts/Contact.php';

class SugarTestContactUtilities
{
    private static $_createdContacts = array();

    private function __construct() {}

    /**
     *
     * @param string $id
     * @param array $contactValues
     * @return Contact
     */
    public static function createContact($id = '', $contactValues = array())
    {
        $time = mt_rand();
        $contact = new Contact();

        if (isset($contactValues['first_name'])) {
            $contact->first_name = $contactValues['first_name'];
        } else {
            $contact->first_name = 'SugarContactFirst' . $time;
        }
        if (isset($contactValues['last_name'])) {
            $contact->last_name = $contactValues['last_name'];
        } else {
            $contact->last_name = 'SugarContactLast';
        }
        if (isset($contactValues['email'])) {
            $contact->email1 = $contactValues['email'];
        } else {
            $contact->email1 = 'contact@'. $time. 'sugar.com';
        }

        if(!empty($id))
        {
            $contact->new_with_id = true;
            $contact->id = $id;
        }
        $contact->save();
        $GLOBALS['db']->commit();
        self::$_createdContacts[] = $contact;
        return $contact;
    }

    public static function setCreatedContact($contact_ids) {
    	foreach($contact_ids as $contact_id) {
    		$contact = new Contact();
    		$contact->id = $contact_id;
        	self::$_createdContacts[] = $contact;
    	} // foreach
    } // fn

    public static function removeAllCreatedContacts()
    {
        $contact_ids = self::getCreatedContactIds();
        $GLOBALS['db']->query('DELETE FROM contacts WHERE id IN (\'' . implode("', '", $contact_ids) . '\')');
    }

    /**
     * removeCreatedContactsEmailAddresses
     *
     * This function removes email addresses that may have been associated with the contacts created
     *
     * @static
     * @return void
     */
    public static function removeCreatedContactsEmailAddresses(){
    	$contact_ids = self::getCreatedContactIds();
        $GLOBALS['db']->query('DELETE FROM email_addresses WHERE id IN (SELECT DISTINCT email_address_id FROM email_addr_bean_rel WHERE bean_module =\'Contacts\' AND bean_id IN (\'' . implode("', '", $contact_ids) . '\'))');
        $GLOBALS['db']->query('DELETE FROM emails_beans WHERE bean_module=\'Contacts\' AND bean_id IN (\'' . implode("', '", $contact_ids) . '\')');
        $GLOBALS['db']->query('DELETE FROM email_addr_bean_rel WHERE bean_module=\'Contacts\' AND bean_id IN (\'' . implode("', '", $contact_ids) . '\')');
    }

    public static function removeCreatedContactsUsersRelationships(){
    	$contact_ids = self::getCreatedContactIds();
        $GLOBALS['db']->query('DELETE FROM contacts_users WHERE contact_id IN (\'' . implode("', '", $contact_ids) . '\')');
    }

    public static function getCreatedContactIds()
    {
        $contact_ids = array();
        foreach (self::$_createdContacts as $contact) {
            $contact_ids[] = $contact->id;
        }
        return $contact_ids;
    }
}
