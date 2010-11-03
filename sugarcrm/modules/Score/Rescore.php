<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 */
require_once('modules/Score/Score.php');


function getModuleCounts( $module ) {
	global $db;

    $moduleClass = loadBean($module);
    
    $tableName = $moduleClass->table_name;
    
    $moduleCounts = array();
        
    $ret = $db->query("SELECT COUNT(*) AS totalCount FROM ".$tableName." AS target WHERE target.deleted = 0",true);
    $row = $db->fetchByAssoc($ret);
    $moduleCounts['total'] = (int)$row['totalCount'];
        
	return($moduleCounts);
}

function runIncrementalRescore() {
    // No longer incrementally rescore since the SQL switchover
/*
    if ( !isset($GLOBALS['sugar_config']['use_fast_rescore']) && $GLOBALS['sugar_config']['use_fast_rescore'] == FALSE ) {
        $scoreModules = Score::getScoreModuleData();
        
        foreach ( $scoreModules as $module => $ignore ) {
            scoreChunk($module,100,'null');
            scoreChunk($module,100,'dirty');
        }
    }
*/
}

function runTotalRescore() {
	$scoreModules = Score::getScoreModuleData();
    foreach ( $scoreModules as $module => $ignore ) {
        score::sqlRescoreModule($module);
    }

// Pre SQL-based rescoring system
/*	
    foreach ( $scoreModules as $module => $ignore ) {
        while ( scoreChunk($module,100,'null') ) {
            $GLOBALS['log']->debug("Scored 100 scoreless records of type $module");
        }
        while ( scoreChunk($module,100,'dirty') ) {
            $GLOBALS['log']->debug("Scored 100 dirty records of type $module");
        }
    }
*/
}

/*
function getNextDirtyIds( $module, $table_name, $num = 100 ) {
	global $db;
	
//	$ret = $db->limitQuery("SELECT DISTINCT s.target_id FROM score s LEFT JOIN ".$table_name." AS target ON s.target_id = target.id WHERE is_dirty = 1 AND target_module = '".$db->quote($module)."' AND target.deleted = 0 ORDER BY target.date_modified DESC",0,$num,true);
	$ret = $db->limitQuery("SELECT DISTINCT s.target_id FROM score s LEFT JOIN ".$table_name." AS target ON s.target_id = target.id WHERE is_dirty = 1 AND target_module = '".$db->quote($module)."' AND target.deleted = 0",0,$num,true);
	
	$dirtyList = array();
	while ( $row = $db->fetchByAssoc($ret) ) {
		$dirtyList[] = $row['target_id'];
	}

	return $dirtyList;
}

function getNextNullIds( $module, $table_name, $num = 100 ) {
	global $db;
	 
	static $tableNameList;
	
//	$ret = $db->limitQuery("SELECT DISTINCT target.id FROM ".$table_name." AS target LEFT JOIN score ON target.id = score.target_id AND score.target_module = '".$db->quote($module)."' WHERE target.deleted = 0 AND score.id IS NULL ORDER BY target.date_modified DESC",0,$num,true);
	$ret = $db->limitQuery("SELECT DISTINCT target.id FROM ".$table_name." AS target LEFT JOIN score ON target.id = score.target_id AND score.target_module = '".$db->quote($module)."' WHERE target.deleted = 0 AND score.id IS NULL",0,$num,true);
	
	$dirtyList = array();
	while ( $row = $db->fetchByAssoc($ret) ) {
		$dirtyList[] = $row['id'];
	}

	return $dirtyList;
}

function scoreChunk( $module, $num = 100, $type = 'both' ) {
    global $db;

	static $moduleClasses;
	static $scoreModules;
	static $moduleRules;

    if ( ! isset($scoreModules) ) {
		$scoreModules = Score::getScoreModuleData();
    }

	if ( ! isset($moduleClasses[$module]) ) {
		$moduleClasses[$module] = loadBean($module);
        $ruleClasses[$module] = Score::getRuleClassesForModule($module);
		$moduleRules[$module] = Score::getModuleConfigs($module);
	}

	if ( count($moduleRules[$module]['rules']) < 1 ) {
		// We have no rules, no need to rescore
		return FALSE;
	}

	$myClass = $moduleClasses[$module];

	$idList = array();
	if ( $type == 'null' || $type == 'both' ) {
		// Fetch null records first
		$idList = getNextNullIds( $module, $myClass->table_name, $num );		
	}
	if ( $type == 'dirty' || $type == 'both' ) {
		// We may have fetched some null id's
		if ( count($idList) < $num ) {
			$idList2 = getNextDirtyIds($module, $myClass->table_name, $num-count($idList) );
			$idList = array_merge($idList,$idList2);
		}
	}

	if ( count($idList) < 1 ) {
		return FALSE;
	}

    $parentModule = $scoreModules[$module]['parentModule'];
    $parentFieldName = $scoreModules[$module]['parentModuleField'];
    $scoreFieldName = $scoreModules[$module]['scoreField'];

    $query = $myClass->create_new_list_query('',$myClass->table_name.".id IN ('".implode("','",$idList)."')",array(),array(),0,'',false,$myClass);
    $ret = $db->query($query,true);
    while ( $row = $db->fetchByAssoc($ret) ) {
        if ( ! empty($row[$parentFieldName]) ) {
            $parentId = $row[$parentFieldName];
        } else {
            $parentId = '';
        }
        $score = Score::scoreRow($myClass->module_dir, $row['id'], $row, $moduleRules[$module],$ruleClasses[$module],$parentModule,$parentId);
        $db->query("UPDATE ".$myClass->table_name." SET ".$scoreFieldName." = '".(float)$score."' WHERE id = '".$db->quote($row['id'])."'",true);
    }

	return(count($idList));
}

*/