<?php

/**
 * micrometa
 *
 * @category Jkphl
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Ports
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

use Jkphl\Micrometa\Application\Item\ItemInterface as ApplicationItemInterface;
use Jkphl\Micrometa\Domain\Exceptions\OutOfBoundsException;
use Jkphl\Micrometa\Ports\Exceptions\InvalidArgumentException;

/**
 * Micro information item
 *
 * @package Jkphl\Micrometa
 * @subpackage Jkphl\Micrometa\Ports
 */
class Item implements ItemInterface
{
    /**
     * Application item
     *
     * @var ApplicationItemInterface
     */
    protected $item;

    /**
     * Item constructor
     *
     * @param ApplicationItemInterface $item Application item
     */
    public function __construct(ApplicationItemInterface $item)
    {
        $this->item = $item;
    }

    /**
     * Return whether the item is of a particular type (or contained in a list of types)
     *
     * @param array ...$types Item types
     * @return boolean Item is contained in the list of types
     * @throws InvalidArgumentException If no item type was given
     */
    public function isOfType(...$types)
    {
        // If no item type was given
        if (!count($types)) {
            throw new InvalidArgumentException(
                sprintf(InvalidArgumentException::MISSING_ITEM_TYPE_STR, __CLASS__.'::'.__METHOD__),
                InvalidArgumentException::MISSING_ITEM_TYPE
            );
        }

        return count(array_intersect($types, $this->item->getType())) > 0;
    }

    /**
     * Get the values or first value of an item property
     *
     * Prepend the property name with an "s" to retrieve the list of all available property values.
     *
     * @param string $name Item property name
     * @return string Values or first value of an item property
     */
    public function __get($name)
    {
        // TODO: Implement __get() method.
    }

    /**
     * Get all values or the first value for a particular property (in a property list)
     *
     * Append the property names with an "s" to retrieve the list of all available property values.
     *
     * @param array ...$names Property names
     * @return string|string[] Property value(s)
     * @throws InvalidArgumentException If no property name was given
     */
    public function firstOf(...$names)
    {
        // If no property name was given
        if (!count($names)) {
            throw new InvalidArgumentException(
                sprintf(InvalidArgumentException::MISSING_PROPERTY_NAME_STR, __CLASS__.'::'.__METHOD__),
                InvalidArgumentException::MISSING_PROPERTY_NAME
            );
        }

        return $this->firstOfPropertyNames($names);
    }

    /**
     * Return the first non-NULL value of a property list
     *
     * @param array $names Property names
     * @return string|array|null First existing property name
     */
    protected function firstOfPropertyNames(array $names)
    {
        // Run through all property names
        foreach ($names as $name) {
            $value = $this->getPropertyValueOrValueList($name);
            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    protected function getPropertyValueOrValueList($name)
    {
        return null;
    }

    /**
     * Return the values of a particular property
     *
     * @param string $name Property name
     * @return array|null Property values
     */
    protected function getPropertyValues($name)
    {
        try {
            return $this->item->getProperty($name);
        } catch (OutOfBoundsException $e) {
            return null;
        }
    }
}
