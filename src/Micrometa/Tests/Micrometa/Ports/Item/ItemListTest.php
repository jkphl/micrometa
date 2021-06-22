<?php

namespace Micrometa\Ports\Item;

use Jkphl\Micrometa\Infrastructure\Factory\MicroformatsFactory;
use Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException;
use Jkphl\Micrometa\Ports\Item\ItemInterface;
use Jkphl\Micrometa\Ports\Item\ItemList;
use Jkphl\Micrometa\Tests\MicroformatsFeedTrait;
use PHPUnit\Framework\TestCase;

class ItemListTest extends TestCase
{
    /**
     * Use the Microformats feed method
     */
    use MicroformatsFeedTrait;

    private $feedItemList;

    protected function setUp(): void
    {
        $this->feedItemList = new ItemList([$this->getFeedItem(), $this->getFeedItem()]);
    }

    public function testNestedItemsCounts()
    {
        // Test the number of nested items
        self::assertCount(2, $this->feedItemList);
        self::assertCount(2, $this->feedItemList->getItems());
    }

    public function testNestedItemsIteration()
    {
        foreach ($this->feedItemList->getItems() as $itemIndex => $entryItem) {
            self::assertInstanceOf(ItemInterface::class, $entryItem);
            self::assertIsInt($itemIndex);
        }
    }

    public function testNestedItemsRetrievalViaArrayAccess()
    {
        $entryItems = $this->feedItemList->getFirstItem()->getItems('h-entry');
        $entryItem = $entryItems[1];

        self::assertInstanceOf(ItemInterface::class, $entryItem);
    }

    public function testFirstNestedItemRetrieval()
    {
        self::assertInstanceOf(ItemInterface::class, $this->feedItemList[0]->getFirstItem('h-entry'));
        self::assertInstanceOf(
            ItemInterface::class,
            $this->feedItemList[0]->getFirstItem('h-entry', MicroformatsFactory::MF2_PROFILE_URI)
        );
    }

    public function testExistingFirstNestedItem()
    {
        self::assertEquals('John Doe', $this->feedItemList[0]->hEntry()->author->name);
        self::assertEquals('John Doe', $this->feedItemList->getFirstItem()->hEntry()->author->name);
    }

    public function testNonExistingSecondNestedItem()
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionCode('1492418999');

        $this->feedItemList->hEntry(2);
    }
}
