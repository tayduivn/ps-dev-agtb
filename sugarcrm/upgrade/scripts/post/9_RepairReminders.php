<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use Sugarcrm\Sugarcrm\Trigger\Repair\Runner\Quiet as TriggerRepairRunner;
use Sugarcrm\Sugarcrm\Trigger\Repair\Repair as TriggerRepair;

/**
 * For all Calls and Meetings will be created jobs for notification.
 *
 * Class SugarUpgradeRepairReminders
 */
class SugarUpgradeRepairReminders extends UpgradeScript
{
    /** @var int */
    public $order = 9900;

    /** @var int */
    public $type = self::UPGRADE_ALL;

    /** @var TriggerRepair */
    protected $runner;

    /**
     * @inheritDoc
     */
    public function __construct($upgrader, $runner = null)
    {
        if (is_null($runner)) {
            $this->runner = new TriggerRepairRunner(new TriggerRepair());
        } else {
            $this->runner = $runner;
        }

        parent::__construct($upgrader);
    }

    /**
     * Running rebuild job for reminder notifications.
     */
    public function run()
    {
        // We should do that only if from version below then 7.8rc2.
        if (version_compare($this->from_version, '7.8.0.0RC2', '<')) {
            $this->runner->run();
        }
    }
}
