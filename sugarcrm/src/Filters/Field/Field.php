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

namespace Sugarcrm\Sugarcrm\Filters\Field;

use ServiceBase;
use Sugarcrm\Sugarcrm\Filters\Serializable;

/**
 * Formats or unformats a filter for a standard field.
 */
class Field implements Serializable
{
    /**
     * The API controller.
     *
     * @var ServiceBase
     */
    private $api;

    /**
     * The field name.
     *
     * @var string
     */
    private $field;

    /**
     * The filter definition.
     *
     * @var mixed Typically a string or array.
     */
    private $filter;

    /**
     * Constructor.
     *
     * @param ServiceBase $api Provides the API context.
     * @param string $field The name of the field.
     * @param mixed $filter The scalar value of the field or an array.
     */
    public function __construct(ServiceBase $api, string $field, $filter)
    {
        $this->api = $api;
        $this->field = $field;
        $this->filter = $filter;
    }

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
