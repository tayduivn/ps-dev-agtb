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

namespace Sugarcrm\Sugarcrm\JobQueue\Handler;

use Sugarcrm\Sugarcrm\JobQueue\Client\ClientInterface;
use Sugarcrm\Sugarcrm\JobQueue\Exception\LogicException;

class ExportRecords implements RunnableInterface, SubtaskCapableInterface
{
    /**
     * @var string $module Module name.
     */
    protected $module;

    /**
     * @var array $data Of Ids.
     */
    protected $data;

    /**
     * @var ClientInterface To create related jobs.
     */
    protected $JQClient;

    /**
     * @var string Id of the Note.
     */
    protected $noteId;

    /**
     * @param string $module
     * @param string $data
     * @param string $noteId Id of the Note.
     * @throws LogicException
     */
    public function __construct($module, $data, $noteId)
    {
        if (empty($data)) {
            throw new LogicException('Nothing to export.');
        }
        $note = \BeanFactory::getBean('Notes', $noteId);
        if (!$note || !$note->id) {
            throw new LogicException('The Note does not exist.');
        }
        $this->module = $module;
        $this->data = $data;
        $this->noteId = $noteId;
    }

    /**
     * Divides target records into chunks and save results into a note.
     * {@inheritdoc}
     */
    public function run()
    {
        $records = array_chunk($this->data, \SugarConfig::getInstance()->get('max_record_fetch_size'));
        foreach ($records as $chunk) {
            $note = \BeanFactory::getBean('Notes');
            $note->id = create_guid();
            $note->new_with_id = true;
            $note->name = self::generateNoteName($this->module, true);
            $note->filename = $note->id  . '.csv';
            $note->file_mime_type = 'text/csv';
            $note->save();

            $this->JQClient->ExportToCSV($this->module, $chunk, $note->id);
        }

        return \SchedulersJob::JOB_RUNNING;
    }

    /**
     * Generates general name of the Note.
     * @param string $module Name fo the module.
     * @param bool $part Is it a chunk note.
     * @return string
     */
    public static function generateNoteName($module, $part = false)
    {
        $dt = new \TimeDate();
        return "Exporting " . ($part ? "(a part) " : "") . "of {$module} at {$dt->now()}";
    }
}
