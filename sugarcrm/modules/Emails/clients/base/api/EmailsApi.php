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

require_once 'clients/base/api/ModuleApi.php';

/**
 * Class EmailsApi
 */
class EmailsApi extends ModuleApi
{
    const STATE_ANY = '*';  // Any Valid API State Value

    private $validStates = array(
        'create' => array(
            array(
                'from' => self::STATE_ANY,
                'to' => array(
                    Email::EMAIL_STATE_READY,
                    Email::EMAIL_STATE_DRAFT,
                    Email::EMAIL_STATE_SCHEDULED,
                    Email::EMAIL_STATE_ARCHIVED,
                ),
            ),
        ),
        'update' => array(
            array(
                'from' => Email::EMAIL_STATE_DRAFT,
                'to' => array(
                    Email::EMAIL_STATE_DRAFT,
                    Email::EMAIL_STATE_SCHEDULED,
                    Email::EMAIL_STATE_READY,     // Ready To Run
                ),
            ),
            array(
                'from' => Email::EMAIL_STATE_SCHEDULED,
                'to' => array(
                    Email::EMAIL_STATE_SCHEDULED, // Scheduled Date Modified
                    Email::EMAIL_STATE_ARCHIVED,  // Cancellation
                    Email::EMAIL_STATE_READY,     // Ready To Run
                ),
            ),
        ),
    );

    /**
     * {@inheritdoc}
     */
    public function registerApiRest()
    {
        $emailsApi = array(
            'create' => array(
                'reqType' => 'POST',
                'path' => array('Emails'),
                'pathVars' => array('module'),
                'method' => 'createRecord',
                'shortHelp' => 'This method creates a new Emails record',
                'longHelp' => 'modules/Emails/clients/base/api/help/emails_post_help.html',
            ),
            'update' => array(
                'reqType' => 'PUT',
                'path' => array('Emails', '?'),
                'pathVars' => array('module', 'record'),
                'method' => 'updateRecord',
                'shortHelp' => 'This method updates an Emails record',
                'longHelp' => 'modules/Emails/clients/base/api/help/emails_put_help.html',
            ),
        );

        return $emailsApi;
    }

    /**
     * {@inheritdoc}
     */
    public function createRecord(ServiceBase $api, $args)
    {
        return parent::createRecord($api, $args);
    }

    /**
     * {@inheritdoc}
     */
    public function updateRecord(ServiceBase $api, $args)
    {
        return parent::updateRecord($api, $args);
    }

    /**
     * {@inheritdoc}
     */
    public function createBean(ServiceBase $api, array $args, array $additionalProperties = array())
    {
        $this->requireArgs($args, array('state'));
        $this->ignoreArgs(
            $args,
            array(
                'id',
                'deleted',
                'type',
                'status',
            )
        );

        if (!$this->isValidateStateTransition(
            $this->validStates,
            'create',
            static::STATE_ANY,
            $args['state']
        )
        ) {
            $message = "Email state transition to {$args['state']} not valid for Email Create";
            throw new SugarApiExceptionInvalidParameter($message);
        }

        return parent::createBean($api, $args, $additionalProperties);
    }

    /**
     * {@inheritdoc}
     */
    protected function updateBean(SugarBean $bean, ServiceBase $api, $args)
    {
        $this->ignoreArgs(
            $args,
            array(
                'deleted',
                'type',
                'status',
            )
        );

        if (!$this->isValidateStateTransition(
            $this->validStates,
            'update',
            $bean->state,
            $args['state']
        )
        ) {
            $message = "Email state transition from {$bean->state} to {$args['state']} not valid for Email Update";
            throw new SugarApiExceptionInvalidParameter($message);
        }

        return parent::updateBean($bean, $api, $args);
    }

    /**
     * Respond as to whether the supplied state transition is valid
     * @param array State validation array based on any set of operations and states
     * @param string $operation
     * @param string $fromState
     * @param string $toState
     * @returns boolean
     */
    protected function isValidateStateTransition($validStates, $operation, $fromState, $toState)
    {
        if (isset($validStates[$operation])) {
            foreach ($validStates[$operation] as $transition) {
                if ($transition['from'] === self::STATE_ANY || $transition['from'] === $fromState) {
                    foreach ($transition['to'] as $validTransitionState) {
                        if ($toState === $validTransitionState) {
                            return true;
                        }
                    }
                }
            }
            return false;
        }
        return true; // default to valid
    }
}
