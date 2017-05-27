<?php

/**
 * micrometa
 *
 * @category Jkphl
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 * @author Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2017 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy of
 *  this software and associated documentation files (the "Software"), to deal in
 *  the Software without restriction, including without limitation the rights to
 *  use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 *  the Software, and to permit persons to whom the Software is furnished to do so,
 *  subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 *  FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 *  COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 *  IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 *  CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 ***********************************************************************************/

namespace Jkphl\Micrometa\Tests\Domain;

use Jkphl\Micrometa\Application\Value\StringValue;
use Jkphl\Micrometa\Domain\Item\Iri;
use Jkphl\Micrometa\Domain\Item\PropertyList;
use Jkphl\Micrometa\Infrastructure\Factory\MicroformatsFactory;

/**
 * Property list tests
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 */
class PropertyListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the property list
     *
     * @expectedException \Jkphl\Micrometa\Domain\Exceptions\ErrorException
     * @expectedExceptionCode 1489784392
     */
    public function testPropertyList()
    {
        $propertyList = new PropertyList();
        $this->assertInstanceOf(PropertyList::class, $propertyList);
        $this->assertEquals(0, count($propertyList));

        // Test adding a property
        $property = (object)[
            'name' => 'name',
            'profile' => MicroformatsFactory::MF2_PROFILE_URI,
            'values' => [new StringValue('John Doe')],
        ];
        $propertyList->add($property);
        $propertyList->add($property);
        $this->assertEquals(1, count($propertyList));

        // Iterate over all properties
        foreach ($propertyList as $propertyName => $propertyValues) {
            $this->assertInstanceOf(Iri::class, $propertyName);
            $this->assertTrue(is_array($propertyValues));
            $this->assertEquals(2, count($propertyValues));
        }

        // Get an unprofiled property
        $unprofiledProperty = $propertyList->offsetGet('name');
        $this->assertTrue(is_array($unprofiledProperty));
        $this->assertInstanceOf(StringValue::class, $unprofiledProperty[0]);
        $this->assertEquals('John Doe', $unprofiledProperty[0]);

        // Get a profiled property via object
        $unprofiledProperty = $propertyList->offsetGet(
            (object)['name' => 'name', 'profile' => MicroformatsFactory::MF2_PROFILE_URI]
        );
        $this->assertTrue(is_array($unprofiledProperty));
        $this->assertInstanceOf(StringValue::class, $unprofiledProperty[1]);
        $this->assertEquals('John Doe', $unprofiledProperty[1]);

        // Get a profiled property via IRI
        $unprofiledProperty = $propertyList->offsetGet(new Iri(MicroformatsFactory::MF2_PROFILE_URI, 'name'));
        $this->assertTrue(is_array($unprofiledProperty));
        $this->assertInstanceOf(StringValue::class, $unprofiledProperty[1]);
        $this->assertEquals('John Doe', $unprofiledProperty[1]);

        // Test unprofiled invalid property
        unset($propertyList['forbidden']);
    }

    /**
     * Test an unprofiled invalid property
     *
     * @expectedException \Jkphl\Micrometa\Domain\Exceptions\OutOfBoundsException
     * @expectedExceptionCode 1488315604
     */
    public function testUnprofiledInvalidProperty()
    {
        $propertyList = new PropertyList();
        $this->assertInstanceOf(PropertyList::class, $propertyList);
        $propertyList['invalid'];
    }

    /**
     * Test an profiled invalid property
     *
     * @expectedException \Jkphl\Micrometa\Domain\Exceptions\OutOfBoundsException
     * @expectedExceptionCode 1488315604
     */
    public function testProfiledInvalidProperty()
    {
        $propertyList = new PropertyList();
        $this->assertInstanceOf(PropertyList::class, $propertyList);
        $propertyList->offsetGet((object)['name' => 'invalid', 'profile' => MicroformatsFactory::MF2_PROFILE_URI]);
    }
}
