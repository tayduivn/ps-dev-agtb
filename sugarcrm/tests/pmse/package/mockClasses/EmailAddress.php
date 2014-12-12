<?php

/*
 * To change this template, choose Tools | Templates
 *
 */

/**
 * Description of Lead
 *
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

?>
