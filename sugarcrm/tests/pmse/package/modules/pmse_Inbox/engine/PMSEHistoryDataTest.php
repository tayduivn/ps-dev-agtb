<?php

class PMSEHistoryDataTest extends PHPUnit_Framework_TestCase
{

    private $module= 'Leads';

    protected function setUp() {
        $this->object = new PMSEHistoryData($this->module);
    }

    public function testSavePredata()
    {
        $value['before_data'][1] = 'Ok';
        $this->object->savePredata(1,'Ok');
        $logData = $this->object->getLog();
        $this->assertEquals($value['before_data'], $logData['before_data']);
    }
    
    public function testSavePostData()
    {
        $value['after_data'][1] = 'Ok';
        $this->object->savePostData(1,'Ok');
        $logData = $this->object->getLog();
        $this->assertEquals($value['after_data'], $logData['after_data']);
    }
    
    public function testVerifyRepeated()
    {
        $value['after_data'][1] = 'Ok';
        $this->object->verifyRepeated('Ok','Ok');
    }
    
    public function testLock()
    {
        $value = 'conditons';
        $this->object->lock($value);
    }
}
 