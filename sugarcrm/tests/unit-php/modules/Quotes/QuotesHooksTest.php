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

namespace Sugarcrm\SugarcrmTestUnit\modules\Quotes;

/**
 * Class QuoteHooksTest
 * @package Sugarcrm\SugarcrmTestUnit\modules\Quotes
 * @coversDefaultClass \QuoteHooks
 */
class QuoteHooksTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        global $beanFiles;
        \SugarAutoLoader::load('../../modules/Quotes/QuoteHooks.php');
        $this->beanFiles = $beanFiles;
        $beanFiles['Contact'] = '../../modules/Contacts/Contact.php';
        $beanFiles['Task'] = '../../modules/Tasks/Task.php';
        $beanFiles['Note'] = '../../modules/Notes/Note.php';
        $beanFiles['Call'] = '../../modules/Calls/Call.php';
        $beanFiles['Lead'] = '../../modules/Leads/Lead.php';
        $beanFiles['Email'] = '../../modules/Emails/Email.php';
        $beanFiles['Product'] = '../../modules/Products/Product.php';
        $beanFiles['ProductBundle'] = '../../modules/ProductBundles/ProductBundle.php';
        \SugarAutoLoader::load('../../modules/Quotes/Quote.php');
        parent::setup();
    }

    public function tearDown()
    {
        global $beanFiles;
        $beanFiles = $this->beanFiles;
        parent::tearDown();
    }

    /**
     * @covers ::setQLIQuoteLink
     */
    public function testSetQLIQuoteLink()
    {
        $mock = new \QuoteHooks();

        $genericBeanMock = $this->getMockBuilder('\QuoteHooksTest_genericBeanMock')
            ->setMethods(['save'])
            ->disableOriginalConstructor()
            ->getMock();

        $genericBeanMock->expects($this->once())
            ->method('save');

        $quote = $this->getMockBuilder('\Quote')
            ->setMethods(['get_linked_beans'])
            ->disableOriginalConstructor()
            ->getMock();

        $quote->expects($this->once())
            ->method('get_linked_beans')
            ->will($this->returnValue(array($genericBeanMock)));

        $mock->setQLIQuoteLink($quote, 'foo', array('isUpdate'=>false));
    }
}

class QuoteHooksTest_genericBeanMock
{
    public function save()
    {

    }

    public function get_linked_beans()
    {

    }
}
