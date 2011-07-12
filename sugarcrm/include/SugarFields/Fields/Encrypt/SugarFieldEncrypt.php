<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/** CAPTIVEA CORE START **/

require_once('include/SugarFields/Fields/Base/SugarFieldBase.php');

class SugarFieldEncrypt extends SugarFieldBase {

    /**
     * Decrypt encrypt fields values before inserting them into the emails
     * 
     * @param string $inputField
     * @param mixed $vardef
     * @param mixed $displayParams
     * @param int $tabindex
     * @return string 
     */
	public function getEmailTemplateValue($inputField, $vardef, $displayParams = array(), $tabindex = 0){
        // Uncrypt the value
        $account = new Account();
        return $account->decrypt_after_retrieve($inputField);
    }
/** CAPTIVEA CORE END **/
}
?>
