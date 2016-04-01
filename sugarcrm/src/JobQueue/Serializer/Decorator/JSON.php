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

namespace Sugarcrm\Sugarcrm\JobQueue\Serializer\Decorator;

use Sugarcrm\Sugarcrm\JobQueue\Exception\InvalidArgumentException;
use Sugarcrm\Sugarcrm\JobQueue\Workload\WorkloadInterface;

/**
 * Class JSON
 * @package JobQueue
 */
class JSON extends AbstractDecorator
{
    /**
     * Apply json_encode().
     * {@inheritdoc}
     */
    public function decorate($data, WorkloadInterface $workload)
    {
        return parent::decorate(json_encode($data), $workload);
    }

    /**
     * Apply json_decode().
     * {@inheritdoc}
     */
    public function undecorate($data)
    {
        if (!is_string($data)) {
            throw new InvalidArgumentException('Invalid data in decorator. String required.');
        }
        return json_decode(parent::undecorate($data), true);
    }
}
