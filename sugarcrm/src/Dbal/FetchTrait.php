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

/**
 * Contains shared implementation of fetched result normalization
 */
trait FetchTrait
{
    protected function normalize($result, $fetchMode)
    {
        if (is_array($result)) {
            $result = array_change_key_case($result, CASE_LOWER);
        }

        return $result;
    }

    protected function normalizeAll($results, $fetchMode)
    {
        if (is_array($results)) {
            $results = array_map(function ($result) use ($fetchMode) {
                return $this->normalize($result, $fetchMode);
            }, $results);
        }

        return $results;
    }
}
