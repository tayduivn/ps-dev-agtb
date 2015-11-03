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

namespace Sugarcrm\Sugarcrm\Security\Validator;

use Sugarcrm\Sugarcrm\Security\Validator\ConstraintReturnValueInterface;
use Sugarcrm\Sugarcrm\Security\Validator\Exception\ConstraintReturnValueException;

/**
 * !!! Do not use yet until we have PHP 5.4+ support !!!
 *
 * Constraint return value trait
 *
 * @see ConstraintReturnValueInterface
 *
 */
trait ConstraintReturnValueTrait
{
    /**
     * @var mixed
     */
    protected $formattedReturnValue;

    /**
     * {@inheritdoc}
     */
    public function getFormattedReturnValue()
    {
        if (count($this->context->getViolations()) !== 0) {
            throw new ConstraintReturnValueException(
                'Cannot get formatted value when violations are present',
                $this->context->getViolations()
            );
        }
        return $this->formattedReturnValue;
    }
}
