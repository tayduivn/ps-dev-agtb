<?php
//FILE SUGARCRM flav=ent ONLY
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
class EmailAddress
{
    public function getAddressesByGUID($id, $module_dir){
        $emailsAddress1 = array('address1' => array (
            'email_address_id'=> '1',
            'primary_address' => 1,
            'email_address'=> 'test1@test.com')
        );
         $emailsAddress2 = array('address1' => array (
            'email_address_id'=> '2',
            'primary_address' => 2,
            'email_address'=> 'test1@test.com')
        ); 
         $emailsAddress3 = array('address1' => array (
            'primary_address' => 2,
            'email_address'=> 'test1@test.com')
        );  
        switch ($id)
        {
            case 1:
                return $emailsAddress1;
            case 2:
                return $emailsAddress2; 
            default:
                return $emailsAddress3;
        }
    }
}
