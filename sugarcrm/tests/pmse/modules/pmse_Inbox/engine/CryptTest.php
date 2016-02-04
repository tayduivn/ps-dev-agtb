<?php
//FILE SUGARCRM flav=ent ONLY
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
class CryptTest extends PHPUnit_Framework_TestCase
{

    protected function setUp() {
        $this->cript = new Crypt();
        $this->mockCriptBF = $this->getMockBuilder('Crypt_Blowfish')
            ->disableOriginalConstructor()
            ->setMethods(array('encrypt', 'decrypt'))
            ->getMock();
    }
    public function testBlowfishEncode() {


        $this->mockCriptBF->expects($this->any())
            ->method('encrypt')
            ->will($this->returnValue('564564564'));

        $res = $this->cript->blowfishEncode('1234', 'encripted text', $this->mockCriptBF);
        $this->assertEquals($res, 'NTY0NTY0NTY0');
    }
    public function testBlowfishDecode() {

        $this->mockCriptBF->expects($this->any())
            ->method('decrypt')
            ->will($this->returnValue('encripted text'));

        $res = $this->cript->blowfishDecode('1234', '564564564', $this->mockCriptBF);
        $this->assertEquals($res, 'encripted text');
    }
    public function testGenerateRequest() {
        $this->mockCriptBF->expects($this->any())
            ->method('encrypt')
            ->will($this->returnValue('1234567'));

        $res = $this->cript->generateRequest('1234', '564564564', $this->mockCriptBF);
        $this->assertEquals($res, "----- BEGIN LICENSE REQUEST -----\nMTIzNDU2Nw==\n----- END LICENSE REQUEST -----\n");


    }
    public function testGenerateRequestLenghMajorto80() {
        $this->mockCriptBF->expects($this->any())
            ->method('encrypt')
            ->will($this->returnValue('1234567asdfasdfwergwergwergfwertsadfgsdfgsdfgsdfgsdfgsdfgsdfgw4egrgwergwergtwergwergwtwetgwergwergwergwergwergwergwergwergwergwergwergwergwergwergwqwergwergwergwergwergwergwergwergwergwergwergwergwerg'));

        $res = $this->cript->generateRequest('1234', '564564564', $this->mockCriptBF);
        print
            $this->assertEquals($res, "----- BEGIN LICENSE REQUEST -----\nMTIzNDU2N2FzZGZhc2Rmd2VyZ3dlcmd3ZXJnZndlcnRzYWRmZ3NkZmdzZGZnc2RmZ3NkZmdzZGZnc2Rm\nZ3c0ZWdyZ3dlcmd3ZXJndHdlcmd3ZXJnd3R3ZXRnd2VyZ3dlcmd3ZXJnd2VyZ3dlcmd3ZXJnd2VyZ3dl\ncmd3ZXJnd2VyZ3dlcmd3ZXJnd2VyZ3dlcmd3cXdlcmd3ZXJnd2VyZ3dlcmd3ZXJnd2VyZ3dlcmd3ZXJn\nd2VyZ3dlcmd3ZXJnd2VyZ3dlcmc=\n----- END LICENSE REQUEST -----\n");

    }
    public function testOpenLicense()
    {
        $this->mockCriptBF->expects($this->any())
            ->method('encrypt')
            ->will($this->returnValue('1234567'));

        $license = "----- BEGIN LICENSE REQUEST -----\nMTIzNDU2Nw==\n----- END LICENSE REQUEST -----\n";

        $res = $this->cript->openLicense('1234', $license, $this->mockCriptBF);

        $this->assertEquals($res, "");

    }
//    public function testOpenLicenseWrong()
//    {
//        $this->mockCriptBF->expects($this->any())
//            ->method('encrypt')
//            ->will($this->returnValue('1234567'));
//
//        $license = "----- BEGIN LICENSE REQUEST -----EQUEST -----\n";
//
//        $res = $this->cript->openLicense('1234', $license, $this->mockCriptBF);
//        $this->assertEquals($res, "");
//
//    }

    public function testDecodeActivationCode()
    {
        $res = $this->cript->decodeActivationCode('CSCI-AESD-ATEH');
        $this->assertEquals(true, $res->success);

    }
    public function testDecodeActivationCodeInvalid()
    {
        $res = $this->cript->decodeActivationCode('AXYWETINSHRCDU');
        $this->assertEquals(false, $res->success);

    }


}