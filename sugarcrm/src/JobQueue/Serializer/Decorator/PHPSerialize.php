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

use Sugarcrm\Sugarcrm\JobQueue\Workload\WorkloadInterface;

/**
 * Class PHPSerialize
 * @package JobQueue
 */
class PHPSerialize extends AbstractDecorator
{
    /**
     * Apply serialize().
     * {@inheritdoc}
     */
    public function decorate($data, WorkloadInterface $workload)
    {
        return parent::decorate(serialize($data), $workload);
    }

    /**
     * Apply unserialize().
     * {@inheritdoc}
     */
    public function undecorate($data)
    {
        return unserialize(parent::undecorate($data));
    }
}
