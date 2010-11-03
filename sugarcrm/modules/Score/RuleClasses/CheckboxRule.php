<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 */
require_once('modules/Score/RuleClassBase.php');

class CheckboxRule extends RuleClassBase {
	public $allowedModules = array('*');
	public $supportedFieldTypes = array('boolean','bool');
	public $ruleName = 'CheckboxRule';
	
	function addConfig( $prefix, $module, &$requestData ) {
		$config = parent::addConfig($prefix, $module, $requestData);
		
		$config['rows'] = array();
		$config['rows']['CHECKED'] = array('value'=>'_CHECKED','score'=>0,'mul'=>0,'enabled'=>true);
		$config['rows']['UNCHECKED'] = array('value'=>'_UNCHECKED','score'=>0,'mul'=>0,'enabled'=>true);

		return($config);
	}

	function scoreRow( &$config, &$module, &$bean, &$scoreList ) {
		$val = $this->getVal($bean,$config['field']);
		if ( $val == 1 || $val == 'on' || $val == 'checked' ) {
			$val = 'CHECKED';
			$scoreRow = $config['rows']['CHECKED'];
		} else {
			$val = 'UNCHECKED';
			$scoreRow = $config['rows']['UNCHECKED'];
		}
        
        if ( $scoreRow['enabled'] != true ) {
            $scoreRow['score'] = 0;
            $scoreRow['mul'] = 0;
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

        $returnSQLScore = "CASE ".$tableName.".".$config['field']." ";
        $returnSQLMul = $returnSQLScore;
        if ( empty($config['weight']) ) {
            $config['weight'] = 0;
        }
        $weight = $config['weight'];

        $checkScore = 0;
        $checkMul = 0;
        $uncheckScore = 0;
        $uncheckMul = 0;

        foreach ( $config['rows'] as $row ) {
            if ( $row['enabled'] == true ) {
                $rowScore = (int)$row['score']*$weight;
                $rowMul = (float)$row['mul'];
            } else {
                // Disabled row, score it as a 0
                $rowScore = 0;
                $rowMul = 0;
            }
            if ( $row['value'] == '_CHECKED' ) {
                $checkScore = $rowScore;
                $checkMul = $rowMul;
            } else {
                $uncheckScore = $rowScore;
                $uncheckMul = $rowMul;
            }
        }
        
        $returnSQLScore .= " WHEN 1 THEN ".$checkScore." ELSE ".$uncheckScore." END ";
        $returnSQLMul .= " WHEN 1 THEN ".$checkMul." ELSE ".$uncheckMul." END ";

        return(array('scoreSQL' => $returnSQLScore,
                     'mulSQL' => $returnSQLMul));
    }

}
