<?php

global $current_user;
if(!is_admin($current_user) && !$current_user->check_role_membership('Sales Operations') && !$current_user->check_role_membership('Sales Manager') && $current_user->user_name != "drew"){
	sugar_die("You cannot access this page.");
}

?>
<h4>Reassign Contacts Related to Sales Rep's Accounts</h4>
<?php

if(!isset($_POST['selectuser'])){
?>
This will find all accounts assigned to the selected users. Then it searches for all contacts related to those accounts, that have an account type in the selected account type values below. At this point, it will reassign all those contacts back to the account owner.<BR><BR>
Please select users from the dropdown below:
<form method=post action="index.php?module=Contacts&action=reassignContacts">
<BR>
User's records to reassign:
<BR>
<select size=5 name=selectuser[] multiple=true>
<?php
echo get_select_options_with_id(get_user_array(FALSE), '');
?>
</select>
<BR>
<BR>
Account Type to include in reassignment:
<BR>
<select tabindex='1' size="5" name='account_type[]' multiple="true">
<?php echo get_select_options_with_id($app_list_strings['account_type_dom'], ''); ?>
</select>
<BR>
<BR>
<input type=submit value=Submit name=steponesubmit>
</form>

<?php
}
else{
	if(empty($_POST['account_type'])){
		sugar_die("Please go back and select at least one account type.");
	}
	if(empty($_POST['selectuser'])){
		sugar_die("Please go back and select at least one account type.");
	}
	
	foreach($_POST['selectuser'] as $selectuser){
		$unrow = $GLOBALS['db']->fetchByAssoc($GLOBALS['db']->query("select user_name from users where id = '$selectuser'"));
		echo "<h5>Updating for user '{$unrow['user_name']}'</h5>";
		
		$in_string = "";
		$empty_check = "";
		foreach($_POST['account_type'] as $account_type){	
			if(empty($account_type))
				$empty_check .= " OR accounts.account_type is null ";
			$in_string .= "'$account_type', ";
		}
		$in_string = substr($in_string, 0, count($in_string) - 3);
		
		$query = "select contacts.id ".
			 "from accounts inner join accounts_contacts on accounts.id = accounts_contacts.account_id ".
			 	      " inner join contacts on contacts.id = accounts_contacts.contact_id ".
			 "where accounts.assigned_user_id = '$selectuser'".
			 "  and (accounts.account_type in ($in_string) $empty_check )";
			 "  and contacts.deleted = 0";
			 "  and accounts.deleted = 0";
			 "  and accounts_contacts.deleted = 0";
		
		$result = $GLOBALS['db']->query($query);
		
		require_once('modules/Contacts/Contact.php');
		$successarr = array();
		$failarr = array();
		$beanarr = array();
		$migrated_contacts = 0;
		$total_contacts = 0;
		$already_assigned = 0;
		$failed_contacts = 0;
		
		while($row = $GLOBALS['db']->fetchByAssoc($result)){
			$bean = new Contact();
			$bean->retrieve($row['id']);
			// So that we don't create new blank records.
			if(!isset($bean->id)){
				continue;
			}
			$total_contacts++;
			if(isset($bean->assigned_user_id) && $bean->assigned_user_id == $selectuser){
				$successarr[] = "{$bean->object_name} \"<i><a href=\"index.php?module={$bean->module_dir}&action=DetailView&record={$bean->id}\">{$bean->name}</a></i>\" already assigned to the selected user.";
				$already_assigned++;
			}
			else{
				$bean->assigned_user_id=$selectuser;
				$bean->team_id = '1';
				if($bean->save()){
					$successarr[] = "Successfully changed {$bean->object_name} \"<i><a href=\"index.php?module={$bean->module_dir}&action=DetailView&record={$bean->id}\">{$bean->name}</a></i>\" assignment to the selected user.";
					$migrated_contacts++;
				}
				else{
					$failarr[] = "Call to \$bean->save() failed for {$bean->object_name} \"<i><a href=\"index.php?module={$bean->module_dir}&action=DetailView&record={$bean->id}\">{$bean->name}</a></i>\".";
					$failed_contacts++;
				}
			}
		}
		
		echo "<h5>The following Contacts have been updated:</h5>";
		foreach($successarr as $ord){
			echo "$ord<BR>";
		}
		if(empty($successarr))
			echo "No contacts updated<BR>";
		
		echo "<h5>The following Contacts could not be processed:</h5>";
		foreach($failarr as $failure){
			echo $failure."<BR>";
		}
		if(empty($failarr))
			echo "No failures<BR>";

	}
	echo "<h5>Summary:</h5>";
	echo "Contacts Reassigned: $migrated_contacts<BR>";
	echo "Contacts Already Assigned: $already_assigned<BR>";
	echo "Contacts Reassignment Failed: $failed_contacts<BR>";
	echo "Total Contacts: $total_contacts<BR>";
	echo "<BR><input type=button value=Return onclick='document.location=\"index.php?module=Contacts&action=reassignContacts\"'>";
}
?>
