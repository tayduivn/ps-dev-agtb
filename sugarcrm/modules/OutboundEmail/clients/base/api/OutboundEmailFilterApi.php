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

class OutboundEmailFilterApi extends FilterApi
{
    /**
     * Don't enforce the limit.
     * @var int
     */
    protected $defaultLimit = -1;

    /**
     * {@inheritdoc}
     *
     * Registers OutboundEmail-specific Filter API routes for all generic Filter API routes.
     */
    public function registerApiRest()
    {
        $endpoints = parent::registerApiRest();

        foreach ($endpoints as $name => &$endpoint) {
            // Replace all occurrences of the <module> variable in the path with "OutboundEmail."
            foreach ($endpoint['path'] as $i => $param) {
                if ($param === '<module>') {
                    $endpoint['path'][$i] = 'OutboundEmail';
                }
            }
        }

        return $endpoints;
    }

    /**
     * {@inheritdoc}
     *
     * Returns the default limit (-1) anytime the parameter is less than one. This enables the limit to not be enforced,
     * as all rows that match the filter should be returned in most cases.
     */
    public function checkMaxListLimit($limit)
    {
        if ($limit < 1) {
            $limit = $this->defaultLimit;
        }

        return $limit;
    }
}
