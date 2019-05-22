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

namespace Sugarcrm\Sugarcrm\Filters\Operand;

use ServiceBase;
use Sugarcrm\Sugarcrm\Filters\Serializable;

/**
 * Formats or unformats a filter for a standard operand.
 */
class Operand implements Serializable
{
    /**
     * The API controller.
     *
     * @var ServiceBase
     */
    private $api;

    /**
     * The filter definition.
     *
     * @var array
     */
    private $filter;

    /**
     * The operand name.
     *
     * @var string
     */
    private $operand;

    /**
     * Constructor.
     *
     * @param ServiceBase $api Provides the API context.
     * @param string $operand The name of the operand.
     * @param array $filter The filter definition.
     */
    public function __construct(ServiceBase $api, string $operand, array $filter)
    {
        $this->api = $api;
        $this->operand = $operand;
        $this->filter = $filter;
    }

    /**
     * Returns the filter definition without making any changes.
     *
     * @return array
     */
    public function format()
    {
        return $this->filter;
    }

    /**
     * Returns the filter definition without making any changes.
     *
     * @return array
     */
    public function unformat()
    {
        return $this->filter;
    }
}
