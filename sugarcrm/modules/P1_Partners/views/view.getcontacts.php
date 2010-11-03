<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************

 * Description:  TODO To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/MVC/View/SugarView.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Accounts/Account.php');

class P1_PartnersViewGetContacts extends SugarView {

	public function __construct()
   	{
  		parent::SugarView();
   	}

    	function process() {
        	$this->display();
    	}

    	function display(){
		if(isset($_REQUEST['account_id']) && !empty($_REQUEST['account_id'])) {
			if(isset($_REQUEST['eval_flag']) && !empty($_REQUEST['eval_flag'])) {
			echo '<p class="dataField" style="padding-top:10px;">Select Account Contact: <span class="required">*</span>&nbsp;</p>';
			} else {
				echo '<p class="dataField" style="padding-top:10px;">Select Partner Contact: <span class="required">*</span>&nbsp;</p>';
			}
			$partnerAccount = new Account();
			$Contact = new Contact();
			$partnerAccount->retrieve($_REQUEST['account_id']);
			$partnerAccount->load_relationship('contacts');
			$partnerContacts = array();
			foreach($partnerAccount->build_related_list($partnerAccount->contacts->getQuery(), new Contact) as $contact) {
				$partnerContacts[$contact->id] = array(
					'id' => $contact->id,
					'name' => $contact->name, 
					'title' => $contact->title,
					'email1' => $contact->email1,
					'portal_name' => $contact->portal_name, 
					'portal_active' => $contact->portal_active, 
					'primary_business_c' => $contact->primary_business_c,
				);
			}
			if(!empty($partnerContacts)) {
				echo '<table border="0" cellpadding="0" cellspacing="0" width="100%">';
				echo '<tr><td class="dataField">&nbsp;</td><td class="dataField"><b>Name</b></td><td class="dataLabel"><b>Title</b></td><td class="dataLabel"><b>Email</b></td><td class="dataLabel"><b>Portal Name</b></td><td class="dataLabel"><b>Portal Active</b></td><td class="dataLabel"><b>Primary Business Contact</b></td></tr>';
				foreach($partnerContacts as $contactid => $value) {
					echo '<tr><td class="dataField" style="padding-right:8px;"><input type="radio" name="P1_Partnerscontact_id" id="P1_Partnerscontact_id" value="'.$partnerContacts[$contactid]['id'].'"';
					if(empty($partnerContacts[$contactid]['email1'])) {
						echo " disabled";
					}
					echo '></td>';
					echo '<td class="dataField"><a href="index.php?module=Contacts&action=DetailView&record='.$partnerContacts[$contactid]['id'].'" target="_blank">'.$partnerContacts[$contactid]['name'].'</a></td>';
					echo '<td class="dataLabel">'.$partnerContacts[$contactid]['title'].'</td>';
					echo '<td class="dataLabel">';
					if(empty($partnerContacts[$contactid]['email1'])) {
						echo "<b>No email addresss</b>";
					} else {
						echo '<a href="mailto: '.$partnerContacts[$contactid]['email1'].'">'.$partnerContacts[$contactid]['email1'].'</a>';
					}
					echo "</td>";
					echo '<td class="dataLabel">'.$partnerContacts[$contactid]['portal_name'].'</td>';
					echo '<td class="dataLabel">';
						if($partnerContacts[$contactid]['portal_active'] == '1') { echo 'Yes'; } else { echo 'No'; }
					echo '</td>';
					echo '<td class="dataLabel">';
						if($partnerContacts[$contactid]['primary_business_c'] == '1') {echo 'Yes'; } else { echo 'No'; }
					echo '</td></tr>';
				}
				echo "</table>";	
			} else {
				echo '<p>The selected account does not have any contacts. Please <a href="index.php?module=Contacts&action=EditView&return_module=Contacts&return_action=index" target="_blank">create a contact</a> to proceed and try again.</p>';
			}
		}
		else {
			echo "<p>Error 1200: The partner assigned to field is empty. Please select an account to proceed</p>";
    		}
	}
}
?>
