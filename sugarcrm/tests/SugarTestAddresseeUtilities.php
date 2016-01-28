<?php
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
 
require_once 'modules/Addressees/Addressee.php';

class SugarTestAddresseeUtilities
{
    protected static $_createdAddressees = array();

    public static function createAddressee($id = '', $addresseeValues = array())
    {
        $time = mt_rand();
        $addressee = new Addressee();

        if (isset($addresseeValues['email'])) {
            $addressee->email1 = $addresseeValues['email'];
        } else {
            $addressee->email1 = 'addressee@'. $time. 'sugar.com';
        }

        if (isset($addresseeValues['last_name'])) {
            $addressee->last_name = $addresseeValues['last_name'];
        } else {
            $addressee->last_name = $addressee->email1;
        }

        if($id)
        {
            $addressee->new_with_id = true;
            $addressee->id = $id;
        }

        $addressee->save();
        $GLOBALS['db']->commit();
        static::$_createdAddressees[] = $addressee;
        return $addressee;
    }

    public static function setCreatedAddressee($addressee_ids)
    {
        foreach ($addressee_ids as $addressee_id) {
            $addressee = new Addressee();
            $addressee->id = $addressee_id;
            static::$_createdAddressees[] = $addressee;
        }
    }
    
    public static function removeAllCreatedAddressees()
    {
        $addressee_ids = static::getCreatedAddresseeIds();
        $GLOBALS['db']->query('DELETE FROM addressees WHERE id IN (\'' . implode("', '", $addressee_ids) . '\')');
        self::removeCreatedAddresseesEmailAddresses();
    }

    /**
     * removeCreatedAddresseesEmailAddresses
     *
     * This function removes email addresses that may have been associated with the addressees created
     *
     * @static
     * @return void
     */
    public static function removeCreatedAddresseesEmailAddresses()
    {
        $addressee_ids = static::getCreatedAddresseeIds();
        $GLOBALS['db']->query('DELETE FROM email_addresses WHERE id IN (SELECT DISTINCT email_address_id FROM email_addr_bean_rel WHERE bean_module =\'Addressees\' AND bean_id IN (\'' . implode("', '", $addressee_ids) . '\'))');
        $GLOBALS['db']->query('DELETE FROM emails_beans WHERE bean_module=\'Addressees\' AND bean_id IN (\'' . implode("', '", $addressee_ids) . '\')');
        $GLOBALS['db']->query('DELETE FROM email_addr_bean_rel WHERE bean_module=\'Addressees\' AND bean_id IN (\'' . implode("', '", $addressee_ids) . '\')');
    }

    public static function removeCreatedAddresseesUsersRelationships()
    {
        $addressee_ids = static::getCreatedAddresseeIds();
        $GLOBALS['db']->query('DELETE FROM addressees_users WHERE addressee_id IN (\'' . implode("', '", $addressee_ids) . '\')');
    }
    
    public static function getCreatedAddresseeIds() 
    {
        $addressee_ids = array();
        foreach (static::$_createdAddressees as $addressee) {
            $addressee_ids[] = $addressee->id;
        }
        return $addressee_ids;
    }
}
