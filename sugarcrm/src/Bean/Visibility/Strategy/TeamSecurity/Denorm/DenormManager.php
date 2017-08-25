<?php
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

namespace Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Denorm;

use Sugarcrm\Sugarcrm\Bean\Visibility\Strategy\TeamSecurity\Exception\DenormManagerException;
use Sugarcrm\Sugarcrm\Logger\LoggerTransition;
use Sugarcrm\Sugarcrm\Util\Uuid;
use Psr\Log\LoggerInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

/**
 *
 * Denormalization Manager
 *
 */
class DenormManager
{
    /**
     * Denormalization tables
     * @var string
     */
    const DENORM_TABLE_1 = 'team_sets_users_denorm1';
    const DENORM_TABLE_2 = 'team_sets_users_denorm2';

    /**
     * Denormalized queue table to push updates during full rebuild.
     * @var string
     */
    const DENORM_QUEUE_TABLE = 'team_set_denorm_queue';

    /**
     * $sugar_config to determine if use of denormalized table is enabled
     * @var string
     */
    const CONFIG_PERF_KEY = "perfProfile.TeamSecurity";

    /**
     * $sugar_config key to determine if inline updates to denormalized table should be applied for admin actions
     * @var string
     */
    const CONFIG_ADMIN_ACTION_UPDATE_KEY = "perfProfile.TeamSecurity.admin_action_denorm_updates";


    /**
     * Administration config settings
     */
    const ADMIN_CONFIG_KEY = 'TeamSecurity';

    /**
     * @var DenormManagerInterface
     */
    protected static $instance;

    /**
     * @var \DBManager
     */
    protected $db;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \SugarConfig
     */
    protected $sugarConfig;

    /**
     * @var string
     */
    protected $activeTable = '';

    /**
     * Setting to determine if data in denormalized table can be used for team security implementation.
     * @var boolean
     */
    protected $isValidForUse = false;

    /**
     * Setting to determine if rebuild of denormalized table is needed during scheduler run.
     * @var boolean
     */
    protected $isRebuildRequired = false;

    /**
     * Setting to determine if currently full rebuild of denormalized table is in progress.
     * @var boolean
     */
    protected $isRebuildRunning = false;

    /**
     * @var Array
     */
    protected static $teamUsers;

    /**
     * @var boolean
     */
    protected $isEnabledUseDenorm;

    /**
     * Ctor
     * @param \DBManager $db
     * @param LoggerInterface $logger
     * @param \SugarConfig $sugarConfig
     */
    public function __construct(\DBManager $db, LoggerInterface $logger, \SugarConfig $sugarConfig)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->sugarConfig = $sugarConfig;

        $settings = \Administration::getSettings('TeamSecurity');
        // set active table
        if (!empty($settings->settings['TeamSecurity_denorm_active_table'])) {
            $active = $settings->settings['TeamSecurity_denorm_active_table'];
            if ($active === self::DENORM_TABLE_1 || $active === self::DENORM_TABLE_2) {
                $this->activeTable = $active;
            }
        }

        $this->isEnabledUseDenorm = $this->getIsEnabledUseDenormOption();

        if (isset($settings->settings['TeamSecurity_denorm_valid_for_use'])) {
            $this->isValidForUse = $settings->settings['TeamSecurity_denorm_valid_for_use']==1? true:false;
        }

        if (isset($settings->settings['TeamSecurity_denorm_rebuild_required'])) {
            $this->isRebuildRequired = $settings->settings['TeamSecurity_denorm_rebuild_required']==1? true:false;
        }

        if (isset($settings->settings['TeamSecurity_denorm_rebuild_running'])) {
            $this->isRebuildRunning = $settings->settings['TeamSecurity_denorm_rebuild_running']==1? true:false;
        }
    }

    /**
     * @return string
     */
    public function getActiveTbl()
    {
        return $this->activeTable;
    }

    /**
     * @param string $value
     */
    protected function setActiveTbl($value)
    {
         $this->setTeamSecuritySettings('denorm_active_table', $value);
         $this->activeTable = $value;
    }

    /**
     * @return boolean
     */
    public function getIsValidForUse()
    {
        return $this->isValidForUse;
    }

    /**
     * @param boolean $isValidForUse
     */
    protected function setIsValidForUse($isValidForUse = false)
    {
        $this->setTeamSecuritySettings('denorm_valid_for_use', $isValidForUse? 1:0);
        $this->isValidForUse = $isValidForUse;
    }

    /**
     * Mark the data in denormalized table as invalid for use. If the data is marked
     * as invalid at any point denormalized table can only be used after full rebuild.
     */
    protected function markInvalidForUse()
    {
        if ($this->isValidForUse !== false) {
            $this->setIsValidForUse(false);
        }
        $this->markRebuildRequired();
    }

    /**
     * @return boolean
     */
    public function getIsRebuildRequired()
    {
        return $this->isRebuildRequired;
    }

    /**
     * @param boolean $isRebuildRequired
     */
    protected function setIsRebuildRequired($isRebuildRequired = false)
    {
        $this->setTeamSecuritySettings('denorm_rebuild_required', $isRebuildRequired? 1:0);
        $this->isRebuildRequired = $isRebuildRequired;
    }

    /**
     * Mark the rebuild required as true. This flag is used to determine
     * if full rebuild should be run during the next scheduler run.
     */
    protected function markRebuildRequired()
    {
        if ($this->isRebuildRequired !== true) {
            $this->setIsRebuildRequired(true);
        }
    }

    /**
     * @return boolean
     */
    public function getIsRebuildRunning()
    {
        return $this->isRebuildRunning;
    }

    /**
     * @param boolean $isRebuildRunning
     */
    protected function setIsRebuildRunning($isRebuildRunning = false)
    {
        $this->setTeamSecuritySettings('denorm_rebuild_running', $isRebuildRunning? 1:0);
        $this->isRebuildRunning = $isRebuildRunning;
    }

    /**
     * Get current standby table.
     * @return string
     */
    protected function getStandbyTbl()
    {
        if (empty($this->activeTable)) {
            return '';
        }

        return $this->activeTable === self::DENORM_TABLE_1 ? self::DENORM_TABLE_2 : self::DENORM_TABLE_1;
    }

    /**
     * Set TeamSecurity admin settings.
     * @param string $key
     * @param boolean|number|string $value
     */
    protected function setTeamSecuritySettings($key, $value)
    {
        $admin = \BeanFactory::newBean('Administration');
        $admin->saveSetting(self::ADMIN_CONFIG_KEY, $key, $value);
    }

    /**
     * Swap current active table with standby table after rebuild.
     */
    protected function swapTables()
    {
        if (empty($this->activeTable)) {
            return;
        }

        $standby = $this->getStandbyTbl();

        $this->setActiveTbl($standby);
    }

    /**
     * Get DenormManager instance
     * @return DenormManagerInterface
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            $db = \DBManagerFactory::getInstance();
            $logger = new LoggerTransition(\LoggerManager::getLogger());
            $sugarConfig = \SugarConfig::getInstance();
            self::$instance = new self($db, $logger, $sugarConfig);
        }
        return self::$instance;
    }

    /**
     * Initialize and rebuild team security denormalization.
     * @throws DenormManagerException
     * @return multitype:boolean number string
     */
    public function initializeAndRebuild()
    {
        try {
            $start = time();
            $success = true;
            $errorMsg = '';

            //If rebuild is already running, skip the current request.
            if ($this->getIsRebuildRunning()) {
                $errorMsg = 'Team Security denormalized table rebuild is already running.';
                $success = false;
                $duration = time() - $start;
                return array($success, $duration, $errorMsg);
            }

            // Make sure our denormalization tables exist. If they don't exist
            // this can be resolved using rebuild process.
            if (!$this->denormTablesExist()) {
                throw new DenormManagerException('Denormalization tables do not exist');
            }

            //Set isRebuildRunning to true when starting a rebuild.
            $this->setIsRebuildRunning(true);

            //Reset isRebuildRequired flag when starting a rebuild.
            $this->setIsRebuildRequired(false);

            //Get the standby table to rebuild.
            $targetTable = empty($this->getStandbyTbl())? self::DENORM_TABLE_1: $this->getStandbyTbl();

            //Rebuild Team Security denormalized data in the standby table.
            $this->rebuild($targetTable);

            //Set the current rebuild target table as active table on successful rebuild.
            $this->setActiveTbl($targetTable);

            //Set isValidForUse to true after successful rebuild.
            $this->setIsValidForUse(true);

            //Reset isRebuildRunning running flag on success.
            $this->setIsRebuildRunning(false);

            //Add records queued in denorm queue table during rebuidl to the target table.
            $this->consumeDataFromDenormQueue();
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $errorMsg = $e->getMessage();
            $success = false;
            //Set isRebuildRequired to true on failure.
            $this->setIsRebuildRequired(true);
            //Reset isRebuildRunning running flag on faliure.
            $this->setIsRebuildRunning(false);
            $duration = time() - $start;
            return array($success, $duration, $errorMsg);
        }
        $duration = time() - $start;
        return array($success, $duration, $errorMsg);
    }

    /**
     * Rebuild target table
     */
    protected function rebuild($targetTable)
    {
        $this->cleanTargetTable($targetTable);
        $this->db->query($this->getRebuildSql($targetTable));
    }

    /**
     * Remove all data from the target table
     */
    protected function cleanTargetTable($targetTable)
    {
        $builder = $this->db->getConnection()->createQueryBuilder();
        $builder->delete($targetTable);
        $builder->execute();
    }

    /**
     * Rebuild query for denormalized table.
     * @param string $targetTable
     * @return string
     */
    protected function getRebuildSql($targetTable)
    {
        return "INSERT INTO {$targetTable}
                    SELECT
                        ts.id AS team_set_id,
                        tm.user_id AS user_id
                    FROM team_sets ts
                    INNER JOIN team_sets_teams tst
                        ON ts.id = tst.team_set_id
                        AND tst.deleted = 0
                    INNER JOIN teams t
                        ON tst.team_id = t.id
                        AND t.deleted = 0
                    INNER JOIN team_memberships tm
                        ON t.id = tm.team_id
                        AND tm.deleted = 0
                    GROUP BY ts.id, tm.user_id";
    }

    /**
     * Check if denormalization tables exist
     * @return boolean
     */
    protected function denormTablesExist()
    {
        $result = true;
        foreach (array(self::DENORM_TABLE_1, self::DENORM_TABLE_2) as $table) {
            if (!$this->db->tableExists($table)) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Verify if inline updates to denormalized table for admin actions is enabled.
     * @return boolean
     */
    public function isEnabledAdminActionUpdate()
    {
        return $this->sugarConfig->get(self::CONFIG_ADMIN_ACTION_UPDATE_KEY, false);
    }

    /**
     * Verify if use of denormalized table is enabled.
     * @return boolean
     */
    public function isEnabledUseDenorm()
    {
        return $this->isEnabledUseDenorm;
    }

    /**
     * Check if use_denorm is enabled for any module and returns the value.
     * @return boolean
     */
    protected function getIsEnabledUseDenormOption()
    {
        $tsConfigs = $this->sugarConfig->get(self::CONFIG_PERF_KEY, array());
        foreach ($tsConfigs as $key => $value) {
            if (is_array($value) && $value['use_denorm'] === true) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verify if denormalization setup is available for use.
     * @return boolean
     */
    public function isAvailable()
    {
        if (!empty($this->getActiveTbl()) && $this->getIsValidForUse()) {
            return true;
        } elseif ($this->isEnabledUseDenorm() || $this->isEnabledAdminActionUpdate()) {
            $this->logger->critical("Team Security is enabled but the normalized table not setup. Run full rebuild.");
        }

        return false;
    }

    /**
     * Sync denormalized table when removing a user from team.
     * @param string $teamId
     * @param string $userId
     */
    public function removeTeamUserFromTeamSets($teamId, $userId)
    {
        if (!($this->isAvailable() && $this->isEnabledAdminActionUpdate())) {
            $this->markRebuildRequired();
            return;
        }
        $teamSetIdSubQuery = $this->db->getConnection()->createQueryBuilder();
        $teamSetIdSubQuery->select('tst.team_set_id')
        ->from('team_sets_teams', 'tst')
        ->join('tst', 'team_memberships', 'team_memberships', 'tst.team_id = team_memberships.team_id')
        ->where(
            $teamSetIdSubQuery->expr()->eq(
                'team_memberships.user_id',
                $teamSetIdSubQuery->createPositionalParameter($userId)
            )
        )->andWhere($teamSetIdSubQuery->expr()->eq('team_memberships.deleted', '0'));

        $builder = $this->db->getConnection()->createQueryBuilder();
        $builder->delete($this->getActiveTbl())
        ->where(
            $builder->expr()->eq('user_id', $builder->createPositionalParameter($userId))
        )->andWhere(
            $builder->expr()->notIn('team_set_id', $builder->importSubQuery($teamSetIdSubQuery))
        );
        $builder->execute();
        if ($this->getIsRebuildRunning()) {
            $action = __FUNCTION__;
            $params = json_encode(func_get_args());
            $this->insertDataToDenormQueue($action, $params);
        }
    }

    /**
     * Sync denormalized table when adding a user to team.
     * @param string $teamId
     * @param string $userId
     */
    public function addTeamUserToTeamSets($teamId, $userId)
    {
        if (!($this->isAvailable() && $this->isEnabledAdminActionUpdate())) {
            $this->markRebuildRequired();
            return;
        }
        $teamTeamSetIds = $this->db->getConnection()->createQueryBuilder();
        $teamTeamSetIds->select('tst.team_set_id')
        ->from('team_sets_teams', 'tst')
        ->where(
            $teamTeamSetIds->expr()->eq('tst.team_id', $teamTeamSetIds->createPositionalParameter($teamId))
        )
        ->andWhere($teamTeamSetIds->expr()->eq('tst.deleted', '0'));
        $teamSetIds = array_unique($teamTeamSetIds->execute()->fetchAll(\PDO::FETCH_COLUMN));

        //Insert TeamSets,User mappings in denormalized table. Ignore if exists (UniqueConstraintViolationException)
        $fieldDefs = $GLOBALS['dictionary'][$this->getActiveTbl()]['fields'];
        foreach ($teamSetIds as $teamSetId) {
            $data = array(
                'team_set_id' => $teamSetId,
                'user_id' => $userId,
            );
            try {
                $this->db->insertParams($this->getActiveTbl(), $fieldDefs, $data);
            } catch (UniqueConstraintViolationException $e) {
                //Do Nothing
            }
        }
        if ($this->getIsRebuildRunning()) {
            $action = __FUNCTION__;
            $params = json_encode(func_get_args());
            $this->insertDataToDenormQueue($action, $params);
        }
    }

    /**
     * Sync denormalized table when deleting a team.
     * @param array $teamSetIds
     * @param string $teamId
     * @param array $userIds
     */
    public function removeTeamFromTeamSets(array $teamSetIds, $teamId, array $userIds = array())
    {
        if (!($this->isAvailable() && $this->isEnabledAdminActionUpdate())) {
            $this->markRebuildRequired();
            return;
        }
        if (empty($userIds)) {
            $userIds = $this->getTeamUsers($teamId);
        }
        $teamSetUsersSubQuery = $this->db->getConnection()->createQueryBuilder();
        $teamSetUsersSubQuery->select('team_memberships.user_id')
        ->from('team_sets_teams', 'tst')
        ->join('tst', 'team_memberships', 'team_memberships', 'tst.team_id = team_memberships.team_id')
        ->where(
            $teamSetUsersSubQuery->expr()->in(
                'tst.team_set_id',
                $teamSetUsersSubQuery->createPositionalParameter(array_values($teamSetIds), Connection::PARAM_STR_ARRAY)
            )
        )->andWhere($teamSetUsersSubQuery->expr()->eq('team_memberships.deleted', '0'));

        $builder = $this->db->getConnection()->createQueryBuilder();
        $builder->delete($this->getActiveTbl())
        ->where(
            $builder->expr()->in(
                'user_id',
                $builder->createPositionalParameter($userIds, Connection::PARAM_STR_ARRAY)
            )
        )->andWhere(
            $builder->expr()->in(
                'team_set_id',
                $builder->createPositionalParameter(array_values($teamSetIds), Connection::PARAM_STR_ARRAY)
            )
        )->andWhere(
            $builder->expr()->notIn('user_id', $builder->importSubQuery($teamSetUsersSubQuery))
        );
        $builder->execute();
        if ($this->getIsRebuildRunning()) {
            $action = __FUNCTION__;
            $params = json_encode(array_merge(func_get_args(), [$userIds]));
            $this->insertDataToDenormQueue($action, $params);
        }
    }

    /**
     * Sync denormalized table when deleting a team set.
     * @param array $teamSetIds
     */
    public function removeTeamSets(array $teamSetIds)
    {
        if (!($this->isAvailable() && $this->isEnabledAdminActionUpdate())) {
            $this->markRebuildRequired();
            return;
        }
        $builder = $this->db->getConnection()->createQueryBuilder();
        $builder->delete($this->getActiveTbl());
        $builder->where(
            $builder->expr()->in(
                'team_set_id',
                $builder->createPositionalParameter(array_values($teamSetIds), Connection::PARAM_STR_ARRAY)
            )
        );
        $builder->execute();
        if ($this->getIsRebuildRunning()) {
            $action = __FUNCTION__;
            $params = json_encode(func_get_args());
            $this->insertDataToDenormQueue($action, $params);
        }
    }

    /**
     * Sync denormalized table when creating a team set.
     * @param string $teamSetId
     * @param string[] $teamIds List of team ids
     */
    public function addTeamSetsTeams($teamSetId, array $teamIds)
    {
        if (!($this->isAvailable()
            && ($this->isEnabledAdminActionUpdate() || $this->isEnabledUseDenorm()))) {
            $this->markInvalidForUse();

            //If rebuild is running we need to push the user action updates to queue table
            if ($this->getIsRebuildRunning()) {
                $action = __FUNCTION__;
                $params = json_encode(func_get_args());
                $this->insertDataToDenormQueue($action, $params);
            }
            return;
        }
        $teamUsersSubQuery = $this->db->getConnection()->createQueryBuilder();
        $teamUsersSubQuery->select('user_id')
        ->from('team_memberships')
        ->where(
            $teamUsersSubQuery->expr()->in(
                'team_id',
                $teamUsersSubQuery->createPositionalParameter(array_values($teamIds), Connection::PARAM_STR_ARRAY)
            )
        )->andWhere($teamUsersSubQuery->expr()->eq('deleted', '0'));
        $userIds = array_unique($teamUsersSubQuery->execute()->fetchAll(\PDO::FETCH_COLUMN));

        //Insert TeamSets,User mappings in denormalized table. Ignore if exists (UniqueConstraintViolationException)
        $fieldDefs = $GLOBALS['dictionary'][$this->getActiveTbl()]['fields'];
        foreach ($userIds as $userId) {
            $data = array(
                'team_set_id' => $teamSetId,
                'user_id' => $userId,
            );
            try {
                $this->db->insertParams($this->getActiveTbl(), $fieldDefs, $data);
            } catch (UniqueConstraintViolationException $e) {
                //Do Nothing
            }
        }
        if ($this->getIsRebuildRunning()) {
            $action = __FUNCTION__;
            $params = json_encode(func_get_args());
            $this->insertDataToDenormQueue($action, $params);
        }
    }

    /**
     * Add denorm table updates to queue table during rebuild.
     * This needs be pushed back to target table after full rebuild.
     * @param string $query
     * @param string $params
     */
    protected function insertDataToDenormQueue($action, $params)
    {
        if (!$this->getIsRebuildRunning()) {
            return;
        }
        $fieldDefs = $GLOBALS['dictionary'][self::DENORM_QUEUE_TABLE]['fields'];
        $data = array(
            'id' => Uuid::uuid4(),
            'action' => $action,
            'params' => $params,
            'date_created' => \TimeDate::getInstance()->nowDb(),
        );
        $this->db->insertParams(self::DENORM_QUEUE_TABLE, $fieldDefs, $data);
    }

    /**
     * Replay all actions added to denorm queue table after rebuild.
     * @param string $targetTable
     */
    protected function consumeDataFromDenormQueue()
    {
        $query = sprintf(
            'SELECT id, action, params FROM %s order by date_created asc',
            self::DENORM_QUEUE_TABLE
        );
        $conn = $this->db->getConnection();
        $stmt = $conn->executeQuery($query);
    
        $deleteFromDenormQueue = array();
        while ($row = $stmt->fetch()) {
            $action = $row['action'];
            $params = json_decode($row['params']);
            call_user_func_array(array($this, $action), $params);
            $deleteFromDenormQueue[] = $row['id'];
        }
        $this->deleteFromDenormQueue($deleteFromDenormQueue);
    }

    /**
     * Delete supplied ids from denorm queue table
     * @param array $ids
     */
    protected function deleteFromDenormQueue(array $ids)
    {
        $builder = $this->db->getConnection()->createQueryBuilder();
        $builder->delete(self::DENORM_QUEUE_TABLE)
        ->where(
            $builder->expr()->in(
                'id',
                $builder->createPositionalParameter($ids, Connection::PARAM_STR_ARRAY)
            )
        );
        $builder->execute();
    }

    /**
     * Set list of users for a team.
     * @param string $teamId
     */
    public function setTeamUsers($teamId)
    {
        if (!($this->isAvailable() && $this->isEnabledAdminActionUpdate())) {
            $this->markRebuildRequired();
            return;
        }

        $userQuery = $this->db->getConnection()->createQueryBuilder();
        $userQuery->select('user_id')
        ->from('team_memberships')
        ->where(
            $userQuery->expr()->eq('team_id', $userQuery->createPositionalParameter($teamId))
        )
        ->andWhere($userQuery->expr()->eq('deleted', '0'));
        $userIds = array_unique($userQuery->execute()->fetchAll(\PDO::FETCH_COLUMN));
        self::$teamUsers[$teamId] = $userIds;
    }

    /**
     * Get list of users for a team.
     * @param string $teamId
     * @return array
     */
    public function getTeamUsers($teamId)
    {
        return self::$teamUsers[$teamId];
    }
}
