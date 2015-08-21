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
 * Class Export
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue
 * Class for export process initialization
 */
class Export implements RunnableInterface
{
    /**
    * @param \SugarBean $sugarBean
    * @throws \Exception
    */
    public function __construct($sugarBean)
    {
        if (!($sugarBean instanceof \SugarBean)) {
            throw new \Exception('Argument should be instance of SugarBean');
        }
        $this->bean = $sugarBean;
    }

    /**
    * start export process for current bean
    * @return string
    */
    public function run()
    {
        return \SchedulersJob::JOB_SUCCESS;
    }
}
