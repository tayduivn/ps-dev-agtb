<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 */
require_once('modules/Score/RuleClassBase.php');

class TextRule extends RuleClassBase {
	public $allowedModules = array('*');
	public $ruleName = 'TextRule';
	public $supportedFieldTypes = array('text','varchar');
	public $isSpecialField = false;

	function addConfig( $prefix, $module, &$requestData ) {
		$config = parent::addConfig($prefix, $module, $requestData);
		
		$config['rows'] = array();
		$config['rows'][0] = array('value'=>'_DEFAULT','score'=>0,'mul'=>0,'enabled'=>true);

		return($config);
	}

	function saveConfig( $origConfig, $module, &$requestData ) {
		$config = parent::saveConfig($origConfig,$module,$requestData);
		foreach ( $config['rows'] as $i => $row ) {
			if ( $row['value'] == '' ) {
				unset($config['rows'][$i]);
			}
		}
		
		return($config);
	}

	function scoreRow( &$config, &$module, &$bean, &$scoreList ) {
		static $keyedConfig = array();

		if ( !isset($keyedConfig[$config['prefix']]) ) {
			if ( !is_array($config['rows']) ) {
				$config['rows'] = array();
			}
			foreach ( $config['rows'] as $row ) {
				if ( $row['enabled'] == 'true' ) {
					$keyedConfig[$config['prefix']][$row['value']] = array('score'=>$row['score'],'mul'=>$row['mul'],'val'=>$row['value']);
				}
			}
		}

		$val = $this->getVal($bean,$config['field']);
		if ( isset($keyedConfig[$config['prefix']][$val]) ) {
			$scoreRow = $keyedConfig[$config['prefix']][$val];
		} else if ( isset($keyedConfig[$config['prefix']]['_DEFAULT']) ) {
			$scoreRow = $keyedConfig[$config['prefix']]['_DEFAULT'];
		}
		if ( isset($scoreRow) ) {
			$scoreList[] = $this->createScoreEntry($config,$scoreRow['val'],$scoreRow['score'],$scoreRow['mul']);
		}
	}

    function getScoreRowSQL( $config, $module ) {
        global $db;

        if ( substr($config['field'],-2) == '_c') {
            $tableName = 'target_c';
        } else {
            $tableName = 'target';
        }

        $returnSQLScore = "CASE ".$tableName.".".$config['field']." ";
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
                    $returnSQLScore .= " WHEN '".$db->quote($row['value'])."' THEN ".(int)$row['score']*$weight;
                    $returnSQLMul .= " WHEN '".$db->quote($row['value'])."' THEN ".(float)$row['mul'];
                }
            }
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