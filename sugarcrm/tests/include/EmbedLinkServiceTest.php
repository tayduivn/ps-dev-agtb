<?php
/**
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
class EmbedLinkServiceTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $embedSrc = '<embed src=www.foo.com>';
    
    /**
     * @covers EmbedLinkService::get
     */
    public function testGet_OneLinkInTextButNothingReturnedFromFetch_ReturnsNoEmbedData()
    {
        $url = 'http://www.sugarcrm.com';
        $mock = $this->getMock('EmbedLinkService', array('fetch'));
        $mock->expects($this->once())->method('fetch')->will($this->returnValue(''));
        $actual = $mock->get($url);
        $this->assertEquals(0, count($actual['embeds']), 'Should not return any embed data');
    }

    /**
     * @covers EmbedLinkService::get
     */
    public function testGet_OneLinkInTextButGetsErrorFromFetch_ReturnsNoEmbedData()
    {
        $url = 'http://www.sugarcrm.com';
        $mock = $this->getMock('EmbedLinkService', array('fetch'));
        $mock->expects($this->once())->method('fetch')->will($this->returnValue(false));
        $actual = $mock->get($url);
        $this->assertEquals(0, count($actual['embeds']), 'Should not return any embed data');
    }

    /**
     * @covers EmbedLinkService::get
     */
    public function testGet_NoLinksInText_ReturnsNoEmbedData()
    {
        $service = new EmbedLinkService();
        $actual = $service->get('foo bar');
        $this->assertEquals(0, count($actual['embeds']), 'Should not return any embed data');
    }

    /**
     * @covers EmbedLinkService::get
     */
    public function testGet_TwoImageLinksInText_ReturnsTwoImageEmbedData()
    {
        $service = new EmbedLinkService();
        $actual = $service->get('http://www.foo.com/images/bar.jpg https://www.sugarcrm.com/logo/logo.gif');
        $this->assertEquals(2, count($actual['embeds']), 'Should return two embed data');
        $this->assertEquals('image', $actual['embeds'][0]['type'], 'Should return image type data');
        $this->assertEquals('image', $actual['embeds'][1]['type'], 'Should return image type data');
        $this->assertEquals(
            'http://www.foo.com/images/bar.jpg',
            $actual['embeds'][0]['src'],
            'Should have the image url'
        );
        $this->assertEquals(
            'https://www.sugarcrm.com/logo/logo.gif',
            $actual['embeds'][1]['src'],
            'Should have the image url'
        );
    }

    /**
     * @covers EmbedLinkService::get
     */
    public function testGet_OneLinkWithVideoJsonEmbedLink_ReturnsVideoEmbedData()
    {
        $json = '{"type":"video","html":"' . $this->embedSrc . '","width":200,"height":100}';
        $oEmbedHtml = '<html><head><link type="application/json+oembed" href="http://www.foo.com"/></head></html>';
        $mock = $this->getMock('EmbedLinkService', array('fetch', 'cleanHtml'));
        $mock->expects($this->at(0))
            ->method('fetch')
            ->with('http://www.sugarcrm.com')
            ->will($this->returnValue($oEmbedHtml));
        $mock->expects($this->at(1))->method('fetch')->with('http://www.foo.com')->will($this->returnValue($json));
        $mock->expects($this->once())->method('cleanHtml')->will($this->returnValue($this->embedSrc));
        $actual = $mock->get('http://www.sugarcrm.com');
        $this->assertEquals(1, count($actual['embeds']), 'Should return one set of embed data');
        $this->assertEquals('video', $actual['embeds'][0]['type'], 'Should be video type');
        $this->assertEquals($this->embedSrc, $actual['embeds'][0]['html'], 'Should return the correct embed html');
        $this->assertEquals(200, $actual['embeds'][0]['width'], 'Should return the correct width');
        $this->assertEquals(100, $actual['embeds'][0]['height'], 'Should return the correct height');
    }

    /**
     * @covers EmbedLinkService::get
     */
    public function testGet_OneLinkWithRichJsonEmbedLink_ReturnsRichEmbedData()
    {
        $json = '{"type":"rich","html":"' . $this->embedSrc . '","width":200,"height":100}';
        $oEmbedHtml = '<html><head><link type="application/json+oembed" href="http://www.foo.com"/></head></html>';
        $mock = $this->getMock('EmbedLinkService', array('fetch', 'cleanHtml'));
        $mock->expects($this->at(0))
            ->method('fetch')
            ->with('http://www.sugarcrm.com')
            ->will($this->returnValue($oEmbedHtml));
        $mock->expects($this->at(1))->method('fetch')->with('http://www.foo.com')->will($this->returnValue($json));
        $mock->expects($this->once())->method('cleanHtml')->will($this->returnValue($this->embedSrc));
        $actual = $mock->get('http://www.sugarcrm.com');
        $this->assertEquals(1, count($actual['embeds']), 'Should return one set of embed data');
        $this->assertEquals('rich', $actual['embeds'][0]['type'], 'Should be video type');
        $this->assertEquals($this->embedSrc, $actual['embeds'][0]['html'], 'Should return the correct embed html');
        $this->assertEquals(200, $actual['embeds'][0]['width'], 'Should return the correct width');
        $this->assertEquals(100, $actual['embeds'][0]['height'], 'Should return the correct height');
    }

    /**
     * @covers EmbedLinkService::get
     */
    public function testGet_OneLinkWithVideoXmlEmbedLink_ReturnsVideoEmbedData()
    {
        $html = '<oembed><type>video</type><html>&lt;embed src=www.foo.com&gt;</html><width>200</width><height>100'
            . '</height></oembed>';
        $oEmbedHtml = '<html><head><link type="text/xml+oembed" href="http://www.foo.com"/></head></html>';
        $mock = $this->getMock('EmbedLinkService', array('fetch', 'cleanHtml'));
        $mock->expects($this->at(0))
            ->method('fetch')
            ->with('http://www.sugarcrm.com')
            ->will($this->returnValue($oEmbedHtml));
        $mock->expects($this->at(1))->method('fetch')->with('http://www.foo.com')->will($this->returnValue($html));
        $mock->expects($this->once())->method('cleanHtml')->will($this->returnValue($this->embedSrc));
        $actual = $mock->get('http://www.sugarcrm.com');
        $this->assertEquals(1, count($actual['embeds']), 'Should return one set of embed data');
        $this->assertEquals('video', $actual['embeds'][0]['type'], 'Should be video type');
        $this->assertEquals($this->embedSrc, $actual['embeds'][0]['html'], 'Should return the correct embed html');
        $this->assertEquals('200', $actual['embeds'][0]['width'], 'Should return the correct width');
        $this->assertEquals('100', $actual['embeds'][0]['height'], 'Should return the correct height');
    }

    /**
     * @covers EmbedLinkService::get
     */
    public function testGet_OneLinkInTextButNoEmbedLinks_ReturnsNoEmbedData()
    {
        $mock = $this->getMock('EmbedLinkService', array('fetch'));
        $mock->expects($this->once())
            ->method('fetch')
            ->with('http://www.sugarcrm.com')
            ->will($this->returnValue('<html><head></head></html>'));
        $actual = $mock->get('http://www.sugarcrm.com');
        $this->assertEquals(0, count($actual['embeds']), 'Should not return any embed data');
    }

    /**
     * @covers EmbedLinkService::get
     */
    public function testGet_OneLinkWithVideoJsonEmbedLinkButGetsErrorFromFetch_ReturnsNoEmbedData()
    {
        $oEmbedHtml = '<html><head><link type="application/json+oembed" href="http://www.foo.com"/></head></html>';
        $mock = $this->getMock('EmbedLinkService', array('fetch'));
        $mock->expects($this->at(0))
            ->method('fetch')
            ->with('http://www.sugarcrm.com')
            ->will($this->returnValue($oEmbedHtml));
        $mock->expects($this->at(1))->method('fetch')->with('http://www.foo.com')->will($this->returnValue(false));
        $actual = $mock->get('http://www.sugarcrm.com');
        $this->assertEquals(0, count($actual['embeds']), 'Should not return any embed data');
    }

    /**
     * @covers EmbedLinkService::get
     */
    public function testGet_OneLinkWithVideoXmlEmbedLinkButGetsErrorFromFetch_ReturnsNoEmbedData()
    {
        $oEmbedHtml = '<html><head><link type="text/xml+oembed" href="http://www.foo.com"/></head></html>';
        $mock = $this->getMock('EmbedLinkService', array('fetch'));
        $mock->expects($this->at(0))
            ->method('fetch')
            ->with('http://www.sugarcrm.com')
            ->will($this->returnValue($oEmbedHtml));
        $mock->expects($this->at(1))->method('fetch')->with('http://www.foo.com')->will($this->returnValue(false));
        $actual = $mock->get('http://www.sugarcrm.com');
        $this->assertEquals(0, count($actual['embeds']), 'Should not return any embed data');
    }

    /**
     * @covers EmbedLinkService::get
     */
    public function testGet_OneLinkWithHttpInHtml_ReturnsRelativeProtocolInHtml()
    {
        $json = '{"type":"video","html":"' . $this->embedSrc . '","width":200,"height":100}';
        $oEmbedHtml = '<html><head><link type="application/json+oembed" href="http://www.foo.com"/></head></html>';
        $mock = $this->getMock('EmbedLinkService', array('fetch', 'cleanHtml'));
        $mock->expects($this->at(0))
            ->method('fetch')
            ->with('http://www.sugarcrm.com')
            ->will($this->returnValue($oEmbedHtml));
        $mock->expects($this->at(1))->method('fetch')->with('http://www.foo.com')->will($this->returnValue($json));
        $mock->expects($this->once())->method('cleanHtml')->will($this->returnValue($this->embedSrc));
        $actual = $mock->get('http://www.sugarcrm.com');
        $this->assertEquals($this->embedSrc, $actual['embeds'][0]['html'], 'Should return the correct embed html');
    }

    /**
     * @covers EmbedLinkService::get
     */
    public function testGet_OneLinkWithHttpsInHtml_ReturnsRelativeProtocolInHtml()
    {
        $json = '{"type":"video","html":"' . $this->embedSrc . '","width":200,"height":100}';
        $oEmbedHtml = '<html><head><link type="application/json+oembed" href="http://www.foo.com"/></head></html>';
        $mock = $this->getMock('EmbedLinkService', array('fetch', 'cleanHtml'));
        $mock->expects($this->at(0))
            ->method('fetch')
            ->with('http://www.sugarcrm.com')
            ->will($this->returnValue($oEmbedHtml));
        $mock->expects($this->at(1))->method('fetch')->with('http://www.foo.com')->will($this->returnValue($json));
        $mock->expects($this->once())->method('cleanHtml')->will($this->returnValue($this->embedSrc));
        $actual = $mock->get('http://www.sugarcrm.com');
        $this->assertEquals($this->embedSrc, $actual['embeds'][0]['html'], 'Should return the correct embed html');
    }

    /**
     * Test regexp that finds all URLs in an input text
     *
     * @dataProvider findAllUrls_DataProvider
     * @covers EmbedLinkService::findAllUrls
     */
    public function testFindAllUrls_InputText_ReturnsCorrectResults($input, $count)
    {
        $service = new EmbedLinkTestServiceProxy();
        $actual = $service->findAllUrls($input);
        $this->assertEquals($count, count($actual));
    }

    /**
     * Data Providers
     */
    public function findAllUrls_DataProvider()
    {
        return array(
            array('input' => 'http://www.foobar.com', 'count' => 1),
            array('input' => 'foo bar', 'count' => 0),
            array('input' => 'foo www.foobar.com bar', 'count' => 1),
            array('input' => 'foo www.bar.com:8888/123/test?q=fdfad&i=fdafdas bar', 'count' => 1),
            array('input' => 'foo https://www.bar.com/123/test?q=fdfad&i=fdafdas bar', 'count' => 1),
            array('input' => 'foo https://www.bar.com bar http://www.foo.uk.co/', 'count' => 2),
            array('input' => 'foo.com', 'count' => 0),
            array('input' => 'foo@bar.com', 'count' => 0),
            array('input' => 'foo.bar.com', 'count' => 0),
            array('input' => 'http://test.foobar.com', 'count' => 1),
            array('input' => 'https://WWW.FOOBAR.COM/', 'count' => 1),
            array('input' => 'http://www.youtube.com/watch?v=N2u44-zZYdo&list=PL37ZVnwpeshF7AHpbZt33aW0brYJyNftx http://www.youtube.com/watch?v=BY0-AI1Sxy0&list=PL37ZVnwpeshF7AHpbZt33aW0brYJyNftx', 'count' => 2),
        );
    }
}

class EmbedLinkTestServiceProxy extends EmbedLinkService
{
    public function __call($name, $args)
    {
        return call_user_func_array(array($this, $name), $args);
    }
}
