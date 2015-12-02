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

namespace Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Format;

/**
 * Interface StrategyInterface
 * @package Sugarcrm\Sugarcrm\Dav\Base\Principal\Search\Format
 */
interface StrategyInterface
{
    /**
     * Format SugarBean in needed uri format such as (Module/id, principalPath, e t.c.)
     * @param \SugarBean $bean
     * @return mixed
     */
    public function formatUri(\SugarBean $bean);

    /**
     * Format SugarBean info in needed extended format such as Module/id/username/full_name/email
     * @param \SugarBean $bean
     * @return mixed
     */
    public function formatBody(\SugarBean $bean);

}
