<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'include/api/RestService.php';

/**
 * Bulk API Rest service class
 * Shortcuts some functions that we don't need to do on bulk requests
 */
class BulkRestService extends RestService
{
    protected $parent;

    public function __construct($parent)
    {
        $this->parent = $parent;
        parent::__construct();
    }

    /**
     * Shortcut authentication since we're already authenticated before
     * @see RestService::authenticateUser()
     */
    protected function authenticateUser()
    {
        $this->user = $this->parent->user;
        return array('isLoggedIn' => true, 'exception' => false);
    }

    /**
     * Don't check metadata - top request checks it
     * @see RestService::isMetadataCurrent()
     */
    protected function isMetadataCurrent()
    {
        return true;
    }

    /**
     * Don't load envt - top request loads it
     * @see ServiceBase::loadUserEnvironment()
     */
    protected function loadUserEnvironment()
    {
    }

    /**
     * Never release session
     * @see ServiceBase::releaseSession()
     */
    protected function releaseSession()
    {
    }
}

