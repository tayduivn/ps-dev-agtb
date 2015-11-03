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
use Sugarcrm\Sugarcrm\Security\Validator\ConstraintReturnValueTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Sugarcrm\Sugarcrm\Security\Validator\Exception\ConstraintReturnValueException;

/**
 *
 * PHP Serialized validator
 *
 * Validate PHP serialized data. This validator will report a violation when
 * objects are detected inside a PHP serialized string. Additionally the
 * unserialize operation is validate as well.
 *
 */
class PhpSerializedValidator extends ConstraintValidator implements ConstraintReturnValueInterface
{
    // use ConstraintReturnValueTrait;

    /* START TRAIT - remove when on +5.4 */

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

    /* END TRAIT */

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PhpSerialized) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\PhpSerialized');
        }

        // check for string
        if (!is_string($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%msg%', 'string expected')
                ->setInvalidValue($value)
                ->setCode(PhpSerialized::ERROR_STRING_REQUIRED)
                ->addViolation();
            return;
        }

        // detect any objects
        preg_match('/[oc]:\d+:/i', $value, $matches);
        if (count($matches)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%msg%', 'object(s) not allowed')
                ->setInvalidValue($value)
                ->setCode(PhpSerialized::ERROR_OBJECT_NOT_ALLOWED)
                ->addViolation();
            return;
        }

        // validate unserialize operation
        $unserialized = @unserialize($value);
        if ($unserialized === false && $value !== 'b:0;') {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%msg%', 'unserialize error')
                ->setInvalidValue($value)
                ->setCode(PhpSerialized::ERROR_UNSERIALIZE)
                ->addViolation();
            return;
        }

        $this->formattedReturnValue = $unserialized;
    }
}
