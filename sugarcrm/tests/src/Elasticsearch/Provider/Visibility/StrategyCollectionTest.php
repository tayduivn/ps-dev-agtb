<?php
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\Visibility\StrategyCollection;

class StrategyCollectionTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHashInvalidArgument()
    {
        $strategyCollection = new StrategyCollection();
        $strategyCollection->getHash(new stdClass());
    }

    public function testHash()
    {
        $visibility = $this->getMockForAbstractClass('\SugarVisibility', array(), 'TestSugarVisibility', false);
        $strategyCollection = new StrategyCollection();
        $this->assertEquals('TestSugarVisibility', $strategyCollection->getHash($visibility));
    }
}
