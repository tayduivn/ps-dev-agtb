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

namespace Sugarcrm\Sugarcrm\JobQueue\Observer;

use Psr\Log\LoggerInterface;
use Sugarcrm\Sugarcrm\JobQueue\Helper\Producer;
use Sugarcrm\Sugarcrm\JobQueue\Workload\WorkloadInterface;

/**
 * Class ExportRecordsObserver
 * @package JobQueue
 */
class ExportRecordsObserver implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Setup resolution helper.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function onAdd(WorkloadInterface $workload)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function onRun(WorkloadInterface $workload)
    {
    }

    /**
     * Resolve parent and child tasks.
     * {@inheritdoc}
     */
    public function onResolve(WorkloadInterface $workload, $resolution)
    {
        $job = \BeanFactory::getBean('SchedulersJobs', $workload->getAttribute('dbId'));
        if (!$job->id) {
            return;
        }

        // link note with job
        list(,,$noteId) = $workload->getData();
        $note = \BeanFactory::getBean('Notes', $noteId);
        $job->load_relationship('notes');
        $job->notes->add($note);

        // if parent task - skip following, we need that only after child is finished
        if (!$job->job_group) {
            return;
        }

        /* @var \SchedulersJob $parentJob */
        $parentJob = \BeanFactory::getBean('SchedulersJobs', $job->job_group);
        if (!$parentJob) {
            return;
        }
        $parentHelper = new Producer($parentJob);
        if ($parentHelper->isAllChildrenDone()) {
            list(,,$parentNoteId) = $parentJob->unserializeData($parentJob->data);
            file_put_contents('upload://' . $parentNoteId, '');

            $first = true;
            foreach ($parentHelper->getChildren(array(\SchedulersJob::JOB_SUCCESS)) as $subjobId) {
                $subjobId = $subjobId['id'];
                /* @var \SchedulersJob $subjob */
                $subjob = \BeanFactory::getBean('SchedulersJobs', $subjobId);
                list(,,$subNoteId) = $subjob->unserializeData($subjob->data);
                if ($piece = fopen('upload://' . $subNoteId, 'r')) {
                    // on first file keep header, skip on others
                    $first = !$first;
                    while ($line = fgets($piece)) {
                        if ($first === true) {
                            // skip headers
                            $first = false;
                            continue;
                        }
                        file_put_contents('upload://' . $parentNoteId, $line, FILE_APPEND);
                    }
                    fclose($piece);
                }
            }
        }
    }
}
