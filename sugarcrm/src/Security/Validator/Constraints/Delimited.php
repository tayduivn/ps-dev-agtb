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

namespace Sugarcrm\Sugarcrm\Security\Validator\Constraints;

use Sugarcrm\Sugarcrm\Security\Validator\ConstraintReturnValueInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 *
 * @see DelimitedValidator
 *
 */
class Delimited extends All implements ConstraintReturnValueInterface
{
    public $delimiter = ',';

    /**
     * {@inheritdoc}
     */
    public function __construct($options = null)
    {
        parent::__construct($options);

        if (!is_string($this->delimiter) || strlen($this->delimiter) !== 1) {
            throw new ConstraintDefinitionException('Delimiter is expected to be a string of one character');
        }
    }

    /**
     * @var mixed
     */
    protected $formattedReturnValue;

    /**
     * {@inheritdoc}
     */
    public function getFormattedReturnValue()
    {
        return $this->formattedReturnValue;
    }

    /**
     * {@inheritdoc}
     */
    public function setFormattedReturnValue($value)
    {
        $this->formattedReturnValue = $value;
    }
}
