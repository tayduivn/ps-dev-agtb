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
 * Class Base64
 * @package JobQueue
 */
class Base64 extends AbstractDecorator
{
    /**
     * Apply base64_decode().
     * {@inheritdoc}
     */
    public function decorate($data, WorkloadInterface $workload)
    {
        if (!is_string($data)) {
            throw new InvalidArgumentException('Invalid data in decorator. String required.');
        }
        return parent::decorate(base64_encode($data), $workload);
    }

    /**
     * Apply base64_decode().
     * {@inheritdoc}
     */
    public function undecorate($data)
    {
        if (!is_string($data)) {
            throw new InvalidArgumentException('Invalid data in decorator. String required.');
        }
        return base64_decode(parent::undecorate($data));
    }
}
