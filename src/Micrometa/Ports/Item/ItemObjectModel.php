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

use Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException;
use Jkphl\Micrometa\Ports\Rel\AlternateInterface;
use Jkphl\Micrometa\Ports\Rel\RelInterface;

/**
 * Item object model
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Ports
 */
class ItemObjectModel extends ItemList implements ItemObjectModelInterface
{
    /**
     * Rel declarations
     *
     * @var RelInterface[]
     */
    protected $rels;

    /**
     * Alternate resources
     *
     * @var AlternateInterface[]
     */
    protected $alternates;

    /**
     * ItemList constructor
     *
     * @param ItemInterface[] $items Items
     * @param RelInterface[] $rels Rel declarations
     * @param AlternateInterface[] $alternates Alternate resources
     */
    public function __construct(array $items = [], array $rels = [], array $alternates = [])
    {
        parent::__construct($items);
        $this->rels = $rels;
        $this->alternates = $alternates;
    }

    /**
     * Return all rel declarations of a particular type
     *
     * @param string $type Rel type
     * @param int|null $index Optional: particular index
     * @return RelInterface|RelInterface[] Single rel=* declaration or list of particular rel declarations
     * @throws OutOfBoundsException If the rel type is out of bounds
     * @throws OutOfBoundsException If the rel index is out of bounds
     * @api
     */
    public function rel($type, $index = null)
    {
        // If the rel type is out of bounds
        if (!array_key_exists($type, $this->rels)) {
            throw new OutOfBoundsException(
                sprintf(OutOfBoundsException::INVALID_REL_TYPE_STR, $type),
                OutOfBoundsException::INVALID_REL_TYPE
            );
        }

        // If all rel values should be returned
        if ($index === null) {
            return $this->rels[$type];
        }

        // If the rel index is out of bounds
        if (!is_int($index) || !array_key_exists($index, $this->rels[$type])) {
            throw new OutOfBoundsException(
                sprintf(OutOfBoundsException::INVALID_REL_INDEX_STR, $index, $type),
                OutOfBoundsException::INVALID_REL_INDEX
            );
        }

        return $this->rels[$type][$index];
    }

    /**
     * Return all rel=* declaration groups
     *
     * @return RelInterface[] Rel=* declaration groups
     * @api
     */
    public function rels()
    {
        return $this->rels;
    }

    /**
     * Return all alternate resources
     *
     * @return AlternateInterface[] Alternate resources
     * @api
     */
    public function alternates()
    {
        return $this->alternates;
    }
}
