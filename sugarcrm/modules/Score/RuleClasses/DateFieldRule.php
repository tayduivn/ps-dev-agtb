<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 */
require_once('modules/Score/RuleClassBase.php');

class DateFieldRule extends RuleClassBase {
	public $allowedModules = array('*');
	public $ruleName = 'DateFieldRule';
	public $supportedFieldTypes = array('date','datetime');
	public $isSpecialField = false;
	public $runNightly = true;

	function addConfig( $prefix, $module, &$requestData ) {
		$config = parent::addConfig($prefix, $module, $requestData);
		
		$config['rows'] = array();
		$config['rows'][0] = array('value'=>'0','score'=>0,'mul'=>0,'enabled'=>true);

		return($config);
	}

	function saveConfig( $origConfig, $module, &$requestData ) {
		$config = parent::saveConfig($origConfig,$module,$requestData);
		$outRows = array();
		// We need to sort the rows by their value
		foreach ( $config['rows'] as $i => $row ) {
			if ( $row['value'] === '' ) {
				continue;
			}
			$outRows[(int)$row['value']] = $row;
		}
		// We need to sort it, but we don't want to keep the keys
		ksort($outRows);
		$config['rows'] = array();
		foreach ( $outRows as $row ) {
			$config['rows'][] = $row;
		}

		return($config);
	}

	function scoreRow( &$config, &$module, &$bean, &$scoreList ) {
		$val = $this->getVal($bean,$config['field']);
		if ( empty($val) || !is_array($config['rows']) ) {
			return;
		}

        $td = new TimeDate();
        $tmp = $td->to_db($val);
        list($tmp) = explode(' ',$tmp);
		list($valYear,$valMonth,$valDate) = explode('-',$tmp);
		$valDateTime = gmmktime(1,1,1,(int)$valMonth,(int)$valDate,(int)$valYear);
		$currtm = time();
		$curDateTime = gmmktime(1,1,1,date('m',$currtm),date('d',$currtm),date('Y',$currtm));
		$dateDiff = floor(($curDateTime - $valDateTime)/(60*60*24));

        foreach ( $config['rows'] as $row ) {
            if ( !isset($row['enabled']) || $row['enabled'] != true ) {
                // Value is not enabled
                continue;
            }
            
            if ( $row['value'] > $dateDiff ) {
                // This is beyond the date we are looking for
                continue;
            }
            
            $scoreRow = $row;
        }

		if ( isset($scoreRow) ) {
			$scoreList[] = $this->createScoreEntry($config,$dateDiff,$scoreRow['score'],$scoreRow['mul']);
		}
	}

/*
// Not used, SQL rescoring is faster than trying to selectively rescore
	function nightlyUpdate ( $config ) {
		global $db;
		$currtm = time();

		foreach ( $config['rows'] as $row ) {
			$affected_date = date('Y',$currtm)."-".date('m',$currtm)."-".(date('d',$currtm)-$row['value']);
			$db->query("UPDATE score SET is_dirty = 1 WHERE rule_id = '".$config['prefix']."' AND rule_data = '".$affected_date."'");
		}
	}
*/

    function getScoreRowSQL( $config, $module ) {
        if ( substr($config['field'],-2) == '_c') {
            $tableName = 'target_c';
        } else {
            $tableName = 'target';
        }
        
        if ( empty($config['weight']) ) {
            $config['weight'] = 0;
        }
        $weight = $config['weight'];

        $mainBit = "WHEN DATEDIFF(NOW(),".$tableName.".".$config['field'].") >= ";
        $returnSQLScore = "CASE ";
        $atLeastOneScore = false;
        $returnSQLMul = $returnSQLScore;
        $atLeastOneMul = false;
        $defaultScore = 0;
        $defaultMul = 0;
        // Need to sort the rows, so we search from largest to smallest
        $sortedRows = array();
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
                
                    $sortedRows[(int)$row['value']] = $row;
                }
            }
        }
        krsort($sortedRows);

        foreach ( $sortedRows as $row ) {
            $returnSQLScore .= $mainBit.(int)$row['value']." THEN ".(int)$row['score']*$weight." ";
            $returnSQLMul .= $mainBit.(int)$row['value']." THEN ".(float)$row['mul']." ";
        }

        $returnSQLScore .= " ELSE ".$defaultScore." END";
        $returnSQLMul .= " ELSE ".$defaultMul." END";

        if ( ! $atLeastOneScore ) {
            $returnSQLScore = " ".(int)$defaultScore." ";
        }
        if ( ! $atLeastOneMul ) {
            $returnSQLMul = " ".(float)$defaultMul." ";
        }

        $GLOBALS['log']->fatal($returnSQLScore);

        return(array('scoreSQL' => $returnSQLScore,
                     'mulSQL' => $returnSQLMul));
    }

}