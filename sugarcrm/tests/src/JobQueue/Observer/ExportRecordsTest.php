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

namespace Sugarcrm\SugarcrmTests\JobQueue\Observer;

use Psr\Log\NullLogger;
use Sugarcrm\Sugarcrm\JobQueue\Observer\ExportRecordsObserver;
use Sugarcrm\Sugarcrm\JobQueue\Observer\Reflection;
use Sugarcrm\Sugarcrm\JobQueue\Workload\Workload;

class ExportRecordsTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var \SchedulersJob
     */
    protected $parentJob;

    /**
     * @var \SchedulersJob
     */
    protected $subJob1;

    /**
     * @var \SchedulersJob
     */
    protected $subJob2;

    /**
     * @var \Note
     */
    protected $note1;

    /**
     * @var \Note
     */
    protected $note2;

    /**
     * @var \Note
     */
    protected $note3;

    /**
     * @var ExportRecordsObserver
     */
    protected $observer;

    /**
     * @var Reflection
     */
    protected $reflection;

    public function setUp()
    {
        $this->observer = new ExportRecordsObserver(new NullLogger());
        $this->reflection = new Reflection(new NullLogger());

        list($this->parentJob, $this->note1) = $this->createJob();
        list($this->subJob1, $this->note2) = $this->createJob($this->parentJob->id);
        list($this->subJob2, $this->note3) = $this->createJob($this->parentJob->id);
    }

    public function tearDown()
    {
        \SugarTestNoteUtilities::removeAllCreatedNotes();
        \SugarTestSchedulersJobUtilities::removeAllCreatedJobs();
        \SugarTestHelper::tearDown();
    }

    /**
     * @return ExportRecordsObserver
     */
    protected function getObserver()
    {
        if (!$this->observer) {
            $this->observer = new ExportRecordsObserver();
        }
        return $this->observer;
    }

    /**
     * @return ExportRecordsObserver
     */
    protected function getReflection()
    {
        if (!$this->reflection) {
            $this->reflection = new Reflection();
        }
        return $this->reflection;
    }

    /**
     * Creates Job and Note, and adds note id into job's data.
     * @param string|null $parentId
     * @return \SchedulersJob
     */
    protected function createJob($parentId = null)
    {
        if (!empty($parentId)) {
            $workload = new Workload('ExportToCSV', array());
            $workload->setAttribute('dbId', $parentId);
        } else {
            $workload = new Workload('ExportRecords', array());
        }

        $note = \SugarTestNoteUtilities::createNote();
        $workload->setData(array($workload->getHandlerName(), array(), $note->id));
        $this->getReflection()->onAdd($workload);

        $job = \BeanFactory::getBean('SchedulersJobs', $workload->getAttribute('dbId'));
        // as $job has been created by Reflection class, add it to the helper, so it will able to delete it
        \SugarTestSchedulersJobUtilities::setCreatedJob(array($job->id));
        return array($job, $note);
    }

    /**
     * Observer should bind SchedulersJobs with its Note.
     */
    public function testJobAndNoteBinding()
    {
        $this->checkJobAndNoteBinding($this->parentJob, $this->note1);
        $this->checkJobAndNoteBinding($this->subJob1, $this->note2);
    }

    /**
     * Check if a note is linked with a job.
     * @param \SchedulersJob $job
     * @param \Note $note
     */
    public function checkJobAndNoteBinding(\SchedulersJob $job, \Note $note)
    {
        $data = $job->unserializeData($job->data);
        $workload = new Workload($job->name, $data, array('dbId' => $job->id));
        $this->getObserver()->onResolve($workload, \SchedulersJob::JOB_SUCCESS);

        $job->load_relationship('notes');
        /* @var \Link2 $link */
        $link = $job->notes;
        $relatedNotes = $link->get();

        // expect one note
        $this->assertEquals(1, count($relatedNotes));
        // expect known note
        $this->assertEquals($note->id, reset($relatedNotes));
    }

    /**
     * Observer should combine subjob's result into one file, cutting the first line (headers) of that files.
     */
    public function testJobFinalResult()
    {
        $header = "Header\n";
        $content1 = "Content1\n";
        $content2 = "Content2\n";

        $targetPath = 'upload://' . $this->note1->id;

        // prepare children data
        file_put_contents('upload://' . $this->note2->id, $header . $content1);
        file_put_contents('upload://' . $this->note3->id, $header . $content2);
        // save job's status
        $this->subJob1->resolution = \SchedulersJob::JOB_SUCCESS;
        $this->subJob1->status = \SchedulersJob::JOB_STATUS_DONE;
        $this->subJob1->save();
        $this->subJob2->resolution = \SchedulersJob::JOB_SUCCESS;
        $this->subJob2->status = \SchedulersJob::JOB_STATUS_DONE;
        $this->subJob2->save();

        $this->assertFileNotExists($targetPath);

        // run observer
        $workload = new Workload(
            $this->subJob1->name,
            array('', '', $this->note2->id),
            array('dbId' => $this->subJob1->id)
        );
        $this->getObserver()->onResolve($workload, \SchedulersJob::JOB_SUCCESS);
        $workload = new Workload(
            $this->subJob2->name,
            array('', '', $this->note3->id),
            array('dbId' => $this->subJob2->id)
        );
        $this->getObserver()->onResolve($workload, \SchedulersJob::JOB_SUCCESS);

        // check result
        $this->assertFileExists($targetPath);
        $this->assertNotEquals(0, filesize($targetPath));
        $result = file_get_contents($targetPath);
        $this->assertContains($content1, $result);
        $this->assertContains($content2, $result);
    }
}
