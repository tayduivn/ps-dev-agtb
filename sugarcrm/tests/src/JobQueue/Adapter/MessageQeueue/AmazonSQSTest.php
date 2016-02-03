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

namespace Sugarcrm\SugarcrmTests\JobQueue\Adapter\MessageQeueue;

use Psr\Log\NullLogger;
use Sugarcrm\Sugarcrm\JobQueue\Adapter\MessageQueue\AmazonSQS;
use Sugarcrm\SugarcrmTests\JobQueue\MessageQueue\MessageQueueTestAbstract;

class AmazonSQSTest extends MessageQueueTestAbstract
{
    public function setUp()
    {
        $config = \SugarConfig::getInstance()->get('job_queue.amazon_sqs');
        if (!$config) {
            $this->markTestSkipped('Skipping, AmazonSQS adapter is not configured.');
        }
        $this->adapter = new AmazonSQS($config, new NullLogger());
    }
}
