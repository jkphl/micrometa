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

use Jkphl\Micrometa\Infrastructure\Parser\LinkType;
use Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException;

/**
 * Item object model
 *
 * @package    Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Ports
 */
class ItemObjectModel extends ItemList implements ItemObjectModelInterface
{
    /**
     * DOM document
     *
     * @var \DOMDocument
     */
    protected $dom;
    /**
     * LinkType item cache
     *
     * @var ItemListInterface
     */
    protected $links = null;

    /**
     * Constructor
     *
     * @param \DOMDocument $dom DOM document
     * @param array $items      Items
     */
    public function __construct(\DOMDocument $dom, $items = [])
    {
        $this->dom = $dom;
        parent::__construct($items);
    }

    /**
     * Return all link declarations of a particular type
     *
     * @param string|null $type Link type
     * @param int|null $index   Optional: particular index
     *
     * @return ItemInterface|ItemListInterface Single LinkType item or list of LinkType items
     * @api
     */
    public function link($type = null, $index = null)
    {
        // One-time caching of link elements
        if ($this->links === null) {
            $this->cacheLinkTypeItems();
        }

        // Find the matching LinkType items
        $links = ($type === null) ? $this->links->getItems() : $this->links->getItems($type);

        // Return link item(s)
        return ($index === null) ? new ItemList($links) : $this->getLinkIndex($links, $type, $index);
    }

    /**
     * One-time caching of LinkType items
     */
    protected function cacheLinkTypeItems()
    {
        $links = [];
        foreach ($this->items as $item) {
            if ($item->getFormat() == LinkType::FORMAT) {
                $links[] = $item;
            }
        }
        $this->links = new ItemList($links);
    }

    /**
     * Return a particular link item by index
     *
     * @param ItemInterface[] $links Link items
     * @param string|null $type      Link type
     * @param int $index             Link item index
     *
     * @return ItemInterface Link item
     * @throws OutOfBoundsException If the link index is out of bounds
     */
    protected function getLinkIndex(array $links, $type, $index)
    {
        // If the link index is out of bounds
        if (!is_int($index) || !array_key_exists($index, $links)) {
            throw new OutOfBoundsException(
                sprintf(OutOfBoundsException::INVALID_LINK_TYPE_INDEX_STR, $index, $type),
                OutOfBoundsException::INVALID_LINK_TYPE_INDEX
            );
        }

        return $links[$index];
    }

    /**
     * Return the original DOM document
     *
     * @return \DOMDocument DOM document
     */
    public function getDom()
    {
        return $this->dom;
    }
}
