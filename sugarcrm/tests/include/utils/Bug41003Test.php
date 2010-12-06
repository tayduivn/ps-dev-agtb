<?php
require_once 'include/utils.php';

class Bug41003Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function testVerifyStrippingOfBrInBr2nlFunction()
    {
        $this->assertEquals("here is my text with no newline", br2nl("here is my text with no newline"));
        $this->assertEquals("here is my text with a newline lowercased\n", br2nl("here is my text with a newline lowercased<br>"));
        $this->assertEquals("here is my text with a newline mixed case\n", br2nl("here is my text with a newline mixed case<Br>"));
        $this->assertEquals("here is my text with a newline mixed case with /\n", br2nl("here is my text with a newline mixed case with /<Br />"));
        $this->assertEquals("here is my text with a newline uppercase\n", br2nl("here is my text with a newline uppercase<BR />"));
        $this->assertEquals("here is my crappy text éèçàô$*%ù§!#with a newline\n in the middle", br2nl("here is my crappy text éèçàô$*%ù§!#with a newline<bR> in the middle"));
    }
}

