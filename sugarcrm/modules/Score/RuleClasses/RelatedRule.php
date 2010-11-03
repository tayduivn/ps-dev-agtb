<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 */
require_once('modules/Score/RuleClassBase.php');

class RelatedRule extends RuleClassBase {
	public $allowedModules = array('LeadContacts','LeadAccounts');
	public $ruleName = 'RelatedRule';
	public $supportedFieldTypes = array('link');
	public $isSpecialField = false;
	
	function addConfig( $prefix, $module, &$requestData ) {
		$config = parent::addConfig($prefix, $module, $requestData);
		
		$config['rows'] = array();
		$config['rows'][0] = array('value'=>'0','score'=>0,'mul'=>0,'enabled'=>true);
		$config['rows'][1] = array('value'=>'1','score'=>0,'mul'=>0,'enabled'=>true);
		$config['rows'][2] = array('value'=>'_DEFAULT','score'=>0,'mul'=>0,'enabled'=>true);
		
		$bean = loadBean($module);
		$config['relationship'] = $bean->field_defs[$config['field']]['relationship'];
		if ( empty($config['relationship']) ) {
			die('Could not find a relationship for the requested field');
		}

		return($config);
	}

	function saveConfig( $origConfig, $module, &$requestData ) {
		$inConfig = parent::saveConfig($origConfig,$module,$requestData);
		$config = $inConfig;
		unset($config['rows']);
        // The theroy here is to sort all the rows and put the default rule at the bottom.
		foreach ( $inConfig['rows'] as $i => $row ) {
			if ( $row['value'] === '' ) {
                continue;
			}
			// We need this so that the _NEW row doesn't try to save to that position
            if ( $row['value'] != '_DEFAULT' ) {
                $config['rows'][$row['value']] = $row;
            } else {
                $defaultRow = $row;
            }
		}
        ksort($config['rows']);
        if (isset($defaultRow) ) {
            $config['rows'][] = $defaultRow;
        }
		
		return($config);
	}

	function getRelatedCount( &$module, &$id, &$rel_name ) {
		global $db;
		// Cache the count related query so we can just do string replacement
		static $relQueryCache = array();

		$cacheString = $module.'^'.$rel_name;
		if ( ! isset($relQueryCache[$cacheString]) ) {
			$bean = loadBean($module);
			$bean->id = '@@@ID@@@';
			$bean->load_relationship($rel_name);
			$queryParts = $bean->$rel_name->getQuery(true);
			$queryParts['select'] = 'SELECT COUNT(DISTINCT id) AS numBeans ';
			$relQueryCache[$cacheString] = $queryParts['select'].' '.$queryParts['from'].' '.$queryParts['where'];
		}
		
		$query = str_replace('@@@ID@@@',$id,$relQueryCache[$cacheString]);
		$GLOBALS['log']->info('Getting related records: '.$query);
		$ret = $db->query($query,true);
		$row = $db->fetchByAssoc($ret);
		return($row['numBeans']);
	}

	function scoreRow( &$config, &$module, &$bean, &$scoreList ) {
		$rel_name = $config['field'];
		$val = $this->getRelatedCount($module,$this->getVal($bean,'id'),$rel_name);

        $scoreRow = array('score'=>0,'mul'=>0.00);

		foreach ($config['rows'] as $row ) {
            if ( !isset($row['enabled']) || $row['enabled'] != 'true' ) {
                continue;
            }
			if ( $row['value'] == '_DEFAULT' ) {
				// Set the scoreRow to the default value, will override it later if we find something better
				$scoreRow = $row;
				continue;
			}
			if ( $val == $row['value'] ) {
				$scoreRow = $row;
				break;
			}
		}
		if ( isset($scoreRow) ) {
			$scoreList[] = $this->createScoreEntry($config,$val,$scoreRow['score'],$scoreRow['mul']);
		}
	}

    function getScoreRowSQL( $config, $module ) {
        $bean = loadBean($module);
        $bean->id = '@@@ID@@@';
        $rel_name = $config['field'];
        $bean->load_relationship($rel_name);
        $queryParts = $bean->$rel_name->getQuery(true);
        $queryParts['select'] = 'SELECT COUNT(id)';
        $subselect = $queryParts['select'].' '.$queryParts['from'].' '.$queryParts['where'];
        $subselect = str_replace("'@@@ID@@@'",'target.id',$subselect);

        $returnSQLScore = "CASE (".$subselect.") ";
        $atLeastOneScore = false;
        $returnSQLMul = $returnSQLScore;
        $atLeastOneMul = false;
        $defaultScore = 0;
        $defaultMul = 0;
        if ( empty($config['weight']) ) {
            $config['weight'] = 0;
        }
        $weight = $config['weight'];


        foreach ( $config['rows'] as $row ) {
            if ( $row['enabled'] == 'true' ) {
                if ( $row['value'] == '_DEFAULT' ) {
                    $defaultScore = (int)$row['score']*$weight;
                    $defaultMul = (float)$row['mul'];
                } else {
                    if ( $row['score'] != 0 ) {
                        $atLeastOneScore = true;
                    }
                    if ( $row['mul'] != 0 ) {
                        $atLeastOneMul = true;
                    }
                    $returnSQLScore .= " WHEN '".$row['value']."' THEN ".(int)$row['score']*$weight;
                    $returnSQLMul .= " WHEN '".$row['value']."' THEN ".(float)$row['mul'];
                }
            }
        }
        $returnSQLScore .= " ELSE ".$defaultScore." END";
        $returnSQLMul .= " ELSE ".$defaultMul." END";

        if ( ! $atLeastOneScore ) {
            $returnSQLScore = " ".$defaultScore." ";
        }
        if ( ! $atLeastOneMul ) {
            $returnSQLMul = " ".$defaultMul." ";
        }

        return(array('scoreSQL' => $returnSQLScore,
                     'mulSQL' => $returnSQLMul));
    }


}