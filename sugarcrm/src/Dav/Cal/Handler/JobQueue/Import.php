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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue;

use Sugarcrm\Sugarcrm\JobQueue\Handler\RunnableInterface;

/**
 * Class Import
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue
 * Class for import process initialization
 */
class Import implements RunnableInterface
{
    /**
     * @param \CalDavEvent $calDavBean
     * @throws \Exception
     */
    public function __construct($calDavBean)
    {
        if (!($calDavBean instanceof \CalDavEvent)) {
            throw new \Exception('Argument should be instance of CalDavEvent');
        }
        $this->bean = $calDavBean;
    }

    /**
     * start imports process for current CalDavEvent object
     * @return string
     */
    public function run()
    {
        return \SchedulersJob::JOB_SUCCESS;
    }
}
