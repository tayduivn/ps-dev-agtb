<?php
require_once 'include/JSON.php';

class JSONTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testCanEncodeBasicArray() 
    {
        $array = array('foo' => 'bar', 'bar' => 'foo');
        $this->assertEquals(
            '{"foo":"bar","bar":"foo"}',
            JSON::encode($array)
        );
    }

    public function testCanEncodeBasicObjects() 
    {
        $obj = new stdClass();
        $obj->foo = 'bar';
        $obj->bar = 'foo';
        $this->assertEquals(
            '{"foo":"bar","bar":"foo"}',
            JSON::encode($obj)
        );
    }
    
    public function testCanEncodeMultibyteData() 
    {
        $array = array('foo' => '契約', 'bar' => '契約');
        $this->assertEquals(
            '{"foo":"\u5951\u7d04","bar":"\u5951\u7d04"}',
            JSON::encode($array)
        );
    }
    
    public function testCanDecodeObjectIntoArray()
    {
        $array = array('foo' => 'bar', 'bar' => 'foo');
        $this->assertEquals(
            JSON::decode('{"foo":"bar","bar":"foo"}'),
            $array
        );
    }
    
    public function testCanDecodeMultibyteData() 
    {
        $array = array('foo' => '契約', 'bar' => '契約');
        $this->assertEquals(
            JSON::decode('{"foo":"\u5951\u7d04","bar":"\u5951\u7d04"}'),
            $array
        );
    }
}
