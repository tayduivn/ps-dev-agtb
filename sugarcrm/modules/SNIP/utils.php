<?php
function get_unlinked_email_query_via_link($params)
{
    $relation = $params['link'];
	$bean = $GLOBALS['app']->controller->bean;
    if(empty($bean->$relation)) {
        $bean->load_relationship($relation);
    }
    $rel_module = $bean->$relation->getRelatedModuleName();
    $rel_join = $bean->$relation->getJoin(array(
    	'join_table_alias' => 'link_bean',
    	'join_table_link_alias' => 'linkt',
    ));
    $return_array['select']='SELECT DISTINCT emails.id';
    $return_array['from']='FROM emails ';
	$return_array['join'] = " JOIN emails_email_addr_rel eear ON eear.email_id = emails.id AND eear.deleted=0
		    	JOIN email_addr_bean_rel eabr ON eabr.email_address_id=eear.email_address_id AND eabr.bean_module = '$rel_module' AND eabr.deleted=0
				JOIN (select '{$bean->id}' as id) {$bean->table_name}
				$rel_join AND link_bean.id = eabr.bean_id
				LEFT JOIN emails_beans direct_link ON direct_link.bean_id = '{$bean->id}' AND direct_link.email_id = emails.id
";
    // exclude directly linked emails
    $return_array['where']="WHERE direct_link.bean_id IS NULL";
	// Special case for Case
    if($bean->object_name == "Case" && !empty($bean->case_number)) {
        $where = str_replace("%1", $bean->case_number, 	$bean->getEmailSubjectMacro());
	    $return_array["where"] .= " AND emails.name LIKE '%$where%'";
    }
	return $return_array;
}

function get_beans_by_email_addr($module_dir)
{
    $bean = $GLOBALS['app']->controller->bean;
    $module_dir = $module_dir['module'];
    $module = get_module_info($module_dir);
    $return_array['select'] = "SELECT DISTINCT {$module->table_name}.id ";
    $return_array['from'] = "FROM {$module->table_name} ";
    $return_array['join'] = " JOIN emails_email_addr_rel eear ON eear.email_id = '$bean->id' AND eear.deleted=0
		    	JOIN email_addr_bean_rel eabr ON eabr.email_address_id=eear.email_address_id AND eabr.bean_id = {$module->table_name}.id AND eabr.bean_module = '$module_dir' AND eabr.deleted=0
				LEFT JOIN emails_beans direct_link ON direct_link.bean_id = '{$bean->id}' AND direct_link.email_id = {$module->table_name}.id
";
    // exclude directly linked emails
    $return_array['where']="WHERE direct_link.bean_id IS NULL";
    return $return_array;
} // fn
