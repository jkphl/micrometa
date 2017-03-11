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
     * @param string|array $type Item type(s)
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
        $this->assertEquals($expectedProperties, $item->getProperties());
        $this->assertEquals($expectedId, $item->getId());
    }

    /**
     * Data provider for item creation tests
     *
     * @return array Item creation arguments
     */
    public function creationArgumentProvider()
    {
        function s($s)
        {
            return new StringValue($s);
        }

        $item = new Item('test');
        return [
            ['test', [], null, ['test'], [], null],
            [['test'], [], null, ['test'], [], null],
            [['test', 'lorem'], [], null, ['test', 'lorem'], [], null],
            [['test', '', 'lorem'], [], null, ['test', 'lorem'], [], null],
            ['test', ['name1' => s('value1')], null, ['test'], ['name1' => [s('value1')]], null],
            ['test', ['name1' => [s('value1')]], null, ['test'], ['name1' => [s('value1')]], null],
            [
                'test',
                ['name1' => [s('value1'), s('value2')]],
                null,
                ['test'],
                ['name1' => [s('value1'), s('value2')]],
                null
            ],
            [
                'test',
                ['name1' => [s('value1'), s(''), s('value2')]],
                null,
                ['test'],
                ['name1' => [s('value1'), s('value2')]],
                null
            ],
            [
                'test',
                ['name1' => s('value1'), 'name2' => [s('value2')]],
                null,
                ['test'],
                ['name1' => [s('value1')], 'name2' => [s('value2')]],
                null
            ],
            ['test', ['name' => $item], null, ['test'], ['name' => [$item]], null],
            ['test', [], 'id', ['test'], [], 'id'],
        ];
    }

    /**
     * Test the item creation with an empty types list
     *
     * @expectedException \Jkphl\Micrometa\Domain\Exceptions\InvalidArgumentException
     * @expectedExceptionCode 1488314667
     */
    public function testEmptyTypesList()
    {
        new Item(null);
    }

    /**
     * Test the item creation with an empty property name
     *
     * @expectedException \Jkphl\Micrometa\Domain\Exceptions\InvalidArgumentException
     * @expectedExceptionCode 1488314921
     */
    public function testEmptyPropertyName()
    {
        new Item('type', ['' => ['value']]);
    }

    /**
     * Test the item creation with an invalid property value
     *
     * @expectedException \Jkphl\Micrometa\Domain\Exceptions\InvalidArgumentException
     * @expectedExceptionCode 1488315339
     */
    public function testInvalidPropertyValue()
    {
        new Item('type', ['name' => [123]]);
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
        $item = new Item('type', ['name' => [new StringValue('123')]]);
        $this->assertEquals([new StringValue('123')], $item->getProperty('name'));
    }
}
