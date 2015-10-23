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

use Sugarcrm\Sugarcrm\JobQueue\Exception\InvalidArgumentException as JQInvalidArgumentException;

/**
 * Class Import
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue
 * Class for import process initialization
 */
class Import extends Base
{
    /**
     * start imports process for current CalDavEvent object
     * @throws \Sugarcrm\Sugarcrm\JobQueue\Exception\InvalidArgumentException if bean not instance of CalDavEvent
     * @throws \Sugarcrm\Sugarcrm\JobQueue\Exception\LogicException if related bean doesn't have adapter
     * @return string
     */
    public function run()
    {
        /** @var \CalDavEvent $bean */
        $bean = $this->getBean();
        if (!($bean instanceof \CalDavEvent)) {
            throw new JQInvalidArgumentException('Bean must be an instance of CalDavEvent. Instance of ' .
                get_class($bean) . ' given');
        }

        if ($this->setJobToEnd($bean)) {
            return \SchedulersJob::JOB_CANCELLED;
        }

        $handler = $this->getHandler();
        $handler->import($bean);
        $bean->getSynchronizationObject()->setJobCounter();

        return \SchedulersJob::JOB_SUCCESS;
    }

    /**
     * @inheritdoc
     */
    protected function reschedule()
    {
        $jqManager = $this->getManager();
        $jqManager->calDavImport($this->fetchedRow, $this->moduleName, $this->saveCounter);
    }
}
