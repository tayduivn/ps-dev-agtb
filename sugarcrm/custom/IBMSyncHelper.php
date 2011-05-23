<?php

// START jvink

/**
 * 
 * Synchronisation helper to sync fields between revenueLineItems, Roadmaps
 * and Forecasts. All additional logic between those modules should be 
 * centralized as well using this helper class. 
 *
 */

class IBMSyncHelper {
	
	// singleton
	private static $instance;
	
	// init flag and object state
	public $isInit, $isError, $isExec;

	// source bean params
	public $bean, $module;
	
	// trigger post sync
	private $do_post_sync = false;
	private $post_sync_params = array();
	
	/**
	 * Field sync settings
	 */ 

	// force save on destination beans (required when altering fields in syncFrom_xxx methods
	public $field_sync_force_save = false;
	// this array holds the beans to sync to
	public $field_sync_queue = array();
	// the actual field sync, which needs to be setup from syncFrom_xxx methods
	public $field_sync = array();
	// fields which should always be kept in sync, used as base to set $field_sync
	public $field_sync_base = array(
		// from
		'ibm_revenueLineItems' => array(
			// to
			'ibm_RevenueLineItemRoadmap' => array(
				// source field -> destination field
				'probability' => 'probability',
				'bill_date' => 'bill_date',
				'revenue_amount' => 'total',
			),
			'Worksheet' => array(
				'probability' => 'probability',
				'bill_date' => 'bill_date',
				'revenue_amount' => 'total',
			),
			// do not remove this one, this placeholder is required !
			'Opportunity' => array(
			),
		),
		// from
		'ibm_RevenueLineItemRoadmap' => array(
			// to
			'ibm_revenueLineItems' => array(
				// source field -> destination field
				'probability' => 'probability',
				'bill_date' => 'bill_date',
				'total' => 'revenue_amount',
			),
			'Worksheet' => array(
				'probability' => 'probability',
				'bill_date' => 'bill_date',
				'total' => 'total',
				'solid' => 'solid',
				'at_risk' => 'at_risk',
				'stretch' => 'stretch',
				'not_in_roadmap' => 'not_in_roadmap',
				'month' => 'month',
				'week' => 'week',
				'fo_week' => 'fo_week',
				'foe_week' => 'foe_week',
				'load_status' => 'load_status',
				'date_order_will_firm' => 'date_order_will_firm',
			),
			// do not remove this one, this placeholder is required !
			'Opportunity' => array(
			),
		),
		// from
		'Worksheet' => array(
			// to
			'ibm_revenueLineItems' => array(
				'probability' => 'probability',
				'bill_date' => 'bill_date',
				'total' => 'revenue_amount',
			),
			'ibm_RevenueLineItemRoadmap' => array(
				'probability' => 'probability',
				'bill_date' => 'bill_date',
				'total' => 'total',
				'solid' => 'solid',
				'at_risk' => 'at_risk',
				'stretch' => 'stretch',
				'not_in_roadmap' => 'not_in_roadmap',
				'month' => 'month',
				'week' => 'week',
				'fo_week' => 'fo_week',
				'foe_week' => 'foe_week',
				'load_status' => 'load_status',
				'date_order_will_firm' => 'date_order_will_firm',
			),
			'ibm_RoadmapSD' => array(
				'probability' => 'rm_probability',
				'bill_date' => 'rm_bill_date',
				'month' => 'rm_month',
				'week' => 'rm_week',
				'solid' => 'rm_solid',
				'at_risk' => 'rm_at_risk',
				'stretch' => 'rm_stretch',
				'total' => 'rm_total',
			),
			// do not remove this one, this placeholder is required !
			'Opportunity' => array(
			),
		),
		// from
		'ibm_RoadmapSD' => array(
			// to
			'Worksheet' => array(
				'rm_probability' => 'probability',
				'rm_bill_date' => 'bill_date',
				'rm_month' => 'month',
				'rm_week' => 'week',
				'rm_solid' => 'solid',
				'rm_at_risk' => 'at_risk',
				'rm_stretch' => 'stretch',
				'rm_total' => 'total',
			),
			// do not remove this one, this placeholder is required !
			'Opportunity' => array(
			),
			
		),
	);
	

	
	/**
	 * INTERNAL FUNCTIONS
	 */
	
	final private function __construct() {

		// all includes go overhere
		require_once('modules/ibm_revenueLineItems/ibm_revenueLineItems.php');
		require_once('modules/ibm_RevenueLineItemRoadmap/ibm_RevenueLineItemRoadmap.php');
		require_once('modules/Forecasts/Worksheet.php');
		require_once('modules/ibm_RoadmapSD/ibm_RoadmapSD.php');

		// we are not initialized yet
		$this->isInit = false;
		
		// no errors yet
		$this->isError = false;
	}
	
	final private function __clone() {}

	// singleton 
	public static function getInstance() {
		if(!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	// setup the sync
	public function init($bean_or_id, $module = false) {
		
		// prevent loops caused by other logic
		if($this->isInit) {
			$GLOBALS['log']->debug(__CLASS__.': Already initialized, skipping init !');
			return;
		}
		
		// reset sync field stuff
		$this->field_sync = array();
		$this->field_sync_queue = array();
		$this->field_sync_force_save = false;
		
		// We need a source bean to start from. If only the record id is passed
		// we need the second argument as well
		if(is_string($bean_or_id)){
			if($module) {
				$this->bean = new $module();
				$this->bean->retrieve($bean_or_id);
			} else {
				$GLOBALS['log']->debug(__CLASS__.': No module defined for record');
				$this->isError = true;
				return;
			}
		} else {
			$this->bean = $bean_or_id;
		}
		
		if(empty($this->bean->id)){
			$GLOBALS['log']->debug(__CLASS__.': Record could not be found');
			$this->isError = true;
			return; 
		}

		$this->module = $this->bean->object_name;
		$this->isInit = true;
		$this->isExec = false;

	}
	
	// main function to trigger synchronisation
	public function execute() {

		// check for any errors
		if($this->isError) {
			$GLOBALS['log']->debug(__CLASS__.': Cannot execute, init error');
			return;
		}
		
		// prevent loops caused by other logic
		if($this->isExec) {
			$GLOBALS['log']->debug(__CLASS__.': Already triggered once, skipping sync !');
			return;
		}

		// check for syncFrom function in this class		
		$func = 'syncFrom_'.$this->module;
		if(! method_exists(self::$instance, $func)) {
			$GLOBALS['log']->debug(__CLASS__.': No sync function available for '.$this->module);
			return;
		}

		// we are in business ...
		$GLOBALS['log']->debug(__CLASS__.': Execution triggered (source->'.$this->module.')');
		$GLOBALS['log']->debug(__CLASS__.': Source bean -> '.$this->bean->id.')');
		$this->isExec = true;

		// custom module logic
		$this->$func();
		
		// sync fields
		$this->syncFields();
		
		// post sync --> for opportunity totals & average probability
		if($this->do_post_sync) {
			$this->post_sync();
		}	
		
		$GLOBALS['log']->debug(__CLASS__.': Done');
	}
	
	// synchronize fields as queued in fields_sync_queue
	private function syncFields() {

		if(!count($this->field_sync_queue)) {
			$GLOBALS['log']->debug(__CLASS__.': No fields to sync');
			return;
		}
		
		foreach($this->field_sync_queue as $dst_bean) {

			// we need a destination bean to work on
			if(empty($dst_bean->id)) {
				$GLOBALS['log']->debug(__CLASS__.': Empty bean passed in');
				continue;
			}
			
			// check for field mappings
			$dst_module = $dst_bean->object_name;
			if(!isset($this->field_sync[$this->module])) {
				$GLOBALS['log']->debug(__CLASS__.': Fieldmap source not available for '.$this->module);
				continue;
			}
			if(!isset($this->field_sync[$this->module][$dst_module])) {
				$GLOBALS['log']->debug(__CLASS__.': Fieldmap destination not available for '.$dst_module);
				continue;
			}
			$map = $this->field_sync[$this->module][$dst_module];

			/**
			 * Fix formatting (need to compare date values)
			 * 
			 * FIXME: as of SugarBean:
			 * "This function will be removed in a future release, it is only here 
			 *  to assist upgrading existing code that expects formatted data in the bean"
			 *  
			 *  What about that ?
			 */
			$dst_bean->fixUpFormatting();
			
			// sync fields
			$doSave = false;
			$GLOBALS['log']->debug(__CLASS__.': Fieldsync from '.$this->module.' to '.$dst_module);
			$GLOBALS['log']->debug(__CLASS__.': *** Destination bean -> '.$dst_bean->id);
			foreach($map as $src_field => $dst_field) {
				if(isset($this->bean->$src_field)) {
					if(isset($dst_bean->$dst_field)) {
						if($dst_bean->$dst_field <> $this->bean->$src_field) {
							$GLOBALS['log']->debug(__CLASS__.': * '.$src_field.'->'.$dst_field.' (old='.$dst_bean->$dst_field.' / new='.$this->bean->$src_field.')');
							$dst_bean->$dst_field = $this->bean->$src_field;
							$doSave = true;
						} else {
							$GLOBALS['log']->debug(__CLASS__.': * '.$src_field.'->'.$dst_field.' (value not changed -> '.$this->bean->$src_field.')');
						}
					} else {
						$GLOBALS['log']->debug(__CLASS__.': * Destination field not present -> '.$dst_field);
					}
				} else {
					$GLOBALS['log']->debug(__CLASS__.': * Source field unknown -> '.$src_field);
				}
			}
			
			// safe if any changes or when we are forced to
			if($doSave || $this->field_sync_force_save) {
				if($doSave) { $GLOBALS['log']->debug(__CLASS__.': *** Saving changes for this bean'); }
				if($this->field_sync_force_save) { $GLOBALS['log']->debug(__CLASS__.': *** Saving changes for this bean (forced)'); }
				$dst_bean->save();
			} else {
				$GLOBALS['log']->debug(__CLASS__.': *** No changes to be saved for this bean');
			}
			
		}
		return;
	}
	
	// add dynamically an additional field to the default sync list
	private function addSyncField($target, $src_field, $dst_field) {
		if(!isset($this->field_sync[$this->module][$target])) {
			$this->field_sync[$this->module][$target] = array();
		}
		$GLOBALS['log']->debug(__CLASS__.': Dynamically adding '.$dst_field.' to sync list');
		$this->field_sync[$this->module][$target][$src_field] = $dst_field;
		return;
	}
	
	/**
	 * POST SYNC --> executed if all the other logic 
	 */
	
	private function post_sync() {

		$GLOBALS['log']->debug(__CLASS__.': *** EXECUTING POST SYNC ***');
		
		// opportunity processing for probability average & total revenue amount
		if(isset($this->post_sync_params['opportunity_id'])) {

			// general stuff
			$doSave = false;
			$opp = new Opportunity();
			$opp->retrieve($this->post_sync_params['opportunity_id']);
			
			if(!empty($opp->id)) {
				$sql = 'SELECT AVG(rev.probability) AS prob_avg,
							SUM(rev.revenue_amount) AS tot_amount,
							MAX(rev.bill_date) AS bill_date
						FROM ibm_revenuepportunities_c rel
						INNER JOIN ibm_revenuelineitems rev
							ON rev.id = rel.ibm_revenu04e3neitems_idb
							AND rev.deleted = 0
						WHERE rel.ibm_revenud375unities_ida = "'.$this->post_sync_params['opportunity_id'].'"
							AND rel.deleted = 0';
				$q_oppty = $GLOBALS['db']->query($sql);
				if($oppty = $GLOBALS['db']->fetchByAssoc($q_oppty)) {

					// average probability
					if($oppty['prob_avg'] <> $opp->probability) {
		            	$GLOBALS['log']->debug(__CLASS__.': * Updating average probability (old=>'.$opp->probability.' new=>'.$oppty['prob_avg'].')');
		            	$opp->probability = $oppty['prob_avg'];
		            	$doSave = true;
					}
					
					// total revenue
					if($oppty['tot_amount'] <> $opp->amount) {
		            	$GLOBALS['log']->debug(__CLASS__.': * Updating total revenue (old=>'.$opp->amount.' new=>'.$oppty['tot_amount'].')');
		            	$opp->amount = $oppty['tot_amount'];
		            	$doSave = true;
					}
					
					// set forecast quarter if none is set yet
					if($oppty['bill_date'] && empty($opp->roadmap_quarter)) {
						global $current_user;
						if($tp = IBMHelper::date_to_timeperiod($oppty['bill_date'])) {
							$opp->roadmap_quarter = $tp['id'];
							$doSave = true;
							$GLOBALS['log']->debug(__CLASS__.': * Automatically setting forecast quarter, none set yet -> '.$tp['id']);
						} else {
							$GLOBALS['log']->debug(__CLASS__.': * Forecast quarter not set yet, cannot find timeperiod to do so !');
						}
					}
				}
				
				if($doSave) {
					$opp->save();
				}
			}
		}
	
	}
	
	/**
	 * CUSTOM CODE FOR EVERY SYNC DIRECTION 
	 */
	
	// sync from ibm_RevenueLineItemRoadmap
	private function syncFrom_ibm_RevenueLineItemRoadmap() {
		$GLOBALS['log']->debug(__CLASS__.': Start '.__FUNCTION__);
		
		/**
		 * Setup field_sync array from default definition:
		 * This is an additional step to be able to sync different dynamic
		 * fields when iterating over multiple related beans
		 */
		$this->field_sync = $this->field_sync_base;
		
		// find related revenue line item
        $lineItem = new ibm_revenueLineItems();
        $lineItem->retrieve($this->bean->revenuelineitem_id_c);

        if(!empty($lineItem->id)) {
	        // queue fieldsync for this revenueLineItems
    	    $this->field_sync_queue[] = $lineItem;
        

	        // find related worksheet
			$ws = new Worksheet();
			$ws->retrieve_by_string_fields(array(
				'related_id' => $lineItem->id,
				'related_forecast_type' => 'ibm_revenuelineitems',
			));
	        if(!empty($ws->id)) {
				$this->field_sync_queue[] = $ws;
			}
			
			// update decision date/qtr if required
			$lineItem->load_relationship('ibm_revenuelineitems_opportunities');
			$this->ih_OpportunitySync($lineItem->ibm_revenuelineitems_opportunities_id, 'bill_date', 'forecast_qtr_yr');
	
        }
		        
        $GLOBALS['log']->debug(__CLASS__.': End '.__FUNCTION__);
		return;
	}
	
	// sync from ibm_revenueLineItems
	private function syncFrom_ibm_revenueLineItems() {
		$GLOBALS['log']->debug(__CLASS__.': Runing '.__FUNCTION__);

		/**
		 * Setup field_sync array from default definition:
		 * This is an additional step to be able to sync different dynamic
		 * fields when iterating over multiple related beans
		 */
		$this->field_sync = $this->field_sync_base;
		
		// find related roadmap
		$roadmap = new ibm_RevenueLineItemRoadmap();
        $roadmap->retrieve_by_string_fields(array('revenuelineitem_id_c'=>$this->bean->id));

        // dynamic field, not always synced
		if(!empty($roadmap->id)) {
			
			// dynamic field, not always synced, needs forced save
			if($this->bean->revenue_amount <> $roadmap->total) {
				$roadmap->invalid_subtotals = 1;
				$this->field_sync_force_save = true;
				$GLOBALS['log']->debug(__CLASS__.': Forcing sync to roadmap for invalid subtotals');
			}
                
        	// queue fieldsync for this RevenueLineItemRoadmap
        	$this->field_sync_queue[] = $roadmap;
		}
		
		// find related worksheet
		$ws = new Worksheet();
		$ws->retrieve_by_string_fields(array(
			'related_id' => $this->bean->id,
			'related_forecast_type' => 'ibm_revenuelineitems',
		));
		if(!empty($ws->id)) {
			$this->field_sync_queue[] = $ws;
		}
		
		// sync to opportunity

		/* strange behaviour --> load_relationship does not seem to work when creating rli */
		//$this->bean->load_relationship('ibm_revenuelineitems_opportunities');
		//$this->ih_OpportunitySync($this->bean->ibm_revenuelineitems_opportunities_id, 'bill_date', false);
		$opp_list = $this->bean->get_linked_beans('ibm_revenuelineitems_opportunities', 'Opportunity');
		$this->ih_OpportunitySync($opp_list[0]->id, 'bill_date', false);
	
		$GLOBALS['log']->debug(__CLASS__.': End '.__FUNCTION__);
		return;
	}

	// sync from Worksheets
	private function syncFrom_Worksheet() {
		$GLOBALS['log']->debug(__CLASS__.': Start '.__FUNCTION__);
		
		/**
		 * Setup field_sync array from default definition:
		 * This is an additional step to be able to sync different dynamic
		 * fields when iterating over multiple related beans
		 */
		$this->field_sync = $this->field_sync_base;
		
		// related revlineitem (in case of Direct report)
		if($this->bean->related_forecast_type == 'ibm_revenuelineitems') {
			$lineItem = new ibm_revenueLineItems();
        	$lineItem->retrieve($this->bean->related_id);
        	if(!empty($lineItem->id)) {
        		$this->field_sync_queue[] = $lineItem;
        	
        	
        		// ... and related roadmap
        		$roadmap = new ibm_RevenueLineItemRoadmap();
        		$roadmap->retrieve_by_string_fields(array('revenuelineitem_id_c'=>$this->bean->related_id));
        		if(!empty($roadmap->id)) {
        			$this->field_sync_queue[] = $roadmap;
        		}
        		
        		// update decision date if required
        		$lineItem->load_relationship('ibm_revenuelineitems_opportunities');
				$this->ih_OpportunitySync($lineItem->ibm_revenuelineitems_opportunities_id, 'bill_date', false);
        		
        	}			
		}
		
		// related roadmapsd (in case of S+D)
		if($this->bean->related_forecast_type == 'opportunities') {
			$rmsd = new ibm_RoadmapSD();
			$rmsd->retrieve_by_string_fields(array('opportunity_id_c'=>$this->bean->related_id));
			if(!empty($rmsd->id)) {
				$this->field_sync_queue[] = $rmsd;
			}
			
			// change sales_stage for oppty if probability is 100% and the oppty
			$this->ih_OpportunityClose($this->bean->related_id,'probability');
		}
		
		$GLOBALS['log']->debug(__CLASS__.': End '.__FUNCTION__);
		return;
	}
	
	// sync from RoadmapSD
	private function syncFrom_ibm_RoadmapSD() {
		$GLOBALS['log']->debug(__CLASS__.': Start '.__FUNCTION__);
		
		/**
		 * Setup field_sync array from default definition:
		 * This is an additional step to be able to sync different dynamic
		 * fields when iterating over multiple related beans
		 */
		$this->field_sync = $this->field_sync_base;
		
		// related worksheet
		$ws = new Worksheet();
		$ws->retrieve_by_string_fields(array('related_id'=>$this->bean->opportunity_id_c, 'related_forecast_type'=>'opportunities'));
		if(!empty($ws->id)) {
			$this->field_sync_queue[] = $ws;
		}		

		// change sales_stage for oppty if probability is 100% and the oppty
		$this->ih_OpportunityClose($this->bean->opportunity_id_c,'rm_probability');
		
		$GLOBALS['log']->debug(__CLASS__.': End '.__FUNCTION__);
		return;
	}
	
	/**
	 * Internal helpers ih_....
	 */
	
	private function ih_OpportunityClose($opp_id, $probability_field) {
		
		// load closed sales stages from IBMHelper as they may change
		$closed_sales_stages = IBMHelper::get_oppty_closed_sales_stages();

		$oppty = new Opportunity();
		$oppty->retrieve($opp_id);
		if(!empty($oppty->id)) {
			if($this->bean->$probability_field == '100' && ! array_search($oppty->sales_stage,$closed_sales_stages)) {
				$oppty->sales_stage = IBMHelper::get_oppty_default_closed_sales_stage();
				$this->field_sync_force_save = true;
				$this->field_sync_queue[] = $oppty;
			}
		} else {
			$GLOBALS['log']->debug(__CLASS__.': OpportunityClose called with invalid oppty id');
		}
	} 
	
	/** 
	 * - Setup decision date and quarter most in the future
	 * - Implicit update average probability & total revenue
	 */
	
	private function ih_OpportunitySync($opp_id, $date_field, $qtr_field) {
		
		$force_update = false;
		$opp = new Opportunity();
		$opp->retrieve($opp_id);
		if(!empty($opp->id)) {
			
			// update decision date if needed
			if($date_field) {
				// we need all the dates in db format
				$opp->fixUpFormatting();
			
				if(strtotime($this->bean->$date_field) > strtotime($opp->date_closed)) {
					$GLOBALS['log']->debug(__CLASS__.': Forcing sync decision date oppty (old=>'.$opp->date_closed.' new=>'.$this->bean->$date_field.')');			
					$opp->date_closed = $this->bean->$date_field;
					
					// if decision date changes, check if roadmap_quarter also needs to be changed
					$tp = IBMHelper::date_to_timeperiod($this->bean->$date_field);
					if($tp['id'] <> $opp->roadmap_quarter) {
						$opp->roadmap_quarter = $tp['id'];
						$GLOBALS['log']->debug(__CLASS__.': Forcing implicit sync decision date oppty because of decision date change');
					}
					$force_update = true;
				}
			}
						
			// update roadmap quarter if needed
			if($qtr_field) {
				$old_qtr = IBMHelper::get_timeperiod($opp->roadmap_quarter);
	           	$new_qtr = IBMHelper::get_timeperiod($this->bean->$qtr_field);
	            if(strtotime($new_qtr['start_date']) > strtotime($old_qtr['start_date'])) {
	            	$GLOBALS['log']->debug(__CLASS__.': Forcing sync quarter oppty (old=>'.$old_qtr['start_date'].' new=>'.$new_qtr['start_date'].')');
	            	$opp->roadmap_quarter = $this->bean->$qtr_field;
	            	$force_update = true;
	            }
			}
			
			// force update
			if($force_update) {
				$this->field_sync_force_save = true;
				$this->field_sync_queue[] = $opp;
			}
			
			// trigger post_sync for oppty total amount & avg probability
			$this->do_post_sync = true;
			$this->post_sync_params['opportunity_id'] = $opp_id;
		} else {
			$GLOBALS['log']->debug(__CLASS__.': OpportunitySync called with invalid oppty id');
		}
	}
	
}

?>
