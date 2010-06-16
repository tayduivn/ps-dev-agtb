<?php
//FILE SUGARCRM flav=int ONLY
class TeamTest{
function getTeamsData($user_list){
	static $ids = array();
	$query = "SELECT tm.id, users.first_name user, teams.name team, tm.explicit_assign explicit, tm.implicit_assign implicit 
	FROM team_memberships tm 
	INNER JOIN users ON users.id=tm.user_id AND users.id in ($user_list) AND users.deleted = 0
	INNER JOIN teams ON teams.id=tm.team_id AND teams.deleted = 0
	WHERE tm.deleted = 0 ORDER BY users.first_name,teams.name";
	$result = $GLOBALS['db']->query($query);
	$data = array();
	while($row = $GLOBALS['db']->fetchByAssoc($result)){
		if(!isset($ids[$row['user'] . $row['team']])){
			$ids[$row['user'] . $row['team']] = count($ids);
		}
		$row['id'] = $ids[$row['user'] . $row['team']];
		$data[$row['id']] = $row;
		
	}
	//ksort($data);
	return $data;
}

function displayTeamsData($data, $old, $expected){
	static $displays = 0;
	$displays++;
	$str= "<table>";
	$failed = false;
	$first_row = true;
	foreach($data as $id=>$row){
		$rowFailed = false;
		if($expected[$id]['implicit'] != $row['implicit'] || $expected[$id]['explicit'] != $row['explicit']){
			$failed = true;
			$rowFailed = true;
		}
		$altered = false;
		if($first_row){
			foreach($row as $key=>$value){
				$str.= '<td>' . $key . '</td>';
			}
		}
		if($rowFailed){
			$str.= '<tr bgcolor="orangered">';
		}else if(empty($old[$id])){
			$str.= '<tr bgcolor="lightgreen">';
		}else if(strcmp(implode('', $old[$id]), implode($row)) != 0){
			$altered = true;
			$str.= '<tr bgcolor="yellow">';
		}else{
			
			$str.= '<tr>';
		}
		
		foreach($row as $key=>$value){
			if($altered && strcmp($row[$key], $old[$id][$key]) != 0){
		
					$str.= '<td bgcolor="orange" title="'.$old[$id][$key].'">' . $value . '</td>';	
				
			}else{
				$str.= '<td>' . $value . '</td>';
			}
		}
		
		
		$str .= '</tr>';
		
	if($rowFailed){
			$str.= '<tr>';
			foreach($row as $key=>$value){
				if($key != 'implicit' && $key!='explicit'){
					$str .= '<td></td>';
				}else{
					$str .='<td bgcolor="lightgreen">' . $expected[$id][$key] . '</td>';
				}
				
			}
			$str .='</tr>';
		}
		
		$first_row = false;
	}
	foreach($old as $id=>$row){
		if(empty($data[$id])){
			$str.= '<tr bgcolor="lightgray">';
			foreach($row as $key=>$value){
					$str.= '<td>' . $value . '</td>';
			}
			$str.= '</tr>';
		}
	}
	
	$str.= '</table>';
	if($failed){
		$text = 'FAILED';
		$style = '';
	}else{
		$text = 'PASSED';
		$style = '';
	
	}
		echo '<H3 onclick="toggleDisplay(\'display_'. $displays . '\');">Test:' .$displays . ' '. $text . '</H3><div style="display:' . $style . '" id="display_'. $displays . '">' . $str . '</div>';
	return !$failed;
	
}


function saveResults($results){
	$save = array();

	foreach($results as $tid=>$test){
		foreach($test as $id=>$row){

			$save[$tid][$id] = array('explicit'=>$row['explicit'], 'implicit'=>$row['implicit']);
		}
	}
$the_string =   "<?php\n" .
                    '//FILE SUGARCRM flav=int ONLY
					// created: ' . date('Y-m-d H:i:s') . "\n" .
                    "\$expected_results = " .
                    var_export_helper( $save ) .
                    ";\n?>\n";
    $fp = sugar_fopen('modules/Teams/TeamTestResults.php', "w" );
    fwrite( $fp, $the_string );
    fclose( $fp );

	
}

function CleanUp($user_list, $teams=array() ){
	$deleted = array();
	$deleted['users'] = array();
	$deleted['teams'] = $teams;
	$deleted['tm'] = array();
	$query = "SELECT users.id user, teams.id team, tm.id t
	FROM team_memberships tm 
	INNER JOIN users ON users.id=tm.user_id AND users.id in ($user_list) 
	INNER JOIN teams ON teams.id=tm.team_id AND teams.private = 1";
	$result = $GLOBALS['db']->query($query);
	while($row = $GLOBALS['db']->fetchByAssoc($result)){

		$deleted['users'][] = $row['user'];
		$deleted['teams'][] = $row['team'];
		$deleted['tm'][] = $row['t'];
	}
	$GLOBALS['db']->query("DELETE FROM users WHERE id in ('" . implode("','", $deleted['users']) . "')");
	$GLOBALS['db']->query("DELETE FROM teams WHERE id in ('" . implode("','", $deleted['teams']) . "')");
	$GLOBALS['db']->query("DELETE FROM team_memberships WHERE user_id  in ('" . implode("','", $deleted['users']) . "')");
	
}

}
$results = array();
$expected_results = array();
@include('modules/Teams/TeamTestResults.php');
$time = date('Y-m-d H:i:s');
$passes = 0;
echo 'creating user A<br>';
$user_a = new User();
$user_a->first_name = 'A';
$user_a->last_name = $time;
$user_a->user_name = 'A ' . $time;
$user_a->save();

echo 'creating user B<br>';
$user_b = new User();
$user_b->first_name = 'B';
$user_b->last_name = $time;
$user_b->user_name = 'B ' . $time;
$user_b->save();

echo 'creating user C<br>';
$user_c = new User();
$user_c->first_name = 'C';
$user_c->last_name = $time;
$user_c->user_name = 'C ' . $time;
$user_c->save();
$user_ids = array($user_a->id, $user_b->id, $user_c->id);
$user_list = "'" . implode("', '", $user_ids) . "'";
$results[] = TeamTest::getTeamsData($user_list);
$passes += TeamTest::displayTeamsData($results[count($results) -1], array(), $expected_results[count($results) -1]);

echo '<br>A reports to B<br><br>';
$user_a->reports_to_id = $user_b->id;
$user_a->save();
$user_a->update_team_memberships('');
$results[] = TeamTest::getTeamsData($user_list);
$passes +=TeamTest::displayTeamsData($results[count($results) -1], $results[count($results) -2], $expected_results[count($results) -1]);

echo '<br>B reports to C<br><br>';
$user_b->reports_to_id = $user_c->id;
$user_b->save();
$user_b->update_team_memberships('');
$results[] = TeamTest::getTeamsData($user_list);
$passes +=TeamTest::displayTeamsData($results[count($results) -1], $results[count($results) -2], $expected_results[count($results) -1]);


echo '<br>Creating Team 1<br><br>';
$team1 = new Team();
$team1->private = false;
$team1->name = 'Team 1 ' . $time;
$team1->save();

echo '<br>Adding User A to Team 1 passing in both the id and the user object<br><br>';
$team1->add_user_to_team($user_a->id, $user_a);
$results[] = TeamTest::getTeamsData($user_list);
$passes +=TeamTest::displayTeamsData($results[count($results) -1], $results[count($results) -2], $expected_results[count($results) -1]);


echo '<br>Creating Team 2<br><br>';
$team2 = new Team();
$team2->private = false;
$team2->name = 'Team 2 ' . $time;
$team2->save();

echo '<br>Adding User A to Team 2 passing in just the id<br><br>';
$team2->add_user_to_team($user_a->id);
$results[] = TeamTest::getTeamsData($user_list);
$passes +=TeamTest::displayTeamsData($results[count($results) -1], $results[count($results) -2], $expected_results[count($results) -1]);


echo '<br>Adding User C to Team 1 passing in both the id and the user object<br><br>';
$team1->add_user_to_team($user_c->id, $user_c);
$results[] = TeamTest::getTeamsData($user_list);
$passes +=TeamTest::displayTeamsData($results[count($results) -1], $results[count($results) -2], $expected_results[count($results) -1]);


echo '<br>User A no longer reports to User B<br><br>';
$old_manager = $user_a->reports_to_id;
$user_a->reports_to_id = '';
$user_a->save();
$user_a->update_team_memberships($old_manager);
$results[] = TeamTest::getTeamsData($user_list);
$passes +=TeamTest::displayTeamsData($results[count($results) -1], $results[count($results) -2], $expected_results[count($results) -1]);


echo '<br>User A Reports to User C now<br><br>';
$old_manager = $user_a->reports_to_id;
$user_a->reports_to_id = $user_c->id;
$user_a->save();
$user_a->update_team_memberships($old_manager);
$results[] = TeamTest::getTeamsData($user_list);
$passes +=TeamTest::displayTeamsData($results[count($results) -1], $results[count($results) -2], $expected_results[count($results) -1]);


echo '<br>User A Reports to User B again<br><br>';
$old_manager = $user_a->reports_to_id;
$user_a->reports_to_id = $user_b->id;
$user_a->save();
$user_a->update_team_memberships($old_manager);
$results[] = TeamTest::getTeamsData($user_list);
$passes +=TeamTest::displayTeamsData($results[count($results) -1], $results[count($results) -2], $expected_results[count($results) -1]);


echo '<br>User A Reports to User C again<br><br>';
$old_manager = $user_a->reports_to_id;
$user_a->reports_to_id = $user_c->id;
$user_a->save();
$user_a->update_team_memberships($old_manager);
$results[] = TeamTest::getTeamsData($user_list);
$passes +=TeamTest::displayTeamsData($results[count($results) -1], $results[count($results) -2], $expected_results[count($results) -1]);

echo '<H3>Passed:' . $passes . ' out of 10 </h3>';
//uncomment when you want to save the results to be used as the base results;
//TeamTest::saveResults($results);



TeamTest::CleanUp($user_list, array($team1->id, $team2->id));







?>