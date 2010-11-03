<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 */
require_once('modules/Score/RuleClassBase.php');

class NumberRule extends RuleClassBase {
	public $allowedModules = array('*');
	public $ruleName = 'NumberRule';
	public $supportedFieldTypes = array('int','float','decimal');
	public $isSpecialField = false;
	
	function addConfig( $prefix, $module, &$requestData ) {
		$config = parent::addConfig($prefix, $module, $requestData);
		
		$config['rows'] = array();
		$config['rows'][0] = array('min'=>'_DEFAULT','max'=>'_DEFAULT','score'=>0,'mul'=>0,'enabled'=>true);

		return($config);
	}

	function saveConfig( $origConfig, $module, &$requestData ) {
		$inConfig = parent::saveConfig($origConfig,$module,$requestData);
		$config = $inConfig;
		unset($config['rows']);
		foreach ( $inConfig['rows'] as $i => $row ) {
			if ( $row['min'] === '' && $row['max'] === '' ) {
				unset($config['rows'][$i]);
				break;
			}
			// We need this so that the _NEW row doesn't try to save to that position
			$config['rows'][] = $row;
		}
		
		return($config);
	}

	function scoreRow( &$config, &$module, &$bean, &$scoreList ) {
		$val = $this->getVal($bean,$config['field']);
		foreach ($config['rows'] as $row ) {
			if ( $row['min'] == '_DEFAULT' ) {
				// Set the scoreRow to the default value, will override it later if we find something better
				$scoreRow = $row;
				continue;
			}
			if ( $val >= $row['min'] && $val <= $row['max'] ) {
				$scoreRow = $row;
				break;
			}
		}
		if ( isset($scoreRow) ) {
			$scoreList[] = $this->createScoreEntry($config,$val,$scoreRow['score'],$scoreRow['mul']);
		}
	}

    function getScoreRowSQL( $config, $module ) {
        if ( substr($config['field'],-2) == '_c') {
            $tableName = 'target_c';
        } else {
            $tableName = 'target';
        }

        $mainBit = "WHEN ".$tableName.".".$config['field']." >= %s AND < %s ";
        $returnSQLScore = "CASE ";
        $atLeastOneScore = false;
        $returnSQLMul = $returnSQLScore;
        $atLeastOneMul = false;
        $defaultScore = 0;
        $defaultMul = 0;
        if ( empty($config['weight']) ) {
            $config['weight'] = 0;
        }
        $weight = $config['weight'];

        // Need to sort the rows, so we search from largest to smallest
        $sortedRows = array();
        foreach ( $config['rows'] as $row ) {
            if ( $row['enabled'] == 'true' ) {
                if ( $row['min'] == '_DEFAULT' ) {
                    $defaultScore = (int)$row['score']*$weight;
                    $defaultMul = (float)$row['mul'];
                } else {
                    if ( $row['score'] != 0 ) {
                        $atLeastOneScore = true;
                    }
                    if ( $row['mul'] != 0 ) {
                        $atLeastOneMul = true;
                    }
                    $sortedRows[(int)$row['min']] = $row;
                }
            }
        }
        $sortedRows = krsort($sortedRows);

        foreach ( $sortedRows as $row ) {
            $returnSQLScore .= sprintf($mainBit,$row['min'],$row['max'])."' THEN ".(int)$row['score']*$weight;
            $returnSQLMul .= sprintf($mainBit,$row['min'],$row['max'])."' THEN ".(float)$row['mul'];
        }

        $returnSQLScore .= " ELSE ".$defaultScore." END";
        $returnSQLMul .= " ELSE ".$defaultMul." END";

        if ( ! $atLeastOneScore ) {
            $returnSQLScore = " ".(int)$defaultScore." ";
        }
        if ( ! $atLeastOneMul ) {
            $returnSQLMul = " ".(float)$defaultMul." ";
        }

        return(array('scoreSQL' => $returnSQLScore,
                     'mulSQL' => $returnSQLMul));
    }

}