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

require_once 'include/SugarQueue/jobs/SugarJobKBSContentUpdateArticles.php';

class SugarJobKBSContentUpdateArticlesTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarJobKBSContentUpdateArticles
     */
    protected $job;

    /**
     * @var KBSContent
     */
    protected $expArticle;

    /**
     * @var KBSContent
     */
    protected $approvedArticle;

    protected function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        $td = new TimeDate();
        $this->expArticle = SugarTestKBSContentUtilities::createBean();
        $this->expArticle->exp_date = $td->nowDate();
        $this->expArticle->save();

        $this->approvedArticle = SugarTestKBSContentUtilities::createBean();
        $this->approvedArticle->active_date = $td->nowDate();
        $this->approvedArticle->save();

        $schedulersJob = $this->getMock('SchedulersJob');
        $schedulersJob->expects($this->any())->method('succeedJob')->will($this->returnValue(true));

        $this->job = new SugarJobKBSContentUpdateArticles();
        $this->job->setJob($schedulersJob);
    }

    public function tearDown()
    {
        SugarTestKBSContentUtilities::removeAllCreatedBeans();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Only published articles can be expired.
     */
    public function testUnpublishedArticleCannotBeExpired()
    {
        $this->expArticle->status = KBSContent::ST_DRAFT;
        $this->job->run(null);

        $this->expArticle->retrieve();
        $this->assertEquals(KBSContent::ST_DRAFT, $this->expArticle->status);
    }

    /**
     * Only approved articles can be published.
     */
    public function testUnapprovedArticleCannotBePublished()
    {
        $this->approvedArticle->status = KBSContent::ST_DRAFT;
        $this->job->run(null);

        $this->approvedArticle->retrieve();
        $this->assertEquals('draft', $this->approvedArticle->status);
    }

    /**
     * If an approved article has expiration date - publish and then expire.
     */
    public function testApproveWhenAlreadyExpired()
    {
        $td = new TimeDate();
        $this->approvedArticle->status = KBSContent::ST_APPROVED;
        $this->approvedArticle->exp_date = $td->nowDate();
        $this->approvedArticle->active_date = $td->nowDate();
        $this->approvedArticle->save();

        $this->job->run(null);

        $this->approvedArticle->retrieve();
        $this->assertEquals(KBSContent::ST_EXPIRED, $this->approvedArticle->status);
        $this->assertEquals($td->nowDate(), $this->approvedArticle->exp_date);
        $this->assertEquals($td->nowDate(), $this->approvedArticle->active_date);
    }

    /**
     * The job should handle previous dates for expiring.
     */
    public function testExpirationDateLessThanNow()
    {
        $td = new TimeDate();
        $newDate = $td->getNow()->modify('-10 days');

        $this->expArticle->status = KBSContent::ST_PUBLISHED;
        $this->expArticle->exp_date = $td->asUserDate($newDate);
        $this->expArticle->save();

        $this->job->run(null);

        $this->expArticle->retrieve();
        $this->assertEquals(KBSContent::ST_EXPIRED, $this->expArticle->status);
    }

    /**
     * The job should handle previous dates for approving.
     */
    public function testApprovedDateLessThanNow()
    {
        $td = new TimeDate();
        $newDate = $td->getNow()->modify('-10 days');

        $this->approvedArticle->status = KBSContent::ST_APPROVED;
        $this->approvedArticle->active_date = $td->asUserDate($newDate);
        $this->approvedArticle->save();
        $this->job->run(null);

        $this->approvedArticle->retrieve();
        $this->assertEquals(KBSContent::ST_PUBLISHED, $this->approvedArticle->status);
    }

    /**
     * Approving with today's date.
     */
    public function testPublishing()
    {
        $this->approvedArticle->status = KBSContent::ST_APPROVED;
        $this->approvedArticle->save();

        $this->job->run(null);

        $this->approvedArticle->retrieve();
        $this->assertEquals(KBSContent::ST_PUBLISHED, $this->approvedArticle->status);
    }

    /**
     * Internal article should be published respectively.
     */
    public function testPublishingAsInternal()
    {
        $this->approvedArticle->status = KBSContent::ST_APPROVED;
        $this->approvedArticle->internal_rev = true;
        $this->approvedArticle->save();

        $this->job->run(null);

        $this->approvedArticle->retrieve();
        $this->assertEquals(KBSContent::ST_PUBLISHED_IN, $this->approvedArticle->status);
    }

    /**
     * Expiring with today's date.
     * @dataProvider providerPublishingStatuses
     */
    public function testExpiration($status)
    {
        $this->expArticle->status = $status;
        $this->expArticle->save();

        $this->job->run(null);

        $this->expArticle->retrieve();
        $this->assertEquals(KBSContent::ST_EXPIRED, $this->expArticle->status);
    }

    public function providerPublishingStatuses()
    {
        return array(
            array(
                KBSContent::ST_PUBLISHED,
            ),
            array(
                KBSContent::ST_PUBLISHED_IN,
            ),
            array(
                KBSContent::ST_PUBLISHED_EX,
            ),
        );
    }
}
