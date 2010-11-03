<?php

define('sugarEntry', true);
chdir('../..');
require_once('include/entryPoint.php');

    function processPageViews(&$return_data, $db_row){
            $search =  array('Visitor: ', ' page views');
            $replace = array('', '');
            $number = str_replace($search, $replace, $db_row['name']);

            if(!empty($return_data['number'])){
                // We increment the value, since we already set the 
                $return_data['number'] += $number;
            }
            else{
                // Otherwise we set the number and find the associated interaction to update, if it exists
                $return_data['number'] = $number;
            }
    }


$main_query = "select distinct source_id from interactions where visitor_activity_id is not null and name like 'Visitor:%' and deleted = 0";

$main_res = $GLOBALS['db']->query($main_query);
$count = 0;
$count_two = 0;
while($main_row = $GLOBALS['db']->fetchByAssoc($main_res)){
	$page_views = array();
	$sub_query = "select id, name from interactions where source_id = '{$main_row['source_id']}' and visitor_activity_id is not null and name like 'Visitor:%' and deleted = 0";
	$sub_res = $GLOBALS['db']->query($sub_query);
	$ids_to_delete = array();
	$last_id = '';
	while($sub_row = $GLOBALS['db']->fetchByAssoc($sub_res)){
		echo $sub_row['name']."\n";
		processPageViews($page_views, $sub_row);
		$last_id = $sub_row['id'];
		$ids_to_delete[$sub_row['id']] = $sub_row['id'];
		$count_two++;
	}
	echo $page_views['number']."\n";
	unset($ids_to_delete[$last_id]);
	print_r($ids_to_delete);
	echo $last_id."\n";
	$update = "update interactions set name = '{$page_views['number']}', type = 'Page Views' where id = '{$last_id}'";
	$delete = "delete from interactions where id in ('".implode("','", $ids_to_delete)."')";
	$GLOBALS['db']->query($update);
	$GLOBALS['db']->query($delete);
	$count++;
}

echo "Total touchpoints: ".$count."\n";
echo "Total interactions: ".$count_two."\n";
