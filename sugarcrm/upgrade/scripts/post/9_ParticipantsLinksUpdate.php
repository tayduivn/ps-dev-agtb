<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
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
 * Upgrade participants links.
 */
class SugarUpgradeParticipantsLinksUpdate extends UpgradeScript
{
    /**
     * {@inheritdoc}
     */
    public $order = 9995;

    /**
     * {@inheritdoc}
     */
    public $type = self::UPGRADE_DB;

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        // This upgrade is for version lower than 7.8.0.0
        if (version_compare($this->from_version, '7.8.0.0', '>=')) {
            return;
        }

        /** @var CalDavEventCollection $bean */
        $bean = BeanFactory::getBean('CalDavEvents');

        $query = $this->getQuery();
        $query->from($bean);
        $query->select(array('id', 'participants_links'));
        $query->orderBy('date_entered', 'ASC');
        $rows = $query->execute();

        foreach ($rows as $row) {
            $participantsLinks = json_decode($row['participants_links'], true);

            $resultUpdate = array();
            if (!isset($participantsLinks['parent'])) {
                foreach ($participantsLinks as $email => $participant) {
                    $resultUpdate['parent'][] = array(
                        'beanName' => $participant['beanName'],
                        'beanId' => $participant['beanId'],
                        'email' => $email,
                        'displayName' => $this->createFormatDisplayName($participant),
                    );
                }

                $this->db->updateParams(
                    $bean->table_name,
                    $bean->getFieldDefinitions(),
                    array('participants_links' => json_encode($resultUpdate)),
                    array('id' => $row['id']),
                    null,
                    true,
                    true
                );
            }
        }
    }

    /**
     * Returns Query class to work with.
     * @return SugarQuery
     */
    protected function getQuery()
    {
        return new SugarQuery();
    }

    /**
     * Create display name by participant.
     * @param array $participant
     * @return mixed
     */
    protected function createFormatDisplayName(array $participant)
    {
        $invitee = BeanFactory::getBean($participant['beanName'], $participant['beanId'], array(
            'strict_retrieve' => true,
            'deleted' => false,
        ));

        return $GLOBALS['locale']->formatName($invitee);
    }
}
