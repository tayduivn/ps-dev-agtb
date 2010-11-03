<?php

define('sugarEntry', true);

require_once('include/entryPoint.php');
require_once('modules/Campaigns/Campaign.php');


$res = $GLOBALS['db']->query("select id, name from campaigns where deleted = 0");

while($row = $GLOBALS['db']->fetchByAssoc($res)){
	if(strpos($row['name'], '...') === 0){
		echo "\"".from_html($row['name'])."\"\n";
		$end_campaign_name = str_replace("...", "", trim(from_html($row['name'])));
		$res_new = $GLOBALS['db']->query("select id, name, assigned_user_id from campaigns where name like '%".$GLOBALS['db']->quote($end_campaign_name)."' and name not like '...%' and deleted = 0");
		//echo "select id, name from campaigns where name like '%".to_html($end_campaign_name)."' and name not like '...%' and deleted = 0\n";
		$row_new = $GLOBALS['db']->fetchByAssoc($res_new);
		echo "\"".$row_new['name']."\" \"{$row_new['id']}\"\n";
		$res_two = $GLOBALS['db']->query("select count(*) count from interactions where campaign_id = '{$row['id']}' and deleted = 0");
		$row_two = $GLOBALS['db']->fetchByAssoc($res_two);
		echo $row_two['count']."\n";
		
		$query = "update interactions set campaign_id = '{$row_new['id']}' where campaign_id = '{$row['id']}'";
		echo $query."\n";
		$GLOBALS['db']->query($query);
	}
}
