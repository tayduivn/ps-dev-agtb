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

namespace Sugarcrm\SugarcrmTestUnit\inc\SugarCache;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 *
 * SugarCacheDb tests
 * @coversDefaultClass \SugarCacheDb
 *
 */
class SugarCacheDbTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \TimeDate
     */
    protected $timeDate;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        // use fixed UTC now date for all tests
        $this->timeDate = new \TimeDate();
        $this->timeDate->setNow(new \SugarDateTime('1918-11-11 11:11:11', new \DateTimeZone('UTC')));
    }

    /**
     * @covers ::_setExternal
     * @covers ::getSqlDateTime
     * @covers ::hashKeyName
     * @dataProvider providerTestSetExternal
     */
    public function testSetExternal($key, $value, $ttl, $sql)
    {
        $db = $this->getDBManagerMock(array('query'));

        $db->expects($this->once())
            ->method('query')
            ->with($this->equalTo($sql));

        $cache = $this->getCacheMock();
        TestReflection::setProtectedValue($cache, 'db', $db);
        TestReflection::setProtectedValue($cache, '_expireTimeout', $ttl);
        TestReflection::callProtectedMethod($cache, '_setExternal', array($key, $value));
    }

    public function providerTestSetExternal()
    {
        return array(
            array(
                'test_key_1',
                'somevalue1',
                0,
                "INSERT INTO key_value_cache (id, value, date_expires) VALUES " .
                "('376d715253018a2a21932b07e3514181', 'czoxMDoic29tZXZhbHVlMSI7', NULL) " .
                "ON DUPLICATE KEY UPDATE value = 'czoxMDoic29tZXZhbHVlMSI7', date_expires = NULL",
            ),
            array(
                'test_key_2',
                'somevalue2',
                300,
                "INSERT INTO key_value_cache (id, value, date_expires) VALUES " .
                "('c307261fe5442632d927962c72924670', 'czoxMDoic29tZXZhbHVlMiI7', '1918-11-11 11:16:11') " .
                "ON DUPLICATE KEY UPDATE value = 'czoxMDoic29tZXZhbHVlMiI7', date_expires = '1918-11-11 11:16:11'",
            ),
        );
    }

    /**
     * @covers ::_getExternal
     * @covers ::getSqlDateTime
     * @covers ::hashKeyName
     * @dataProvider providerTestGetExternal
     */
    public function testGetExternal($key, $ttl, $expected)
    {
        $db = $this->getDBManagerMock(array('fetchOne'));

        $db->expects($this->once())
            ->method('fetchOne')
            ->with($this->equalTo($expected));

        $cache = $this->getCacheMock();
        TestReflection::setProtectedValue($cache, 'db', $db);
        TestReflection::setProtectedValue($cache, '_expireTimeout', $ttl);
        TestReflection::callProtectedMethod($cache, '_getExternal', array($key));
    }

    public function providerTestGetExternal()
    {
        return array(
            array(
                "key1",
                0,
                "SELECT value FROM key_value_cache WHERE id = 'c2add694bf942dc77b376592d9c862cd' " .
                "AND (date_expires IS NULL OR date_expires > '1918-11-11 11:11:11')",
            ),
            array(
                "key2",
                300,
                "SELECT value FROM key_value_cache WHERE id = '78f825aaa0103319aaa1a30bf4fe3ada' " .
                "AND (date_expires IS NULL OR date_expires > '1918-11-11 11:11:11')",
            ),
        );
    }

    /**
     * @covers ::encode
     * @dataProvider providerTestEncode
     */
    public function testEncodeDecode($value, $expected)
    {
        $cache = $this->getCacheMock();
        $encoded = TestReflection::callProtectedMethod($cache, 'encode', array($value));
        $this->assertSame($expected, $encoded);
    }

    public function providerTestEncode()
    {
        return array(
            array(
                '',
                'czowOiIiOw==',
            ),
            array(
                'stringstuff',
                'czoxMToic3RyaW5nc3R1ZmYiOw==',
            ),
            array(
                array('foo' => 'bar'),
                'YToxOntzOjM6ImZvbyI7czozOiJiYXIiO30=',
            ),
            array(
                new \StdClass(),
                'Tzo4OiJzdGRDbGFzcyI6MDp7fQ==',
            ),
            array(
                true,
                'YjoxOw==',
            ),
        );
    }

    /**
     * @covers ::decode
     * @dataProvider providerTestDecode
     */
    public function testDecode($value, $expected)
    {
        $cache = $this->getCacheMock();
        $decoded = TestReflection::callProtectedMethod($cache, 'decode', array($value, 'key'));
        $this->assertEquals($expected, $decoded);
    }

    public function providerTestDecode()
    {
        return array(
            array(
                'failunserialize',
                null,
            ),
            array(
                'YToxOntzOjM6ImZvbyI7czozOiJiYXIiO30=',
                array('foo' => 'bar'),
            ),
            array(
                'czoxMToic3RyaW5nc3R1ZmYiOw==',
                'stringstuff',
            ),
        );
    }

    /**
     * @return \SugarCacheDb
     */
    protected function getCacheMock(array $methods = null)
    {
        $mock = $this->getMockBuilder('SugarCacheDb')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        TestReflection::setProtectedValue($mock, 'timeDate', $this->timeDate);
        TestReflection::setProtectedValue($mock, '_keyPrefix', '');
        TestReflection::setProtectedValue($mock, 'logger', $this->createMock('Psr\Log\LoggerInterface'));
        return $mock;
    }

    /**
     * @return \DBManager
     */
    protected function getDBManagerMock(array $methods)
    {
        $methods = array_merge(array('quoted', 'convert'), $methods);

        $mock = $this->getMockBuilder('DBManager')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMockForAbstractClass();

        // stub quoted method
        $mock->expects($this->any())
            ->method('quoted')
            ->will($this->returnCallback(array($this, 'dbQuoted')));

        // stub convert method
        $mock->expects($this->any())
            ->method('convert')
            ->will($this->returnCallback(array($this, 'dbConvert')));

        return $mock;
    }

    /**
     * Callback for stubbed \DBManager::quoted
     */
    public function dbQuoted()
    {
        $args = func_get_args();
        return "'" . array_shift($args) . "'";
    }

    /**
     * Callback for stubbed \DBManager::convert
     */
    public function dbConvert()
    {
        $args = func_get_args();
        return array_shift($args);
    }
}
