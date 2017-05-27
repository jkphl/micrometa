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

namespace Jkphl\Micrometa\Tests\Application;

use Jkphl\Micrometa\Application\Factory\ItemFactory;
use Jkphl\Micrometa\Application\Item\Item;
use Jkphl\Micrometa\Application\Value\StringValue;
use Jkphl\Micrometa\Domain\Item\Iri;
use Jkphl\Micrometa\Infrastructure\Factory\MicroformatsFactory;

/**
 * Item factory tests
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 */
class ItemFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the item factory
     */
    public function testItemFactory()
    {
        $itemFactory = new ItemFactory(0);
        $rawItem = (object)['type' => ['test']];
        $item = $itemFactory($rawItem);
        $this->assertInstanceOf(Item::class, $item);
        $this->assertEquals([new Iri('', 'test')], $item->getType());
        $this->assertNull($item->getValue());
    }

    /**
     * Test an item property list with alias
     */
    public function testAliasedItemProperty()
    {
        $itemFactory = new ItemFactory(0);
        $rawItem = (object)[
            'type' => ['test'],
            'properties' => [
                (object)[
                    'name' => 'alias-property',
                    'profile' => MicroformatsFactory::MF2_PROFILE_URI,
                    'values' => ['value']
                ]
            ],
            'children' => [
                (object)[
                    'type' => ['test']
                ]
            ]
        ];
        $item = $itemFactory($rawItem);
        $this->assertInstanceOf(Item::class, $item);
        $this->assertEquals(
            [MicroformatsFactory::MF2_PROFILE_URI.'alias-property' => [new StringValue('value')]],
            $item->getProperties()->export()
        );
        $propertyList = $item->getProperties();
        $this->assertTrue(
            $propertyList->offsetExists(
                (object)['name' => 'alias-property', 'profile' => MicroformatsFactory::MF2_PROFILE_URI]
            )
        );
        /** @noinspection PhpIllegalArrayKeyTypeInspection */
        $this->assertTrue(
            isset(
                $propertyList[(object)['name' => 'alias-property', 'profile' => MicroformatsFactory::MF2_PROFILE_URI]]
            )
        );
        $this->assertTrue(
            $propertyList->offsetExists(
                (object)['name' => 'aliasProperty', 'profile' => MicroformatsFactory::MF2_PROFILE_URI]
            )
        );
        $this->assertTrue(
            $propertyList->offsetExists('alias-property')
        );
        $this->assertFalse(
            $propertyList->offsetExists('invalid-alias-property')
        );
        $this->assertEquals(
            (object)[
                'format' => 0,
                'id' => null,
                'lang' => null,
                'types' => ['test'],
                'properties' => [
                    MicroformatsFactory::MF2_PROFILE_URI.'alias-property' => ['value']
                ],
                'items' => [
                    (object)[
                        'format' => 0,
                        'id' => null,
                        'lang' => null,
                        'types' => ['test'],
                        'properties' => [],
                        'items' => []
                    ]
                ]
            ], $item->export()
        );
    }

    /**
     * Test an invalid item property list
     */
    public function testInvalidItemPropertyList()
    {
        $itemFactory = new ItemFactory(0);
        $rawItem = (object)['type' => ['test'], 'properties' => ['test' => false]];
        $item = $itemFactory($rawItem);
        $this->assertInstanceOf(Item::class, $item);
        $this->assertEquals([], $item->getProperties()->export());
    }

    /**
     * Test an invalid item property value list
     */
    public function testInvalidItemPropertyValueList()
    {
        $itemFactory = new ItemFactory(0);
        $rawItem = (object)[
            'type' => ['test'],
            'properties' => [
                'test' => (object)[
                    'profile' => MicroformatsFactory::MF2_PROFILE_URI,
                    'name' => 'name',
                    'values' => false
                ]
            ]
        ];
        $item = $itemFactory($rawItem);
        $this->assertInstanceOf(Item::class, $item);
        $this->assertEquals([], $item->getProperties()->export());
    }
}
