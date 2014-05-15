<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */

require_once 'modules/KBSContents/KBSContentsApiHelper.php';

class KBSContentsTest extends Sugar_PHPUnit_Framework_TestCase 
{
    /**
     * @var KBSContentMock
     */
    protected $bean;

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('current_user', array(true, true));
        $this->bean = SugarTestKBSContentUtilities::createBean();
    }

    public function tearDown()
    {
        SugarTestKBSContentUtilities::removeAllCreatedBeans();
        SugarTestHelper::tearDown();
    }

    public function testFormatForApi() 
    {
        $helper = new KBSContentsApiHelper(SugarTestRestUtilities::getRestServiceMock());
        $data = $helper->formatForApi($this->bean);
        $lang = $this->bean->getPrimaryLanguage();

        $this->assertEquals($data['name'], $this->bean->name);
        $this->assertEquals($data['language'], $lang['key']);
        $this->assertInternalType('array', $data['attachment_list']);
    }

    public function testKBSContentsDefaults()
    {
        $this->assertEquals($this->bean->language, 'en');
        $this->assertEquals($this->bean->status, KBSContent::DEFAULT_STATUS);
        $this->assertEquals($this->bean->revision, 1);
    }

    public function testPrimaryLanguage()
    {
        $this->assertEquals(array(
            'label' => 'English',
            'key' => 'en'
        ), $this->bean->getPrimaryLanguage());
    }

    public function testDocumentRelationship()
    {
        $doc = BeanFactory::getBean('KBSDocuments');
        $doc->fetch($this->bean->kbsdocument_id);
        $this->assertEquals($this->bean->name, $doc->name);
    }

    public function testArticleRelationship()
    {
        $article = BeanFactory::getBean('KBSArticles');
        $article->fetch($this->bean->kbsarticle_id);
        $this->assertEquals($this->bean->name, $article->name);
    }

    public function testLocalizationsLink()
    {
        $this->bean->load_relationship('localizations');
        $this->assertInstanceOf('LocalizationsLink', $this->bean->localizations);

        $query = new SugarQuery;
        $query->from($this->bean);

        $joinSugarQuery = $this->bean->localizations->buildJoinSugarQuery($query);
        $this->assertInternalType('array', $joinSugarQuery);
    }

    public function testRevisionsLink()
    {
        $this->bean->load_relationship('revisions');
        $this->assertInstanceOf('RevisionsLink', $this->bean->revisions);

        $query = new SugarQuery;
        $query->from($this->bean);

        $joinSugarQuery = $this->bean->revisions->buildJoinSugarQuery($query);
        $this->assertInternalType('array', $joinSugarQuery);
    }

    public function testResetActivRev()
    {
        $this->assertEquals($this->bean->active_rev, 1);

        $this->bean->resetActiveRevision();
        $contents = BeanFactory::getBean('KBSContents');
        $contents->fetch($this->bean->id);

        $this->assertEquals($contents->active_rev, 0);
    }

}
