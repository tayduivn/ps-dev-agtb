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

require_once 'include/SugarQueue/jobs/SugarJobKBSContentExpireArticles.php';

class SugarJobKBSContentExpireArticlesTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarJobKBSContentExpireArticles
     */
    protected $job;

    /**
     * @var KBSContent
     */
    protected $article;

    protected function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        $td = new TimeDate();
        $this->article = SugarTestKBSContentUtilities::createBean();
        $this->article->exp_date = $td->nowDate();
        $this->article->save();

        $schedulersJob = $this->getMock('SchedulersJob');
        $schedulersJob->expects($this->any())->method('succeedJob')->will($this->returnValue(true));

        $this->job = new SugarJobKBSContentExpireArticles();
        $this->job->setJob($schedulersJob);
    }

    public function tearDown()
    {
        SugarTestKBSContentUtilities::removeAllCreatedBeans();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testUnpublishedArticleCannotBeExpired()
    {
        $this->job->run(null);

        $this->article->retrieve();
        $this->assertEquals(KBSContent::DEFAULT_STATUS, $this->article->status);
    }

    public function testPublishedArticleExpiration()
    {
        $this->article->status = 'published';
        $this->article->save();

        $this->job->run(null);

        $this->article->retrieve();
        $this->assertEquals('expired', $this->article->status);
    }

}
