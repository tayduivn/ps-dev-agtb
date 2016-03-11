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

namespace Sugarcrm\Sugarcrm\Trigger\Repair\Runner;

use Sugarcrm\Sugarcrm\Trigger\Repair\Repair;

/**
 * Dot runner rebuilding calls and meetings reminders.
 *
 * Class Doc
 * @package Sugarcrm\Sugarcrm\Trigger\Repair\Runner
 */
class Dot
{
    /**
     * @var Repair
     */
    protected $repair;

    /**
     * @param Repair $repair
     */
    public function __construct(Repair $repair)
    {
        $this->repair = $repair;
    }

    /**
     * Running process of repairing.
     */
    public function run()
    {
        set_time_limit(0);
        foreach ($this->repair->getBeans() as $bean) {
            $this->repair->rebuild($bean);
            echo '. ';
            flush();
        }
    }
}
