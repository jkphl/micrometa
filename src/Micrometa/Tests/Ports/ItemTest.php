<?php

/**
 * micrometa
 *
 * @category Jkphl
 * @package Jkphl\Micrometa
 * @subpackage Infrastructure
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

namespace Jkphl\Micrometa\Tests\Ports;

use Jkphl\Micrometa\Application\Factory\AliasFactory;
use Jkphl\Micrometa\Application\Item\Item as ApplicationItem;
use Jkphl\Micrometa\Application\Value\StringValue;
use Jkphl\Micrometa\Infrastructure\Factory\MicroformatsFactory;
use Jkphl\Micrometa\Infrastructure\Parser\Microformats;
use Jkphl\Micrometa\Ports\Item\Item;

/**
 * Parser factory tests
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 */
class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test an item
     */
    public function testItemTypes()
    {
        $feedItem = $this->getFeedItem();
        $this->assertInstanceOf(Item::class, $feedItem);

        // Test the item type
        $this->assertTrue($feedItem->isOfType('h-feed'));
        $this->assertTrue($feedItem->isOfType('h-feed', MicroformatsFactory::MF2_PROFILE_URI));
    }

    /**
     * Create and return an h-feed Microformats item
     *
     * @return Item h-feed item
     */
    protected function getFeedItem()
    {
        $authorItem = new ApplicationItem(
            Microformats::FORMAT,
            new AliasFactory(),
            (object)['profile' => MicroformatsFactory::MF2_PROFILE_URI, 'name' => 'h-card'],
            [
                (object)[
                    'profile' => MicroformatsFactory::MF2_PROFILE_URI,
                    'name' => 'name',
                    'values' => [
                        new StringValue('John Doe')
                    ]
                ],
                (object)[
                    'profile' => MicroformatsFactory::MF2_PROFILE_URI,
                    'name' => 'email',
                    'values' => [
                        new StringValue('john@example.com')
                    ]
                ]
            ]
        );


        $entryItem = new ApplicationItem(
            Microformats::FORMAT,
            new AliasFactory(),
            (object)['profile' => MicroformatsFactory::MF2_PROFILE_URI, 'name' => 'h-entry'],
            [
                (object)[
                    'profile' => MicroformatsFactory::MF2_PROFILE_URI,
                    'name' => 'name',
                    'values' => [
                        new StringValue('Famous blog post')
                    ]
                ],
                (object)[
                    'profile' => MicroformatsFactory::MF2_PROFILE_URI,
                    'name' => 'author',
                    'values' => [
                        $authorItem
                    ]
                ]
            ]
        );


        $feedItem = new ApplicationItem(
            Microformats::FORMAT,
            new AliasFactory(),
            (object)['profile' => MicroformatsFactory::MF2_PROFILE_URI, 'name' => 'h-feed'],
            [
                (object)[
                    'profile' => MicroformatsFactory::MF2_PROFILE_URI,
                    'name' => 'name',
                    'values' => [
                        new StringValue('John Doe\'s Blog')
                    ]
                ],
                (object)[
                    'profile' => MicroformatsFactory::MF2_PROFILE_URI,
                    'name' => 'author',
                    'values' => [
                        $authorItem
                    ]
                ],
                (object)[
                    'profile' => MicroformatsFactory::MF2_PROFILE_URI,
                    'name' => 'custom-property',
                    'values' => [
                        new StringValue('Property for alias testing')
                    ]
                ],
            ],
            [$entryItem, $entryItem]
        );

        return new Item($feedItem);
    }

    /**
     * Test an unprofiled property
     *
     * @expectedException \Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException
     * @expectedExceptionCode 1488315604
     */
    public function testUnprofiledProperty()
    {
        $feedItem = $this->getFeedItem();
        $this->assertInstanceOf(Item::class, $feedItem);

        // Test the item name as an unprofiled property value list
        $feedNameList = $feedItem->getProperty('name');
        $this->assertTrue(is_array($feedNameList));
        $this->assertEquals(1, count($feedNameList));
        $this->assertInstanceOf(StringValue::class, $feedNameList[0]);
        $this->assertEquals('John Doe\'s Blog', strval($feedNameList[0]));

        // Test the item name as an unprofiled single property value
        $feedName = $feedItem->getProperty('name', null, 0);
        $this->assertInstanceOf(StringValue::class, $feedName);
        $this->assertEquals('John Doe\'s Blog', strval($feedName));

        // Test an invalid unprofiled property
        $feedItem->getProperty('invalid');
    }

    /**
     * Test a profiled property
     *
     * @expectedException \Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException
     * @expectedExceptionCode 1488315604
     */
    public function testProfiledProperty()
    {
        $feedItem = $this->getFeedItem();
        $this->assertInstanceOf(Item::class, $feedItem);

        // Test the item name as an unprofiled property value list
        $feedNameList = $feedItem->getProperty('name', MicroformatsFactory::MF2_PROFILE_URI);
        $this->assertTrue(is_array($feedNameList));
        $this->assertEquals(1, count($feedNameList));
        $this->assertInstanceOf(StringValue::class, $feedNameList[0]);
        $this->assertEquals('John Doe\'s Blog', strval($feedNameList[0]));

        // Test the item name as an unprofiled single property value
        $feedName = $feedItem->getProperty('name', MicroformatsFactory::MF2_PROFILE_URI, 0);
        $this->assertInstanceOf(StringValue::class, $feedName);
        $this->assertEquals('John Doe\'s Blog', strval($feedName));

        // Test an invalid unprofiled property
        $feedItem->getProperty('invalid', MicroformatsFactory::MF2_PROFILE_URI);
    }

    /**
     * Test an unprofiled property
     *
     * @expectedException \Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException
     * @expectedExceptionCode 1488315604
     */
    public function testAliasedProperty()
    {
        $feedItem = $this->getFeedItem();
        $this->assertInstanceOf(Item::class, $feedItem);

        // Test the custom item property as an unprofiled property value list
        $feedCustomPropList = $feedItem->getProperty('custom-property');
        $this->assertTrue(is_array($feedCustomPropList));
        $this->assertEquals(1, count($feedCustomPropList));
        $this->assertInstanceOf(StringValue::class, $feedCustomPropList[0]);
        $this->assertEquals('Property for alias testing', strval($feedCustomPropList[0]));

        // Test the custom item property as an unprofiled single property value
        $feedCustomProp = $feedItem->getProperty('custom-property', null, 0);
        $this->assertInstanceOf(StringValue::class, $feedCustomProp);
        $this->assertEquals('Property for alias testing', strval($feedCustomProp));

        // Test the custom item property via the convenience getter
        $feedCustomProp = $feedItem->customProperty;
        $this->assertInstanceOf(StringValue::class, $feedCustomProp);
        $this->assertEquals('Property for alias testing', strval($feedCustomProp));

        // Test an invalid property
        $feedItem->invalidProperty;
    }

    /**
     * Test a property stack
     *
     * @expectedException \Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException
     * @expectedExceptionCode 1488315604
     */
    public function testPropertyStack()
    {
        $feedItem = $this->getFeedItem();
        $this->assertInstanceOf(Item::class, $feedItem);

        // Request a valid property stack
        $propertyValues = $feedItem->getFirstProperty('photo', MicroformatsFactory::MF2_PROFILE_URI, 'name');
        $this->assertEquals([new StringValue('John Doe\'s Blog')], $propertyValues);

        // Request unknown properties only
        $feedItem->getFirstProperty('photo', MicroformatsFactory::MF2_PROFILE_URI, 'invalid');
    }
}
