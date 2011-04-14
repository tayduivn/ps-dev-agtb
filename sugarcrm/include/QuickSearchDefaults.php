<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 *$Id: QuickSearchDefaults.php 53116 2009-12-10 01:24:37Z mitani $
 ********************************************************************************/

/**
 * QuickSearchDefaults class, outputs default values for setting up quicksearch
 *
 * @copyright  2004-2007 SugarCRM Inc.
 * @license    http://www.sugarcrm.com/crm/products/sugar-professional-eula.html  SugarCRM Professional End User License
 * @since      Class available since Release 4.0
 */

class QuickSearchDefaults {

	var $form_name = 'EditView';
	
	function setFormName($name = 'EditView') {
		$this->form_name = $name;
	}
	
    function getQSParent($parent = 'Accounts') {
        global $app_strings;

        $qsParent = array(
                    'form' => $this->form_name,
                    'method' => 'query',
                    'modules' => array($parent),
                    'group' => 'or',
                    'field_list' => array('name', 'id'),
                    'populate_list' => array('parent_name', 'parent_id'),
                    'required_list' => array('parent_id'),
                    'conditions' => array(array('name'=>'name','op'=>'like_custom','end'=>'%','value'=>'')),
                    'order' => 'name',
                    'limit' => '30',
                    'no_match_text' => $app_strings['ERR_SQS_NO_MATCH']
                    );

        return $qsParent;
    }

    function getQSAccount($nameKey, $idKey, $billingKey = null, $shippingKey = null, $additionalFields = null) {

        global $app_strings;


        $field_list = array('name', 'id');
        $populate_list = array($nameKey, $idKey);
        if($billingKey != null) {
            $field_list = array_merge($field_list, array('billing_address_street', 'billing_address_city',
                                                           'billing_address_state', 'billing_address_postalcode', 'billing_address_country'));

            $populate_list = array_merge($populate_list, array($billingKey . "_address_street", $billingKey . "_address_city",
                                                                $billingKey . "_address_state", $billingKey . "_address_postalcode", $billingKey . "_address_country"));
        } //if

        if($shippingKey != null) {
            $field_list = array_merge($field_list, array('shipping_address_street', 'shipping_address_city',
                                                           'shipping_address_state', 'shipping_address_postalcode', 'shipping_address_country'));

            $populate_list = array_merge($populate_list, array($shippingKey . "_address_street", $shippingKey . "_address_city",
                                                                $shippingKey . "_address_state", $shippingKey . "_address_postalcode", $shippingKey . "_address_country"));
        }

        if(!empty($additionalFields) && is_array($additionalFields)) {
           $field_list = array_merge($field_list, array_keys($additionalFields));
           $populate_list = array_merge($populate_list, array_values($additionalFields));
        }

        $qsParent = array(
					'form' => $this->form_name,
                    'method' => 'query',
                    'modules' => array('Accounts'),
                    'group' => 'or',
                    'field_list' => $field_list,
                    'populate_list' => $populate_list,
                    'conditions' => array(array('name'=>'name','op'=>'like_custom','end'=>'%','value'=>'')),
                    'required_list' => array($idKey),
                    'order' => 'name',
                    'limit' => '30',
                    'no_match_text' => $app_strings['ERR_SQS_NO_MATCH']
                    );

        return $qsParent;
    }

    /**
     * getQSContact
     * This is a customized method to handle returning in JSON notation the QuickSearch formats
     * for searching the Contacts module for a contact name.  The method takes into account
     * the locale settings (s = salutation, f = first name, l = last name) that are permissible.
     * It should be noted though that any other characters present in the formatting will render
     * this widget non-functional.
     * @return The JSON format of a QuickSearch definition for the Contacts module
     */
    function getQSContact($name, $idName) {
        global $app_strings, $locale;

        $qsContact = array('form' => $this->form_name,
        				   'method'=>'get_contact_array',
                           'modules'=>array('Contacts'),
                           'field_list' => array('salutation', 'first_name', 'last_name', 'id'),
                           'populate_list' => array($name, $idName, $idName, $idName),
                           'required_list' => array($idName),
                           'group' => 'or',
                           'conditions' => array(
                                                 array('name'=>'first_name', 'op'=>'like_custom','end'=>'%','value'=>''),
                                                 array('name'=>'last_name', 'op'=>'like_custom','end'=>'%','value'=>'')
                                           ),
                           'order'=>'last_name',
                           'limit'=>'30',
                           'no_match_text'=> $app_strings['ERR_SQS_NO_MATCH']);
        return $qsContact;
    }

    function getQSUser($p_name = 'assigned_user_name', $p_id ='assigned_user_id') {
        global $app_strings;

        $qsUser = array('form' => $this->form_name,
        				'method' => 'get_user_array', // special method
                        'field_list' => array('user_name', 'id'),
                        'populate_list' => array($p_name, $p_id),
                        'required_list' => array($p_id),
                        'conditions' => array(array('name'=>'user_name','op'=>'like_custom','end'=>'%','value'=>'')),
                        'limit' => '30','no_match_text' => $app_strings['ERR_SQS_NO_MATCH']);
        return $qsUser;
    }
    //BEGIN SUGARCRM flav!=sales ONLY
    function getQSCampaigns() {
        global $app_strings;

        $qsCampaign = array('form' => $this->form_name,
        					'method' => 'query',
                            'modules'=> array('Campaigns'),
                            'group' => 'or',
                            'field_list' => array('name', 'id'),
                            'populate_list' => array('campaign_name', 'campaign_id'),
                            'conditions' => array(array('name'=>'name','op'=>'like_custom','end'=>'%','value'=>'')),
                            'required_list' => array('campaign_id'),
                            'order' => 'name',
                            'limit' => '30',
                            'no_match_text' => $app_strings['ERR_SQS_NO_MATCH']);
        return $qsCampaign;
    }

    //END SUGARCRM flav!=sales ONLY
    //BEGIN SUGARCRM flav=pro ONLY

    function getQSTeam() {
        global $app_strings;

        $qsTeam = array(
        			'form' => $this->form_name,
                    'method' => 'query',
                    'modules'=> array('Teams'),
                    'group' => 'or',
                    'field_list' => array('name', 'id'),
                    'populate_list' => array('team_name', 'team_id'),
                    'required_list' => array('team_id'),
                    'whereExtra'=> "( teams.associated_user_id IS NULL OR teams.associated_user_id NOT IN ( SELECT id FROM users WHERE status = 'Inactive' OR portal_only = '1' ))",
                    'conditions' => array(array('name'=>'name','op'=>'like_custom','end'=>'%','value'=>''),
                                                 array('name'=>'name','op'=>'like_custom','begin'=>'(','end'=>'%','value'=>'')),
                    'order' => 'name', 'limit' => '30', 'no_match_text' => $app_strings['ERR_SQS_NO_MATCH']);
        return $qsTeam;
    }
    //END SUGARCRM flav=pro ONLY

    // BEGIN QuickSearch functions for 4.5.x backwards compatibility support
    function getQSScripts() {
		global $sugar_version, $sugar_config, $theme;
		$qsScripts = '<script type="text/javascript" src="' . getJSPath('include/JSON.js') .'"></script>
		<script type="text/javascript">sqsWaitGif = "' . SugarThemeRegistry::current()->getImageURL('sqsWait.gif') . '";</script>
		<script type="text/javascript" src="'. getJSPath('include/javascript/quicksearch.js') . '"></script>';
		return $qsScripts;
	}

	function getQSScriptsNoServer() {
		return $this->getQSScripts();
	}

	function getQSScriptsJSONAlreadyDefined() {
		global $sugar_version, $sugar_config, $theme;
		$qsScriptsJSONAlreadyDefined = '<script type="text/javascript">sqsWaitGif = "' . SugarThemeRegistry::current()->getImageURL('sqsWait.gif') . '";</script><script type="text/javascript" src="' . getJSPath('include/javascript/quicksearch.js') . '"></script>';
		return $qsScriptsJSONAlreadyDefined;
	}
    // END QuickSearch functions for 4.5.x backwards compatibility support
}

?>