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

namespace Sugarcrm\SugarcrmTests\Dav\Cal\Adapter;

/**
 * Class AdapterCRYS1639Test
 * @covers Sugarcrm\Sugarcrm\Dav\Cal\Adapter\AdapterAbstract
 */
class AdapterCRYS1639Test extends \PHPUnit_Framework_TestCase
{
    /** @var \User */
    protected $origUser = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->origUser = $GLOBALS['current_user'];

        $userMock = $this->getMock(get_class($GLOBALS['current_user']), array('getPreference'));
        $userMock->method('getPreference')->will($this->returnValueMap(array(
            array('timezone', 'global', 'Europe/Minsk'),
        )));

        $GLOBALS['current_user'] = $userMock;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $GLOBALS['current_user'] = $this->origUser;
        parent::tearDown();
    }

    /**
     * Return source file content with replaced new line symbols.
     *
     * @param string $name
     * @return string
     */
    protected static function getSourceIcsFile($name)
    {
        return preg_replace('/\n */', "\n", trim(file_get_contents(__DIR__ . '/sources/' . $name . '.ics')));
    }

    /**
     * Provider for testPrepareForImport.
     *
     * @see Sugarcrm\SugarcrmTests\Dav\Cal\Adapter\AdapterCRYS1639Test::testPrepareForImport
     * @return array
     */
    public static function prepareForImportProvider()
    {
        $randomDataHashes = array(
            array(\Sugarcrm\Sugarcrm\Util\Uuid::uuid1(), rand(1000, 1999)),
            array(\Sugarcrm\Sugarcrm\Util\Uuid::uuid1(), rand(2000, 2999)),
            array(\Sugarcrm\Sugarcrm\Util\Uuid::uuid1(), rand(3000, 3999)),
        );

        $addressees = array(
            'parent' => array(
                array(
                    'beanName' => 'Addressees',
                    'beanId' => $randomDataHashes[0][0],
                    'email' => 'test_1@test.com',
                    'displayName' => 'Custom Lead',
                ),
                array(
                    'beanName' => 'Addressees',
                    'beanId' => $randomDataHashes[1][0],
                    'email' => 'test_2@test.com',
                    'displayName' => 'Custom Lead',
                ),
                array(
                    'beanName' => 'Addressees',
                    'beanId' => $randomDataHashes[2][0],
                    'email' => 'test_3@test.com',
                    'displayName' => 'Custom Lead',
                ),
            ),
        );

        $groupId = \Sugarcrm\Sugarcrm\Util\Uuid::uuid1();

        $participants_links = json_encode($addressees);

        return array(
            'RemoveParentEventFromRecurring' => array(
                'participants_links' => $participants_links,
                'before' => static::getSourceIcsFile('RemoveRecurringParent.before'),
                'after' => static::getSourceIcsFile('RemoveRecurringParent.after'),
                'expected' => array(
                    array(
                        array('delete', null, null, '2016-03-02 06:00:00', null, $groupId),
                        array(),
                        array(),
                    ),
                ),
                'groupId' => $groupId,
            ),
        );
    }

    /**
     * Checking the data preparation for imports. Removed events should be pushed in result.
     *
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Adapter\AdapterAbstract::prepareForImport
     * @dataProvider prepareForImportProvider
     * @param string $participantsLinks
     * @param string $before
     * @param string $after
     * @param array $expected
     * @param string $groupId
     */
    public function testPrepareForImport($participantsLinks, $before, $after, array $expected, $groupId)
    {
        /** @var \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings|\PHPUnit_Framework_MockObject_MockObject $mockAdapter */
        $mockAdapter = $this->getMock('\Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Meetings', array('createGroupId'));
        $mockAdapter->method('createGroupId')->willReturn($groupId);

        $collection = new \CalDavEventCollection();
        $collection->setData($after);
        $collection->participants_links = $participantsLinks;

        $actual = $mockAdapter->prepareForImport($collection, array('update', $before));
        $this->assertEquals($expected, $actual);
    }
}
