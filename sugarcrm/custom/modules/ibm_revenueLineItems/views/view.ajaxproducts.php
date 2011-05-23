<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.ajax.php');

class ibm_revenueLineItemsViewAjaxproducts extends ViewAjax {

	var $level_internal_to_ibm = array();
	var $level_ibm_to_internal = array();
	
	function __construct(){
		
		// load internal and ibm levels from vardefs
		$bean = new ibm_revenueLineItems();
		foreach($bean->field_defs as $field) {
			if(isset($field['revitems_level']) && isset($field['revitems_level_ibm'])) {
				$this->level_internal_to_ibm[$field['revitems_level']] = $field['revitems_level_ibm'];
				$this->level_ibm_to_internal[$field['revitems_level_ibm']] = $field['revitems_level'];
			}
		}
		parent::ViewAjax();
	}

	function display(){

		$result = array();
		
		// search query 
		if(! empty($_REQUEST['type']) && $_REQUEST['type'] == 'search'
				&& isset($_REQUEST['level']) && isset($_REQUEST['q'])
				&& isset($_REQUEST['searchtype'])) {

			// jostrow -- hacky fix, but okay for now
			// sometimes the EditView doesn't send anything for 'searchtype' -- we should default to 'product'
			if (empty($_REQUEST['searchtype'])) {
				$_REQUEST['searchtype'] = 'product';
			}

			// translate internal level id to ibm level id (used in db table)
			$level = $this->level_internal_to_ibm[$_REQUEST['level']];
	
			// base query
			$sql = 'SELECT id,name,level FROM ibm_revenuelineitems_products 
						WHERE type = "'.$_REQUEST['searchtype'].'" ';

			// search name field to find matches on
			if(!empty($_REQUEST['q'])) {
				$sql .= ' AND name LIKE "%'.$_REQUEST['q'].'%" ';
			}
			
			
			// level specific search
			if($_REQUEST['level'] <> '0') {
				$sql .= ' AND level = "'.$level.'" ';
			} else {
				$sql .= ' AND level != "10" ';
			}

			// parentid specific search
			if(! empty($_REQUEST['parentid'])) {
				$sql .= ' AND parent_id = "'.$_REQUEST['parentid'].'" ';
			}

			// sort them from level 10 -> 40
			$sql .= ' ORDER BY level ASC ';
			
			// limit our result, but for sei non global searches return everything !
			if($_REQUEST['searchtype'] == 'product' || $_REQUEST['level'] == '0') {
				$sql .= ' LIMIT 0,20 ';
			}
			
			$q_search = $GLOBALS['db']->query($sql);
			while($match = $GLOBALS['db']->fetchByAssoc($q_search)) {
				$level = $this->level_ibm_to_internal[$match['level']];

				// we alter the text if using global search to include the level
				if($_REQUEST['level'] == 0) {
					$name = 'Level '.$match['level'].': '.$match['name'];
				} else {
					$name = $match['name'];
				}
				$result['option_items'][] = array('key' => $match['id'], 'text' => $name, 'level' => $level);
			}
		}

		// lookup query
		if(! empty($_REQUEST['type']) && $_REQUEST['type'] == 'lookup'
				&& ! empty($_REQUEST['level']) && ! empty($_REQUEST['q'])) {

			$level = $_REQUEST['level'];
			$id = $_REQUEST['q'];

			while($level > 0) {
				$sql = 'SELECT parent.id, parent.name, parent.level
						FROM ibm_revenuelineitems_products child
						INNER JOIN ibm_revenuelineitems_products parent
							ON parent.id = child.parent_id
						WHERE child.id = "'.$id.'"';
				$q_lookup = $GLOBALS['db']->query($sql);
				if($lookup = $GLOBALS['db']->fetchByAssoc($q_lookup)) {
					$parent_level = $this->level_ibm_to_internal[$lookup['level']];
					// remark: child_level is passed as well to have a backreference to trigger image animations
					$result['parents'][] = array('key' => $lookup['id'], 'text' => $lookup['name'], 'level' => $parent_level, 'childlevel' => $_REQUEST['level']);
					$id = $lookup['id'];
				}
				$level = $level - 1;
			}

		}

		// load query
		if(! empty($_REQUEST['type']) && $_REQUEST['type'] == 'load'
			&& ! empty($_REQUEST['q'])) {

			$sql = 'SELECT id,name,level FROM ibm_revenuelineitems_products WHERE id = "'.$_REQUEST['q'].'"';
			$q_load = $GLOBALS['db']->query($sql);
			if($product = $GLOBALS['db']->fetchByAssoc($q_load)) {
				$level = $this->level_ibm_to_internal[$product['level']];
				$result['product'][] = array('text' => $product['name'], 'level' => $level, 'key' => $product['id']);
			}
		}

		// output json code to client
		echo "{$_REQUEST['callback']}(" . json_encode($result) . ");";
	}

	// return actual ibm level for internal level
	function getIbmLevel($level) {
				
	}
}
