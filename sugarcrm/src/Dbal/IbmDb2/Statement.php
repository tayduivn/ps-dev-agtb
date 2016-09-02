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

namespace Sugarcrm\Sugarcrm\Dbal\IbmDb2;

use Doctrine\DBAL\Driver\IBMDB2\DB2Statement as BaseStatement;
use Sugarcrm\Sugarcrm\Dbal\FetchTrait;

/**
 * IBM DB2 statement
 */
class Statement extends BaseStatement
{
    use FetchTrait;

    /**
     * {@inheritdoc}
     */
    public function fetch($fetchMode = null)
    {
        $result = parent::fetch($fetchMode);
        $result = $this->normalize($result, $fetchMode);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll($fetchMode = null)
    {
        $results = parent::fetchAll($fetchMode);
        $results = $this->normalizeAll($results, $fetchMode);

        return $results;
    }

    /**
     * {@inheritdoc}
     *
     * Do not free the statement since it contradicts the purpose of the method
     */
    public function closeCursor()
    {
        $re = new \ReflectionProperty(get_parent_class($this), '_stmt');
        $re->setAccessible(true);
        $stmt = $re->getValue($this);

        if (!$stmt) {
            return false;
        }

        return db2_free_result($stmt);
    }
}
