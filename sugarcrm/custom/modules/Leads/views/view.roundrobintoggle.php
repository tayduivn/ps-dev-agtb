<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/SugarView.php');

class LeadsViewRoundrobintoggle extends SugarView
{	
    /**
     * Constructor
     */
 	public function __construct()
    {
 		parent::SugarView();
    }
    
 	/** 
     * @see SugarView::display()
     */
 	public function display()
    {
$allowedUsers = array(
    'vrandazzo',
    'rmeeker',
    'deepali',
    'dtam',
);
if(!in_array($GLOBALS['current_user']->user_name, $allowedUsers)){
    sugar_die('You do not have access to this page. Please contact <a href="mailto:internalsystems@sugarcrm.com">Internal Systems</a> if you think you should have access.');
}

echo "<h3>Round Robin Toggle</h3>\n";

// BEGIN INITIAL FORM - SKIPPED ONCE THEY SUBMIT
if(empty($_POST['submit'])){
$user_query = "
select id, department, first_name, last_name, user_name
from users
where users.status = 'Active' and (users.department in ('Sales - Inside - West', 'Sales - Inside - Southeast', 'Sales - Inside - Northeast', 'Sales - Inside - Central') or users.department like 'Sales - Inside - %NoRobin')
";

$user_res = $GLOBALS['db']->query($user_query);

$output =<<<EOQ
<form method=post action="{$_SERVER['REQUEST_URI']}" name=financeform>
<table border="0" cellpadding="0" cellspacing="0" width="80%">
    <tr>
    <td class="tabDetailViewDL">
    User
    </td>
    <td class="tabDetailViewDL">
	West
	</td>
	<td class="tabDetailViewDL">
	Northeast
	</td>
	<td class="tabDetailViewDL">
	Southeast
	</td>
	<td class="tabDetailViewDL">
        Central
        </td>
	<td class="tabDetailViewDL">
	No Round Robin
	</td>
	</tr>
EOQ;
while($user_row = $GLOBALS['db']->fetchByAssoc($user_res)){
	$west = false;
	$northeast = false;
	$southeast = false;
	$central = false;
	$norobin = false;
	$robin_value = substr($user_row['department'], strrpos($user_row['department'], "-") + 2) . "NoRobin";
	if(strpos($user_row['department'], "NoRobin") !== false){
		$norobin = 'checked';
		$robin_value = substr($user_row['department'], strrpos($user_row['department'], "-") + 2);
	}
	else if($user_row['department'] == 'Sales - Inside - West'){
		$west = 'checked';
	}
	else if($user_row['department'] == 'Sales - Inside - Southeast'){
		$southeast = 'checked';
	}
	else if($user_row['department'] == 'Sales - Inside - Northeast'){
		$northeast = 'checked';
	}
	else if($user_row['department'] == 'Sales - Inside - Central'){
                $central = 'checked';
        }
	else{
		continue;
	}
	
	$output .= "\t<tr>\n";
	$output .= "\t<td class=\"tabDetailViewDL\">\n";
	$output .= "\t{$user_row['first_name']} {$user_row['last_name']}\n";
	$output .= "\t</td>\n";
	$output .= "\t<td class=\"tabDetailViewDL\">\n";
	$output .= "\t<input type=\"radio\" name=\"roundrobin{$user_row['id']}\" value=\"West\" $west>\n";
	$output .= "\t</td>\n";
	$output .= "\t<td class=\"tabDetailViewDL\">\n";
	$output .= "\t<input type=\"radio\" name=\"roundrobin{$user_row['id']}\" value=\"Northeast\" $northeast>\n";
	$output .= "\t</td>\n";
	$output .= "\t<td class=\"tabDetailViewDL\">\n";
	$output .= "\t<input type=\"radio\" name=\"roundrobin{$user_row['id']}\" value=\"Southeast\" $southeast>\n";
	$output .= "\t</td>\n";
	$output .= "\t<td class=\"tabDetailViewDL\">\n";
        $output .= "\t<input type=\"radio\" name=\"roundrobin{$user_row['id']}\" value=\"Central\" $central>\n";
        $output .= "\t</td>\n";
	$output .= "\t<td class=\"tabDetailViewDL\">\n";
	$output .= "\t<input type=\"radio\" name=\"roundrobin{$user_row['id']}\" value=\"$robin_value\" $norobin>\n";
	$output .= "\t</td>\n";
	$output .= "\t</tr>\n";
}
$output .=<<<EOQ
</table>
<input name=submit type=submit value="Submit">
</form>
EOQ;

echo $output;
}
// END INITIAL FORM - SKIPPED ONCE THEY SUBMIT
else{
	$users_to_update = array();
	foreach($_POST as $key => $value){
		$prefix = substr($key, 0, 10);
		if($prefix != 'roundrobin')
			continue;
		
		$user_id = substr($key, 10);
		$users_to_update[$user_id] = 'Sales - Inside - '.$value;
	}
	
	foreach($users_to_update as $user_id => $department){
		$user_row = $GLOBALS['db']->fetchByAssoc($GLOBALS['db']->query("select first_name, last_name, department from users where id = '$user_id'"));
		if($user_row['department'] != $department){
			$update_user_query = "update users set department = '$department' where id = '$user_id'";
			echo "Updating {$user_row['first_name']} {$user_row['last_name']}'s department from '{$user_row['department']}' to '$department'<BR>";
			$GLOBALS['db']->query($update_user_query);
		}
		else{
			echo "Department didn't change for {$user_row['first_name']} {$user_row['last_name']}, no action taken.<BR>";
		}
	}
	
}
		
	}


}
?>
