<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Micrometa
 * @subpackage Infrastructure
 * @author     Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @copyright  Copyright © 2018 Joschi Kuphal <joschi@kuphal.net> / @jkphl
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 */

/***********************************************************************************
 *  The MIT License (MIT)
 *
 *  Copyright © 2018 Joschi Kuphal <joschi@kuphal.net> / @jkphl
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

use Jkphl\Micrometa\Application\Item\PropertyList;
use Jkphl\Micrometa\Application\Value\StringValue;
use Jkphl\Micrometa\Infrastructure\Factory\MicroformatsFactory;
use Jkphl\Micrometa\Ports\Item\Item;
use Jkphl\Micrometa\Ports\Item\ItemInterface;
use Jkphl\Micrometa\Ports\Item\ItemList;
use Jkphl\Micrometa\Tests\AbstractTestBase;
use Jkphl\Micrometa\Tests\MicroformatsFeedTrait;
use Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException;

/**
 * Parser factory tests
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 */
class ItemTest extends AbstractTestBase
{
    /**
     * Use the Microformats feed method
     */
    use MicroformatsFeedTrait;

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
        $this->assertFalse($feedItem->isOfType('invalid', MicroformatsFactory::MF2_PROFILE_URI));

        // Test other item properties
        $this->assertEquals('feed-id', $feedItem->getId());
        $this->assertEquals('feed-language', $feedItem->getLanguage());
        $this->assertEquals('feed-value', $feedItem->getValue());
    }

    /**
     * Test the item properties
     */
    public function testItemProperties()
    {
        $this->expectException('Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException');
        $this->expectExceptionCode('1491672553');
        $feedItem = $this->getFeedItem();
        $this->assertInstanceOf(Item::class, $feedItem);

        $properties = $feedItem->getProperties();
        $this->assertInstanceOf(PropertyList::class, $properties);
        $this->assertEquals(3, count($properties));

        // Get an unknown property
        $feedItem->getProperty('name', null, 2);
    }

    /**
     * Test the item export
     */
    public function testItemExport()
    {
        $feedItem = $this->getFeedItem();
        $this->assertInstanceOf(Item::class, $feedItem);

        $export = $feedItem->toObject();
        $this->assertInstanceOf(\stdClass::class, $export);
        foreach (['format', 'types', 'properties', 'items'] as $property) {
            $this->assertTrue(isset($export->$property));
        }
    }

    /**
     * Test an unprofiled property
     */
    public function testItemUnprofiledProperty()
    {
        $this->expectException('Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException');
        $this->expectExceptionCode('1488315604');
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
     */
    public function testItemProfiledProperty()
    {
        $this->expectException('Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException');
        $this->expectExceptionCode('1488315604');
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
     */
    public function testItemAliasedProperty()
    {
        $this->expectException('Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException');
        $this->expectExceptionCode('1488315604');
        $feedItem = $this->getFeedItem();
        $this->assertInstanceOf(Item::class, $feedItem);

        // Run the custom item property tests
        $this->runCustomItemPropertyTests($feedItem);

        // Test an invalid property
        $feedItem->invalidProperty;
    }

    /**
     * Run the custom item property tests
     *
     * @param Item $feedItem Feed item
     */
    protected function runCustomItemPropertyTests(Item $feedItem)
    {
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
    }

    /**
     * Test a property stack
     */
    public function testItemPropertyStack()
    {
        $this->expectException('Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException');
        $this->expectExceptionCode('1488315604');
        $feedItem = $this->getFeedItem();
        $this->assertInstanceOf(Item::class, $feedItem);

        // Request a valid property stack
        $propertyValues = $feedItem->getFirstProperty('photo', MicroformatsFactory::MF2_PROFILE_URI, 'name');
        $this->assertEquals(['John Doe\'s Blog'], $propertyValues);

        // Request unknown properties only
        $feedItem->getFirstProperty('photo', MicroformatsFactory::MF2_PROFILE_URI, 'invalid');
    }

    /**
     * Test a property item
     */
    public function testItemPropertyItem()
    {
        $feedItem = $this->getFeedItem();
        $this->assertInstanceOf(Item::class, $feedItem);

        // Request a valid property stack
        /** @var ItemInterface[] $authors */
        $authors = $feedItem->getFirstProperty('author');
        $this->assertTrue(is_array($authors));
        $this->assertInstanceOf(ItemInterface::class, $authors[0]);

        // Test the author name as an unprofiled single property value
        $authorName = $authors[0]->getProperty('name', MicroformatsFactory::MF2_PROFILE_URI, 0);
        $this->assertInstanceOf(StringValue::class, $authorName);
        $this->assertEquals('John Doe', strval($authorName));
    }

    /**
     * Test nested items
     */
    public function testItemNestedItems()
    {
        $feedItem = $this->getFeedItem();
        self::assertInstanceOf(Item::class, $feedItem);

        // Test the number of nested items
        $this->assertEquals(2, count($feedItem));
        $this->assertEquals(2, count($feedItem->getItems()));
        foreach ($feedItem as $itemIndex => $entryItem) {
            $this->assertInstanceOf(ItemInterface::class, $entryItem);
            $this->assertTrue(is_int($itemIndex));
        }
        $this->assertInstanceOf(ItemInterface::class, $feedItem->getFirstItem('h-entry'));
        $this->assertInstanceOf(
            ItemInterface::class,
            $feedItem->getFirstItem('h-entry', MicroformatsFactory::MF2_PROFILE_URI)
        );

        // Test the second entry item
        $entryItem = $feedItem->getItems('h-entry')[1];
        self::assertInstanceOf(ItemInterface::class, $entryItem);
    }

    /**
     * Test non-existent nested item
     */
    public function testNonExistentNestedItems()
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionCode('1492418999');

        $feedItem = $this->getFeedItem();

        $this->assertEquals('John Doe', $feedItem->hEntry()->author->name);
        $feedItem->hEntry(2);
    }

    /**
     * Test the item list export
     */
    public function testItemListExport()
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionCode('1492030227');

        $feedItem = $this->getFeedItem();
        $itemList = new ItemList([$feedItem]);
        $this->assertInstanceOf(ItemList::class, $itemList);

        $export = $itemList->toObject();
        $this->assertInstanceOf(\stdClass::class, $export);
        $this->assertTrue(isset($export->items));
        $this->assertTrue(is_array($export->items));
        $this->assertEquals($feedItem->toObject(), current($export->items));

        $itemList->getFirstItem('invalid');
    }

    /**
     * Test the item list immutability
     */
    public function testItemListImmutabilitySet()
    {
        $this->expectException('Jkphl\Micrometa\Ports\Exceptions\RuntimeException');
        $this->expectExceptionCode('1495988721');
        $feedItem = $this->getFeedItem();
        $itemList = new ItemList([$feedItem]);
        $this->assertInstanceOf(ItemList::class, $itemList);
        $this->assertEquals($feedItem, $itemList[0]);
        $itemList[1] = $feedItem;
    }

    /**
     * Test the item list immutability
     */
    public function testItemListImmutabilityUnset()
    {
        $this->expectException('Jkphl\Micrometa\Ports\Exceptions\RuntimeException');
        $this->expectExceptionCode('1495988721');
        $feedItem = $this->getFeedItem();
        $itemList = new ItemList([$feedItem]);
        $this->assertInstanceOf(ItemList::class, $itemList);
        $this->assertEquals($feedItem, $itemList[0]);
        unset($itemList[0]);
    }
}
