<?php
// BEGIN sadek - SIMILAR OPPORTUNITY CALCULATOR

/**
 * 
create table `ibm_similar_opp_calc_queue` (
  `id` varchar(36) NOT NULL,
  `type` varchar(1) NOT NULL default 'g',
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
 * 
 * make sure the following indexes are in place
 * 
alter table ibm_revenuelineitems add index `idx_brand_code` (`brand_code`, `deleted`);
alter table ibm_revenuelineitems add index `idx_product_information` (`product_information`, `deleted`);
alter table accounts add index `idx_industry` (`industry`, `deleted`);
alter table accounts add index `idx_billing_address_country` (`billing_address_country`, `deleted`);
 *
 */

class IBMSimilarOpportunities {
	
	public static $queue_table = 'ibm_similar_opp_calc_queue';
	
	/**
	 * 
	 * $type -  'g' means general - run all checks
	 *			'r' means revenue line item changed - check that only
	 *			'i' means account industry changed - check that only
	 *			'c' means country changed - check that only
	 *
	 * If $data is passed in, it expects the following:
	 * 
	 * $data = array(
	 *   'brand_codes' => array( $k => $v, <$k and $v = brand codes associated with revenue line items tied to this opportunity> ),
	 *   'brand_code_product_map' => array( $k => $v <$k = brand code for a rev line item, $v = product information for that same rev line item> ),
	 *   'account_industry' => '<account_industry>', // This should be the industry of the account associated to this opp
	 *   'account_country'  => '<account_country>', // This should be the primary address country of the account associated to this opp
	 * );
	 */
	public static function processOpp($id, $type = 'g', $data = array()){
		if(empty($id)){
			$GLOBALS['log']->fatal("IBMSimilarOpportunity::processOpp empty id passed in");
		}
		$GLOBALS['log']->info("IBMSimilarOpportunity::processOpp for id {$id}");
		
		// First, we delete all existing relationships
		self::removeExistingRelationships($id);
		
		$final_opps = array();
		
		if(empty($data)){
			$query = "SELECT rli.brand_code, rli.product_information \n".
					 "FROM ibm_revenuepportunities_c rli_opps \n".
					 "     INNER JOIN ibm_revenuelineitems rli ON rli_opps.ibm_revenu04e3neitems_idb = rli.id AND rli_opps.deleted = 0 \n".
					 "WHERE rli_opps.ibm_revenud375unities_ida = '{$id}' \n".
					 "      AND rli_opps.deleted = 0";
			
			$GLOBALS['log']->info("IBMSimilarOpportunity::processOpp data rli query: {$query}");
			$res = $GLOBALS['db']->query($query);
			while($row = $GLOBALS['db']->fetchByAssoc($res)){
				if(!empty($row['product_information'])){
					$data['brand_codes'][$row['brand_code']] = $row['brand_code'];
					$data['brand_code_prod_map'][$row['brand_code']] = $row['product_information'];
				}
			}
			
			$query = "SELECT accounts.industry, accounts.billing_address_country \n".
					 "FROM accounts_opportunities \n".
					 "     INNER JOIN accounts ON accounts.id = accounts_opportunities.account_id AND accounts_opportunities.deleted = 0 \n".
					 "WHERE accounts_opportunities.opportunity_id = '{$id}' \n".
					 "      AND accounts.deleted = 0";
			$GLOBALS['log']->info("IBMSimilarOpportunity::processOpp data accounts query: {$query}");
			$res = $GLOBALS['db']->query($query);
			$row = $GLOBALS['db']->fetchByAssoc($res);
			if($row){
				$data['account_industry'] = $row['industry'];
				$data['account_country'] = $row['billing_address_country'];
			}
		}
		
		$GLOBALS['log']->info("IBMSimilarOpportunity::processOpp data array is: ".var_export($data, true));
		
		// Because we set $final_opps[$row['opp_id']] = 1; instead of incrementing in this if,
		//   it needs to be the first one that runs.
		if(!empty($data['brand_codes']) && ($type == 'g' || $type == 'r') ){
			$prod_results = array();
			
			$brand_code_in = "('".implode("','", $data['brand_codes'])."')";
			$query = "SELECT rli_opps.ibm_revenud375unities_ida opp_id, rli.brand_code, rli.product_information \n".
					 "FROM ibm_revenuepportunities_c rli_opps \n".
					 "     INNER JOIN ibm_revenuelineitems rli ON rli_opps.ibm_revenu04e3neitems_idb = rli.id AND rli_opps.deleted = 0 \n".
					 "WHERE rli.brand_code in {$brand_code_in} \n".
					 "      AND rli_opps.deleted = 0 \n".
					 "      AND rli_opps.ibm_revenud375unities_ida != '{$id}'\n";
			
			$GLOBALS['log']->info("IBMSimilarOpportunity::processOpp brand_code lookup query is: {$query}");
			$res = $GLOBALS['db']->query($query);
			while($row = $GLOBALS['db']->fetchByAssoc($res)){
				if($data['brand_code_prod_map'][$row['brand_code']] == $row['product_information']){
					$final_opps[$row['opp_id']] = 1;
				}
			}
		}
		
		if(!empty($data['account_industry']) && ($type == 'g' || $type == 'i') ){
			$query = "SELECT accounts_opportunities.opportunity_id opp_id \n".
					 "FROM accounts_opportunities \n".
					 "     INNER JOIN accounts ON accounts.id = accounts_opportunities.account_id AND accounts_opportunities.deleted = 0 \n".
					 "WHERE accounts.industry = '{$data['account_industry']}' \n".
					 "      AND accounts_opportunities.opportunity_id != '{$id}' \n".
					 "      AND accounts.deleted = 0";
			
			$GLOBALS['log']->info("IBMSimilarOpportunity::processOpp account_industry lookup query is: {$query}");
			$res = $GLOBALS['db']->query($query);
			while($row = $GLOBALS['db']->fetchByAssoc($res)){
				$final_opps[$row['opp_id']] = isset($final_opps[$row['opp_id']]) ? $final_opps[$row['opp_id']] + 1 : 1;
			}
		}
		
		if(!empty($data['account_country']) && ($type == 'g' || $type == 'c') ){
			$query = "SELECT accounts_opportunities.opportunity_id opp_id \n".
					 "FROM accounts_opportunities \n".
					 "     INNER JOIN accounts ON accounts.id = accounts_opportunities.account_id AND accounts_opportunities.deleted = 0 \n".
					 "WHERE accounts.billing_address_country = '{$data['account_country']}' \n".
					 "      AND accounts_opportunities.opportunity_id != '{$id}' \n".
					 "      AND accounts.deleted = 0";
			
			$GLOBALS['log']->info("IBMSimilarOpportunity::processOpp account_country lookup query is: {$query}");
			$res = $GLOBALS['db']->query($query);
			while($row = $GLOBALS['db']->fetchByAssoc($res)){
				$final_opps[$row['opp_id']] = isset($final_opps[$row['opp_id']]) ? $final_opps[$row['opp_id']] + 1 : 1;
			}
		}
		
		$GLOBALS['log']->info("IBMSimilarOpportunity::processOpp final_opps list is: ".var_export($final_opps, true));
		
		foreach($final_opps as $opp_id => $count){
			self::insertSimilarity($id, $opp_id, $count);
		}
	}
	
	public static function insertSimilarity($opp_id_a, $opp_id_b, $count){
		// Safe guard against linking to oneself
		if($opp_id_a == $opp_id_b){
			$GLOBALS['log']->info("IBMSimilarOpportunity::insertSimilarity opp_id_a = opp_id_b ({$opp_id_b}), returning");
			return false;
		}
		
		$insert = "REPLACE INTO related_opportunities \n".
				  "SET id = '".md5($opp_id_a.$opp_id_b)."', \n".
				  "    date_modified = NOW(), \n".
				  "    opp_id_a = '{$opp_id_a}', \n".
				  "    opp_id_b = '{$opp_id_b}', \n".
				  "    score = '{$count}', \n".
				  "    deleted = 0; \n";
		
		$GLOBALS['log']->info("IBMSimilarOpportunity::insertSimilarity opp_id_a = '{$opp_id_a}', opp_id_b = '{$opp_id_b}', count = '{$count}'");
		
		$res = $GLOBALS['db']->query($insert);
		if(!$res){
			$GLOBALS['log']->fatal("IBMSimilarOpportunity::insertSimilarity failed, query: {$insert}");
		}
		
		return $res;
	}
	
	public static function runQueue(){
		$query = "SELECT id, type FROM ".self::$queue_table;
		$res = $GLOBALS['db']->query($query);
		while($row = $GLOBALS['db']->fetchByAssoc($res)){
			if(!empty($row['id'])){
				self::processOpp($row['id'], $row['type']);
				$GLOBALS['log']->info("IBMSimilarOpportunity::runQueue processing for id {$row['id']}");
				self::removeFromQueue($row['id']);
			}
			else{
				$GLOBALS['log']->fatal("IBMSimilarOpportunity::runQueue empty id row in database");
			}
		}
		
		return $res;
	}
	
	public static function addToQueue($id, $type = 'g'){
		if(empty($id) || empty($type)){
			$GLOBALS['log']->fatal("IBMSimilarOpportunity::addToQueue failed: id = '{$id}', type = '{$type}'"); 
			return false;
		}
		
		// Known corner case: if there already exists an entry in the queue, and it is of a certain type, that type
		//                    will get overridden by the second entry that comes in. Good ol' REPLACE INTO
		$insert = "REPLACE INTO ".self::$queue_table." SET id = '{$id}', type = '{$type}', date_added = '".gmdate("Y-m-d H:i:s")."' ";
		$res = $GLOBALS['db']->query($insert);
		$GLOBALS['log']->info("IBMSimilarOpportunity::addToQueue query is: {$insert}, success is '{$res}'");
		
		// Here we have to add any related Opportunities to the calc queue, since this one changed
		$related = self::getRelatedOpps($id);
		foreach($related as $opp_id){
			$insert = "REPLACE INTO ".self::$queue_table." SET id = '{$opp_id}', type = '{$type}', date_added = '".gmdate("Y-m-d H:i:s")."' ";
			$res = $GLOBALS['db']->query($insert);
			$GLOBALS['log']->info("IBMSimilarOpportunity::addToQueue related opp added, id is {$opp_id}");
		}
		
		return $res;
	}
	
	public static function removeFromQueue($id){
		if(empty($id)){
			$GLOBALS['log']->fatal("IBMSimilarOpportunity::removeFromQueue failed: id is blank"); 
			return false;
		}
		
		$remove = "DELETE FROM ".self::$queue_table." WHERE id = '{$id}'";
		$res = $GLOBALS['db']->query($remove);
		
		$GLOBALS['log']->info("IBMSimilarOpportunity::removeFromQueue for id '{$id}', success is '{$res}'");
		if(!$res){
			$GLOBALS['log']->fatal("IBMSimilarOpportunity::removeFromQueue for id '{$id}', failed removing!");
		}
		
		return $res;
	}
	
	public static function getRelatedOpps($id){
		if(empty($id)){
			$GLOBALS['log']->fatal("IBMSimilarOpportunity::getRelatedOpps failed: id is blank"); 
			return false;
		}

		$query = "SELECT opp_id_b FROM related_opportunities WHERE opp_id_a = '{$id}' AND deleted = 0";
		$res = $GLOBALS['db']->query($query);
		
		$return = array();
		while($row = $GLOBALS['db']->fetchByAssoc($res)){
			if(empty($row['opp_id_b'])){
				continue;
			}
			$return[$row['opp_id_b']] = $row['opp_id_b'];
		}
		
		return $return;
	}
	
	public static function removeExistingRelationships($id){
		if(empty($id)){
			$GLOBALS['log']->fatal("IBMSimilarOpportunity::removeExistingRelationships failed: id is blank"); 
			return false;
		}
		
		$query = "DELETE FROM related_opportunities WHERE opp_id_a = '{$id}'";
		$res = $GLOBALS['db']->query($query);
	}
}
