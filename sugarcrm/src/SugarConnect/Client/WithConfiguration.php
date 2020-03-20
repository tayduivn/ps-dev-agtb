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

namespace Sugarcrm\Sugarcrm\SugarConnect\Client;

use Sugarcrm\Sugarcrm\SugarConnect\Configuration\Configuration;
use Sugarcrm\Sugarcrm\SugarConnect\Configuration\Repository;

abstract class WithConfiguration implements Client
{
    /**
     * The SugarConnect configuration.
     *
     * @var Repository
     */
    protected $config;

    /**
     * Creates a client with a SugarConnect configuration.
     *
     * @param ?Repository $config The SugarConnect configuration. The default is
     *                            an instance of {@link Configuration}.
     */
    public function __construct(?Repository $config = null)
    {
        $this->config = $config ?? new Configuration();
    }
}
