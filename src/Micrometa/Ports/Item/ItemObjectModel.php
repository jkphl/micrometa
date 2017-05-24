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

use Jkphl\Micrometa\Infrastructure\Parser\LinkRel;
use Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException;

/**
 * Item object model
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Ports
 */
class ItemObjectModel extends ItemList implements ItemObjectModelInterface
{
    /**
     * LinkRel item cache
     *
     * @var ItemListInterface
     */
    protected $rels = null;

    /**
     * Return all rel declarations of a particular type
     *
     * @param string|null $type Rel type
     * @param int|null $index Optional: particular index
     * @return ItemInterface|ItemInterface[] Single LinkRel item or list of LinkRel items
     * @throws OutOfBoundsException If the rel index is out of bounds
     * @api
     */
    public function rel($type = null, $index = null)
    {
        // One-time caching of rel elements
        if ($this->rels === null) {
            $this->cacheLinkRelItems();
        }

        // Find the matching LinkRel items
        $rels = ($type === null) ? $this->rels->getItems() : $this->rels->getItems($type);

        // If all LinkRels should be returned
        if ($index === null) {
            return $rels;
        }

        // If the rel index is out of bounds
        if (!is_int($index) || !array_key_exists($index, $rels)) {
            throw new OutOfBoundsException(
                sprintf(OutOfBoundsException::INVALID_REL_INDEX_STR, $index, $type),
                OutOfBoundsException::INVALID_REL_INDEX
            );
        }

        return $rels[$index];
    }

    /**
     * One-time caching of LinkRel items
     */
    protected function cacheLinkRelItems()
    {
        $rels = [];
        foreach ($this->items as $item) {
            if ($item->getFormat() == LinkRel::FORMAT) {
                $rels[] = $item;
            }
        }
        $this->rels = new ItemList($rels);
    }
}
