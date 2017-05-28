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

namespace Jkphl\Micrometa\Tests\Ports;

use Jkphl\Micrometa\Application\Factory\PropertyListFactory;
use Jkphl\Micrometa\Application\Item\Item as ApplicationItem;
use Jkphl\Micrometa\Application\Value\StringValue;
use Jkphl\Micrometa\Domain\Item\Iri;
use Jkphl\Micrometa\Infrastructure\Factory\ItemFactory;
use Jkphl\Micrometa\Infrastructure\Parser\LinkType;
use Jkphl\Micrometa\Ports\Item\Item;
use Jkphl\Micrometa\Ports\Item\ItemInterface;
use Jkphl\Micrometa\Ports\Item\ItemObjectModel;
use Jkphl\Micrometa\Tests\AbstractTestBase;
use Jkphl\Micrometa\Tests\MicroformatsFeedTrait;

/**
 * Item object model tests
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Tests
 */
class ItemObjectModelTest extends AbstractTestBase
{
    /**
     * Use the Microformats feed method
     */
    use MicroformatsFeedTrait;

    /**
     * Test the item object model
     *
     * @expectedException \Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException
     * @expectedExceptionCode 1489268571
     */
    public function testItemObjectModel()
    {
        $itemObjectModel = new ItemObjectModel($this->getItems());
        $this->assertInstanceOf(ItemObjectModel::class, $itemObjectModel);

        // Test all LinkType items
        $links = $itemObjectModel->link();
        $this->assertEquals(2, count($links));
        foreach ($links as $link) {
            $this->assertInstanceOf(ItemInterface::class, $link);
        }

        // Test the second LinkType item
        $secondLink = $itemObjectModel->link(null, 1);
        $this->assertInstanceOf(Item::class, $secondLink);
        $this->assertEquals(
            [new Iri(LinkType::HTML_PROFILE_URI, 'alternate')],
            $secondLink->getType()
        );

        $this->runStylesheetTests($itemObjectModel);
    }

    /**
     * Test the stylesheet items
     *
     * @param ItemObjectModel $itemObjectModel Item object model
     */
    protected function runStylesheetTests(ItemObjectModel $itemObjectModel) {
        // Test all stylesheet LinkType items
        $stylesheetLinks = $itemObjectModel->link('stylesheet');
        $this->assertTrue(is_array($stylesheetLinks));
        $this->assertEquals(1, count($stylesheetLinks));
        $this->assertInstanceOf(Item::class, $stylesheetLinks[0]);
        $this->assertEquals(
            [new Iri(LinkType::HTML_PROFILE_URI, 'stylesheet')],
            $stylesheetLinks[0]->getType()
        );

        // Test the first stylesheet LinkType item
        $firstStylesheetLink = $itemObjectModel->link('stylesheet', 0);
        $this->assertInstanceOf(Item::class, $firstStylesheetLink);
        $this->assertEquals(
            [new Iri(LinkType::HTML_PROFILE_URI, 'stylesheet')],
            $firstStylesheetLink->getType()
        );

        // Test an invalid item index
        $itemObjectModel->link('stylesheet', 1);
    }

    /**
     * Return a list of 3 items
     *
     * @return Item[] Items
     */
    protected function getItems()
    {
        $items = ItemFactory::createFromApplicationItems(
            [
                // Stylesheet link item
                new ApplicationItem(
                    LinkType::FORMAT,
                    new PropertyListFactory(),
                    (object)['profile' => LinkType::HTML_PROFILE_URI, 'name' => 'stylesheet'],
                    [
                        (object)[
                            'profile' => LinkType::HTML_PROFILE_URI,
                            'name' => 'type',
                            'values' => [
                                new StringValue('text/css')
                            ]
                        ],
                        (object)[
                            'profile' => LinkType::HTML_PROFILE_URI,
                            'name' => 'href',
                            'values' => [
                                new StringValue('style.css')
                            ]
                        ]
                    ],
                    [],
                    'main-stylesheet'
                ),

                // Atom feed link item
                new ApplicationItem(
                    LinkType::FORMAT,
                    new PropertyListFactory(),
                    (object)['profile' => LinkType::HTML_PROFILE_URI, 'name' => 'alternate'],
                    [
                        (object)[
                            'profile' => LinkType::HTML_PROFILE_URI,
                            'name' => 'type',
                            'values' => [
                                new StringValue('application/atom+xml')
                            ]
                        ],
                        (object)[
                            'profile' => LinkType::HTML_PROFILE_URI,
                            'name' => 'href',
                            'values' => [
                                new StringValue('http://example.com/blog.atom')
                            ]
                        ],
                        (object)[
                            'profile' => LinkType::HTML_PROFILE_URI,
                            'name' => 'title',
                            'values' => [
                                new StringValue('Atom feed')
                            ]
                        ],
                        (object)[
                            'profile' => 'http://example.com/test-ns',
                            'name' => 'prop',
                            'values' => [
                                new StringValue('arbitrary')
                            ]
                        ]
                    ],
                    [],
                    'main-stylesheet'
                ),
            ]
        );

        $items[] = $this->getFeedItem();

        return $items;
    }
}
