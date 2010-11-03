<?php
chdir('..');

error_reporting(E_ALL);
ini_set("display_errors", 1);

define('sugarEntry', true);
require_once('include/entryPoint.php');
require_once('account_array.php');

$master_array = array('batch1'=>$batch1);
$prospect_array = array('batch1'=>'c4378de7-d27b-c95b-7521-4c1c01bd0ca7');

require_once('modules/Accounts/Account.php');
require_once('modules/Contacts/Contact.php');

//iterate through accounts in each array in the master array
foreach($master_array as $key=>$accounts){
        $pl = new ProspectList();
        $pl->disable_row_level_security = true;
        $pl->retrieve($prospect_array[$key]);
        $pl->load_relationship("contacts");

        foreach($accounts as $acc_id){
                //retrieve the account
                $acc = new Account();
                $added = false;
                $acc->disable_row_level_security = true;
                $acc->retrieve($acc_id);

                //add the renewal contact
                if(!empty($acc->contact_id3_c)){
                        $pl->contacts->add($acc->contact_id3_c);
                        $added = true;
                }

                //retrieve the related contacts
                $acc->load_relationship("contacts");
        //        $contacts = $acc->get_linked_beans('contacts','Contact');
                $contacts = $acc->contacts->get();
                foreach ($contacts as $con_id){

                    //iterate through contacts and see if contact is support authorized:
                    $contact = new Contact();
                    $contact->disable_row_level_security = true;
                    $contact->retrieve($con_id);
                    if(!empty($contact->support_authorized_c) && isset($contact->support_authorized_c) && !empty($contact->email1)){
                        //add to support authorized return list
                                                $pl->contacts->add($con_id);
                                                $added = true;
                    }
                }
                //if none 
                if(!$added){
                   //add all contacts to prospect list
                                foreach($contacts as $con_id)
                                $pl->contacts->add($con_id);
                }
        }
}

?>
