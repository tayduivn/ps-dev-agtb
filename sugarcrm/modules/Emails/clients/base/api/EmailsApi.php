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
     * Don't allow existing Notes to be attached.
     *
     * {@inheritdoc}
     */
    protected function linkRelatedRecords(
        ServiceBase $service,
        SugarBean $bean,
        array $ids,
        $securityTypeLocal = 'view',
        $securityTypeRemote = 'view'
    ) {
        unset($ids['attachments']);

        parent::linkRelatedRecords($service, $bean, $ids, $securityTypeLocal, $securityTypeRemote);
    }

    /**
     * Create an array of Attachment File Ids needed by parent method
     *
     * {@inheritdoc}
     */
    protected function createRelatedRecords(ServiceBase $service, SugarBean $bean, array $data)
    {
        $relate = array();

        foreach ($data as $linkName => $records) {
            if ($linkName === 'attachments') {
                $relate[$linkName] = array();

                foreach ($records as $record) {
                    $sourceFile = $this->getAttachmentSource($record);
                    if (!empty($sourceFile)) {
                        unset($record['_file']);
                        $destinationFile = $this->setupAttachmentNoteRecord($bean, $record);

                        $uploaded = !empty($record['_uploaded']);
                        unset($record['_uploaded']);
                        if ($this->moveOrCopyAttachment($sourceFile, $destinationFile, $uploaded)) {
                            $relate[$linkName][] = $record;
                        }
                    }
                }
            } else {
                $relate[$linkName] = $records;
            }
        }

        parent::createRelatedRecords($service, $bean, $relate);
    }

    /**
     * Respond as to whether the supplied state transition is valid
     * @param array $validStates - state validation array based on any set of operations and states
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

    /**
     * Return the qualified upload source file if the attachment record is valid
     * Note: An attachment record is valid if an attachment file is specified and it exists in the upload directory
     * @param array $record
     * @return null|string  - null if not a valid Attachment
     */
    protected function getAttachmentSource(array $record)
    {
        if (!empty($record['_file'])) {
            $guid = preg_replace('/[^a-z0-9\-]/', '', $record['_file']);
            $source = "upload://{$guid}";
            if (file_exists($source)) {
                return $source;
            }
        }
        return null; // Not a valid Attachment
    }

    /**
     * Spec out a new Notes object in the array format that ModuleApi::createBean() expects.
     * Preset the ID and return the qualified destinationFile for the subsequent move/copy.
     * @param SugarBean $bean
     * @param array $record
     * @return string
     */
    protected function setupAttachmentNoteRecord(SugarBean $bean, array &$record)
    {
        $record['id'] = create_guid();
        $record['email_id'] = $bean->id;
        $record['email_type'] = $bean->module_dir;
        $record['team_id'] = $bean->team_id;
        $record['team_set_id'] = $bean->team_set_id;

        return "upload://{$record['id']}";
    }

    /**
     *  If the file was uploaded for this Email, simply rename the file.
     *  Otherwise, to avoid multiplication of read-only attachment files, try first to hard link the file
     *  and only copy the file if the hard link is not successful.
     * @param string $source
     * @param string $dest
     * @param bool $uploaded - true if file was uploaded for this Email ... Move it instead of Copying it
     * @return bool
     */
    protected function moveOrCopyAttachment($source, $dest, $uploaded = false)
    {
        // Resolve upload and relative paths.
        $source = UploadFile::realpath($source);
        $dest = UploadFile::realpath($dest);

        if ($uploaded) {
            $result = rename($source, $dest);
        } elseif (link($source, $dest)) {
            $result = true; // create a hard link if possible
        } else {
            $result = copy($source, $dest);
        }

        if (!$result) {
            $GLOBALS['log']->error("Failed to link/copy file from {$source} to {$dest}");
        }
        return $result;
    }
}
