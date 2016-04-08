<?php
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

/**
 * CalendarEvents hook handler class
 * contains hook configuration for CalendarEvents
 */
class CalendarEventsHookManager
{
    protected $inviteeRelationships = array(
        'meetings_users' => true,
        'meetings_contacts' => true,
        'meetings_leads' => true,
        'meetings_addressees' => true,
        'calls_users' => true,
        'calls_contacts' => true,
        'calls_leads' => true,
        'calls_addressees' => true,
    );

    /**
     * CalendarEvents initialization hook
     *
     * Serve "before_relationship_update" hook handling
     */
    public function beforeRelationshipUpdate(SugarBean $bean, $event, $args)
    {
        $relationship = $args['relationship'];
        if (($bean->module_name === 'Meetings' || $bean->module_name === 'Calls') &&
            !empty($this->inviteeRelationships[$relationship]) &&
             empty($bean->updateAcceptStatus)
        ) {
            throw new BypassRelationshipUpdateException();
        }
    }

    /**
     * CalendarEvents after relationships update hook.
     * Serve "after_relationship_update" hook handling.
     *
     * @param SugarBean|Meeting|Call $bean
     * @param string $event
     * @param array $args
     */
    public function afterRelationshipUpdate(SugarBean $bean, $event, $args)
    {
        $relationship = $args['relationship'];
        if (!empty($this->inviteeRelationships[$relationship])) {
            $invitee = $this->getInviteeInfo($bean, $args);
            $this->changesInvitees($bean, 'changed', $invitee);
        }
    }

    /**
     * CalendarEvents after relationships add hook.
     * Serve "after_relationship_add" hook handling.
     *
     * @param SugarBean|Meeting|Call $bean
     * @param string $event
     * @param array $args
     */
    public function afterRelationshipAdd(SugarBean $bean, $event, $args)
    {
        $relationship = $args['relationship'];
        if (!empty($this->inviteeRelationships[$relationship]) && $bean->isUpdate()) {
            $invitee = $this->getInviteeInfo($bean, $args);
            $this->changesInvitees($bean, 'added', $invitee);
        }
    }

    /**
     * CalendarEvents after relationships delete hook.
     * Serve "after_relationship_delete" hook handling.
     *
     * @param SugarBean|Meeting|Call $bean
     * @param string $event
     * @param array $args
     */
    public function afterRelationshipDelete(SugarBean $bean, $event, $args)
    {
        $relationship = $args['relationship'];
        if (!empty($this->inviteeRelationships[$relationship]) && $bean->isUpdate()) {
            $invitee = $this->getInviteeInfo($bean, $args);
            $this->changesInvitees($bean, 'deleted', $invitee);
        }
    }

    /**
     * Extract and get info about current changed invitee.
     *
     * @param SugarBean $bean Primary bean object.
     * @param array $args Arguments of relationship modification action.
     * @return array Invitee information.
     */
    protected function getInviteeInfo($bean, $args)
    {
        $link = $args['link'];
        $bean->load_relationship($link);
        $acceptStatus = 'none';
        if (isset($bean->$link->rows[$args['related_id']])) {
            $acceptStatus = $bean->$link->rows[$args['related_id']]['accept_status'];
        }
        $inviteeBean = BeanFactory::getBean($args['related_module'], $args['related_id']);
        $invitee = array(
            $inviteeBean->module_name,
            $inviteeBean->id,
            $inviteeBean->emailAddress->getPrimaryAddress($inviteeBean),
            $acceptStatus,
            $GLOBALS['locale']->formatName($inviteeBean),
        );

        return $invitee;
    }

    /**
     * Send to export invitee or conservation for sent later.
     *
     * @param SugarBean|Meeting|Call $bean
     * @param string $action
     * @param array $invitee
     */
    protected function changesInvitees(SugarBean $bean, $action, array $invitee)
    {
        if ($bean->isUpdate()) {
            $bean->getCalDavHook()->export($bean, array('update', array(), array($action => array($invitee))));
        } else {
            if (!isset($bean->inviteesChanges[$action])) {
                $bean->inviteesChanges[$action] = array();
            }
            $bean->inviteesChanges[$action][] = $invitee;
        }
    }
}
