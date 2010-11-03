<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 */
require_once('modules/Score/RuleClassBase.php');

class CampaignRule extends RuleClassBase {
	public $allowedModules = array('Interactions','Touchpoints');
	public $ruleName = 'CampaignRule';
	public $supportedFieldTypes = array();
	public $isSpecialField = true;

	function prepare( $module, $config ) {
		$config = parent::prepare($module,$config);
		$config['label'] = translate('LBL_CAMPAIGNRULE_TITLE','Score');
		return($config);
	}

	function scoreRow( &$config, &$module, &$bean, &$scoreList ) {
		global $db;

        if ( $module == "Interactions" ) {
            $touchpoint_id = $this->getVal($bean,'source_id');
            $source_module = $this->getVal($bean,'source_module');
            if ( $source_module != "Touchpoints" ) {
                return;
            }
            $ret = $db->query("SELECT campaign_id FROM touchpoints WHERE id = '".$db->quote($touchpoint_id)."'",true);
            $row = $db->fetchByAssoc($ret);
            if ( isset($row['campaign_id']) ) {
                $campaign_id = $row['campaign_id'];
            } else {
                return;
            }
        } else {
            $campaign_id = $this->getVal($bean,'campaign_id');
        }
		$val = $this->getVal($bean,'campaign_score');
		$mul = $this->getVal($bean,'campaign_mul');
		if ( (!isset($val) || empty($val)) && !empty($campaign_id) ) {
			// Have to lookup the campaign score the hard way
			$ret = $db->query("SELECT score,mul FROM campaigns WHERE id = '".$db->quote($campaign_id)."'",true);
			$row = $db->fetchByAssoc($ret);
			$val = $row['score'];
			$mul = $row['mul'];
		}

		$scoreList[] = $this->createScoreEntry($config,$campaign_id,$val,$mul);
	}
    function getScoreRowSQL( $config, $module ) {
        if ( $module == 'Interactions' ) {
            $returnSQLScore = " COALESCE((SELECT campaigns.score FROM campaigns, touchpoints WHERE target.source_module = 'Touchpoints' AND target.source_id = touchpoints.id AND touchpoints.campaign_id = campaigns.id ),0) ";
            $returnSQLMul = " COALESCE((SELECT campaigns.mul FROM campaigns, touchpoints WHERE target.source_module = 'Touchpoints' AND target.source_id = touchpoints.id AND touchpoints.campaign_id = campaigns.id ),0) ";
        } else {
            $returnSQLScore = " COALESCE((SELECT score FROM campaigns WHERE target.campaign_id = campaigns.id ),0) ";
            $returnSQLMul = " COALESCE((SELECT mul FROM campaigns WHERE target.campaign_id = campaigns.id ),0) ";
        }

        return(array('scoreSQL' => $returnSQLScore,
                     'mulSQL' => $returnSQLMul));
    }
	function getScoreInfo ( &$scoreRow, &$config, &$module ) {
        $info = parent::getScoreInfo($scoreRow, $config, $module);
        $bean = loadBean('Campaigns');
        $bean->retrieve($scoreRow['rule_data']);
		$info['val'] = $bean->name;
		return($info);
	}

}