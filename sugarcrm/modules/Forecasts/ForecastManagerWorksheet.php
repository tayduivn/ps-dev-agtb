<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('modules/Users/User.php');

class ForecastManagerWorksheet extends SugarBean
{
    public $args;
    public $user_id;
    public $version;
    public $id;
    public $assigned_user_id;
    public $currency_id;
    public $base_rate;
    public $name;
    public $best_case;
    public $likely_case;
    public $worst_case;
    public $timeperiod_id;
    public $quota_id;
    public $commit_stage;
    public $quota;
    public $best_case_adjusted;
    public $likely_case_adjusted;
    public $worst_case_adjusted;
    public $show_history_log = 0;
    public $draft = 0;
    public $object_name = 'ForecastManagerWorksheet';
    public $module_name = 'ForecastManagerWorksheets';
    public $module_dir = 'Forecasts';
    public $table_name = 'forecast_manager_worksheets';
    public $disable_custom_fields = true;
    public $isManager = false;

    /**
     * Commit a manager forecast from the draft records
     *
     * @param User $manager
     * @param string $timeperiod
     * @return bool
     */
    public function commitManagerForecast(User $manager, $timeperiod)
    {
        // make sure that the User passed in is actually a manager
        if (!User::isManager($manager->id)) {
            return false;
        }

        /* @var $db DBManager */
        $db = DBManagerFactory::getInstance();

        $sql = 'SELECT name, assigned_user_id, team_id, team_set_id, quota, best_case, best_case_adjusted,
                likely_case, likely_case_adjusted, worst_case, worst_case_adjusted, currency_id, base_rate,
                timeperiod_id, user_id, opp_count, pipeline_opp_count, pipeline_amount
                FROM ' . $this->table_name . ' WHERE assigned_user_id = "' . $manager->id . '"
                AND timeperiod_id = "' . $db->quote($timeperiod) . '" and draft = 1 and deleted = 0';

        $results = $db->query($sql);

        while ($row = $db->fetchByAssoc($results)) {
            /* @var $worksheet ForecastManagerWorksheet */
            $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');

            $worksheet->retrieve_by_string_fields(
                array(
                    'user_id' => $row['user_id'],
                    // user id comes from the user model
                    'assigned_user_id' => $row['assigned_user_id'],
                    // the assigned user of the row is who the user reports to
                    'timeperiod_id' => $row['timeperiod_id'],
                    // the current timeperiod
                    'draft' => 0,
                    // we want to update the committed row
                    'deleted' => 0,
                )
            );
            foreach ($row as $key => $value) {
                $worksheet->$key = $value;
            }
            $worksheet->draft = 0; // make sure this is always 0!
            $worksheet->save();

            // commit the quota from the worksheet values
            $this->commitQuota($worksheet->quota, $worksheet->user_id, $worksheet->timeperiod_id);
            // recalculate the quotas now
            $this->recalcQuotas($worksheet->user_id, $worksheet->timeperiod_id, true);
        }

        return true;
    }

    /**
     * Commit a quota to the quotas table
     *
     * @param string $quota_amount
     * @param string $user_id
     * @param string $timeperiod_id
     */
    protected function commitQuota($quota_amount, $user_id, $timeperiod_id)
    {
        global $current_user;
        $quota_type = ($user_id == $current_user->id) ? 'Direct' : 'Rollup';
        /* @var $quota Quota */
        $quota = BeanFactory::getBean('Quotas');
        $quota->retrieve_by_string_fields(
            array(
                'timeperiod_id' => $timeperiod_id,
                'user_id' => $user_id,
                'committed' => 1,
                'quota_type' => $quota_type,
                'deleted' => 0
            )
        );

        // set all the values just to make sure
        $quota->timeperiod_id = $timeperiod_id;
        $quota->user_id = $user_id;
        $quota->committed = 1;
        $quota->quota_type = $quota_type;
        $quota->amount = $quota_amount;
        $quota->save();
    }


    /**
     * Roll up the data from the rep-worksheets to the manager worksheets
     *
     * @param User $reportee
     * @param $data
     * @return boolean
     */
    public function reporteeForecastRollUp(User $reportee, $data)
    {

        if (!isset($data['timeperiod_id']) || !is_guid($data['timeperiod_id'])) {
            $data['timeperiod_id'] = TimePeriod::getCurrentId();
        }

        // handle top level managers
        $reports_to = $reportee->reports_to_id;
        if (empty($reports_to)) {
            $reports_to = $reportee->id;
        }

        if (isset($data['forecast_type'])) {
            // check forecast type to see if the assigned_user_id should be equal to the $reportee as it's their own
            // rep worksheet
            if ($data['forecast_type'] == "Direct" && User::isManager($reportee->id)) {
                // this is the manager committing their own data, the $reports_to should be them
                // and not their actual manager
                $reports_to = $reportee->id;
            } else {
                if ($data['forecast_type'] == "Rollup" && $reports_to == $reportee->id) {
                    // if type is rollup and reports_to is equal to the $reportee->id (aka no top level manager),
                    // we don't want to update their draft record so just ignore this,
                    return false;
                }
            }
        }

        if (isset($data['draft']) && $data['draft'] == '1' && $GLOBALS['current_user']->id == $reportee->id) {
            // this data is for the current user, but is not a commit so we need to update their own draft record
            $reports_to = $reportee->id;
        }

        $this->retrieve_by_string_fields(
            array(
                'user_id' => $reportee->id, // user id comes from the user model
                'assigned_user_id' => $reports_to, // the assigned user of the row is who the user reports to
                'timeperiod_id' => $data['timeperiod_id'], // the current timeperiod
                'draft' => 1, // we only ever update the draft row
                'deleted' => 0,
            )
        );

        $copyMap = array(
            'likely_case',
            array('likely_case_adjusted' => 'likely_adjusted'),
            'best_case',
            array('best_case_adjusted' => 'best_adjusted'),
            'worst_case',
            array('worst_case_adjusted' => 'worst_adjusted'),
            'currency_id',
            'base_rate',
            'timeperiod_id',
            'quota',
            'opp_count',
            'pipeline_opp_count',
            'pipeline_amount'
        );

        // we don't have a row to update, so set the values to the adjusted column
        if (empty($this->id)) {
            // array key equals value on the bean, array value equals field in the data variable
            if (!isset($data['likely_adjusted'])) {
                $copyMap[] = array('likely_case_adjusted' => 'likely_case');
            }
            if (!isset($data['best_adjusted'])) {
                $copyMap[] = array('best_case_adjusted' => 'best_case');
            }
            if (!isset($data['worst_adjusted'])) {
                $copyMap[] = array('worst_case_adjusted' => 'worst_case');
            }

            if (!isset($data['quota']) || empty($data['quota'])) {
                // we need to get the quota if one exists
                /* @var $quotaSeed Quota */
                $quotaSeed = BeanFactory::getBean('Quotas');

                // check if we need to get the roll up amount
                $getRollupQuota = (User::isManager(
                    $reportee->id
                ) && isset($data['forecast_type']) && $data['forecast_type'] == 'Rollup');

                $quota = $quotaSeed->getRollupQuota($data['timeperiod_id'], $reportee->id, $getRollupQuota);

                $data['quota'] = $quota['amount'];
            }
        }

        $this->copyValues($copyMap, $data);

        // set the team to the default ones from the passed in user
        $this->team_set_id = $reportee->team_set_id;
        $this->team_id = $reportee->team_id;

        $this->name = $reportee->full_name;
        $this->user_id = $reportee->id;
        $this->assigned_user_id = $reports_to;
        $this->draft = 1;

        $this->save();

        // roll up the draft value for best/likely/worst case values to the committed record if one exists
        $this->rollupDraftToCommittedWorksheet($this);

        return true;
    }

    /**
     * @param ForecastManagerWorksheet $worksheet
     * @return bool
     */
    protected function rollupDraftToCommittedWorksheet(ForecastManagerWorksheet $worksheet)
    {
        /* @var $committed_worksheet ForecastManagerWorksheet */
        $committed_worksheet = BeanFactory::getBean($this->module_name);
        $committed_worksheet->retrieve_by_string_fields(
            array(
                'user_id' => $worksheet->user_id, // user id comes from the user model
                'assigned_user_id' => $worksheet->assigned_user_id,
                'timeperiod_id' => $worksheet->timeperiod_id, // the current timeperiod
                'draft' => 0,
                'deleted' => 0,
            )
        );

        if (empty($committed_worksheet->id)) {
            return false;
        }

        $copyMap = array(
            'likely_case',
            'best_case',
            'worst_case',
        );

        $this->copyValues($copyMap, $worksheet->toArray(), $committed_worksheet);

        $committed_worksheet->save();

        return true;
    }

    /**
     * Copy the fields from the $seed bean to the worksheet object
     *
     * @param array $fields
     * @param array $seed
     * @param ForecastManagerWorksheet $bean        Optional, If not set, it will be set to the current object
     */
    protected function copyValues($fields, array $seed, ForecastManagerWorksheet &$bean = null)
    {
        if (is_null($bean)) {
            $bean = $this;
        }

        foreach ($fields as $field) {
            $key = $field;
            if (is_array($field)) {
                // if we have an array it should be a key value pair, where the key is the destination
                // value and the value, is the seed value
                $key = array_shift(array_keys($field));
                $field = array_shift($field);
            }
            // make sure the field is set, as not to cause a notice since a field might get unset() from the $seed class
            if (isset($seed[$field])) {
                $bean->$key = $seed[$field];
            }
        }
    }

    /**
     * Save Worksheet
     *
     * @param bool $check_notify
     */
    public function saveWorksheet($check_notify = false)
    {
        $this->isManager = User::isManager($this->user_id);

        // save to the manager worksheet table (new table)
        // get the user object
        /* @var $userObj User */
        $userObj = BeanFactory::getBean('Users', $this->user_id);
        /* @var $mgr_worksheet ForecastManagerWorksheet */
        $mgr_worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $mgr_worksheet->reporteeForecastRollUp($userObj, $this->args);


    }

    public function fill_in_additional_detail_fields()
    {
        parent::fill_in_additional_detail_fields();
        // see if the value should show the historyLog Button
        $sq = new SugarQuery();
        $sq->select('date_modified');
        $sq->from(BeanFactory::getBean($this->module_name))->where()
            ->equals('assigned_user_id', $this->assigned_user_id)
            ->equals('user_id', $this->user_id)
            ->equals('draft', 0)
            ->equals('timeperiod_id', $this->timeperiod_id);
        $beans = $sq->execute();

        if (empty($beans)) {
            $this->show_history_log = 0;
        } else {
            $bean = $beans[0];
            $committed_date = $this->db->fromConvert($bean["date_modified"], "datetime");
            $timedate = TimeDate::getInstance();
            $this->show_history_log = intval(
                strtotime($committed_date) < strtotime($timedate->to_db($this->date_modified))
            );
        }
    }

    /**
     * Sets Worksheet args so that we save the supporting tables.
     *
     * @param array $args Arguments passed to save method through PUT
     */
    public function setWorksheetArgs($args)
    {
        // save the args publiciable
        $this->args = $args;

        // loop though the args and assign them to the corresponding key on the object
        foreach ($args as $arg_key => $arg) {
            $this->$arg_key = $arg;
        }
    }

    /**
     * Gets a sum of the passed in user's reportees quotas for a specific timeperiod
     *
     * @param string $userId The userID for which you want a reportee quota sum.
     * @param string $timeperiodId      the timeperiod to use
     * @return int Sum of quota amounts.
     */
    protected function getQuotaSum($userId, $timeperiodId)
    {
        $sql = "SELECT sum(q.amount) amount " .
            "FROM quotas q " .
            "INNER JOIN users u ON u.reports_to_id = '" . $userId . "' " .
            "AND q.user_id = u.id " .
            "AND q.timeperiod_id = '" . $timeperiodId . "' " .
            "AND q.quota_type = 'Rollup'";
        $amount = 0;

        $result = $this->db->query($sql);
        while ($row = $this->db->fetchByAssoc($result)) {
            $amount = $row['amount'];
        }

        return $amount;
    }

    /**
     * Gets the passed in user's committed quota value and direct quota ID
     *
     * @param string userId User id to query for
     * @param string $timeperiodId      the timeperiod to use
     * @return array id, Quota value
     */
    protected function getManagerQuota($userId, $timeperiodId)
    {
        /*
         * This info is in two rows, and either of them might not exist.  The union
         * is here to make sure data is returned if one or the other exists.  This statement
         * lets us grab both bits with one call to the db rather than two separate smaller
         * calls.
         *
         * We are looking for the ID of the quota where quota_type = Direct
         * and the AMOUNT of the quota where quota_type = Rollup
         */
        $sql = "SELECT q1.amount, q2.id FROM quotas q1 " .
            "left outer join quotas q2 " .
            "on q1.user_id = q2.user_id " .
            "and q1.timeperiod_id = q2.timeperiod_id " .
            "and q2.quota_type = 'Direct' " .
            "where q1.user_id = '" . $userId . "' " .
            "and q1.timeperiod_id = '" . $timeperiodId . "'" .
            "and q1.quota_type = 'Rollup' " .
            "union all " .
            "SELECT q2.amount, q1.id FROM quotas q1 " .
            "left outer join quotas q2 " .
            "on q1.user_id = q2.user_id " .
            "and q1.timeperiod_id = q2.timeperiod_id " .
            "and q2.quota_type = 'Rollup' " .
            "where q1.user_id = '" . $userId . "' " .
            "and q1.timeperiod_id = '" . $timeperiodId . "'" .
            "and q1.quota_type = 'Direct'";

        $quota = array();

        $result = $this->db->query($sql);
        while ($row = $this->db->fetchByAssoc($result)) {
            $quota["amount"] = $row["amount"];
            $quota["id"] = $row["id"];
        }

        return $quota;
    }

    /**
     * Recalculates quotas based on committed values and reportees' quota values
     *
     * @param string $user_id
     * @param string $timeperiodId
     * @param bool $fromCommit
     */
    protected function recalcQuotas($user_id, $timeperiodId, $fromCommit = false)
    {
        global $current_user;

        //Calculate Manager direct
        $mgr_quota = $this->recalcUserQuota($current_user->id, $timeperiodId);

        // update the quota for the managers if the reportee's have changed.
        $this->updateManagerWorksheetQuota($current_user->id, $timeperiodId, $mgr_quota, true);
        if ($fromCommit == true) {
            // when it's from a commit, we need to update the committed record as well.
            $this->updateManagerWorksheetQuota($current_user->id, $timeperiodId, $mgr_quota, false);
        }
        //Calculate reportee direct
        $rep_quota = $this->recalcUserQuota($user_id, $timeperiodId);

        // update the quota for the managers if the reportee's have changed.
        $this->updateManagerWorksheetQuota($user_id, $timeperiodId, $rep_quota, true);
        if ($fromCommit == true) {
            // when it's from a commit, we need to update the committed record as well.
            $this->updateManagerWorksheetQuota($user_id, $timeperiodId, $rep_quota, false);
        }
    }

    /**
     * Update the manager draft record with the recalculated quota
     *
     * @param string $manager_id
     * @param string $timeperiod
     * @param number $quota
     * @param boolean $isDraft
     * @return bool
     */
    protected function updateManagerWorksheetQuota($manager_id, $timeperiod, $quota, $isDraft = true)
    {
        // safe guard to make sure user is actually a manager
        if (!User::isManager($manager_id)) {
            return false;
        }

        /* @var $worksheet ForecastManagerWorksheet */
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');

        $return = $worksheet->retrieve_by_string_fields(
            array(
                'user_id' => $manager_id, // user id comes from the user model
                'assigned_user_id' => $manager_id, // the assigned user of the row is who the user reports to
                'timeperiod_id' => $timeperiod, // the current timeperiod
                'draft' => intval($isDraft),
                'deleted' => 0,
            )
        );

        if (is_null($return)) {
            // no record found, just ignore this
            return false;
        }

        if ($quota != $worksheet->quota) {
            // only update the date_modified if it's a draft version
            $worksheet->update_date_modified = false;
            $worksheet->quota = $quota;
            $worksheet->save();
        }

        return true;
    }

    /**
     * Recalculates a specific user's direct quota
     *
     * @param string $userId    User Id of quota that needs recalculated.
     * @param string $timeperiodId      the timeperiod to use
     * @return number           The New total for the passed in user
     */
    protected function recalcUserQuota($userId, $timeperiodId)
    {
        $reporteeTotal = $this->getQuotaSum($userId, $timeperiodId);
        $managerQuota = $this->getManagerQuota($userId, $timeperiodId);
        $managerAmount = (isset($managerQuota['amount'])) ? $managerQuota['amount'] : '0';
        $newTotal = SugarMath::init($managerAmount)->sub($reporteeTotal)->result();
        if ($newTotal < 0) {
            $newTotal = '0';
        }

        //save Manager quota
        /* @var $quota Quota */
        $quota = BeanFactory::getBean('Quotas', isset($managerQuota['id']) ? $managerQuota['id'] : null);
        $quota->user_id = $userId;
        $quota->timeperiod_id = $timeperiodId;
        $quota->quota_type = 'Direct';
        $quota->amount = $newTotal;
        $quota->save();

        return $newTotal;
    }

    /**
     * This method emulates the Forecast Manager Worksheet calculateTotals method.
     *
     * @param string $userId
     * @param string $timeperiodId
     * @return array|bool            Return calculated totals or boolean false if timeperiod is not valid
     */
    public function worksheetTotals($userId, $timeperiodId)
    {
        /* @var $tp TimePeriod */
        $tp = BeanFactory::getBean('TimePeriods', $timeperiodId);
        if (empty($tp->id)) {
            // timeperiod not found
            return false;
        }

        $return = array(
            "quota" => '0',
            "best_case" => '0',
            "best_adjusted" => '0',
            "likely_case" => '0',
            "likely_adjusted" => '0',
            "worst_case" => '0',
            "worst_adjusted" => '0',
            "included_opp_count" => 0,
            "pipeline_opp_count" => 0,
            "pipeline_amount" => '0'
        );

        require_once('include/SugarQuery/SugarQuery.php');
        $sq = new SugarQuery();
        $sq->select(array('*'));
        $sq->from(BeanFactory::getBean($this->module_name))->where()
            ->equals('timeperiod_id', $tp->id)
            ->equals('assigned_user_id', $userId)
            ->equals('draft', 1)
            ->equals('deleted', 0);
        $results = $sq->execute();

        foreach ($results as $row) {
            $return['quota'] = SugarMath::init($return['quota'], 6)->add(
                SugarCurrency::convertWithRate($row['quota'], $row['base_rate'])
            )->result();
            $return['best_case'] = SugarMath::init($return['best_case'], 6)->add(
                SugarCurrency::convertWithRate($row['best_case'], $row['base_rate'])
            )->result();
            $return['best_adjusted'] = SugarMath::init($return['best_adjusted'], 6)->add(
                SugarCurrency::convertWithRate($row['best_case_adjusted'], $row['base_rate'])
            )->result();
            $return['likely_case'] = SugarMath::init($return['likely_case'], 6)->add(
                SugarCurrency::convertWithRate($row['likely_case'], $row['base_rate'])
            )->result();
            $return['likely_adjusted'] = SugarMath::init($return['likely_adjusted'], 6)->add(
                SugarCurrency::convertWithRate($row['likely_case_adjusted'], $row['base_rate'])
            )->result();
            $return['worst_case'] = SugarMath::init($return['worst_case'], 6)->add(
                SugarCurrency::convertWithRate($row['worst_case'], $row['base_rate'])
            )->result();
            $return['worst_adjusted'] = SugarMath::init($return['worst_adjusted'], 6)->add(
                SugarCurrency::convertWithRate($row['worst_case_adjusted'], $row['base_rate'])
            )->result();

            $return['included_opp_count'] += $row['opp_count'];
            $return['pipeline_opp_count'] += $row['pipeline_opp_count'];
            $return['pipeline_amount'] = SugarMath::init($return['pipeline_amount'], 6)
                ->add($row['pipeline_amount'])->result();
        }

        return $return;
    }


}

