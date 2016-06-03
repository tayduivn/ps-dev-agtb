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
 * Interface DecoratorInterface
 * @package JobQueue
 */
interface DecoratorInterface
{
    /**
     * Decorate data.
     * @param mixed $data
     * @param WorkloadInterface $workload Original workload.
     * @return mixed
     */
    public function decorate($data, WorkloadInterface $workload);

    /**
     * Undecorate data.
     * @param string $data
     * @return mixed
     */
    public function undecorate($data);
}
