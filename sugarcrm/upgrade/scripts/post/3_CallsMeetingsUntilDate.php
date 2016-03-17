<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * Converts Calls and Meetings with until date to datetime with user timezone relation
 */
class SugarUpgradeCallsMeetingsUntilDate extends UpgradeScript
{
    public $order = 3001;
    public $type = self::UPGRADE_DB;

    /**
     * List of modules for upgrade.
     * @var array $modulesForUpgrade
     */
    protected $modulesForUpgrade = array(
        'Calls',
        'Meetings',
    );

    /**
     * Checks if upgrade allowed for current sugar version.
     *
     * @return bool
     */
    protected function isUpgradeAllowed()
    {
        return (bool)version_compare($this->from_version, '7.8RC3', '<');
    }

    /**
     * Returns Query object to work with.
     *
     * @return SugarQuery
     */
    protected function getSugarQuery()
    {
        return new SugarQuery();
    }

    /**
     * Returns SugarBean by module name.
     *
     * @param string $module
     * @return null|SugarBean
     */
    protected function getSugarBean($module)
    {
        return BeanFactory::getBean($module);
    }

    /**
     * Returns user object by it ID.
     *
     * @param string $userId
     * @return null|User
     */
    protected function getUserBean($userId)
    {
        return BeanFactory::getBean('Users', $userId);
    }

    /**
     * Run upgrade process for $modulesForUpgrade array.
     *
     * @throws SugarQueryException
     */
    public function run()
    {
        if (!$this->isUpgradeAllowed()) {
            return;
        }

        foreach ($this->modulesForUpgrade as $module) {
            $bean = $this->getSugarBean($module);

            $query = $this->getSugarQuery();
            $query->from($bean);
            $query->select(array('id', 'created_by', 'repeat_until'));
            $query->where()->notNull('repeat_type');
            $query->where()->notNull('repeat_until');
            $rows = $query->execute();

            foreach ($rows as $row) {
                if (empty($row['repeat_until'])) {
                    continue;
                }

                $row['repeat_until'] = $this->db->fromConvert($row['repeat_until'], 'datetime');
                $user = $this->getUserBean($row['created_by']);
                $timeDate = new \TimeDate($user);
                $utcValue = $timeDate->fromDb($row['repeat_until']);
                $userValue = $timeDate->tzUser($utcValue)->setTime(23, 59, 0);
                $userDependingDbValue = $timeDate->asDb($userValue);
                $this->db->updateParams(
                    $bean->table_name,
                    $bean->getFieldDefinitions(),
                    array('repeat_until' => $userDependingDbValue),
                    array('id' => $row['id']),
                    null,
                    true,
                    true
                );
            }
        }
    }
}
