<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
//FILE SUGARCRM flav=int ONLY



/**
 * class Queue
 * TODO: writeup of class functionality
 */
class Queue extends SugarBean {
	// fielddefs
	var $id;
	var $deleted;
	var $date_entered;
	var $date_modified;
	var $modified_user_id;
	var $created_by;
	var $created_by_name;
	var $modified_by_name;
	var $name;
	var $status;	// Active/Inactive flag
	var $owner_id; // related to other beans
	var $queue_type;
	var $workflows;
	var $persistent_memory;
	// related fielddefs
	var $child_queues;
	var $child_queues_delete;
	var $parent_queues;
	var $parent_queues_delete;
	var $child_ids;
	var $parent_ids;
	var $objects;
	var $queues_workflow;
	var $queues_emails;	// collection of emails beans
	var $queuedItems;
	// object attributes
	var $object_name			= 'Queue';
	var $table_name				= 'queues';
	var $module_dir				= 'Queues';
	var $new_schema				= true;
	var $process_save_dates 	= true;
	var $queueCachePath			= '';
	var $queueCacheFile			= 'Queue.cache.php';
	// relationship stuff
	var $queue_id;
	var $relationship_fields 	= array('queue_id' => 'parent_queues',
										'queue_id' => 'child_queues',
										'queue_id' => 'queues_emails',
										'queue_id' => 'queues_workflow');
	var $required_fields		= array('name');

	/** 
	 * Sole constructor
	 */
	function Queue() {
		parent::SugarBean();
		
		$this->queueCachePath = $GLOBALS['sugar_config']['cache_dir'].'modules/Queues';
		
		// these load if SugarBean fails to.
		if(empty($this->field_defs)) {
			require_once('modules/Queues/vardefs.php');
			$this->field_defs = $dictionary['Queues']['fields'];
		}
		if(empty($this->column_fields) || empty($this->list_fields) || empty($this->required_fields)) {
			include('modules/Queues/field_arrays.php');
			$this->column_fields = $fields_array['Queues']['column_fields'];
			$this->list_fields = $fields_array['Queues']['list_fields'];
			$this->required_fields = $fields_array['Queues']['required_fields'];
		}
	}
	
	function forceLoadFieldDefs() {
		$dictionary = '';
		require('modules/Queues/vardefs.php');
		
		$this->field_defs = $dictionary['Queues']['fields']; 	
	}
	
	/**
	 * scans 2 directories for Queue-specific workflows to assign to queues
	 * @return	$workflows	multi-dimensional array of key/values of
	 * workflow_id/function
	 */
	function getWorkflows() {
		$workflows = array();
		if(file_exists('custom/modules/Queues/actions_array.php')) {
			require_once('custom/modules/Queues/actions_array.php');
			$workflows = array_merge($workflows, $action_meta_array);
		}
		if(file_exists('modules/Queues/StandardWorkflows.php')) {
			require_once('modules/Queues/StandardWorkflows.php');
			$workflows = array_merge($workflows, $standards);
		}
		
		return $workflows;
	}
	
	
	
	/**
	 * moves items from a parent queue into this
	 * 
	 * @param	$limit		int max number of items to move
	 */
	function moveItemsIntoMyQueue($limit) {
		$qParent = 'SELECT parent_id FROM queues_queue ' .
				'	LEFT JOIN queues ON (queues_queue.queue_id = queues.id) ' .
				'	WHERE queues_queue.deleted = 0 AND queues.deleted=0 ' .
				'	AND queues.status = "Active" AND queue_id = "'.$this->id.'"';
		
		$rParent = $this->db->query($qParent);
		if($this->db->getRowCount($rParent) > 0) {
			$qMove = 'UPDATE queues_beans SET queue_id = "'.$this->id.'" WHERE deleted = 0 AND queue_id IN (';
		
			while($aParent = $this->db->fetchByAssoc($rParent)) {
				$qMove .= "'".$aParent['parent_id']."', "; 
			}
		
			$qMove = substr_replace($qMove, '', -2);
			
			$qMove .= ') ORDER BY date_entered ASC ';
			$qMove .= ' LIMIT '.$limit;
			
			$this->db->query($qMove);
		}
	}
	
	/** 
	 * This function gets all valid queue items recursively
	 * @param	$seen	breadcrumb trail array of queue IDs
	 * @return array Objects
	 */
	function getQueueItemsRecursively($seen = array()) {
		$thisItems = $this->getQueueItems();//_pp($this->name.' has '.count($thisItems).' items');
		
		$q = 'SELECT parent_id FROM queues_queue WHERE queue_id = "'.$this->id.'"';
		$r = $this->db->query($q);

		if($this->db->getRowCount($r) > 0) {
			/* loop through parent queues, get them to return their items*/
			$parentItems = array();
			while($a = $this->db->fetchByAssoc($r)) {
				
				if(in_array($a['parent_id'], $seen)) { // seen this parent already
					continue;
				} else { // new queue to get items from
					$seen[] = $a['parent_id'];	/** add this queue's parent to seen list */
					$parentQueue = new Queue();	/** instantiate parent */
					$parentQueue->retrieve($a['parent_id']);
					$parentItems = array_merge($parentItems, $parentQueue->getQueueItemsRecursively($seen));
				}
			}
			return array_merge($parentItems, $thisItems);
		} else {
			// no parents.
			return $thisItems;
		}
	}
	
	/**
	 * This function gets SugarBeans from one Queue
	 */
	function getQueueItems() {
		$r = $this->db->query('SELECT object_id, module_dir FROM queues_beans WHERE deleted = 0 AND queue_id = "'.$this->id.'"');
		if($this->db->getRowCount($r) > 0) {
			global $beanList;
			$returnArray = array();
			while($a = $this->db->fetchByAssoc($r)) {
				// hardcode
				$a['module_dir'] = 'Emails';
				$objectName = $beanList[$a['module_dir']];
				
				require_once('modules/'.$a['module_dir'].'/'.$objectName.'.php');
				$newObject = new $objectName();
				$newObject->retrieve($a['object_id']);
				$returnArray[] = $newObject;
			}
			return $returnArray;
		} else {
			return;
		}
	}
	
	
	/**
	 * This function returns the number of items in queue
	 */
	function getNumberOfQueuedItems() {
		$r = $this->db->query('SELECT count(*) AS count FROM queues_beans WHERE queue_id = "'.$this->id.'" AND deleted=0');
		$a = $this->db->fetchByAssoc($r);
		if($a['count'] == 0) {
			return 0;
		} else {
			return $a['count'];
		}
	}
	

	
	/**
	 * This function retrieves a queue based on its owner id
	 * @param	$id		GUID of owner object
	 */
	function getQueueFromOwnerId($id, $self=false) {
		$q = "SELECT id FROM queues WHERE owner_id = '".$id."'";
		$r = $this->db->query($q);
		if($this->db->getRowCount($r) > 0) {
			$a = $this->db->fetchByAssoc($r);
			
			if(!$self) {
				$queue = new Queue();
//				$queue->disable_row_level_security = true;
				$queue->retrieve($a['id']);
				return $queue;
			} else {
				$this->retrieve($a['id']);
				return;
			}
		} else {
			return false;
		}
	}
	
	/** 
	 * takes all existing queues and writes it to a cached file for performance
	 */
	function writeToCache() {
		if(!function_exists('mkdir_recursive')) {
			
		}
		if(!function_exists('write_array_to_file')) {
			
		}
		// cache results
		if(!file_exists($this->queueCachePath) || !file_exists($this->queueCachePath.'/'.$this->queueCacheFile)) {
			// create directory if not existent
			mkdir_recursive($this->queueCachePath, false);
		}
		// write cache file
		write_array_to_file('queuesCached', $this->getQueuesWithGuids(), $this->queueCachePath.'/'.$this->queueCacheFile);
	}
	
	/**
	 * This function gathers all parent and children queues
	 */
	function getQueues() {
		if(empty($this->db)) {
			
			$this->db = DBManagerFactory::getInstance();
		}
		$defaultWhere = " AND queues.deleted = 0 AND queues_queue.deleted=0 AND queues.status = 'Active'";

		// child queues
		$q = "	SELECT queue_id FROM queues_queue LEFT JOIN queues ON (queues.id = queues_queue.queue_id)
 				WHERE parent_id = '".$this->id."' ".$defaultWhere;
		$r = $this->db->query($q);
		$rows = $this->db->getRowCount($r);
		$children = array();
		if($rows > 0) {
			while($a = $this->db->fetchByAssoc($r)) {
				$children[] = $a['queue_id'];
			}
		}
		$this->child_ids = $children;
		
		// parent queues
		$q2 = "	SELECT parent_id FROM queues_queue LEFT JOIN queues ON (queues.id = queues_queue.queue_id) 
				WHERE queue_id = '".$this->id."' ".$defaultWhere;	
		$r2 = $this->db->query($q2);
		$rows2 = $this->db->getRowCount($r2);
		$parents = array();
		if($rows2 > 0) {
			while($a2 = $this->db->fetchByAssoc($r2)) {
				$parents[] = $a2['parent_id'];	
			}	
		}
		$this->parent_ids = $parents;
	}
	
		
	/**
	 * Returns an associative array with GUID => Queue Name of all queues in the
	 * db
	 */
	function getQueuesWithGuids() {
		$qQueues = "SELECT id, name FROM queues WHERE deleted = 0 AND status='Active'";
		$rQueues = $this->db->query($qQueues);
		while($aQueues = $this->db->fetchByAssoc($rQueues)) {
			$queueArray[$aQueues['id']] = $aQueues['name'];
		}
		return $queueArray;
	}
	
	
	
	/**
	 * gets a piece of persistent_memory, or creates it if non-existent
	 * @param	$key	key to the array for the value requested
	 * @return	$value	the value to the persistent array
	 */
	function getPersistentMemory($key) {
		$r = $this->db->query('SELECT persistent_memory FROM queues WHERE id = "'.$this->id.'"');
		$a = $this->db->fetchByAssoc($r);
		$persistent_memory = unserialize(base64_decode($a['persistent_memory']));
		if(empty($persistent_memory[$key])) {
			_pp($this->name.' did not return anything from $persistent_memory with key of '.$key);
			return false;
		} 
		$this->persistent_memory = $persistent_memory;
		return $persistent_memory[$key];
	}
	
	/**
	 * sets a value in the persistent_memory array
	 * @param	$key	key to the array
	 * @param	$value	value that is stored
	 */
	function setPersistentMemory($key, $value) {
		$this->persistent_memory[$key] = $value;
		$b64ser = base64_encode(serialize($this->persistent_memory));
		$r = $this->db->query('UPDATE queues SET persistent_memory = "'.$b64ser.'" WHERE id = "'.$this->id.'"');
	}
	
	/**
	 * Removes an item from queue
	 * @param	$id		id of the bean to be removed
	 */
	function removeItemFromQueue($parentQueue, $itemId) {
		_pp('deleting Queue Item from '.$parentQueue->name.'\'s Queue.');
		$q = 'UPDATE queues_beans SET deleted=1 WHERE queue_id = "'.$parentQueue->id.'" AND object_id = "'.$itemId.'"';
		$this->db->query($q);
	}
	
	/** 
	 * Adds bean to queue_beans table then redistributes them if a workflow
	 * exists for it.
	 * @param	$beanId		the bean id
	 * @param	$beanDir	the module_dir of the bean
	 * @param	$beanName	needed for workflows
	 * @param	$remove		flag to remove the item from the Queue beforehand
	 */
	function addItemToQueue($beanId, $beanDir, $beanName, $remove=false, $parentQueue='') {
		if($remove) {
			$this->removeItemFromQueue($parentQueue, $beanId);
		}
		
		$q = "INSERT INTO queues_beans 
				(id, deleted, date_entered, date_modified, queue_id, module_dir, object_id) 
				VALUES (
					'".create_guid()."',
					0,
					'".gmdate($GLOBALS['timedate']->get_db_date_time_format())."',
					'".gmdate($GLOBALS['timedate']->get_db_date_time_format())."',
					'".$this->id."',
					'".$beanDir."',
					'".$beanId."')"; 
		$r = $this->db->query($q);
		
		$GLOBALS['log']->debug($q);
		
		if(!empty($this->workflows)) {
			$GLOBALS['log']->debug('InboundEmail @queue->addItemToQueue() found Queue ('.$this->name.') workflow: '.$this->workflows);
			// have to re-require this every time if we go deeper than 1 queue - something stomps out the values in the queue object
			require('modules/Queues/StandardWorkflows.php');
		
			$func = "return \$this->".$standards[$this->workflows]['function'];
			$GLOBALS['log']->debug('evaling: '.$func);
			if(eval($func)) {

				$GLOBALS['log']->debug('Workflow [ '.$standards[$this->workflows]['function'].' ] completed successfully.');
			} else {
				$GLOBALS['log']->fatal('Workflow [ '.$standards[$this->workflows]['function'].' ] completed successfully.');
				_pp('died in NO WORKFLOW');
			}
			
		} else {
			$GLOBALS['log']->debug('InboundEmail found Queue ('.$this->name.') with NO WORKFLOW!');
		}
		
		return;
	}
	
	/**
	 * checks Child queues against inactive Users and Queues 
	 */
	function getActiveQueues() {
		global $app_list_strings;
//		_ppd($this->child_queues);
		if(empty($this->child_queues)) {
			$this->load_relationship('child_queues');
		}
		$childIds = $this->child_queues->get();
		
		if(!empty($childIds)) {
			$childIdsRev = array_flip($childIds);
			
			$r = $this->db->query('SELECT queues.id AS qid FROM queues LEFT JOIN users ON owner_id = users.id 
					WHERE queues.deleted=0 AND users.deleted=0 AND users.status = "'.$app_list_strings['user_status_dom']['Inactive'].'" ' .
							'OR queues.status="'.$app_list_strings['user_status_dom']['Inactive'].'"');
			if($this->db->getRowCount($r) > 0) {
				while($a = $this->db->fetchByAssoc($r)) {
					$inactive[] = $a['qid'];
				}
				
				foreach($childIds as $k => $id) {
					if(in_array($id, $inactive)) {
						unset($childIdsRev[$id]);
					}
				}
				$childIds = array_flip($childIdsRev);
				sort($childIds);
				return $childIds;
			} else {
				sort($childIds);
				return $childIds;
			}
		} else {
			return false; // no child queues
		}
	}
	/**
	 * helper function for leastBusy()
	 * this will dig down through child queues that have workflows that
	 * redistribute to other queues.  it will return on queues that have 'none'
	 * or 'manualPick' for workflows.
	 * @param	$qid		GUID of the queue being dug
	 */
	function getItemCountsRecursively($qid) {
		$queue = new Queue();
		$queue->retrieve($qid);
		$queueChildIds = $queue->getActiveQueues();
		$count = 0;
		if(!empty($queueChildIds) && $queue->workflows != 'none' && $queue->workflows != 'manualPick') {
			_pp('leastBusy found child-queues for '.$queue->name.' -- going deeper');
			foreach($queueChildIds as $k => $id) {
				$count = $count + $queue->getItemCountsRecursively($id);
			}
			return $count;
		} else {
			_pp('leastBusy didn\'t find child-queues for '.$queue->name);
			$r = $queue->db->query('SELECT count(id) AS c FROM queues_beans WHERE deleted=0 AND queue_id = "'.$queue->id.'"');
			$a = $queue->db->fetchByAssoc($r);
			$count = $a['c'];
//			_ppd('SELECT count(id) AS c FROM queues_beans WHERE deleted=0 AND queue_id = "'.$queue->id.'"');
			return $count;
		}
	}
	///////////////////////////////////////////////////////////////////////////
	////	WORKFLOW FUNCTIONS
	///////////////////////////////////////////////////////////////////////////
	function doNothing() {
		
		_pp($this->name.' is doNothing()');
		return true;
	}
	
	function roundRobin($beanId, $beanName) {
		_pp('----- IN ROUNDROBIN for queue: '.$this->name.'---Using object_name ['.$beanName.'] -----');
		$GLOBALS['log']->info('InboundEmail got distribution type of "roundRobin"');
		global $beanFiles;
		
		// get the child queues' IDs
		if(!$this->load_relationship('child_queues')) {
			$GLOBALS['log']->debug('Workflow roundRobin COULD NOT LOAD RELATIONSHIPS!');
			return false;
		} else {
			$childIds = $this->getActiveQueues();
			$childIdsRev = array_flip($childIds);
			//_pp($childIds);
		}

		// check if we have a last_robin persistent_memory
		if(is_array($childIds) && !empty($childIds)) {
			$lastRobin = $this->getPersistentMemory('last_robin');
			if($lastRobin) { // above returns false on no value
				// $lastRobin contains the id of the Queue last assigned an item
				// get next in sequence from $childIdsRev
				//_pp('childIdsRev[lastRobin]: '.$childIdsRev[$lastRobin]);
				if(!empty($childIdsRev[$lastRobin]) || $childIdsRev[$lastRobin] === 0) {
					$nextKey = $childIdsRev[$lastRobin];
					$testNext = $nextKey + 1;
					if(!empty($childIds[$testNext])) {
						$nextId = $childIds[$testNext];
					} else { // we've come full circle
						//_pp('[ '.$this->name.' ] we\'ve come full circle, starting at 0');
						$nextId = $childIds[0];
					}
				} else {  // queue structure changes, and the last_robin key is no longer valid
					//_pp('[ '.$this->name.' ] lastRobin doesn\'t exist in childIds array - starting at 0.');
					$nextId = $childIds[0];
				}
			} else {	// we start at key 0 since this is a new round-robin distribution
				//_pp('[ '.$this->name.' ] got NO lastRobin - starting at 0');
				$nextId = $childIds[0];
			}
			// with $nextId set above, we get the target queue
			$nextQ = new Queue();
			$nextQ->retrieve($nextId);
			// now set last_robin to nextQ's id
			//_pp('lastRobin: '.$lastRobin.'::nextRobin:'.$nextId);
			$this->setPersistentMemory('last_robin', $nextId);
			
			// now that we have a target Queue, prep the item to add to it
			if(!class_exists($beanName)) {
				require($beanFiles[$beanName]);
			}
			$addBean = new $beanName();
			$addBean->retrieve($beanId);
			
			_pp('adding item for: '.$nextQ->name);
			$nextQ->addItemToQueue($addBean->id, $addBean->module_dir, $addBean->object_name, true, $this);
			//_pp('finished item for: '.$nextQ->name);
			return true;
		} else {
			// no child queues to distribute to
			_pp('END DISTRIBUTION hit leaf node for queues: '.$this->name);
			return false;
		}
	}
	
	function leastBusy($beanId, $beanName) {
		_pp('----- IN LEASTBUSY for queue: '.$this->name.'---Using object_name ['.$beanName.'] -----');
		$GLOBALS['log']->info('InboundEmail got distribution type of "leastBusy"');
		global $beanFiles;
		
		// get the child queues' IDs
		if(!$this->load_relationship('child_queues')) {
			$GLOBALS['log']->fatal('Workflow leastBusy COULD NOT LOAD RELATIONSHIPS!');
			return false;
		}
		
		$childIds = $this->getActiveQueues();
		$counts = array();
		_pp('Initialized $counts array for '.$this->name);
		foreach($childIds as $k => $id){
			$counts[$id] = $this->getItemCountsRecursively($id);
		}
		_pp($this->name.' finished finding child-queues\' counts recursively:');
		_pp($counts);
		asort($counts); // lowest to highest counts
		$countsKeys = array_flip($counts); // now IDs are values
		$leastBusy = array_shift($countsKeys); // pop top value (lowest item count)
		$nextQueue = new Queue();
		$nextQueue->retrieve($leastBusy);
		
		
		// instantiate new class of passed bean info
		$addBean = new $beanName();
		$addBean->retrieve($beanId);
		
		
		_pp('Adding item ('.$addBean->object_name.':'.$addBean->name.') to Queue ('.$nextQueue->name.')');
		$nextQueue->addItemToQueue($addBean->id, $addBean->module_dir, $addBean->object_name, true, $this);
//		_ppd('got this far');
		return true;
	}
	

	
	function manualPick() {
	
	}
	
	///////////////////////////////////////////////////////////////////////////
	////	SugarBean OVERRIDES
	//////////////////////////////////////////////////////////////////////////
	/**
	 * Override's SugarBean's
	 */
	function create_export_query($order_by, $where, $show_deleted = 0) {
		return $this->create_new_list_query($order_by, $where,array(),array(), $show_deleted = 0);
	}
	
	/**
	 * Override's SugarBean's
	 */
	/**
	 * Override's SugarBean's
	 */
	function fill_in_additional_list_fields() {
		$this->fill_in_additional_detail_fields();
	}

	/**
	 * Override's SugarBean's
	 */
	function fill_in_additional_detail_fields() {
		$this->created_by_name = get_assigned_user_name($this->created_by);
		$this->modified_by_name = get_assigned_user_name($this->modified_user_id);
		$this->queuedItems = $this->getNumberOfQueuedItems();
	}

	/**
	 * Override's SugarBean's
	 */
	function get_list_view_data(){
		global $standards;
		if(empty($standards)) { 
			include('modules/Queues/StandardWorkflows.php');
		}
		$temp_array = $this->get_list_view_array();
		$temp_array['DISTRIBUTION'] = $standards[$this->workflows]['name'];
		return $temp_array;
	}

	/**
	 * returns the bean name - overrides SugarBean's
	 */
	function get_summary_text() {
		return $this->name;
	}
	
	/**
	 * This function overrides SugarBean's
	 */
	function save_relationship_changes($is_update) {
    	if (isset($this->relationship_fields) && is_array($this->relationship_fields)) {
    		foreach ($this->relationship_fields as $id => $rel_name) {

	    		if(!empty($this->$id)) {
					$this->load_relationship($rel_name);
					$this->$rel_name->add($this->$id);
		    	}
		    	else {
					//if before value is not empty the attempt to delete relationship.
		    		if(!empty($this->rel_fields_before_value[$id])) {
		    			$GLOBALS['log']->debug('Attempting to remove the relationship record, using relationship attribute'.$rel_name);
						$this->load_relationship($rel_name);
						$this->$rel_name->delete($this->id,$this->rel_fields_before_value[$id]);
		    		}
		    	}
    		}
    	}

	}

} // end Queue class def
?>
