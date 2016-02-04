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

namespace Sugarcrm\Sugarcrm\JobQueue\Handler;

use Sugarcrm\Sugarcrm\JobQueue\Exception\LogicException;

require_once 'include/export_utils.php';

class ExportToCSV implements RunnableInterface
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
     * Export records to CSV file.
     * {@inheritdoc}
     */
    public function run()
    {
        $filename = 'upload://' . $this->noteId;
        $result = export($this->module, implode(',', $this->data));
        file_put_contents($filename, $result);

        return \SchedulersJob::JOB_SUCCESS;
    }
}
