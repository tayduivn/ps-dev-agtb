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

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 *
 * Component name validator
 *
 */
class ComponentNameValidator extends ConstraintValidator
{
    /**
     * List of reseverd SQL keywords
     * @var array
     */
    protected $sqlKeywords = array();

    /**
     * Ctor
     */
    public function __construct()
    {
        $this->sqlKeywords = \DBManager::$reserved_words;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ComponentName) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\ComponentName');
        }

        // check for string
        if (!is_string($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%msg%', 'string expected')
                ->setInvalidValue($value)
                ->setCode(ComponentName::ERROR_STRING_REQUIRED)
                ->addViolation();
            return;
        }

        // check for invalid characters
        if (!preg_match('/^[a-z][a-z0-9_]*$/i', $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter(
                    '%msg%',
                    'must start with a letter and may only consist of letters, numbers, and underscores.'
                )
                ->setInvalidValue($value)
                ->setCode(ComponentName::ERROR_INVALID_COMPONENT_NAME)
                ->addViolation();
            return;
        }

        // check for reserved SQL keyword
        if (isset($this->sqlKeywords[strtoupper($value)])) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%msg%', 'reserved SQL keyword not allowed')
                ->setInvalidValue($value)
                ->setCode(ComponentName::ERROR_RESERVED_KEYWORD)
                ->addViolation();
            return;
        }
    }
}
