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
use Jkphl\Micrometa\Domain\Exceptions\OutOfBoundsException as DomainOutOfBoundsException;
use Jkphl\Micrometa\Infrastructure\Factory\ProfiledNamesFactory;
use Jkphl\Micrometa\Infrastructure\Parser\ProfiledNamesList;
use Jkphl\Micrometa\Ports\Exceptions\InvalidArgumentException;
use Jkphl\Micrometa\Ports\Exceptions\OutOfBoundsException;

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
     * The item type(s) can be specified in a variety of ways, @see ProfiledNamesFactory::createFromArguments().
     *
     * @param string $name Name
     * @param string|null $profile Profile
     * @return boolean Item type is contained in the list of types
     */
    public function isOfType($name, $profile = null)
    {
        /** @var ProfiledNamesList $types */
        $types = ProfiledNamesFactory::createFromArguments(func_get_args());

        // Run through all item types
        /** @var \stdClass $itemType */
        foreach ($this->item->getType() as $itemType) {
            // Run through all query types
            /** @var \stdClass $queryType */
            foreach ($types as $queryType) {
                if (($queryType->name == $itemType->name) &&
                    (($queryType->profile === null) ? true : ($queryType->profile == $itemType->profile))
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get a single property (value)
     *
     * @param string $name Property name
     * @param string $profile Property profile
     * @param int $index Property value index
     * @return array|string|ItemInterface Property value(s)
     * @throws OutOfBoundsException If the property name is unknown
     * @throws OutOfBoundsException If the property value index is out of bounds
     */
    public function getProperty($name, $profile = null, $index = null)
    {
        try {
            $propertyValues = $this->item->getProperty($name, $profile);
        } catch (DomainOutOfBoundsException $e) {
            throw new OutOfBoundsException($e->getMessage(), $e->getCode());
        }

        // If all property values should be returned
        if ($index === null) {
            return $propertyValues;
        }

        // If the property value index is out of bounds
        if (!isset($propertyValues[$index])) {
            throw new OutOfBoundsException(
                sprintf(OutOfBoundsException::INVALID_PROPERTY_VALUE_INDEX_STR, $index),
                OutOfBoundsException::INVALID_PROPERTY_VALUE_INDEX
            );
        }

        return $propertyValues[$index];
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
        return '';
    }

    /**
     * Get all values or the first value for a particular property (in a property list)
     *
     * The property name(s) can be specified in a variety of ways, @see ProfiledNamesFactory::createFromArguments().
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

        return '';
    }
}
