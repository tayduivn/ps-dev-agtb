<?php declare(strict_types=1);
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

namespace Sugarcrm\Sugarcrm\Filters;

/**
 * The default implementation of the {@link Serializable} interface. It simply returns
 * the filter definition without making any changes.
 */
trait SerializableDefaultImplementation
{
    /**
     * The filter definition. Constructor injection is recommended.
     *
     * @var mixed Typically a string or array.
     */
    private $filter;

    /**
     * Returns the filter definition without making any changes.
     *
     * @return mixed
     */
    public function format()
    {
        return $this->filter;
    }

    /**
     * Returns the filter definition without making any changes.
     *
     * @return mixed
     */
    public function unformat()
    {
        return $this->filter;
    }
}
