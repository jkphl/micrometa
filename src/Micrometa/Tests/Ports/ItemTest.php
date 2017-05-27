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

use Jkphl\Micrometa\Application\Item\PropertyList;
use Jkphl\Micrometa\Application\Value\StringValue;
use Jkphl\Micrometa\Infrastructure\Factory\MicroformatsFactory;
use Jkphl\Micrometa\Ports\Item\Item;
use Jkphl\Micrometa\Ports\Item\ItemInterface;
use Jkphl\Micrometa\Ports\Item\ItemList;
use Jkphl\Micrometa\Tests\AbstractTestBase;
use Jkphl\Micrometa\Tests\MicroformatsFeedTrait;

/**
 * Parser factory tests
 *
 * @package Jkphl\Micrometa
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
     *
     * @expectedException \Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException
     * @expectedExceptionCode 1491672553
     */
    public function testItemProperties()
    {
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
     *
     * @expectedException \Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException
     * @expectedExceptionCode 1488315604
     */
    public function testItemUnprofiledProperty()
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
    public function testItemProfiledProperty()
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
    public function testItemAliasedProperty()
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
    public function testItemPropertyStack()
    {
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
     *
     * @expectedException \Jkphl\Micrometa\Ports\Exceptions\InvalidArgumentException
     * @expectedExceptionCode 1492418709
     */
    public function testItemNestedItems()
    {
        $feedItem = $this->getFeedItem();
        $this->assertInstanceOf(Item::class, $feedItem);

        // Test the number of nested items
        $this->assertEquals(2, count($feedItem));
        $this->assertEquals(2, count($feedItem->getItems()));
        foreach ($feedItem as $itemIndex => $entryItem) {
            $this->assertInstanceOf(ItemInterface::class, $entryItem);
            $this->assertTrue(is_int($itemIndex));
        }
        $this->assertInstanceOf(ItemInterface::class, $feedItem->getFirstItem('h-entry'));
        $this->assertInstanceOf(
            ItemInterface::class, $feedItem->getFirstItem('h-entry', MicroformatsFactory::MF2_PROFILE_URI)
        );

        // Test the second entry item
        /** @var Item $entryItem */
        $entryItem = $feedItem->getItems('h-entry')[1];
        $this->assertInstanceOf(ItemInterface::class, $entryItem);

        // Test the magic item getter / item type aliases
        /** @noinspection PhpUndefinedMethodInspection */
        $entryItem = $feedItem->hEntry(0);
        $this->assertInstanceOf(ItemInterface::class, $entryItem);
        /** @noinspection PhpUndefinedMethodInspection */
        $feedItem->hEntry(-1);
    }

    /**
     * Test non-existent nested item
     *
     * @expectedException \Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException
     * @expectedExceptionCode 1492418999
     */
    public function testItemNonExistentNestedItems()
    {
        $feedItem = $this->getFeedItem();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals('John Doe', $feedItem->hEntry()->author->name);
        /** @noinspection PhpUndefinedMethodInspection */
        $feedItem->hEntry(2);
    }

    /**
     * Test the item list export
     *
     * @expectedException \Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException
     * @expectedExceptionCode 1492030227
     */
    public function testItemListExport()
    {
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
}
