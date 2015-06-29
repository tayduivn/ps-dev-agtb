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

namespace Sugarcrm\SugarcrmTestsUnit\Util\Arrays\TrackableArray;

use Sugarcrm\Sugarcrm\Util\Arrays\TrackableArray\TrackableArray;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Util\Arrays\TrackableArray\TrackableArray
 */
class TrackableArrayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests that a new OrderedHash respects the order given to constructor.
     * @covers ::getChangedKeys
     * @covers ::enableTracking
     */
    public function testModificationsAreTracked()
    {
        $arr = new TrackableArray();
        $arr['a'] = 1;
        $arr['b'] = 'string';
        $arr['c'] = array('data');
        $arr['d'] = 'not changed';
        $arr->enableTracking();
        $arr['a'] += 1;
        $arr['b'] = 'new value';
        $arr['c']['new'] = 'value';

        $changedKeys = $arr->getChangedKeys();
        sort($changedKeys);
        $this->assertSame(array('a', 'b', 'c'), $changedKeys);
    }

    /**
     * Tests that a new OrderedHash respects the order given to constructor.
     * @covers ::getChangedKeys
     * @covers ::enableTracking
     * @covers ::offsetSet
     * @covers ::offsetGet
     */
    public function testValuesAppendedAreTracked()
    {
        $arr = new TrackableArray();
        $arr[] = "zero";
        $arr->enableTracking();
        $arr[] = "one";
        $this->assertSame(array(1), $arr->getChangedKeys());
        $arr[] = "two";
        $this->assertSame(array(1, 2), $arr->getChangedKeys());
        $arr["4"] = "four";
        $arr[] = "five";
        $this->assertSame(array(1, 2, 4, 5), $arr->getChangedKeys());
    }


    /**
     * Tests that a new OrderedHash respects the order given to constructor.
     * @covers ::getChangedKeys
     * @covers ::enableTracking
     * @covers ::offsetSet
     * @covers ::offsetGet
     */
    public function testUnsetsAreTracked()
    {
        $arr = new TrackableArray();
        $arr['a'] = 'foo';
        $arr['b'] = 'bar';
        $arr->enableTracking();
        unset($arr['a']);
        $this->assertSame(array('a'), $arr->getChangedKeys());
    }


    /**
     * Tests that a new OrderedHash respects the order given to constructor.
     * @covers ::getArrayCopy
     * @covers ::offsetGet
     * @covers ::offsetSet
     * @covers ::offsetUnset
     */
    public function testArrayValuesAreIdentical()
    {
        $array = array();
        $tracked = new TrackableArray();

        //Appending value to empty array
        $array[] = "a";
        $tracked[] = "a";

        //Adding value by key
        $array["zz"] = "z";
        $tracked["zz"] = "z";

        //Appending value to non-empty array
        $array[] = "b";
        $tracked[] = "b";

        //Adding value by numeric key
        $array[5] = "five";
        $tracked[5] = "five";

        //Appending value to non-empty array with out of order numeric key
        $array[] = "c";
        $tracked[] = "c";

        //Nested array creation by reference
        $array['arr']['a'] = "A";
        $tracked['arr']['a'] = "A";

        //unset values
        $array['gone'] = 'not here';
        $tracked['gone'] = 'not here';
        unset($array['gone']);
        unset($tracked['gone']);

        //unset nested values
        $array['arr']['c'] = "c";
        $tracked['arr']['c'] = "c";
        unset($array['arr']['c']);
        unset($tracked['arr']['c']);

        $this->assertSame($array, $tracked->getArrayCopy());
    }


    /**
     * Tests that a new OrderedHash respects the order given to constructor.
     * @covers ::applyTrackedChangesToArray
     */
    public function testApplyChangesToArray()
    {
        $tracked = new TrackableArray();
        $tracked['toBeUnset'] = true;
        $tracked['tobeChanged'] = true;
        $tracked['deep']['a'] = 1;
        $tracked['deep']['unchanged'] = true;
        $tracked->enableTracking();
        $tracked['tobeAdded'] = true;
        unset($tracked['toBeUnset']);
        $tracked['deep']['a'] = 2;
        $tracked['new_deep']['a'] = 1;
        $tracked['tobeChanged'] = 'changed';
        $arr = array(
            'toBeUnset' => true,
            'tobeChanged' => 1,
            'deep' => array('a' => 1)
        );
        $tracked->applyTrackedChangesToArray($arr);
        $this->assertSame(array(
            'tobeChanged' => 'changed',
            'deep' => array(
                'a' => 2
            ),
            'new_deep' => array(
                'a' => 1
            ),
            'tobeAdded' => true,
        ), $arr);
    }

    /**
     * Tests that a new OrderedHash respects the order given to constructor.
     * @covers ::populateFromArray
     * @covers ::__construct
     */
    public function testPopulateFromArray()
    {
        $arr = array(
            'a' => 1,
            'deep' => array('b' => 2),
        );
        $trackable = new TrackableArray($arr);


        //$this->assertInstanceOf('Sugarcrm\Sugarcrm\Util\Arrays\TrackableArray\TrackableArray', $trackable['deep']);
        $this->assertEquals(1, $trackable['a']);
        $this->assertSame($arr, $trackable->getArrayCopy());

        //Verify Tracking for deep arrays created this way
        $trackable->enableTracking();
        $trackable['deep']['b'] = 3;
        $trackable->applyTrackedChangesToArray($arr);

        $this->assertSame($arr, $trackable->getArrayCopy());

        $trackable = new TrackableArray($arr);
        $trackable->populateFromArray($arr);

        $this->assertSame($arr, $trackable->getArrayCopy());
    }

}
