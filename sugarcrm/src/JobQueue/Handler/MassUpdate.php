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

namespace Sugarcrm\Sugarcrm\JobQueue\Handler;

use Sugarcrm\Sugarcrm\JobQueue\Exception\LogicException;
use Sugarcrm\Sugarcrm\Logger\LoggerTransition;

class MassUpdate implements RunnableInterface
{
    /**
     * Ability to rerun the task.
     */
    const RERUN = true;

    /**
     * @var array $allowedActions Allowed values for $data['action'] param.
     */
    protected $allowedActions = array('save', 'delete');

    /**
     * @var \MassUpdate
     */
    protected $massUpdate;

    /**
     * @var string $action Action name.
     */
    protected $action;

    /**
     * @var string $module Module name.
     */
    protected $module;

    /**
     * @var array $uid Of ids.
     */
    protected $uid;

    /**
     * @var array $data For update.
     */
    protected $data;

    /**
     * @var array $prospectLists
     */
    protected $prospectLists;

    /**
     * @param string $action
     * @param string $module
     * @param array $uid
     * @param array $data
     * @param array $prospectLists
     * @throws LogicException
     */
    public function __construct($action, $module, array $uid, array $data = array(), $prospectLists = array())
    {
        if (!in_array($action, $this->allowedActions)) {
            throw new LogicException('Unsupported action.');
        }
        if (empty($uid)) {
            throw new LogicException('Nothing to update.');
        }
        $this->action = $action;
        $this->module = $module;
        $this->uid = $uid;
        $this->data = $data;
        $this->prospectLists = $prospectLists;
        $this->massUpdate = new \MassUpdate();
    }

    /**
     * Perform mass update.
     * {@inheritdoc}
     */
    public function run()
    {
        $logger = new LoggerTransition(\LoggerManager::getLogger());
        $fakeApi = new \RestService();
        $fakeApi->user = $GLOBALS['current_user'];
        $helper = \ApiHelper::getHelper($fakeApi, \BeanFactory::newBean($this->module));

        foreach ($this->uid as $id) {
            $bean = \BeanFactory::retrieveBean($this->module, $id);
            if (!$bean || !$bean->aclAccess($this->action)) {
                // Team permissions may have changed, or a deletion, we won't hold it against them.
                // ACL might not let them modify this bean, but we should still do the rest.
                $logger->info("Could not retrieve the bean {$id}.");
                continue;
            }
            if ($this->action == 'delete') {
                $bean->mark_deleted($id);
                continue;
            }
            try {
                $helper->populateFromApi($bean, $this->data, array('massUpdate' => true));
                $check_notify = $helper->checkNotify($bean);
                $bean->save($check_notify);
            } catch (\SugarApiExceptionNotAuthorized $e) {
                $logger->info("Could not populate the bean {$id}");
                continue;
            }
        }

        foreach ($this->prospectLists as $listId) {
            if ($this->action == 'save') {
                $success = $this->massUpdate->add_prospects_to_prospect_list($this->module, $listId, $this->uid);
            } else {
                $success = $this->massUpdate->remove_prospects_from_prospect_list($this->module, $listId, $this->uid);
            }
            if (!$success) {
                $logger->error("Could not find a relationship to the ProspectLists module for the list {$listId}.");
            }
        }

        return \SchedulersJob::JOB_SUCCESS;
    }
}
