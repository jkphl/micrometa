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
use Jkphl\Micrometa\Domain\Item\Item;
use Jkphl\Micrometa\Domain\Item\PropertyListInterface;
use Jkphl\Micrometa\Domain\Value\ValueInterface;

/**
 * Item tests
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 */
class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Public function test the item creation
     *
     * @param string|\stdClass|\stdClass[] $type Item type(s)
     * @param array $properties Item properties
     * @param $itemId Item id
     * @param array $expectedTypes Expected item types
     * @param array $expectedProperties Expected item properties
     * @param string $expectedId Expected item id
     * @dataProvider creationArgumentProvider
     */
    public function testItemCreation(
        $type,
        array $properties,
        $itemId,
        array $expectedTypes,
        array $expectedProperties,
        $expectedId
    ) {
        $item = new Item($type, $properties, $itemId);
        $this->assertInstanceOf(Item::class, $item);
        $this->assertEquals($expectedTypes, $item->getType());
        $this->assertEquals($expectedProperties, $this->convertPropertyListToArray($item->getProperties()));
        $this->assertEquals($expectedId, $item->getId());
    }

    /**
     * Convert a property list to a plain array
     *
     * @param PropertyListInterface $propertyList Property list
     * @return array Property list array
     */
    protected function convertPropertyListToArray(PropertyListInterface $propertyList)
    {
        $propertyListValues = [];
        foreach ($propertyList as $iri => $values) {
            $propertyListValues[$iri->profile.$iri->name] = $values;
        }
        return $propertyListValues;
    }

    /**
     * Data provider for item creation tests
     *
     * @return array Item creation arguments
     */
    public function creationArgumentProvider()
    {
        $item = new Item('test');
        return [
            ['test', [], null, [$this->t('test')], [], null],
            [$this->t('test', 'a'), [], null, [$this->t('test', 'a')], [], null],
            [['test'], [], null, [$this->t('test')], [], null],
            [['test', 'lorem'], [], null, [$this->t('test'), $this->t('lorem')], [], null],
            [['test', '', 'lorem'], [], null, [$this->t('test'), $this->t('lorem')], [], null],
            [
                'test',
                [$this->p('name1', 'value1')],
                null,
                [$this->t('test')],
                ['name1' => [$this->s('value1')]],
                null
            ],
            [
                'test',
                [$this->p('name1', '')],
                null,
                [$this->t('test')],
                [],
                null
            ],
            [
                'test',
                [$this->p('name1', [])],
                null,
                [$this->t('test')],
                [],
                null
            ],
            [
                'test',
                [$this->p('name1', 'value1', 'profile1/')],
                null,
                [$this->t('test')],
                ['profile1/name1' => [$this->s('value1')]],
                null
            ],
            [
                'test',
                [$this->p('name1', 'value1')],
                null,
                [$this->t('test')],
                ['name1' => [$this->s('value1')]],
                null
            ],
            [
                'test',
                [$this->p('name1', 'value1'), $this->p('name1', 'value2')],
                null,
                [$this->t('test')],
                ['name1' => [$this->s('value1'), $this->s('value2')]],
                null
            ],
            [
                'test',
                [$this->p('name1', 'value1'), $this->p('name2', 'value2')],
                null,
                [$this->t('test')],
                ['name1' => [$this->s('value1')], 'name2' => [$this->s('value2')]],
                null
            ],
            [
                'test',
                [$this->p('name', [$item])],
                null,
                [$this->t('test')],
                ['name' => [$item]],
                null
            ],
            ['test', [], 'id', [$this->t('test')], [], 'id'],
        ];
    }

    /**
     * Create a type object
     *
     * @param string $n Type name
     * @param string $p Type profile
     * @return object Type object
     */
    protected function t($n, $p = '')
    {
        return (object)['profile' => $p, 'name' => $n];
    }

    /**
     * Create a property object
     *
     * @param string $n Property name
     * @param mixed $s Property value(s)
     * @param string $p Property profiles
     * @return \stdClass Property object
     */
    protected function p($n, $s, $p = '')
    {
        $values = array_map([$this, 's'], (array)$s);
        return (object)['profile' => $p, 'name' => $n, 'values' => $values];
    }

    /**
     * Create a string value
     *
     * @param string $s Value
     * @return ValueInterface String value
     */
    protected function s($s)
    {
        return ($s instanceof ValueInterface) ? $s : new StringValue($s);
    }

    /**
     * Test the item creation with an empty types list
     *
     * @expectedException \Jkphl\Micrometa\Domain\Exceptions\InvalidArgumentException
     * @expectedExceptionCode 1490814631
     */
    public function testEmptyTypesList()
    {
        new Item(null);
    }

    /**
     * Test the item creation with an empty types list
     *
     * @expectedException \Jkphl\Micrometa\Domain\Exceptions\InvalidArgumentException
     * @expectedExceptionCode 1488314667
     */
    public function testEmptyTypeName()
    {
        new Item('');
    }

    /**
     * Test the item creation with an empty property name
     *
     * @expectedException \Jkphl\Micrometa\Domain\Exceptions\InvalidArgumentException
     * @expectedExceptionCode 1488314921
     */
    public function testEmptyPropertyName()
    {
        new Item('type', [$this->p('', 'value')]);
    }

    /**
     * Test empty property value list
     *
     * @expectedException \Jkphl\Micrometa\Domain\Exceptions\InvalidArgumentException
     * @expectedExceptionCode 1490814554
     */
    public function testInvalidPropertyStructure()
    {
        new Item('type', [(object)['invalid' => 'structure']]);
    }

    /**
     * Test the item creation with an invalid property value
     *
     * @expectedException \Jkphl\Micrometa\Domain\Exceptions\InvalidArgumentException
     * @expectedExceptionCode 1488315339
     */
    public function testInvalidPropertyValue()
    {
        new Item('type', [(object)['profile' => '', 'name' => 'test', 'values' => [123]]]);
    }

    /**
     * Test the item creation with an invalid property value
     *
     * @expectedException \Jkphl\Micrometa\Domain\Exceptions\OutOfBoundsException
     * @expectedExceptionCode 1488315604
     */
    public function testUnknownPropertyName()
    {
        $item = new Item('type');
        $item->getProperty('name');
    }

    /**
     * Test the item property getter
     */
    public function testItemPropertyGetter()
    {
        $item = new Item('type', [$this->p('name', 123)]);
        $this->assertEquals([new StringValue('123')], $item->getProperty('name'));
    }
}
