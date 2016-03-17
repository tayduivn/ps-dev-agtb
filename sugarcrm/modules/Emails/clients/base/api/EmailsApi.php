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

class EmailsApi extends ModuleApi
{
    /**
     * Wildcard state value.
     *
     * @var string
     */
    const STATE_ANY = '*';

    /**
     * The valid transitions for an Emails record's state.
     *
     * @var array
     */
    private $validStateTransitions = array(
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
                    Email::EMAIL_STATE_READY, // Ready To Run
                ),
            ),
            array(
                'from' => Email::EMAIL_STATE_SCHEDULED,
                'to' => array(
                    Email::EMAIL_STATE_SCHEDULED, // Scheduled Date Modified
                    Email::EMAIL_STATE_ARCHIVED, // Cancellation
                    Email::EMAIL_STATE_READY, // Ready To Run
                ),
            ),
        ),
    );

    /**
     * An instance that can be shared across the entire class.
     *
     * @var SugarEmailAddress
     */
    protected $sugarEmailAddress;

    /**
     * {@inheritdoc}
     */
    public function registerApiRest()
    {
        return array(
            'create' => array(
                'reqType' => 'POST',
                'path' => array('Emails'),
                'pathVars' => array('module'),
                'method' => 'createRecord',
                'shortHelp' => 'This method creates a new Emails record',
                'longHelp' => 'modules/Emails/clients/base/api/help/emails_record_post_help.html',
            ),
            'update' => array(
                'reqType' => 'PUT',
                'path' => array('Emails', '?'),
                'pathVars' => array('module', 'record'),
                'method' => 'updateRecord',
                'shortHelp' => 'This method updates an Emails record',
                'longHelp' => 'modules/Emails/clients/base/api/help/emails_record_put_help.html',
            ),
        );
    }

    /**
     * Prevents the creation of a bean when the state transition is invalid.
     *
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

        $transition = $this->isValidStateTransition('create', static::STATE_ANY, $args['state']);

        if (!$transition) {
            $message = "State transition to {$args['state']} is invalid for creating an email";
            throw new SugarApiExceptionInvalidParameter($message);
        }

        return parent::createBean($api, $args, $additionalProperties);
    }

    /**
     * Prevents the update of a bean when the state transition is invalid.
     *
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

        if (isset($args['state'])) {
            $transition = $this->isValidStateTransition('update', $bean->state, $args['state']);

            if (!$transition) {
                $message = "State transition from {$bean->state} to {$args['state']} is invalid for an email";
                throw new SugarApiExceptionInvalidParameter($message);
            }
        }

        return parent::updateBean($bean, $api, $args);
    }

    /**
     * Prevents existing Notes records from being linked as attachments.
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
     * The sender cannot be removed. Replace the current sender by linking another existing record or creating a new
     * related record.
     *
     * {@inheritdoc}
     */
    protected function unlinkRelatedRecords(ServiceBase $service, SugarBean $bean, array $ids)
    {
        $def = $bean->getFieldDefinition('from');

        foreach ($def['links'] as $link) {
            $linkName = is_array($link) ? $link['name'] : $link;
            unset($ids[$linkName]);
        }

        parent::unlinkRelatedRecords($service, $bean, $ids);
    }

    /**
     * Prepares attachments for being related. This includes patching the related record arguments for attachments to
     * contain the data necessary for creating the requisite Notes records, as well as placing the file.
     *
     * Creating records for the links from the from, to, cc, and bcc collection fields is not supported. Only existing
     * records can be added for these links, with the exception of email_addresses_from, email_addresses_to,
     * email_addresses_cc, and email_addresses_bcc.
     *
     * {@inheritdoc}
     */
    protected function createRelatedRecords(ServiceBase $service, SugarBean $bean, array $data)
    {
        $relate = array();
        $skip = array();
        $doNotSkip = array(
            'email_addresses_from',
            'email_addresses_to',
            'email_addresses_cc',
            'email_addresses_bcc',
        );

        foreach (array('from', 'to', 'cc', 'bcc') as $field) {
            $def = $bean->getFieldDefinition($field);

            foreach ($def['links'] as $link) {
                $linkName = is_array($link) ? $link['name'] : $link;

                if (!in_array($linkName, $doNotSkip)) {
                    $skip[] = $linkName;
                }
            }
        }

        foreach ($data as $linkName => $records) {
            switch ($linkName) {
                case 'attachments':
                    $relate[$linkName] = array();

                    foreach ($records as $record) {
                        $sourceFile = $this->getAttachmentSource($record);

                        if (!empty($sourceFile)) {
                            unset($record['_file']);
                            $destinationFile = $this->setupAttachmentNoteRecord($bean, $record);

                            $uploaded = (!empty($record['file_source']) &&
                                $record['file_source'] === Email::EMAIL_ATTACHMENT_UPLOADED);

                            if ($this->moveOrCopyAttachment($sourceFile, $destinationFile, $uploaded)) {
                                $relate[$linkName][] = $record;
                            }
                        }
                    }

                    break;
                case in_array($linkName, $skip):
                    // Creating records over these links is not supported.
                    break;
                default:
                    $relate[$linkName] = $records;
            }
        }

        parent::createRelatedRecords($service, $bean, $relate);
    }

    /**
     * Fixes the arguments for the links email_addresses_from, email_addresses_to, email_addresses_cc, and
     * email_addresses_bcc to prevent the creation of duplicate email addresses.
     *
     * {@inheritdoc}
     */
    protected function getRelatedRecordArguments(SugarBean $bean, array $args, $action)
    {
        $fixup = array(
            'email_addresses_from',
            'email_addresses_to',
            'email_addresses_cc',
            'email_addresses_bcc',
        );
        $fixedArgs = array();

        foreach ($args as $field => $value) {
            $fixedArgs[$field] = $value;

            if (!in_array($field, $fixup) || !is_array($value)) {
                // Let the parent function handle it.
                continue;
            }

            $this->fixupRelatedEmailAddressesArgs($fixedArgs[$field]);
        }

        return parent::getRelatedRecordArguments($bean, $fixedArgs, $action);
    }

    /**
     * Is the supplied state transition valid?
     *
     * @param string $operation
     * @param string $fromState
     * @param string $toState
     * @return boolean
     */
    protected function isValidStateTransition($operation, $fromState, $toState)
    {
        if ($fromState === $toState) {
            return true;
        }

        $transitions = $this->validStateTransitions[$operation];

        foreach ($transitions as $transition) {
            if (in_array($transition['from'], array(self::STATE_ANY, $fromState)) &&
                in_array($toState, $transition['to'])
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the qualified upload source file if the attachment record is valid or null.
     *
     * An attachment record is valid if an attachment file is specified and it exists in the upload directory.
     *
     * @param array $record
     * @return null|string
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

        return null;
    }

    /**
     * Specs out a new Notes object in the array format that {@link ModuleApi::createBean()} expects.
     *
     * @param SugarBean $bean
     * @param array $record The data for the Notes record. This method generates the ID.
     * @return string The location of the file for the subsequent move or copy.
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
     * Puts the file in the correct place to be used as an attachment.
     *
     * Moves the file if it was uploaded. Otherwise, to avoid duplication of read-only attachment files, this method
     * first tries to hard link the file and copies the file to the destination if hard linking fails.
     *
     * @param string $source
     * @param string $destination
     * @param bool $uploaded
     * @return bool
     */
    protected function moveOrCopyAttachment($source, $destination, $uploaded = false)
    {
        $source = UploadFile::realpath($source);
        $destination = UploadFile::realpath($destination);

        if ($uploaded) {
            $result = rename($source, $destination);
        } elseif (link($source, $destination)) {
            $result = true; // create a hard link if possible
        } else {
            $result = copy($source, $destination);
        }

        if (!$result) {
            $GLOBALS['log']->error("Failed to link/copy file from {$source} to {$destination}");
        }

        return $result;
    }

    /**
     * If any email address in the "create" arguments already exists, then those arguments are moved to the "add"
     * arguments with the respective IDs.
     *
     * @param array $args
     */
    protected function fixupRelatedEmailAddressesArgs(array &$args)
    {
        if (empty($args['create']) || !is_array($args['create'])) {
            return;
        }

        $sea = $this->getSugarEmailAddress();

        if (empty($args['add'])) {
            $args['add'] = array();
        }

        for ($i = 0; $i < count($args['create']); $i++) {
            $data = $args['create'][$i];

            if (!empty($data['email_address'])) {
                $guid = $sea->getGuid($data['email_address']);

                if (!empty($guid)) {
                    // This email address already exists, so just link it instead of creating it.
                    $args['add'][] = array_merge($data, array('id' => $guid));
                    unset($args['create'][$i]);
                }
            }
        }

        if (empty($args['add'])) {
            unset($args['add']);
        }

        if (empty($args['create'])) {
            unset($args['create']);
        }
    }

    /**
     * {@link SugarEmailAddress} extends {@link SugarBean}, which has a very expensive constructor. This method allows
     * us to avoid executing that constructor multiple times.
     *
     * @return SugarEmailAddress
     */
    protected function getSugarEmailAddress()
    {
        if (!$this->sugarEmailAddress) {
            $this->sugarEmailAddress = new SugarEmailAddress();
        }

        return $this->sugarEmailAddress;
    }
}
