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

namespace Sugarcrm\Sugarcrm\Dbal\Platforms;

use Doctrine\DBAL\Platforms\DB2Platform;

/**
 * @link https://github.com/doctrine/dbal/commit/e64c76d
 */
class IbmDb2Platform extends DB2Platform
{
    /**
     * {@inheritDoc}
     *
     * @link https://github.com/doctrine/dbal/commit/e64c76d
     */
    protected function doModifyLimitQuery($query, $limit, $offset = null)
    {
        if ($limit === null && $offset === null) {
            return $query;
        }

        $limit = (int) $limit;
        $offset = (int) (($offset)?:0);

        // Todo OVER() needs ORDER BY data!
        $sql = 'SELECT db22.* FROM (SELECT db21.*, ROW_NUMBER() OVER() AS DC_ROWNUM'
            . ' FROM (' . $query . ') db21) db22'
            . ' WHERE db22.DC_ROWNUM BETWEEN ' . ($offset + 1) . ' AND ' . ($offset + $limit);

        return $sql;
    }
}
