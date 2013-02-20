<?php

namespace Raekke\Tests\Util;

use Raekke\Util\ArrayCollection;

class ArrayCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testItGetsDefaultWhenValuesIsUnknown()
    {
        $collection = new ArrayCollection(array(
            'known1' => 'value1',
            'known2' => null,
            'known3' => false,
            'known4' => 0,
        ));

        $this->assertEquals('value1', $collection->get('known1'));
        $this->assertEquals(null, $collection->get('known2', 'default'));
        $this->assertEquals(false, $collection->get('known3', 'default'));
        $this->assertEquals(0, $collection->get('known4', 'default'));
        $this->assertEquals('default', $collection->get('unknown', 'default'));
    }
}
