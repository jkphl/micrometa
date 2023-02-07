<?php

/**
 * micrometa
 *
 * @category   Jkphl
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Ports\Item
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

namespace Jkphl\Micrometa\Ports\Item;

use Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException;
use Jkphl\Micrometa\Ports\Exceptions\RuntimeException;

/**
 * Item list
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Ports
 */
class ItemList extends Collection implements ItemListInterface
{
    /**
     * Items
     *
     * @var ItemInterface[]
     */
    protected $items;

    /**
     * Internal pointer
     *
     * @var int
     */
    protected $pointer;

    /**
     * ItemList constructor
     *
     * @param ItemInterface[] $items Items
     *
     * @api
     */
    public function __construct(array $items = [])
    {
        $this->items   = array_values($items);
        $this->pointer = 0;
    }

    /**
     * Return the current item
     *
     * @return ItemInterface Item
     * @api
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->items[$this->pointer];
    }

    /**
     * Move forward to next element
     *
     * @return void
     * @api
     */
    public function next(): void
    {
        ++$this->pointer;
    }

    /**
     * Return the position of the current element
     *
     * @return int Position of the current element
     * @api
     */
    public function key(): int
    {
        return $this->pointer;
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean The current position is valid
     * @api
     */
    public function valid(): bool
    {
        return isset($this->items[$this->pointer]);
    }

    /**
     * Rewind the item list to the first element
     *
     * @return void
     * @api
     */
    public function rewind(): void
    {
        $this->pointer = 0;
    }

    /**
     * Test if an offset exists
     *
     * @param int $offset Offset
     *
     * @return boolean Offset exists
     * @api
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * Return the item at a particular offset
     *
     * @param int $offset Offset
     *
     * @return ItemInterface Item
     * @api
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * Set an item at a particular offset
     *
     * @param int $offset          Offset
     * @param ItemInterface $value Item
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @api
     */
    public function offsetSet($offset, $value): void
    {
        throw new RuntimeException(RuntimeException::IMMUTABLE_ITEM_LIST_STR, RuntimeException::IMMUTABLE_ITEM_LIST);
    }

    /**
     * Delete an item at a particular offset
     *
     * @param int $offset Offset
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function offsetUnset($offset): void
    {
        throw new RuntimeException(RuntimeException::IMMUTABLE_ITEM_LIST_STR, RuntimeException::IMMUTABLE_ITEM_LIST);
    }

    /**
     * Return an object representation of the item list
     *
     * @return \stdClass Micro information items
     */
    public function toObject()
    {
        return (object)[
            'items' => array_map(
                function (ItemInterface $item) {
                    return $item->toObject();
                },
                $this->items
            )
        ];
    }

    /**
     * Return the first item, optionally of particular types
     *
     * @param array ...$types Item types
     *
     * @return ItemInterface Item
     * @throws OutOfBoundsException If there are no matching items
     * @api
     */
    public function getFirstItem(...$types)
    {
        $items = $this->getItems(...$types);

        // If there are no matching items
        if (!count($items)) {
            throw new OutOfBoundsException(
                OutOfBoundsException::NO_MATCHING_ITEMS_STR,
                OutOfBoundsException::NO_MATCHING_ITEMS
            );
        }

        return $items[0];
    }

    /**
     * Return all items as an array, optionally filtered by item type(s)
     *
     * @param array ...$types Item types
     *
     * @return ItemInterface[] Items matching the requested types
     * @api
     */
    public function getItems(...$types)
    {
        // If particular item types should be filtered
        if (count($types)) {
            return array_filter(
                $this->items,
                function (ItemInterface $item) use ($types) {
                    return $item->isOfType(...$types);
                }
            );
        }

        return $this->items;
    }

    /**
     * Return the number of items in this list
     *
     * @return int Number of items
     * @api
     */
    public function count(): int
    {
        return count($this->items);
    }
}
