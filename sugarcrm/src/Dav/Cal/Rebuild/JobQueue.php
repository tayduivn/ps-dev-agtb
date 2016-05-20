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


namespace Sugarcrm\Sugarcrm\Dav\Cal\Rebuild;

use Sugarcrm\Sugarcrm\Util\Runner\Quiet as QuietRunner;
use Sugarcrm\Sugarcrm\JobQueue\Handler\RunnableInterface;

/**
 * Re-export calls and meetings to external application.
 *
 * Class JobQueue
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Rebuild
 */
class JobQueue implements RunnableInterface
{
    /** @var QuietRunner */
    protected $runner;

    /**
     * JobQueue constructor.
     *
     * @param QuietRunner|null $runner
     */
    public function __construct(QuietRunner $runner = null)
    {
        if (is_null($runner)) {
            $this->runner = new QuietRunner(new Executer());
        } else {
            $this->runner = $runner;
        }
    }

    /**
     * Re-export Calls and Meeting to external application.
     *
     * @return string SchedulersJob resolution.
     */
    public function run()
    {
        $this->clearCalDavTables();
        $this->runner->run();
        return \SchedulersJob::JOB_SUCCESS;
    }

    /**
     * Truncate CalDav tables.
     */
    protected function clearCalDavTables()
    {
        $tables = array(
            'caldav_events',
            'caldav_calendars',
            'caldav_changes',
            'caldav_scheduling',
            'caldav_synchronization',
            'caldav_queue',
        );
        $db = \DBManagerFactory::getInstance();
        foreach ($tables as $table) {
            $db->query($db->truncateTableSQL($table));
        }
    }
}
