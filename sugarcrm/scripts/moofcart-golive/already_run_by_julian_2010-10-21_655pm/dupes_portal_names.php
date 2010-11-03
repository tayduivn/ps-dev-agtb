<?php

mysql_connect('si-db1','sugarinternal','rI3pSTukiD6D');
mysql_select_db('sugarinternal');


$relationships = array('calls_contacts','contacts_bugs','contacts_cases','emails_contacts','meetings_contacts','opportunities_contacts');

$names = array();
$dupes = array();
$sql = "SELECT * FROM contacts WHERE portal_active = 1 AND deleted=0 AND (portal_name IS NOT NULL OR portal_name != '')";

$result = mysql_query($sql);

while($row = mysql_fetch_array($result)) {
        if(!in_array($row['portal_name'], $names))
                $names[] = $row['portal_name'];
        else {
                if(!isset($dupes[$row['portal_name']]))
                        $dupes[$row['portal_name']] = 0;
                $dupes[$row['portal_name']]++;
        }
}

mysql_free_result($result);

foreach($dupes as $name => $count) {
	printf("\n-- processing duplicate name %s\n", $name);
	$sql = sprintf("SELECT * FROM contacts WHERE portal_active=1 AND deleted=0 and portal_name = '%s'", $name);
	$contacts = mysql_query($sql);

	$highestcontact = 0;
	$highestcontactname = '';
	$contact_ids = array(); // save all the contact ids to loop through after so we don't have to query again
	
	while($contact = mysql_fetch_array($contacts)) {
		$contact_count = 0;
		$contact_ids[] = $contact['id'];
		foreach($relationships as $table) {
			$sql = sprintf("SELECT count(*) AS num FROM %s WHERE contact_id = '%s' and deleted=0", $table, $contact['id']);
			$relres = mysql_query($sql);
			$relrow = mysql_fetch_array($relres);
			$contact_count += $relrow['num']; 
		}
		printf("-- -- %-50s %-10d\n", $contact['last_name'] . ', ' . $contact['first_name'], $contact_count);
		if($contact_count > $highestcontact) {
			$highestcontactname = $contact['last_name'] . ', ' . $contact['first_name'];
			$highestcontact = $contact_count;
			$highestcontactid = $contact['id'];
		}
	}
	
	printf("-- highest contact: %-50s (%d) %s\n", $highestcontactname, $highestcontact, $highestcontactid);
	
	foreach($contact_ids as $contact_id) {
		
		// no need to modify the highestcontactid records
		if($contact_id == $highestcontactid)
			continue;
			
		// grab the description of the contact record to append to it
		$sql = sprintf("SELECT description FROM contacts WHERE id='%s'", $contact_id);
		$desc_res = mysql_query($sql);
		$desc_row = mysql_fetch_array($desc_res);
		
		$description = sprintf("%s\nAUTO-REMOVED PORTAL NAME: %s\nCORRECTED CONTACT: https://sugarinternal.sugarondemand.com/index.php?module=Contacts&action=DetailView&record=%s\n", $desc_row['description'], $name, $highestcontactid);
		
		printf("UPDATE contacts SET portal_active=0, portal_name='' WHERE id='%s';\n", $contact_id);
		printf("UPDATE contacts SET description='%s' where id='%s';\n", $description, $contact_id);

	}
	
}
