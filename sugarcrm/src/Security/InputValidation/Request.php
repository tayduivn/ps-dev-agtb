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

namespace Sugarcrm\Sugarcrm\Security\InputValidation;

use Sugarcrm\Sugarcrm\Security\Validator\Constraints as Assert;
use Sugarcrm\Sugarcrm\Security\Validator\ConstraintReturnValueInterface;
use Sugarcrm\Sugarcrm\Security\Validator\ConstraintBuilder;
use Sugarcrm\Sugarcrm\Security\InputValidation\Exception\ViolationException;
use Sugarcrm\Sugarcrm\Security\InputValidation\Exception\SuperglobalException;
use Sugarcrm\Sugarcrm\Security\InputValidation\Sanitizer\SanitizerInterface;
use Sugarcrm\Sugarcrm\Security\InputValidation\Sanitizer\ConstraintSanitizerInterface;
use Sugarcrm\Sugarcrm\Logger\LoggerTransition;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Psr\Log\LoggerInterface;

/**
 *
 * Request validator
 *
 */
class Request
{
    /**
     * @var Superglobals
     */
    protected $superglobals;

    /**
     * @var SanitizerInterface|null
     */
    protected $sanitizer;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var ConstraintBuilder
     */
    protected $constraintBuilder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ContextualValidatorInterface
     */
    protected $context;

    /**
     * When softFail mode is enabled, request validation violations are only
     * reported through logger (warning) without throwing exceptions.
     * @var boolean
     */
    protected $softFail = false;

    /**
     * Supported input type mapping
     * @var array
     */
    protected $inputTypes = array(
        Superglobals::GET => array('get' => 'getRawGet', 'has' => 'hasRawGet'),
        Superglobals::POST => array('get' => 'getRawPost', 'has' => 'hasRawPost'),
        Superglobals::REQUEST => array('get' => 'getRawRequest', 'has' => 'hasRawRequest'),
    );

    /**
     * Ctor
     * @param Superglobals $superglobals
     * @param ValidatorInterface $validator
     * @param ConstraintBuilder $constraintBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        Superglobals $superglobals,
        ValidatorInterface $validator,
        ConstraintBuilder $constraintBuilder,
        LoggerInterface $logger = null
    ) {
        $this->superglobals = $superglobals;
        $this->validator = $validator;
        $this->constraintBuilder = $constraintBuilder;
        $this->logger = $logger ?: new LoggerTransition(\LoggerManager::getLogger());
    }

    /**
     * Set optional generic sanitizer
     * @param SanitizerInterface $sanitizer
     */
    public function setSanitizer(SanitizerInterface $sanitizer)
    {
        $this->sanitizer = $sanitizer;
    }

    /**
     * Get sanitizer
     * @return SanitizerInterface|null
     */
    public function getSanitizer()
    {
        return $this->sanitizer;
    }

    /**
     * Set softFail mode
     * @param boolean $toggle
     */
    public function setSoftFail($toggle)
    {
        $this->softFail = (bool) $toggle;
    }

    /**
     * Get validated input from $_GET
     * @param string $key
     * @param string|array $constraints ConstraintBuilder compat constraints
     * @param mixed $default Return value if input param does not exist
     * @return mixed
     */
    public function getValidInputGet($key, $constraints = null, $default = null)
    {
        return $this->getValidInput(Superglobals::GET, $key, $constraints, $default);
    }

    /**
     * Get validated input from $_POST
     * @param string $key
     * @param string|array $constraints ConstraintBuilder compat constraints
     * @param mixed $default Return value if input param does not exist
     * @return mixed
     */
    public function getValidInputPost($key, $constraints = null, $default = null)
    {
        return $this->getValidInput(Superglobals::POST, $key, $constraints, $default);
    }

    /**
     * Get validated input from $_REQUEST
     * @param string $key
     * @param string|array $constraints ConstraintBuilder compat constraints
     * @param mixed $default Return value if input param does not exist
     * @return mixed
     */
    public function getValidInputRequest($key, $constraints = null, $default = null)
    {
        return $this->getValidInput(Superglobals::REQUEST, $key, $constraints, $default);
    }

    /**
     * Get validated input value from SuperGlobals
     *
     * @param string $type GET|POST|REQUEST
     * @param string $key The input parameter you are looking for
     * @param string|array $constraints ConstraintBuilder compat constraints
     * @param mixed $default Return value if input param does not exist
     * @return mixed
     */
    public function getValidInput($type, $key, $constraints = null, $default = null)
    {
        // Build actual constraints
        $constraints = $this->constraintBuilder->build($constraints);

        // Validate superglobals type
        $this->validateSuperglobalsType($type);

        // Return default if input parameter is not set, the default is not validated
        $has = $this->inputTypes[$type]['has'];
        if (!$this->superglobals->$has($key)) {
            return $default;
        }

        // Get raw value from superglobals
        $value = $this->getRawValue($type, $key);

        // Generic sanitizing
        if ($this->sanitizer) {
            $value = $this->sanitizer->sanitize($value);
        }

        // Start new validator context
        $this->context = $this->validator->startContext();

        $value = $this->validateConstraints($type, $value, $constraints);
        $this->handleViolations($type, $key);

        return $value;
    }

    /**
     * Get last context violations
     * @return ConstraintViolationListInterface
     */
    public function getViolations()
    {
        return $this->context->getViolations();
    }

    /**
     * Validate constraints against given value
     * @param string $type GET|POST|REQUEST
     * @param mixed $value The value to be validated
     * @param Constraint|Constraint[] $constraints The constrait definition(s)
     * @return mixed
     */
    protected function validateConstraints($type, $value, $constraints)
    {
        $constraints = $this->normalizeConstraints($type, $constraints);

        foreach ($constraints as $constraint) {

            // update value using constraint sanitizer
            $value = $this->applyConstraintSanitizer($constraint, $value);

            // perform validation
            $this->context->validate($value, $constraint);

            // update value if constraint supplies a formatted return value
            if ($constraint instanceof ConstraintReturnValueInterface) {

                // if any violations exist we cannot continue
                if (count($this->context->getViolations()) !== 0) {
                    break;
                }

                $value = $constraint->getFormattedReturnValue();

            }
        }
        return $value;
    }

    /**
     * Verify if superglobals type is valid
     * @param string $type
     * @throws SuperglobalException
     */
    protected function validateSuperglobalsType($type)
    {
        if (!array_key_exists($type, $this->inputTypes)) {
            throw new SuperglobalException("Invalid superglobal [$type] requested");
        }
    }

    /**
     * Handle violations on current context
     * @param GET|POST|REQUEST $type
     * @param string $key
     * @throws ViolationException
     */
    protected function handleViolations($type, $key)
    {
        $violations = $this->context->getViolations();
        if (count($violations) !== 0) {

            $this->logViolations($type, $key, $violations);

            if (!$this->softFail) {
                throw new ViolationException(
                    sprintf('Violation for %s -> %s', $type, $key),
                    $violations
                );
            }
        }
    }

    /**
     * Normalize list of constraints"
     *  - We need to have an array
     *  - Attach generic validator
     *
     * @param string $type
     * @param Constraint|Constraint[] $constraints
     */
    protected function normalizeConstraints($type, $constraints)
    {
        // One or more constraints can be set, make sure we have an array here
        if (!is_array($constraints)) {
            if ($constraints instanceof Constraint) {
                $constraints = array($constraints);
            } else {
                $constraints = array();
            }
        }

        // Attach generic input validation
        $inputConstraint = new Assert\InputParameters(array(
            'inputType' => $type,
        ));

        array_unshift($constraints, $inputConstraint);

        return $constraints;
    }

    /**
     * Apply constraint sanitizer for given value
     * @param Constraint $constraints
     * @param mixed $value
     * @return mixed
     */
    protected function applyConstraintSanitizer(Constraint $constraint, $value)
    {
        if ($constraint instanceof ConstraintSanitizerInterface) {
            $value = $constraint->sanitize($value);
        }
        return $value;
    }

    /**
     * Get raw value from superglobals
     * @param string $type
     * @param string $key
     * @return mixed
     *
     */
    protected function getRawValue($type, $key)
    {
        $get = $this->inputTypes[$type]['get'];
        return $this->superglobals->$get($key);
    }

    /**
     * Log violations
     * @param string $type GET|POST|REQUEST
     * @param string $key The input parameter you are looking for
     * @param ConstraintViolationListInterface $violations
     */
    protected function logViolations($type, $key, ConstraintViolationListInterface $violations)
    {
        foreach ($violations as $violation) {

            /* @var $violation ConstraintViolationInterface */
            $message = sprintf(
                'InputValidation: [%s] %s -> %s',
                $type,
                $key,
                $violation->getMessage()
            );

            if ($this->softFail) {
                $this->logger->warning($message);
            } else {
                $this->logger->critical($message);
            }
        }
    }
}
