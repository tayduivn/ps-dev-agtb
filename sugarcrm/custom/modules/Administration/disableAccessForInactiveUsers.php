<?php

// SADEK - Means to remove access for Inactive users by adding them all to the 'No access' role

// The id of the Role you'd like to add the users to - In this case, it's the "No access" role, disabling the users from all modules
$role_id = "e84dc942-e2ca-3502-8fb9-4686e9957209";
$role_name = "No access";

// Get all the users that are already in this role and exclude them, since they're already in the role.
$res = mysql_query("select users.id, users.user_name from users inner join acl_roles_users on users.id = acl_roles_users.user_id and acl_roles_users.role_id = \"$role_id\"");
$exclude_array = array();
while($row = mysql_fetch_row($res)){
	$exclude_array[$row[0]] = $row[1];
}

// Get all Inactive users to add them
$res = mysql_query("select users.id, users.user_name from users where status=\"Inactive\"");
while($row = mysql_fetch_row($res)){
	$all_array[$row[0]] = $row[1];
}

// Diff the two arrays to get the users you need to add to the role.
$the_array = array_diff($all_array, $exclude_array);

// Add them by inserting rows
$added = false;
foreach($the_array as $user_id => $user_name){
	$guid = create_guid();
	mysql_query("insert into acl_roles_users set id=\"$guid\", role_id=\"$role_id\", user_id=\"$user_id\", date_modified=NOW(), deleted=0");
	echo "Added <i>$user_name</i> to the '$role_name' role.<BR>";
	$added = true;
}

if(!$added){
	echo "No users found that needed to be added to the '$role_name' role.";
}

?>
