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

namespace Sugarcrm\Sugarcrm\Dbal;

use Doctrine\DBAL\Connection as BaseConnection;
use Sugarcrm\Sugarcrm\Dbal\Query\QueryBuilder;

/**
 * {@inheritDoc}
 */
class Connection extends BaseConnection
{
    /**
     * {@inheritDoc}
     *
     * @return \Sugarcrm\Sugarcrm\Dbal\Query\QueryBuilder
     */
    public function createQueryBuilder()
    {
        return new QueryBuilder($this);
    }
}
