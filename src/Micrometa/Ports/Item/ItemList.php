<?php

/**
 * micrometa
 *
 * @category Jkphl
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Ports\Item
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

namespace Jkphl\Micrometa\Ports\Item;

/**
 * Item list
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Ports
 */
class ItemList implements ItemListInterface
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
     */
    public function __construct(array $items = [])
    {
        $this->items = array_values($items);
        $this->pointer = 0;
    }

    /**
     * Return the current item
     *
     * @return ItemInterface Item
     */
    public function current()
    {
        return $this->items[$this->pointer];
    }

    /**
     * Move forward to next element
     *
     * @return void
     */
    public function next()
    {
        ++$this->pointer;
    }

    /**
     * Return the position of the current element
     *
     * @return mixed Position of the current element
     */
    public function key()
    {
        return $this->pointer;
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean The current position is valid
     */
    public function valid()
    {
        return isset($this->items[$this->pointer]);
    }

    /**
     * Rewind the item list to the first element
     *
     * @return void
     */
    public function rewind()
    {
        $this->pointer = 0;
    }

    /**
     * Return an object representation of the item list
     *
     * @return \stdClass Micro information items
     * @api
     */
    public function toObject()
    {
        return new \stdClass();
    }

    /**
     * Return a JSON representation of the item list
     *
     * @return string Micro information items
     * @api
     */
    public function toJson()
    {
        return json_encode(new \stdClass());
    }

    /**
     * Return the first item, optionally of particular types
     *
     * @param array ...$types Item types
     * @return ItemInterface Item
     * @api
     */
    public function item(...$types)
    {
        return $this->items(...$types)[0];
    }

    /**
     * Filter the items by item type(s)
     *
     * @param array ...$types Item types
     * @return ItemListInterface Items matching the requested types
     * @api
     */
    public function filter(...$types)
    {
        return new static($this->items(...$types));
    }

    /**
     * Return all items as an array, optionally filtered by item type(s)
     *
     * @param array ...$types Item types
     * @return ItemInterface[] Items matching the requested types
     * @api
     */
    public function items(...$types)
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
}
